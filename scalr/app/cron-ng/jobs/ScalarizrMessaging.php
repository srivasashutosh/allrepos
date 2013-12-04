<?php
use Scalr\Service\Aws\Ec2\DataType\VolumeFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\ImageFilterNameType;

class Scalr_Cronjob_ScalarizrMessaging extends Scalr_System_Cronjob_MultiProcess_DefaultWorker
{

    static function getConfig()
    {
        return array(
            "description"      => "Process ingoing Scalarizr messages",
            "processPool"      => array(
                "daemonize"         => false,
                "workerMemoryLimit" => 40000,  // 40Mb
                "startupTimeout"    => 10000,  // 10 seconds
                "size"              => 3 // 3 workers
            ),
            "waitPrevComplete" => true,
            "fileName"         => __FILE__,
            "getoptRules"      => array(
                'farm-id-s'         => 'Affect only this farm'
            )
        );
    }

    private $logger;

    /**
     * @var \ADODB_mysqli
     */
    private $db;

    private $serializer;

    function __construct()
    {
        $this->logger = Logger::getLogger(__CLASS__);
        $this->serializer = new Scalr_Messaging_XmlSerializer();
        $this->jsonSerializer = new Scalr_Messaging_JsonSerializer();
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

    function enqueueWork($workQueue)
    {
        $this->logger->info("Fetching servers...");
        $farmid = $this->runOptions['getopt']->getOption('farm-id');
        if ($farmid) {
            $rows = $this->db->GetAll("
                SELECT distinct(m.server_id)
                FROM messages m
                INNER JOIN servers s ON m.server_id = s.server_id
                WHERE m.type = ? AND m.status = ? AND m.isszr = ? AND s.farm_id = ?
            ", array(
                "in",
                MESSAGE_STATUS::PENDING,
                1,
                $farmid
            ));
        } else {
            $rows = $this->db->GetAll("
                SELECT distinct(server_id) FROM messages
                WHERE type = ? AND status = ? AND isszr = ?
            ", array(
                "in",
                MESSAGE_STATUS::PENDING,
                1
            ));
        }
        $this->logger->info("Found " . count($rows) . " servers");
        foreach ($rows as $row) {
            $workQueue->put($row["server_id"]);
        }
    }

    function handleWork($serverId)
    {
        try {
            $dbserver = DBServer::LoadByID($serverId);
            if ($dbserver->farmId) {
                if ($dbserver->GetFarmObject()->Status == FARM_STATUS::TERMINATED) {
                    throw new ServerNotFoundException("");
                }
            }
        } catch (Exception $e) {
            $this->db->Execute("
                DELETE FROM messages WHERE server_id=? AND `type`='in'
            ", array(
                $serverId
            ));
            return;
        }

        $rs = $this->db->Execute("
            SELECT * FROM messages
            WHERE server_id = ? AND type = ? AND status = ?
            ORDER BY id ASC
        ", array(
            $serverId,
            "in",
            MESSAGE_STATUS::PENDING
        ));

        while ($row = $rs->FetchRow()) {
            try {
                if ($row["message"]) {
                    $message = $this->serializer->unserialize($row["message"]);
                } else {
                    $message = $this->jsonSerializer->unserialize($row["json_message"]);
                }
                $message->messageIpAddress = $row['ipaddress'];
                $event = null;
                $startTime = microtime(true);
                // Update scalarizr package version
                if ($message->meta[Scalr_Messaging_MsgMeta::SZR_VERSION]) {
                    $dbserver->SetProperty(
                        SERVER_PROPERTIES::SZR_VESION,
                        $message->meta[Scalr_Messaging_MsgMeta::SZR_VERSION]);
                }
                if ($message->meta[Scalr_Messaging_MsgMeta::SZR_UPD_CLIENT_VERSION]) {
                    $dbserver->SetProperty(
                        SERVER_PROPERTIES::SZR_UPD_CLIENT_VERSION,
                        $message->meta[Scalr_Messaging_MsgMeta::SZR_UPD_CLIENT_VERSION]);
                }
                try {
                    if ($message instanceof Scalr_Messaging_Msg_OperationResult) {
                        $this->db->Execute("
                            UPDATE server_operations SET `status` = ? WHERE id = ?
                        ", array(
                            $message->status,
                            $message->id
                        ));
                        if ($message->status == 'ok') {
                            if ($message->name == 'Grow MySQL/Percona data volume') {
                                $volumeConfig = $message->data;
                                $oldVolumeId = $dbserver->GetFarmRoleObject()->GetSetting(Scalr_Db_Msr::VOLUME_ID);
                                $engine = $dbserver->GetFarmRoleObject()->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE);
                                try {
                                    $storageVolume = Scalr_Storage_Volume::init();
                                    try {
                                        $storageVolume->loadById($volumeConfig->id);
                                        $storageVolume->setConfig($volumeConfig);
                                        $storageVolume->save();
                                    } catch (Exception $e) {
                                        if (strpos($e->getMessage(), 'not found')) {
                                            $storageVolume->loadBy(array(
                                                'id'           => $volumeConfig->id,
                                                'client_id'    => $dbserver->clientId,
                                                'env_id'       => $dbserver->envId,
                                                'name'         => "'{$volumeConfig->tags->service}' data volume",
                                                'type'         => $engine,
                                                'platform'     => $dbserver->platform,
                                                'size'         => $volumeConfig->size,
                                                'fstype'       => $volumeConfig->fstype,
                                                'purpose'      => $volumeConfig->tags->service,
                                                'farm_roleid'  => $dbserver->farmRoleId,
                                                'server_index' => $dbserver->index
                                            ));
                                            $storageVolume->setConfig($volumeConfig);
                                            $storageVolume->save(true);
                                        } else {
                                            throw $e;
                                        }
                                    }
                                    $dbserver->GetFarmRoleObject()->SetSetting(Scalr_Db_Msr::VOLUME_ID, $volumeConfig->id);
                                    if ($engine == MYSQL_STORAGE_ENGINE::EBS) {
                                        $dbserver->GetFarmRoleObject()->SetSetting(
                                            Scalr_Db_Msr::DATA_STORAGE_EBS_SIZE, $volumeConfig->size
                                        );
                                    } elseif ($engine == MYSQL_STORAGE_ENGINE::RAID_EBS) {
                                        $dbserver->GetFarmRoleObject()->SetSetting(
                                            Scalr_Db_Msr::DATA_STORAGE_RAID_DISK_SIZE, $volumeConfig->size
                                        );
                                    }
                                    // Remove old
                                    $storageVolume->delete($oldVolumeId);
                                } catch (Exception $e) {
                                    Logger::getLogger(__CLASS__)->error(new FarmLogMessage(
                                        $dbserver->farmId, "Cannot save storage volume: {$e->getMessage()}"
                                    ));
                                }
                            }
                        } elseif ($message->status == 'error') {
                            if ($message->name == 'Initialization') {
                                $dbserver->SetProperty(SERVER_PROPERTIES::SZR_IS_INIT_FAILED, 1);
                            }
                            if ($message->error) {
                                $msg = $message->error->message;
                                $trace = $message->error->trace;
                                $handler = $message->error->handler;
                                $dbserver->SetProperty(SERVER_PROPERTIES::SZR_IS_INIT_ERROR_MSG, $message->error->message);
                            }
                            $this->db->Execute("
                                INSERT INTO server_operation_progress
                                SET `operation_id` = ?,
                                    `timestamp` = ?,
                                    `phase` = ?,
                                    `step` = ?,
                                    `status` = ?,
                                    `message`= ?,
                                    `trace` = ?,
                                    `handler` = ?,
                                    `progress` = ?,
                                    `stepno` = ?
                                ON DUPLICATE KEY
                                UPDATE status = ?,
                                       progress = ?,
                                       trace = ?,
                                       handler = ?,
                                       message = ?
                        ", array(
                                $message->id,
                                $message->getTimestamp(),
                                $message->phase,
                                $message->step,
                                $message->status,
                                $msg,
                                $trace,
                                $handler,
                                $message->progress,
                                $message->stepno,
                                //
                                $message->status,
                                $message->progress,
                                $trace,
                                $handler,
                                $msg
                            ));
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_UpdateControlPorts) {
                        $apiPort = $message->api;
                        $ctrlPort = $message->messaging;
                        $snmpPort = $message->snmp;
                        // Check API port;
                        $currentApiPort = $dbserver->GetProperty(SERVER_PROPERTIES::SZR_API_PORT);
                        if (!$currentApiPort) $currentApiPort = 8010;
                        if ($apiPort && $apiPort != $currentApiPort) {
                            $this->logger->warn(new FarmLogMessage(
                                $dbserver->farmId, "Scalarizr API port was changed from {$currentApiPort} to {$apiPort}"
                            ));
                            $dbserver->SetProperty(SERVER_PROPERTIES::SZR_API_PORT, $apiPort);
                        }
                        // Check Control port
                        $currentCtrlPort = $dbserver->GetProperty(SERVER_PROPERTIES::SZR_CTRL_PORT);
                        if (!$currentCtrlPort) $currentCtrlPort = 8013;
                        if ($ctrlPort && $ctrlPort != $currentCtrlPort) {
                            $this->logger->warn(new FarmLogMessage(
                                $dbserver->farmId, "Scalarizr Control port was changed from {$currentCtrlPort} to {$ctrlPort}"
                            ));
                            $dbserver->SetProperty(SERVER_PROPERTIES::SZR_CTRL_PORT, $ctrlPort);
                        }
                        //Check SNMP port
                        $currentSnmpPort = $dbserver->GetProperty(SERVER_PROPERTIES::SZR_SNMP_PORT);
                        if (!$currentSnmpPort) $currentSnmpPort = 8014;
                        if ($snmpPort && $snmpPort != $currentSnmpPort) {
                            $this->logger->warn(new FarmLogMessage(
                                $dbserver->farmId, "Scalarizr SNMP port was changed from {$currentSnmpPort} to {$snmpPort}"
                            ));
                            $dbserver->SetProperty(SERVER_PROPERTIES::SZR_SNMP_PORT, $snmpPort);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_Win_HostDown) {
                        $status = PlatformFactory::NewPlatform($dbserver->platform)->GetServerRealStatus($dbserver);
                        if ($status->isRunning()) {
                            $event = new RebootBeginEvent($dbserver);
                        } else {
                            if ($dbserver->platform == SERVER_PLATFORMS::EC2) {
                                if (!$status->isTerminated()) {
                                    //Stopping
                                    $this->logger->error(new FarmLogMessage(
                                        $dbserver->farmId, "Server is in '{$status->getName()}' state. Ignoring HostDown event."
                                    ));
                                    $isStopping = true;
                                }
                            }
                            if (!$isStopping) $event = new HostDownEvent($dbserver);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_Win_PrepareBundleResult) {
                        try {
                            $bundleTask = BundleTask::LoadById($message->bundleTaskId);
                        } catch (Exception $e) {
                        }
                        if ($bundleTask) {
                            if ($message->status == 'ok') {
                                $metaData = array(
                                    'szr_version' => $message->meta[Scalr_Messaging_MsgMeta::SZR_VERSION],
                                    'os'          => $message->os,
                                    'software'    => $message->software
                                );
                                $bundleTask->setMetaData($metaData);
                                $bundleTask->Save();
                                PlatformFactory::NewPlatform($bundleTask->platform)->CreateServerSnapshot($bundleTask);
                            } else {
                                $bundleTask->SnapshotCreationFailed("PrepareBundle procedure failed: {$message->lastError}");
                            }
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_DeployResult) {
                        try {
                            $deploymentTask = Scalr_Model::init(Scalr_Model::DM_DEPLOYMENT_TASK)->loadById($message->deployTaskId);
                        } catch (Exception $e) {
                        }
                        if ($deploymentTask) {
                            if ($message->status == 'error') {
                                $deploymentTask->status = Scalr_Dm_DeploymentTask::STATUS_FAILED;
                                $deploymentTask->lastError = $message->lastError;
                            } else {
                                $deploymentTask->status = Scalr_Dm_DeploymentTask::STATUS_DEPLOYED;
                                $deploymentTask->dtDeployed = date("Y-m-d H:i:s");
                            }
                            $deploymentTask->save();
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_Hello) {
                        $event = $this->onHello($message, $dbserver);
                    } elseif ($message instanceof Scalr_Messaging_Msg_FireEvent) {
                        //Validate event
                        $isEventExist = $this->db->GetOne("
                            SELECT id FROM event_definitions
                            WHERE name = ? AND env_id = ?
                        ", array(
                            $message->eventName,
                            $dbserver->envId
                        ));
                        if ($isEventExist) {
                            $event = new CustomEvent($dbserver, $message->eventName, (array)$message->params);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_MongoDb) {
                        /********* MONGODB *********/
                        try {
                            $dbFarmRole = $dbserver->GetFarmRoleObject();
                        } catch (Exception $e) {
                        }
                        if ($dbFarmRole instanceof DBFarmRole) {
                            foreach (Scalr_Role_Behavior::getListForFarmRole($dbFarmRole) as $behavior)
                                $behavior->handleMessage($message, $dbserver);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_DbMsr) {
                        /********* DBMSR *********/
                        try {
                            $dbFarmRole = $dbserver->GetFarmRoleObject();
                        } catch (Exception $e) {
                        }
                        if ($dbFarmRole instanceof DBFarmRole) {
                            foreach (Scalr_Role_Behavior::getListForFarmRole($dbFarmRole) as $behavior)
                                $behavior->handleMessage($message, $dbserver);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_HostInit) {
                        $event = $this->onHostInit($message, $dbserver);
                    } elseif ($message instanceof Scalr_Messaging_Msg_HostUp) {
                        $event = $this->onHostUp($message, $dbserver);
                    } elseif ($message instanceof Scalr_Messaging_Msg_HostDown) {
                        $isMoving = false;
                        if ($dbserver->platform == SERVER_PLATFORMS::RACKSPACE) {
                            $p = PlatformFactory::NewPlatform($dbserver->platform);
                            $status = $p->GetServerRealStatus($dbserver)->getName();
                            if (stristr($status, 'MOVE') || stristr($status, 'REBOOT')) {
                                $this->logger->error(new FarmLogMessage(
                                    $dbserver->farmId, "Rackspace server is in MOVING state. Ignoring HostDown message."
                                ));
                                $isMoving = true;
                            }
                        }
                        if (in_array($dbserver->platform, array(
                            SERVER_PLATFORMS::OPENSTACK,
                            SERVER_PLATFORMS::RACKSPACENG_US,
                            SERVER_PLATFORMS::RACKSPACENG_UK
                        ))) {
                            $p = PlatformFactory::NewPlatform($dbserver->platform);
                            $status = $p->GetServerRealStatus($dbserver)->getName();
                            if (stristr($status, 'REBOOT') || stristr($status, 'HARD_REBOOT')) {
                                $this->logger->error(new FarmLogMessage(
                                    $dbserver->farmId, "Rackspace server is in {$status} state. Ignoring HostDown message."
                                ));
                                $isRebooting = true;
                            }
                        }
                        if ($dbserver->platform == SERVER_PLATFORMS::EC2) {
                            //TODO: Check is is stopping or shutting-down procedure.
                            $p = PlatformFactory::NewPlatform($dbserver->platform);
                            $status = $p->GetServerRealStatus($dbserver);
                            if (!$status->isTerminated()) {
                                //Stopping
                                $this->logger->error(new FarmLogMessage(
                                    $dbserver->farmId, "Server is in '{$status->getName()}' state. Ignoring HostDown event."
                                ));
                                $isStopping = true;
                            }
                        }
                        if (!$isMoving && !$isStopping && !$isRebooting) {
                            $event = new HostDownEvent($dbserver);
                        }
                        if ($isRebooting) {
                            $event = new RebootBeginEvent($dbserver);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_RebootStart) {
                        $event = new RebootBeginEvent($dbserver);
                    } elseif ($message instanceof Scalr_Messaging_Msg_RebootFinish) {
                        $event = new RebootCompleteEvent($dbserver);
                    } elseif ($message instanceof Scalr_Messaging_Msg_BeforeHostUp) {
                        $event = new BeforeHostUpEvent($dbserver);
                    } elseif ($message instanceof Scalr_Messaging_Msg_BlockDeviceAttached) {
                        if ($dbserver->platform == SERVER_PLATFORMS::EC2) {
                            $aws = $dbserver->GetEnvironmentObject()->aws($dbserver->GetProperty(EC2_SERVER_PROPERTIES::REGION));
                            $instanceId = $dbserver->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID);
                            //The main goal of using filters there is to considerably decrease the size of the response.
                            $volumes = $aws->ec2->volume->describe(null, array(
                                array(
                                    'name' => VolumeFilterNameType::attachmentInstanceId(),
                                    'value' => (string) $instanceId
                                ),
                                array(
                                    'name' => VolumeFilterNameType::attachmentDevice(),
                                    'value' => (string) $message->deviceName
                                ),
                                array(
                                    'name' => VolumeFilterNameType::status(),
                                    'value' => AMAZON_EBS_STATE::IN_USE
                                )
                            ));
                            foreach ($volumes as $volume) {
                                /* @var $volume Scalr\Service\Aws\Ec2\DataType\VolumeData */
                                if ($volume->status == AMAZON_EBS_STATE::IN_USE &&
                                    count($volume->attachmentSet) &&
                                    $volume->attachmentSet[0]->instanceId == $instanceId &&
                                    $volume->attachmentSet[0]->device == $message->deviceName) {
                                    $message->volumeId = $volume->volumeId;
                                }
                            }
                            //Releases memory
                            unset($volumes);
                            $dbserver->GetEnvironmentObject()->getContainer()->release('aws');
                            unset($aws);
                        }
                        $event = new EBSVolumeAttachedEvent($dbserver, $message->deviceName, $message->volumeId);
                    } elseif ($message instanceof Scalr_Messaging_Msg_BlockDeviceMounted) {
                        // Single volume
                        $ebsinfo = $this->db->GetRow("
                            SELECT * FROM ec2_ebs WHERE volume_id=?
                        ", array(
                            $message->volumeId
                        ));
                        if ($ebsinfo) {
                            $this->db->Execute("
                                UPDATE ec2_ebs
                                SET mount_status=?, isfsexist='1'
                                WHERE id=?
                            ", array(
                                EC2_EBS_MOUNT_STATUS::MOUNTED,
                                $ebsinfo['id']
                            ));
                        }
                        $event = new EBSVolumeMountedEvent(
                            $dbserver, $message->mountpoint, $message->volumeId, $message->deviceName
                        );
                    } elseif ($message instanceof Scalr_Messaging_Msg_RebundleResult) {
                        if ($message->status == Scalr_Messaging_Msg_RebundleResult::STATUS_OK) {
                            $metaData = array(
                                'szr_version' => $message->meta[Scalr_Messaging_MsgMeta::SZR_VERSION],
                                'dist'        => $message->dist,
                                'os'          => $message->os,
                                'software'    => $message->software
                            );
                            if ($dbserver->platform == SERVER_PLATFORMS::EC2) {
                                if ($message->aws) {
                                    if ($message->aws->rootDeviceType == 'ebs') {
                                        $tags[] = ROLE_TAGS::EC2_EBS;
                                    }
                                    if ($message->aws->virtualizationType == 'hvm') {
                                        $tags[] = ROLE_TAGS::EC2_HVM;
                                    }
                                } else {
                                    $aws = $dbserver->GetEnvironmentObject()->aws($dbserver);
                                    try {
                                        $info = $aws->ec2->image->describe($dbserver->GetProperty(EC2_SERVER_PROPERTIES::AMIID))->get(0);
                                        if ($info->rootDeviceType == 'ebs') {
                                            $tags[] = ROLE_TAGS::EC2_EBS;
                                        } else {
                                            try {
                                                $bundleTask = BundleTask::LoadById($message->bundleTaskId);
                                                if ($bundleTask->bundleType == SERVER_SNAPSHOT_CREATION_TYPE::EC2_EBS) {
                                                    $tags[] = ROLE_TAGS::EC2_EBS;
                                                }
                                            } catch (Exception $e) {
                                            }
                                        }
                                        if ($info->virtualizationType == 'hvm') {
                                            $tags[] = ROLE_TAGS::EC2_HVM;
                                        }
                                        unset($info);
                                    } catch (Exception $e) {
                                        $metaData['tagsError'] = $e->getMessage();
                                        try {
                                            $bundleTask = BundleTask::LoadById($message->bundleTaskId);
                                            if ($bundleTask->bundleType == SERVER_SNAPSHOT_CREATION_TYPE::EC2_EBS) {
                                                $tags[] = ROLE_TAGS::EC2_EBS;
                                            }
                                        } catch (Exception $e) {
                                        }
                                    }
                                    //Releases memory
                                    $dbserver->GetEnvironmentObject()->getContainer()->release('aws');
                                    unset($aws);
                                }
                            } elseif ($dbserver->platform == SERVER_PLATFORMS::NIMBULA) {
                                $metaData['init_root_user'] = $message->sshUser;
                                $metaData['init_root_pass'] = $message->sshPassword;
                            }
                            $metaData['tags'] = $tags;
                            $event = new RebundleCompleteEvent($dbserver, $message->snapshotId, $message->bundleTaskId, $metaData);
                        } else if ($message->status == Scalr_Messaging_Msg_RebundleResult::STATUS_FAILED) {
                            $event = new RebundleFailedEvent($dbserver, $message->bundleTaskId, $message->lastError);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_Mysql_CreateDataBundleResult) {
                        if ($message->status == "ok") {
                            $event = new MysqlBackupCompleteEvent($dbserver, MYSQL_BACKUP_TYPE::BUNDLE, array(
                                'snapshotConfig' => $message->snapshotConfig,
                                'logFile'        => $message->logFile,
                                'logPos'         => $message->logPos,
                                'dataBundleSize' => $message->dataBundleSize,
                               /* @deprecated */
                               'snapshotId'      => $message->snapshotId
                            ));
                        } else {
                            $event = new MysqlBackupFailEvent($dbserver, MYSQL_BACKUP_TYPE::BUNDLE);
                            $event->lastError = $message->lastError;
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_Mysql_CreateBackupResult) {
                        if ($message->status == "ok") {
                            $event = new MysqlBackupCompleteEvent($dbserver, MYSQL_BACKUP_TYPE::DUMP, array());
                            $event->backupParts = $message->backupParts;
                        } else {
                            $event = new MysqlBackupFailEvent($dbserver, MYSQL_BACKUP_TYPE::DUMP);
                            $event->lastError = $message->lastError;
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_Mysql_PromoteToMasterResult) {
                        $event = $this->onMysql_PromoteToMasterResult($message, $dbserver);
                    } elseif ($message instanceof Scalr_Messaging_Msg_Mysql_CreatePmaUserResult) {
                        $farmRole = DBFarmRole::LoadByID($message->farmRoleId);
                        if ($message->status == "ok") {
                            $farmRole->SetSetting(DbFarmRole::SETTING_MYSQL_PMA_USER, $message->pmaUser);
                            $farmRole->SetSetting(DbFarmRole::SETTING_MYSQL_PMA_PASS, $message->pmaPassword);
                        } else {
                            $farmRole->SetSetting(DBFarmRole::SETTING_MYSQL_PMA_REQUEST_TIME, "");
                            $farmRole->SetSetting(DBFarmRole::SETTING_MYSQL_PMA_REQUEST_ERROR, $message->lastError);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_RabbitMq_SetupControlPanelResult) {
                        $farmRole = $dbserver->GetFarmRoleObject();
                        if ($message->status == "ok") {
                            $farmRole->SetSetting(Scalr_Role_Behavior_RabbitMQ::ROLE_CP_SERVER_ID, $dbserver->serverId);
                            $farmRole->SetSetting(Scalr_Role_Behavior_RabbitMQ::ROLE_CP_URL, $message->cpanelUrl);
                            $farmRole->SetSetting(Scalr_Role_Behavior_RabbitMQ::ROLE_CP_REQUEST_TIME, "");
                        } else {
                            $farmRole->SetSetting(Scalr_Role_Behavior_RabbitMQ::ROLE_CP_SERVER_ID, "");
                            $farmRole->SetSetting(Scalr_Role_Behavior_RabbitMQ::ROLE_CP_REQUEST_TIME, "");
                            $farmRole->SetSetting(Scalr_Role_Behavior_RabbitMQ::ROLE_CP_ERROR_MSG, $message->lastError);
                        }
                    } elseif ($message instanceof Scalr_Messaging_Msg_AmiScriptsMigrationResult) {
                        try {
                            //Open security group:
                            if ($dbserver->platform == SERVER_PLATFORMS::EC2) {
                                $info = PlatformFactory::NewPlatform($dbserver->platform)->GetServerExtendedInformation($dbserver);
                                $sg = explode(", ", $info['Security groups']);
                                foreach ($sg as $sgroup) {
                                    if ($sgroup != 'default') {
                                        // For Scalarizr
                                        $group_rules = array(
                                            array('rule' => 'tcp:8013:8013:0.0.0.0/0'),
                                            array('rule' => 'udp:8014:8014:0.0.0.0/0'),
                                        );
                                        $aws = $dbserver->GetEnvironmentObject()->aws($dbserver);
                                        $ipPermissions = new \Scalr\Service\Aws\Ec2\DataType\IpPermissionList();
                                        foreach ($group_rules as $rule) {
                                            $group_rule = explode(":", $rule["rule"]);
                                            $ipPermissions->append(new \Scalr\Service\Aws\Ec2\DataType\IpPermissionData(
                                                $group_rule[0], $group_rule[1], $group_rule[2],
                                                new \Scalr\Service\Aws\Ec2\DataType\IpRangeData($group_rule[3])));
                                        }
                                        $aws->ec2->securityGroup->authorizeIngress($ipPermissions, null, $sgroup);
                                        $dbserver->GetEnvironmentObject()->getContainer()->release('aws');
                                        unset($aws);
                                        unset($ipPermissions);
                                        break;
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $this->logger->fatal($e->getMessage());
                        }
                        $dbserver->SetProperty(SERVER_PROPERTIES::SZR_SNMP_PORT, 8014);
                        $dbserver->SetProperty(SERVER_PROPERTIES::SZR_VESION, "0.7.217");
                        if ($message->mysql) {
                            $event = $this->onHostUp($message, $dbserver, true);
                        }
                    }
                    $handle_status = MESSAGE_STATUS::HANDLED;
                } catch (Exception $e) {
                    $handle_status = MESSAGE_STATUS::FAILED;
                    $this->logger->error(sprintf("Cannot handle message '%s' (message_id: %s) " . "from server '%s' (server_id: %s). %s",
                        $message->getName(),
                        $message->messageId,
                        $dbserver->remoteIp ? $dbserver->remoteIp : '*no-ip*',
                        $dbserver->serverId,
                        $e->getMessage() . "({$e->getFile()}:{$e->getLine()})"
                    ));
                }
                $totalTime = microtime(true) - $startTime;
                $this->db->Execute("
                    UPDATE messages
                    SET status = ?, instance_id = ?
                    WHERE messageid = ?
                ", array(
                    $handle_status,
                    $totalTime,
                    $message->messageId
                ));
                if ($event) {
                    Scalr::FireEvent($dbserver->farmId, $event);
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage(), $e);
            }
        }
    }

    private function onHello($message, DBServer $dbserver)
    {
        if ($dbserver->status == SERVER_STATUS::TEMPORARY) {
            $bundleTask = BundleTask::LoadById($dbserver->GetProperty(SERVER_PROPERTIES::SZR_IMPORTING_BUNDLE_TASK_ID));
            $bundleTask->status = SERVER_SNAPSHOT_CREATION_STATUS::PENDING;
            $bundleTask->Log("Received Hello message from scalarizr on server. Creating image");
            $bundleTask->osFamily = $message->dist->distributor;
            $bundleTask->osName = $message->dist->codename;
            $bundleTask->osVersion = $message->dist->release;
            $bundleTask->save();
        }
        if ($dbserver->status == SERVER_STATUS::IMPORTING) {
            if (!$dbserver->remoteIp || !$dbserver->localIp) {
                if ($message->remoteIp && $dbserver->platform != SERVER_PLATFORMS::IDCF) {
                    $dbserver->remoteIp = $message->remoteIp;
                }
                if ($message->localIp) {
                    $dbserver->localIp = $message->localIp;
                }
                if (!$message->behaviour) {
                    $message->behaviour = array('base');
                }
                if ((!$dbserver->remoteIp || $dbserver->localIp == $dbserver->remoteIp) &&
                    $message->messageIpAddress != $dbserver->remoteIp) {
                    $dbserver->remoteIp = $message->messageIpAddress;
                }
                $dbserver->SetProperty(SERVER_PROPERTIES::SZR_IMPORTING_BEHAVIOR, @implode(",", $message->behaviour));
                $dbserver->save();
            }

            switch ($dbserver->platform) {
                case SERVER_PLATFORMS::EC2:
                    $dbserver->SetProperties(array(
                        EC2_SERVER_PROPERTIES::AMIID         => $message->awsAmiId,
                        EC2_SERVER_PROPERTIES::INSTANCE_ID   => $message->awsInstanceId,
                        EC2_SERVER_PROPERTIES::INSTANCE_TYPE => $message->awsInstanceType,
                        EC2_SERVER_PROPERTIES::AVAIL_ZONE    => $message->awsAvailZone,
                        EC2_SERVER_PROPERTIES::REGION        => substr($message->awsAvailZone, 0, -1),
                        SERVER_PROPERTIES::ARCHITECTURE      => $message->architecture
                    ));
                    break;

                case SERVER_PLATFORMS::EUCALYPTUS:
                    $dbserver->SetProperties(array(
                        EUCA_SERVER_PROPERTIES::EMIID         => $message->awsAmiId,
                        EUCA_SERVER_PROPERTIES::INSTANCE_ID   => $message->awsInstanceId,
                        EUCA_SERVER_PROPERTIES::INSTANCE_TYPE => $message->awsInstanceType,
                        EUCA_SERVER_PROPERTIES::AVAIL_ZONE    => $message->awsAvailZone,
                        SERVER_PROPERTIES::ARCHITECTURE       => $message->architecture
                    ));
                    break;

                case SERVER_PLATFORMS::GCE:
                    $dbserver->SetProperties(array(
                        GCE_SERVER_PROPERTIES::CLOUD_LOCATION => $message->{$dbserver->platform}->cloudLocation,
                        GCE_SERVER_PROPERTIES::SERVER_ID      => $message->{$dbserver->platform}->serverId,
                        GCE_SERVER_PROPERTIES::SERVER_NAME    => $message->{$dbserver->platform}->serverName,
                        GCE_SERVER_PROPERTIES::MACHINE_TYPE   => $message->{$dbserver->platform}->machineType,
                        SERVER_PROPERTIES::ARCHITECTURE       => $message->architecture
                    ));
                    break;

                case SERVER_PLATFORMS::NIMBULA:
                    $dbserver->SetProperties(array(
                        NIMBULA_SERVER_PROPERTIES::NAME => $message->serverName,
                        SERVER_PROPERTIES::ARCHITECTURE => $message->architecture
                    ));
                    break;

                case SERVER_PLATFORMS::IDCF:
                case SERVER_PLATFORMS::UCLOUD:
                case SERVER_PLATFORMS::CLOUDSTACK:
                    $dbserver->SetProperties(array(
                        CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID      => $message->cloudstack->instanceId,
                        CLOUDSTACK_SERVER_PROPERTIES::CLOUD_LOCATION => $message->cloudstack->availZone,
                        SERVER_PROPERTIES::ARCHITECTURE              => $message->architecture
                    ));
                    break;

                case SERVER_PLATFORMS::RACKSPACE:
                    $env = $dbserver->GetEnvironmentObject();
                    $cs = Scalr_Service_Cloud_Rackspace::newRackspaceCS(
                        $env->getPlatformConfigValue(
                            Modules_Platforms_Rackspace::USERNAME, true,
                            $dbserver->GetProperty(RACKSPACE_SERVER_PROPERTIES::DATACENTER)
                        ),
                        $env->getPlatformConfigValue(
                            Modules_Platforms_Rackspace::API_KEY, true,
                            $dbserver->GetProperty(RACKSPACE_SERVER_PROPERTIES::DATACENTER)
                        ),
                        $dbserver->GetProperty(RACKSPACE_SERVER_PROPERTIES::DATACENTER)
                    );
                    $csServer = null;
                    $list = $cs->listServers(true);
                    if ($list) {
                        foreach ($list->servers as $_tmp) {
                            if ($_tmp->addresses->public && in_array($message->remoteIp, $_tmp->addresses->public)) {
                                $csServer = $_tmp;
                            }
                        }
                    }
                    if (!$csServer) {
                        $this->logger->error(sprintf(
                            "Server not found on CloudServers (server_id: %s, remote_ip: %s, local_ip: %s)",
                            $dbserver->serverId, $message->remoteIp, $message->localIp
                        ));
                        return;
                    }
                    $dbserver->SetProperties(array(
                        RACKSPACE_SERVER_PROPERTIES::SERVER_ID  => $csServer->id,
                        RACKSPACE_SERVER_PROPERTIES::NAME       => $csServer->name,
                        RACKSPACE_SERVER_PROPERTIES::IMAGE_ID   => $csServer->imageId,
                        RACKSPACE_SERVER_PROPERTIES::FLAVOR_ID  => $csServer->flavorId,
                        RACKSPACE_SERVER_PROPERTIES::HOST_ID    => $csServer->hostId,
                        SERVER_PROPERTIES::ARCHITECTURE         => $message->architecture
                    ));
                    break;

                case SERVER_PLATFORMS::RACKSPACENG_UK:
                case SERVER_PLATFORMS::RACKSPACENG_US:
                case SERVER_PLATFORMS::OPENSTACK:
                    $env = $dbserver->GetEnvironmentObject();

                    $os = $env->openstack($dbserver->platform, $dbserver->GetProperty(OPENSTACK_SERVER_PROPERTIES::CLOUD_LOCATION));


                    $csServer = null;
                    $list = $os->servers->list(true);
                    if ($list) {
                        foreach ($list as $_tmp) {
                            $ipaddresses = array();
                            if (!is_array($_tmp->addresses)) {
                                $_tmp->addresses = (array)$_tmp->addresses;
                            }
                            foreach ($_tmp->addresses as $net => $addresses) {
                                foreach ($addresses as $addr) {
                                    if ($addr->version == 4) {
                                        array_push($ipaddresses, $addr->addr);
                                    }
                                }
                            }

                            if ($_tmp->accessIPv4)
                                array_push($ipaddresses, $_tmp->accessIPv4);

                            if (in_array($message->localIp, $ipaddresses) ||
                                in_array($message->remoteIp, $ipaddresses)) {
                                $osServer = $_tmp;
                            }
                        }
                    }
                    if (!$osServer) {
                        $this->logger->error(sprintf(
                            "Server not found on Openstack (server_id: %s, remote_ip: %s, local_ip: %s)",
                            $dbserver->serverId, $message->remoteIp, $message->localIp
                        ));
                        return;
                    }
                    $dbserver->SetProperties(array(
                        OPENSTACK_SERVER_PROPERTIES::SERVER_ID => $osServer->id,
                        OPENSTACK_SERVER_PROPERTIES::NAME      => $osServer->name,
                        OPENSTACK_SERVER_PROPERTIES::IMAGE_ID  => $osServer->image->id,
                        OPENSTACK_SERVER_PROPERTIES::FLAVOR_ID => $osServer->flavor->id,
                        OPENSTACK_SERVER_PROPERTIES::HOST_ID   => $osServer->hostId,
                        SERVER_PROPERTIES::ARCHITECTURE        => $message->architecture
                    ));
                    break;
            }
            // Bundle image
            $creInfo = new ServerSnapshotCreateInfo(
                $dbserver, $dbserver->GetProperty(SERVER_PROPERTIES::SZR_IMPORTING_ROLE_NAME),
                SERVER_REPLACEMENT_TYPE::NO_REPLACE
            );
            $bundleTask = BundleTask::Create($creInfo);
            $bundleTask->osFamily = $message->dist->distributor;
            $bundleTask->osName = $message->dist->codename;
            $bundleTask->osVersion = $message->dist->release;
            if ($message->dist->distributor == 'oel' &&
                $dbserver->platform == SERVER_PLATFORMS::EC2) {
                $bundleTask->bundleType = SERVER_SNAPSHOT_CREATION_TYPE::EC2_EBS_HVM;
            }

            $bundleTask->setDate("started");

            $bundleTask->Save();
        }
    }

    private function onHostInit($message, DBServer $dbserver)
    {
        if ($dbserver->status == SERVER_STATUS::PENDING) {
            $platform = PlatformFactory::NewPlatform($dbserver->platform);
            // Update server crypto key
            $srv_props = array();
            if ($message->cryptoKey) {
                $srv_props[SERVER_PROPERTIES::SZR_KEY] = trim($message->cryptoKey);
                $srv_props[SERVER_PROPERTIES::SZR_KEY_TYPE] = SZR_KEY_TYPE::PERMANENT;
            }
            $srv_props[SERVER_PROPERTIES::SZR_SNMP_PORT] = $message->snmpPort;
            if (!in_array($dbserver->platform, array(
                SERVER_PLATFORMS::CLOUDSTACK,
                SERVER_PLATFORMS::IDCF,
                SERVER_PLATFORMS::UCLOUD
            ))) {
                $remoteIp = $message->remoteIp;
            } else {
                if ($dbserver->farmRoleId) {
                    $dbFarmRole = $dbserver->GetFarmRoleObject();
                    $networkType = $dbFarmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_NETWORK_TYPE);
                    if ($networkType == 'Direct') {
                        $remoteIp = $message->localIp;
                    } else {
                        $sharedIp = $dbFarmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_SHARED_IP_ADDRESS);
                        if (!$sharedIp) {
                            $env = $dbserver->GetEnvironmentObject();
                            $remoteIp = $platform->getConfigVariable(
                                Modules_Platforms_Cloudstack::SHARED_IP . "." . $dbserver->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::CLOUD_LOCATION), $env, false
                            );
                        } else {
                            $remoteIp = $sharedIp;
                        }
                    }
                } else {
                    $remoteIp = $message->localIp;
                }
            }
            if (in_array($dbserver->platform, array(
                SERVER_PLATFORMS::OPENSTACK
            ))) {
                if ($dbserver->farmRoleId) {
                    $dbFarmRole = $dbserver->GetFarmRoleObject();
                    $ipPool = $dbFarmRole->GetSetting(DBFarmRole::SETTING_OPENSTACK_IP_POOL);
                    if ($ipPool) {
                        //TODO:
                        $osClient = $dbserver->GetEnvironmentObject()->openstack(
                            $dbserver->platform, $dbserver->GetProperty(OPENSTACK_SERVER_PROPERTIES::CLOUD_LOCATION)
                        );
                        //Check free existing IP
                        $ips = $osClient->servers->floatingIps->list($ipPool);
                        foreach ($ips as $ip) {
                            if (!$ip->instance_id) {
                                $ipAddress = $ip->ip;
                                break;
                            }
                        }
                        // If no free IP allocate new from pool
                        if (!$ipAddress) {
                            $ip = $osClient->servers->floatingIps->create($ipPool);
                            $ipAddress = $ip->ip;
                        }
                        // Associate floating IP with Instance
                        $osClient->servers->addFloatingIp($dbserver->GetCloudServerID(), $ipAddress);
                        $remoteIp = $ipAddress;
                    } else {
                        if ($message->remoteIp) {
                            $remoteIp = $message->remoteIp;
                        } else {
                            $remoteIp = $message->localIp;
                        }
                    }
                } else {
                    $remoteIp = $message->localIp;
                }
            }
            if (!$remoteIp) {
                $ips = $platform->GetServerIPAddresses($dbserver);
                $remoteIp = $ips['remoteIp'];
            }
            $dbserver->remoteIp = $remoteIp;
            //Update auto-update settings
            //TODO: Check auto-update client version
            if ($dbserver->IsSupported('0.7.225')) {
                $dbserver->SetProperties($srv_props);
                try {
                    $repo = $dbserver->GetFarmObject()->GetSetting(DBFarm::SETTING_SZR_UPD_REPOSITORY);
                    $schedule = $dbserver->GetFarmObject()->GetSetting(DBFarm::SETTING_SZR_UPD_SCHEDULE);
                    if ($repo && $schedule) {
                        $updateClient = new Scalr_Net_Scalarizr_UpdateClient($dbserver);
                        $updateClient->configure($repo, $schedule);
                    }
                } catch (Exception $e) {
                }
            }
            // MySQL specific
            $dbFarmRole = $dbserver->GetFarmRoleObject();
            if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
                $master = $dbFarmRole->GetFarmObject()->GetMySQLInstances(true);
                // If no masters in role this server becomes it
                if (!$master[0] && !(int) $dbFarmRole->GetSetting(DbFarmRole::SETTING_MYSQL_SLAVE_TO_MASTER)) {
                    $srv_props[SERVER_PROPERTIES::DB_MYSQL_MASTER] = 1;
                }
            }
            //MSR Replication Master
            //TODO: MySQL
            if ($dbFarmRole->GetRoleObject()->getDbMsrBehavior()) {
                $servers = $dbFarmRole->GetServersByFilter(array(
                    'status' => array(
                        SERVER_STATUS::INIT,
                        SERVER_STATUS::RUNNING
                    )
                ));
                if (!$dbFarmRole->GetSetting(Scalr_Db_Msr::SLAVE_TO_MASTER)) {
                    $masterFound = false;
                    foreach ($servers as $server) {
                        if ($server->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER)) $masterFound = true;
                    }
                    if (!$masterFound) $srv_props[Scalr_Db_Msr::REPLICATION_MASTER] = 1;
                } elseif ($dbFarmRole->GetSetting(Scalr_Db_Msr::SLAVE_TO_MASTER) && count($servers) == 0) {
                    $dbFarmRole->SetSetting(Scalr_Db_Msr::SLAVE_TO_MASTER, 0);
                    $srv_props[Scalr_Db_Msr::REPLICATION_MASTER] = 1;
                }
            }
            $dbserver->SetProperties($srv_props);
            return new HostInitEvent($dbserver, $message->localIp, $remoteIp, $message->sshPubKey);
        } else {
            /*
               $this->logger->error("Strange situation. Received HostInit message"
                       . " from server '{$dbserver->serverId}' ({$message->remoteIp})"
                       . " with state {$dbserver->status}!");
            */
            //TOOD: Check if instance terminating we probably can cancel termination and continue initialization
        }
    }

    /**
     * @param Scalr_Messaging_Msg $message
     * @param DBServer $dbserver
     */
    private function onHostUp($message, $dbserver, $skipStatusCheck = false)
    {
        if ($dbserver->status == SERVER_STATUS::INIT || $skipStatusCheck) {
            $event = new HostUpEvent($dbserver, "");
            $dbFarmRole = $dbserver->GetFarmRoleObject();
            foreach (Scalr_Role_Behavior::getListForFarmRole($dbFarmRole) as $behavior)
                $behavior->handleMessage($message, $dbserver);
                //TODO: Move MySQL to MSR
                /****** MOVE TO MSR ******/
                //TODO: Legacy MySQL code
            if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
                if (!$message->mysql) {
                    $this->logger->error(sprintf(
                        "Strange situation. HostUp message from MySQL behavior doesn't contains `mysql` property. Server %s (%s)",
                        $dbserver->serverId, $dbserver->remoteIp
                    ));
                    return;
                }
                $mysqlData = $message->mysql;
                if ($dbserver->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER)) {
                    if ($mysqlData->rootPassword) {
                        $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_REPL_PASSWORD, $mysqlData->replPassword);
                        $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_ROOT_PASSWORD, $mysqlData->rootPassword);
                        $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_STAT_PASSWORD, $mysqlData->statPassword);
                    }
                    $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_LOG_FILE, $mysqlData->logFile);
                    $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_LOG_POS, $mysqlData->logPos);
                    if ($dbserver->IsSupported("0.7")) {
                        //$dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_SNAPSHOT_ID, $mysqlData->snapshotConfig);
                        //$dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_SNAPSHOT_ID, $mysqlData->volumeConfig);
                        if ($mysqlData->volumeConfig) {
                            try {
                                $storageVolume = Scalr_Storage_Volume::init();
                                try {
                                    $storageVolume->loadById($mysqlData->volumeConfig->id);
                                    $storageVolume->setConfig($mysqlData->volumeConfig);
                                    $storageVolume->save();
                                } catch (Exception $e) {
                                    if (strpos($e->getMessage(), 'not found')) {
                                        $storageVolume->loadBy(array(
                                            'id'           => $mysqlData->volumeConfig->id,
                                            'client_id'    => $dbserver->clientId,
                                            'env_id'       => $dbserver->envId,
                                            'name'         => "MySQL data volume",
                                            'type'         => $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_DATA_STORAGE_ENGINE),
                                            'platform'     => $dbserver->platform,
                                            'size'         => $mysqlData->volumeConfig->size,
                                            'fstype'       => $mysqlData->volumeConfig->fstype,
                                            'purpose'      => ROLE_BEHAVIORS::MYSQL,
                                            'farm_roleid'  => $dbserver->farmRoleId,
                                            'server_index' => $dbserver->index
                                        ));
                                        $storageVolume->setConfig($mysqlData->volumeConfig);
                                        $storageVolume->save(true);
                                    } else
                                        throw $e;
                                }
                                $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_SCALR_VOLUME_ID, $storageVolume->id);
                            } catch (Exception $e) {
                                $this->logger->error(new FarmLogMessage(
                                    $event->DBServer->farmId, "Cannot save storage volume: {$e->getMessage()}"
                                ));
                            }
                        }
                        if ($mysqlData->snapshotConfig) {
                            try {
                                $storageSnapshot = Scalr_Storage_Snapshot::init();
                                try {
                                    $storageSnapshot->loadById($mysqlData->snapshotConfig->id);
                                    $storageSnapshot->setConfig($mysqlData->snapshotConfig);
                                    $storageSnapshot->save();
                                } catch (Exception $e) {
                                    if (strpos($e->getMessage(), 'not found')) {
                                        $storageSnapshot->loadBy(array(
                                            'id'          => $mysqlData->snapshotConfig->id,
                                            'client_id'   => $dbserver->clientId,
                                            'farm_id'     => $dbserver->farmId,
                                            'farm_roleid' => $dbserver->farmRoleId,
                                            'env_id'      => $dbserver->envId,
                                            'name'        => sprintf(_("MySQL data bundle #%s"), $mysqlData->snapshotConfig->id),
                                            'type'        => $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_DATA_STORAGE_ENGINE),
                                            'platform'    => $dbserver->platform,
                                            'description' => sprintf(
                                                _("MySQL data bundle created on Farm '%s' -> Role '%s'"),
                                                $dbFarmRole->GetFarmObject()->Name,
                                                $dbFarmRole->GetRoleObject()->name
                                            ),
                                            'ismysql'     => true,
                                            'service'     => ROLE_BEHAVIORS::MYSQL
                                        ));
                                        $storageSnapshot->setConfig($mysqlData->snapshotConfig);
                                        $storageSnapshot->save(true);
                                    } else
                                        throw $e;
                                }
                                $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_SCALR_SNAPSHOT_ID, $storageSnapshot->id);
                            } catch (Exception $e) {
                                $this->logger->error(new FarmLogMessage(
                                    $event->DBServer->farmId, "Cannot save storage snapshot: {$e->getMessage()}"
                                ));
                            }
                        }
                    } else {
                        //@deprecated
                        $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_SNAPSHOT_ID, $mysqlData->snapshotId);
                    }
                }
            }
            return $event;
        } else {
            $this->logger->error(
                "Strange situation. Received HostUp message"
              . " from server '{$dbserver->serverId}' ('{$message->remoteIp})"
              . " with state {$dbserver->status}!"
            );
        }
    }

    /**
     * @param Scalr_Messaging_Msg_Mysql_PromoteToMasterResult $message
     * @param DBServer $dbserver
     */
    private function onMysql_PromoteToMasterResult($message, DBServer $dbserver)
    {
        $dbserver->GetFarmRoleObject()->SetSetting(DBFarmRole::SETTING_MYSQL_SLAVE_TO_MASTER, 0);
        if ($message->status == Scalr_Messaging_Msg_Mysql_PromoteToMasterResult::STATUS_OK) {
            $dbFarm = $dbserver->GetFarmObject();
            $dbFarmRole = $dbserver->GetFarmRoleObject();
            $oldMaster = $dbFarm->GetMySQLInstances(true);
            if ($dbserver->IsSupported("0.7")) {
                if ($message->volumeConfig) {
                    try {
                        $storageVolume = Scalr_Storage_Volume::init();
                        try {
                            $storageVolume->loadById($message->volumeConfig->id);
                            $storageVolume->setConfig($message->volumeConfig);
                            $storageVolume->save();
                        } catch (Exception $e) {
                            if (strpos($e->getMessage(), 'not found')) {
                                $storageVolume->loadBy(array(
                                    'id'           => $message->volumeConfig->id,
                                    'client_id'    => $dbserver->clientId,
                                    'env_id'       => $dbserver->envId,
                                    'name'         => "MySQL data volume",
                                    'type'         => $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_DATA_STORAGE_ENGINE),
                                    'platform'     => $dbserver->platform,
                                    'size'         => $message->volumeConfig->size,
                                    'fstype'       => $message->volumeConfig->fstype,
                                    'purpose'      => ROLE_BEHAVIORS::MYSQL,
                                    'farm_roleid'  => $dbserver->farmRoleId,
                                    'server_index' => $dbserver->index
                                ));
                                $storageVolume->setConfig($message->volumeConfig);
                                $storageVolume->save(true);
                            } else {
                                throw $e;
                            }
                        }
                    } catch (Exception $e) {
                        $this->logger->error(new FarmLogMessage(
                            $dbserver->farmId, "Cannot save storage volume: {$e->getMessage()}"
                        ));
                    }
                }
                if ($message->snapshotConfig) {
                    try {
                        $snapshot = Scalr_Model::init(Scalr_Model::STORAGE_SNAPSHOT);
                        $snapshot->loadBy(array(
                            'id'          => $message->snapshotConfig->id,
                            'client_id'   => $dbserver->clientId,
                            'env_id'      => $dbserver->envId,
                            'name'        => "Automatical MySQL data bundle",
                            'type'        => $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_DATA_STORAGE_ENGINE),
                            'platform'    => $dbserver->platform,
                            'description' => "MySQL data bundle created automatically by Scalr",
                            'ismysql'     => true
                        ));
                        $snapshot->setConfig($message->snapshotConfig);
                        $snapshot->save(true);
                        $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_SCALR_SNAPSHOT_ID, $snapshot->id);
                        $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_LOG_FILE, $message->logFile);
                        $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_LOG_POS, $message->logPos);
                    } catch (Exception $e) {
                        $this->logger->error(new FarmLogMessage(
                            $dbserver->farmId, "Cannot save storage snapshot: {$e->getMessage()}"
                        ));
                    }
                }
            } else {
                // TODO: delete old slave volume if new one was created
                $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_MASTER_EBS_VOLUME_ID, $message->volumeId);
            }
            return new NewMysqlMasterUpEvent($dbserver, "", $oldMaster[0]);
        } elseif ($message->status == Scalr_Messaging_Msg_Mysql_PromoteToMasterResult::STATUS_FAILED) {
            $dbserver->SetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER, 0);
            $dbserver->SetProperty(Scalr_Db_Msr::REPLICATION_MASTER, 0);
            // XXX: Need to do smth
            $this->logger->error(sprintf(
                "Promote to Master failed for server %s. Last error: %s",
                $dbserver->serverId, $message->lastError
            ));
        }
    }
}
