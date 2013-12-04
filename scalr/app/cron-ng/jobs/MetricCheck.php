<?php

use \Scalr\Server\Alerts;

class Scalr_Cronjob_MetricCheck extends Scalr_System_Cronjob_MultiProcess_DefaultWorker
{

    static function getConfig ()
    {
        return array(
            "description"      => "Metrics check",
            "processPool"      => array(
                "daemonize"         => false,
                "workerMemoryLimit" => 40000,
                "size"              => 20,
                "startupTimeout"    => 10000 // 10 seconds
            ),
            "waitPrevComplete" => true,
            "fileName"         => __FILE__,
            "memoryLimit"      => 500000
        );
    }

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \ADODB_mysqli
     */
    private $db;

    public function __construct()
    {
        $this->logger = Logger::getLogger(__CLASS__);
        $this->timeLogger = Logger::getLogger('time');
        $this->db = $this->getContainer()->adodb;
    }

    /**
     * {@inheritdoc}
     * @see Scalr_System_Cronjob_MultiProcess_DefaultWorker::startForking()
     */
    function startForking($workQueue)
    {
        // Reopen DB connection after daemonizing
        $this->db = $this->getContainer()->adodb;
    }

    /**
     * {@inheritdoc}
     * @see Scalr_System_Cronjob_MultiProcess_DefaultWorker::startChild()
     */
    function startChild()
    {
        // Reopen DB connection in child
        $this->db = $this->getContainer()->adodb;
        // Reconfigure observers;
        Scalr::ReconfigureObservers();
    }

    /**
     * {@inheritdoc}
     * @see Scalr_System_Cronjob_MultiProcess_DefaultWorker::enqueueWork()
     */
    function enqueueWork ($workQueue)
    {
        $rows = $this->db->GetAll("
            SELECT id FROM farms
            WHERE status=? AND clientid
            IN (SELECT id FROM clients WHERE status = 'Active')
        ",
            array(FARM_STATUS::RUNNING)
        );

        foreach ($rows as $row) {
            $workQueue->put($row["id"]);
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr_System_Cronjob_MultiProcess_DefaultWorker::handleWork()
     */
    function handleWork ($farmId)
    {
        try {
            $dbFarm = DBFarm::LoadByID($farmId);
            if ($dbFarm->Status != FARM_STATUS::RUNNING)
                return;
        } catch (Exception $e) {
            return;
        }

        foreach ($dbFarm->GetFarmRoles() as $dbFarmRole) {
            $instancesHealth = array();
            if ($dbFarmRole->Platform == SERVER_PLATFORMS::EC2) {
                $env = Scalr_Environment::init()->loadById($dbFarm->EnvID);
                $aws = $env->aws($dbFarmRole);

                $statusList = $aws->ec2->instance->describeStatus();
                foreach ($statusList as $sd) {
                    /* @var $sd \Scalr\Service\Aws\Ec2\DataType\InstanceStatusData */
                    $instancesHealth[$sd->instanceId] = $sd;
                }
                unset($statusList);
                //Releases memory
                $env->getContainer()->release('aws');
                unset($aws);
            }

            $servers = $this->db->Execute("
                SELECT server_id FROM servers
                WHERE farm_roleid = ? AND status = ?
            ", array(
                $dbFarmRole->ID, SERVER_STATUS::RUNNING
            ));
            while ($server = $servers->FetchRow()) {

                $dbServer = DBServer::LoadByID($server['server_id']);

                // Do not support ami-scripts
                if (!$dbServer->IsSupported("0.5"))
                    continue;

                // Do not support windows
                if ($dbServer->IsSupported("0.8") && !$dbServer->IsSupported("0.9"))
                    continue;

                $subStatus = $dbServer->GetProperty(SERVER_PROPERTIES::SUB_STATUS);
                if ($subStatus != '') {
                    if ($subStatus == 'stopped') {
                        //Need to solve ALL failed metrics
                        $serverAlerts = new Alerts($dbServer);
                        if ($serverAlerts->getActiveAlertsCount()) {
                            $serverAlerts->solveAlert();
                        }
                    }

                    continue;
                }

                if ($dbServer->GetProperty(SERVER_PROPERTIES::REBOOTING))
                    continue;

                $serverAlerts = new Alerts($dbServer);

                //Check AWS healthchecks
                if ($dbServer->platform == SERVER_PLATFORMS::EC2) {
                    try {
                        /* @var $statusInfo \Scalr\Service\Aws\Ec2\DataType\InstanceStatusData */
                        $statusInfo = isset($instancesHealth[$dbServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID)]) ?
                            $instancesHealth[$dbServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID)] : null;
                        if ($statusInfo) {
                            $check = Alerts::METRIC_AWS_SYSTEM_STATUS;
                            $hasActiveAlert = $serverAlerts->hasActiveAlert($check);
                            if ($statusInfo->systemStatus->status == 'ok' && $hasActiveAlert) {
                                Scalr::FireEvent(
                                    $dbServer->farmId,
                                    new CheckRecoveredEvent($dbServer, $check)
                                );
                            } elseif ($statusInfo->systemStatus->status != 'ok' && !$hasActiveAlert) {
                                $txtDetails = "";
                                $details = $statusInfo->systemStatus->details;
                                if ($details) {
                                    foreach ($details as $d) {
                                        /* @var $d \Scalr\Service\Aws\Ec2\DataType\InstanceStatusDetailsSetData */
                                        $txtDetails .= " {$d->name} is {$d->status},";
                                    }
                                    $txtDetails = trim($txtDetails, " ,");
                                    unset($details);
                                }

                                Scalr::FireEvent(
                                    $dbServer->farmId,
                                    new CheckFailedEvent(
                                        $dbServer, $check, "{$statusInfo->systemStatus->status}: {$txtDetails}"
                                    )
                                );
                            }

                            $check = Alerts::METRIC_AWS_INSTANCE_STATUS;
                            $hasActiveAlert = $serverAlerts->hasActiveAlert($check);
                            if ($statusInfo->instanceStatus->status == 'ok' && $hasActiveAlert) {
                                Scalr::FireEvent($dbServer->farmId,
                                    new CheckRecoveredEvent($dbServer, $check)
                                );
                            } else if ($statusInfo->instanceStatus->status != 'ok' && !$hasActiveAlert) {
                                $txtDetails = "";
                                $details = $statusInfo->instanceStatus->details;
                                if ($details) {
                                    foreach ($details as $d) {
                                        /* @var $d \Scalr\Service\Aws\Ec2\DataType\InstanceStatusDetailsSetData */
                                        $txtDetails .= " {$d->name} is {$d->status},";
                                    }
                                    $txtDetails = trim($txtDetails, " ,");
                                    unset($details);
                                }

                                Scalr::FireEvent(
                                    $dbServer->farmId,
                                    new CheckFailedEvent(
                                        $dbServer, $check, "{$statusInfo->instanceStatus->status}: {$txtDetails}"
                                    )
                                );
                            }
                            unset($statusInfo);
                        }
                    } catch (Exception $e) {
                    }
                }


                //Not supported by VPC yet.
                if ($dbFarm->GetSetting(DBFarm::SETTING_EC2_VPC_ID))
                    continue;

                //Check scalr-upd-client status
                $check = Alerts::METRIC_SCALARIZR_UPD_CLIENT_CONNECTIVITY;
                $port = $dbServer->GetProperty(SERVER_PROPERTIES::SZR_UPDC_PORT);
                if (!$port) {
                    $port = 8008;
                }
                $result = $this->checkPort($dbServer->remoteIp, $port);
                $hasActiveAlert = $serverAlerts->hasActiveAlert($check);
                if (!$result['status'] && !$hasActiveAlert) {
                    Scalr::FireEvent(
                        $dbServer->farmId,
                        new CheckFailedEvent($dbServer, $check, $result['error'])
                    );
                } elseif ($result['status'] && $hasActiveAlert) {
                    Scalr::FireEvent(
                        $dbServer->farmId,
                        new CheckRecoveredEvent($dbServer, $check)
                    );
                } elseif ($hasActiveAlert) {
                    $serverAlerts->updateLastCheckTime($check);
                }

                //Check scalarizr connectivity status
                $check = Alerts::METRIC_SCALARIZR_CONNECTIVITY;
                $port = $dbServer->GetProperty(SERVER_PROPERTIES::SZR_CTRL_PORT);
                if (!$port) {
                    $port = 8013;
                }
                $result = $this->checkPort($dbServer->remoteIp, $port);
                $hasActiveAlert = $serverAlerts->hasActiveAlert($check);
                if (!$result['status'] && !$hasActiveAlert) {
                    Scalr::FireEvent(
                        $dbServer->farmId,
                        new CheckFailedEvent($dbServer, $check, $result['error'])
                    );
                } elseif ($result['status'] && $hasActiveAlert) {
                    Scalr::FireEvent(
                        $dbServer->farmId,
                        new CheckRecoveredEvent($dbServer, $check)
                    );
                } elseif ($hasActiveAlert) {
                    $serverAlerts->updateLastCheckTime($check);
                }
            }
        }
        exit();
    }

    private function checkPort($host, $port)
    {
        $ret = null;
        try {
            $chk = @fsockopen($host, $port, $errno, $errstr, 5);
            if (!$chk) {
                $ret = array(
                    'status' => false,
                    'error'  => "{$errstr} ({$errno})",
                );
            } else {
                @fclose($chk);
                $ret = array(
                    'status' => true,
                );
            }
        } catch (Exception $e) {
            $ret = array(
                'status' => false,
                'error'  => $e->getMessage(),
            );
        }
        return $ret;
    }
}
