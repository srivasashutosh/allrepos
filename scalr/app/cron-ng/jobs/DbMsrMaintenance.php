<?php

class Scalr_Cronjob_DbMsrMaintenance extends Scalr_System_Cronjob_MultiProcess_DefaultWorker
{
    static function getConfig () {
        return array(
            "description" => "Maintenace procedures for MSR databases",
            "processPool" => array(
                "daemonize" => false,
                "workerMemoryLimit" => 40000,
                "size" => 12,
                "startupTimeout" => 10000 // 10 seconds
            ),
            "waitPrevComplete" => true,
            "fileName" => __FILE__,
            "memoryLimit" => 500000
        );
    }

    private $logger;

    /**
     * @var ADODB_mysqli
     */
    private $db;

    public function __construct()
    {
        $this->logger = Logger::getLogger(__CLASS__);

        $this->timeLogger = Logger::getLogger('time');

        $this->db = $this->getContainer()->adodb;
    }

    function startForking($workQueue)
    {
        // Reopen DB connection after daemonizing
        $this->db = $this->getContainer()->adodb;
    }

    function startChild()
    {
        // Reopen DB connection in child
        $this->db = $this->getContainer()->adodb;
        // Reconfigure observers;
        Scalr::ReconfigureObservers();
    }

    function enqueueWork($workQueue) {

        $rows = $this->db->GetAll("SELECT id FROM farm_roles WHERE role_id IN (SELECT role_id FROM role_behaviors WHERE behavior IN (?,?,?,?))",
            array(ROLE_BEHAVIORS::POSTGRESQL, ROLE_BEHAVIORS::REDIS, ROLE_BEHAVIORS::MYSQL2, ROLE_BEHAVIORS::PERCONA)
        );
        $this->logger->info("Found ".count($rows)." DbMsr farm roles...");

        foreach ($rows as $row) {
            $workQueue->put($row["id"]);
        }
    }

    private function performDbMsrAction($action, DBFarmRole $dbFarmRole, $tz)
    {
        if ($dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_ENABLED")) && $dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_EVERY")) != 0) {
            if ($dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_IS_RUNNING")) == 1) {
                // Wait for timeout time * 2 (Example: NIVs problem with big mysql snapshots)
                // We must wait for running bundle process.
                $timeout = $dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_EVERY"))*(3600*2);
                $lastTs = $dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_RUNNING_TS"));
                if ($lastTs+$timeout < time())
                    $timeouted = true;

                if ($timeouted)
                    $dbFarmRole->SetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_IS_RUNNING"), 0);
            }
            else {
                /*
                 * Check bundle window
                 */
                $period = $dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_EVERY"));
                $timeout = $period*3600;
                $lastActionTime = $dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_LAST_TS"));

                $performAction = false;
                if ($period % 24 == 0) {
                    if ($lastActionTime) {
                        $days = $period / 24;

                        $dateTime = new DateTime(null, new DateTimeZone($tz));
                        $currentDate = (int)$dateTime->format("Ymd");

                        $dateTime->setTimestamp(strtotime("+{$days} day", $lastActionTime));
                        $nextDate = (int)$dateTime->format("Ymd");

                        if ($nextDate > $currentDate)
                            return;
                    }

                    $pbwFrom = (int)($dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_TIMEFRAME_START_HH")).$dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_TIMEFRAME_START_MM")));
                    $pbwTo = (int)($dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_TIMEFRAME_END_HH")).$dbFarmRole->GetSetting(Scalr_Db_Msr::getConstant("DATA_{$action}_TIMEFRAME_END_MM")));

                    if ($pbwFrom && $pbwTo) {
                        $dateTime = new DateTime(null, new DateTimeZone($tz));
                        $currentTime = (int)$dateTime->format("Hi");
                        //$current_time = (int)date("Hi");

                        if ($pbwFrom <= $currentTime && $pbwTo >= $currentTime)
                            $performAction = true;
                    }
                    else
                        $performAction = true;
                }
                else {
                    //Check timeout
                    if ($lastActionTime+$timeout < time())
                        $performAction = true;
                }

                if ($performAction)
                {
                    $behavior = Scalr_Role_Behavior::loadByName($dbFarmRole->GetRoleObject()->getDbMsrBehavior());

                    if ($action == 'BUNDLE') {
                        $behavior->createDataBundle($dbFarmRole, array(
                            'compressor' => $dbFarmRole->GetSetting(Scalr_Role_DbMsrBehavior::ROLE_DATA_BUNDLE_COMPRESSION),
                            'useSlave' => $dbFarmRole->GetSetting(Scalr_Role_DbMsrBehavior::ROLE_DATA_BUNDLE_USE_SLAVE)
                            //TODO: dataBundleType
                        ));
                    }

                    if ($action == 'BACKUP') {
                        $behavior->createBackup($dbFarmRole);
                    }
                }
            }
        }
    }

    function handleWork ($farmRoleId) {

        try {
            $dbFarmRole = DBFarmRole::LoadByID($farmRoleId);
            $dbFarm = $dbFarmRole->GetFarmObject();

            $env = Scalr_Model::init(Scalr_Model::ENVIRONMENT)->loadById($dbFarm->EnvID);
            $tz = $env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::TIMEZONE);
            if (!$tz)
                $tz = date_default_timezone_get();

            //skip terminated farms
            if ($dbFarm->Status != FARM_STATUS::RUNNING)
                return;

        } catch (Exception $e) {
            return;
        }


        //********* Check Replication status *********/
        //TODO:

        //********* Bundle database data ***********/
           $this->performDbMsrAction('BUNDLE', $dbFarmRole, $tz);

           $backupsNotSupported = in_array($dbFarmRole->Platform, array(
               SERVER_PLATFORMS::CLOUDSTACK,
               SERVER_PLATFORMS::IDCF,
               SERVER_PLATFORMS::UCLOUD
           ));

           //********* Backup database data ***********/
           if (!$backupsNotSupported)
            $this->performDbMsrAction('BACKUP', $dbFarmRole, $tz);
    }
}
