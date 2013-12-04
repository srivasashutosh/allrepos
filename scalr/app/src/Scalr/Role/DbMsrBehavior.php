<?php
    class Scalr_Role_DbMsrBehavior extends Scalr_Role_Behavior
    {
        /* ALL SETTINGS IN SCALR_DB_MSR_* */
        const ROLE_DATA_STORAGE_LVM_VOLUMES = 'db.msr.storage.lvm.volumes';
        const ROLE_DATA_BUNDLE_USE_SLAVE	= 'db.msr.data_bundle.use_slave';
        const ROLE_DATA_BUNDLE_COMPRESSION  = 'db.msr.data_bundle.compression';
        const ROLE_NO_DATA_BUDNLE_ON_PROMOTE = 'db.msr.no_data_bundle_on_promote';
        const ROLE_NO_DATA_BUNDLE_FOR_SLAVES  = 'db.msr.data_bundle.not_exists';

        const ROLE_MASTER_PASSWORD = 'db.msr.master_password';

        protected $behavior;

        public function __construct($behaviorName)
        {
            parent::__construct($behaviorName);
        }

        public function onFarmTerminated(DBFarmRole $dbFarmRole)
        {
            if (in_array($this->behavior, array(ROLE_BEHAVIORS::PERCONA, ROLE_BEHAVIORS::MYSQL2, ROLE_BEHAVIORS::MARIADB))) {
                $dbFarmRole->SetSetting(self::ROLE_NO_DATA_BUNDLE_FOR_SLAVES, 1);
            }
        }

        public function makeUpscaleDecision(DBFarmRole $dbFarmRole)
        {
            $master = $this->getMasterServer($dbFarmRole);

            $storageType = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE);
            $storageGeneration = $storageType == 'lvm' ? 2 : 1;

            if ($storageGeneration == 2) {
                if (in_array($this->behavior, array(ROLE_BEHAVIORS::PERCONA, ROLE_BEHAVIORS::MYSQL2, ROLE_BEHAVIORS::MARIADB)) && $master && $dbFarmRole->GetSetting(self::ROLE_NO_DATA_BUNDLE_FOR_SLAVES)) {
                    Logger::getLogger(LOG_CATEGORY::FARM)->warn(new FarmLogMessage($dbFarmRole->FarmID,
                        sprintf("No suitable data bundle found for launching slaves on %s -> %s. Please perform data bundle on master to be able to launch slaves.",
                            $dbFarmRole->GetFarmObject()->Name,
                            $dbFarmRole->GetRoleObject()->name
                        )
                    ));
                    return Scalr_Scaling_Decision::NOOP;
                }
            }

            return false;
        }

        public function getMasterServer(DBFarmRole $dbFarmRole)
        {
            $servers = $dbFarmRole->GetServersByFilter(array('status' => array(SERVER_STATUS::RUNNING)));
            foreach ($servers as $dbServer) {

                $isMaster = $dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER);
                if ($isMaster)
                    return $dbServer;

            }

            return null;
        }

        public function createBackup(DBFarmRole $dbFarmRole)
        {
            if ($dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BACKUP_IS_RUNNING) == 1)
                throw new Exception("Backup already in progress");

            $currentServer = $this->getServerForBackup($dbFarmRole);

            if (!$currentServer)
                throw new Exception("No suitable server for backup");

            $currentServer->SendMessage(new Scalr_Messaging_Msg_DbMsr_CreateBackup());
            $dbFarmRole->SetSetting(Scalr_Db_Msr::getConstant("DATA_BACKUP_IS_RUNNING"), 1);
            $dbFarmRole->SetSetting(Scalr_Db_Msr::getConstant("DATA_BACKUP_RUNNING_TS"), time());
            $dbFarmRole->SetSetting(Scalr_Db_Msr::getConstant("DATA_BACKUP_SERVER_ID"), $currentServer->serverId);
        }

        public function createDataBundle(DBFarmRole $dbFarmRole, array $params = array()) {

            if (!$params['dataBundleType'])
                $params['dataBundleType'] = 'full';

            if ($params['compressor'] === null)
                $params['compressor'] = 'gzip';

            //$params['useSlave']

            if ($dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BUNDLE_IS_RUNNING) == 1)
                throw new Exception("Data bundle already in progress");

            $currentServer = $this->getServerForDataBundle($dbFarmRole, $params['useSlave']);
            if (!$currentServer)
                throw new Exception("No suitable server for data bundle");

            $message = new Scalr_Messaging_Msg_DbMsr_CreateDataBundle();

            $storageType = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE);
            $storageGeneration = $storageType == 'lvm' ? 2 : 1;

            switch($dbFarmRole->Platform) {
                case SERVER_PLATFORMS::EC2:
                    $driver = 's3';
                    break;

                case SERVER_PLATFORMS::GCE:
                    $driver = 'gcs';
                    break;
                case SERVER_PLATFORMS::RACKSPACENG_US:
                case SERVER_PLATFORMS::RACKSPACENG_UK:
                case SERVER_PLATFORMS::OPENSTACK:
                    $driver = 'swift';
                    break;
            }

            if ($storageGeneration == 2) {
                $behavior = $dbFarmRole->GetRoleObject()->getDbMsrBehavior();

                if (!isset($message->{$behavior}))
                    $message->{$behavior} = new stdClass();

                $message->{$behavior}->backup = new stdClass();
                $message->{$behavior}->backup->type = 'xtrabackup';
                $message->{$behavior}->backup->compressor = $params['compressor'];
                $message->{$behavior}->backup->backupType = $params['dataBundleType'];
                $message->{$behavior}->backup->cloudfsTarget = sprintf(
                    "%s://scalr-%s-%s-%s/data-bundles/%s/%s/",
                    $driver,
                    SCALR_ID,
                    $dbFarmRole->GetFarmObject()->EnvID,
                    $dbFarmRole->CloudLocation,
                    $dbFarmRole->FarmID,
                    $behavior
                );

                if ($params['dataBundleType'] == 'incremental') {
                    $previousManifest = $this->db->GetOne("SELECT manifest FROM storage_restore_configs WHERE farm_roleid = ? ORDER BY id DESC", array($dbFarmRole->ID));
                    $message->{$behavior}->backup->prevCloudfsSource = $previousManifest;
                }
            }

            $message->storageType = $storageType;

            $currentServer->SendMessage($message);

            $dbFarmRole->SetSetting(Scalr_Db_Msr::getConstant("DATA_BUNDLE_IS_RUNNING"), 1);
            $dbFarmRole->SetSetting(Scalr_Db_Msr::getConstant("DATA_BUNDLE_RUNNING_TS"), time());
            $dbFarmRole->SetSetting(Scalr_Db_Msr::getConstant("DATA_BUNDLE_SERVER_ID"), $currentServer->serverId);
        }

        public function getServerForDataBundle(DBFarmRole $dbFarmRole, $useSlave = false)
        {
            // perform data bundle on master
               $servers = $dbFarmRole->GetServersByFilter(array('status' => array(SERVER_STATUS::RUNNING)));
            $currentServer = null;
            $currentMetric = 0;
            foreach ($servers as $dbServer) {

                $isMaster = $dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER);

                if ($isMaster) {
                    $masterServer = $dbServer;
                    if (!$useSlave) {
                        $currentServer = $dbServer;
                        break;
                    }
                }

                if (!$isMaster) {
                    $currentServer = $dbServer;
                    break;
                }
            }

            if ($useSlave && !$currentServer)
                $currentServer = $masterServer;

            return $currentServer;
        }

        public function getServerForBackup(DBFarmRole $dbFarmRole)
        {
            $servers = $dbFarmRole->GetServersByFilter(array('status' => array(SERVER_STATUS::RUNNING)));
            $currentServer = null;
            $currentMetric = 0;
            foreach ($servers as $dbServer) {

                $isMaster = $dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER);

                if ($isMaster) {
                    $masterServer = $dbServer;
                    if (!$currentServer) {
                        $currentServer = $dbServer;
                    }
                }

                if (!$isMaster) {
                    $currentServer = $dbServer;
                    break;
                }
            }

            return $currentServer;
        }

        public function extendMessage(Scalr_Messaging_Msg $message, DBServer $dbServer)
        {
            $message = parent::extendMessage($message, $dbServer);

            try {
                $dbFarmRole = $dbServer->GetFarmRoleObject();
                $storageType = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE);
                $storageGeneration = $storageType == 'lvm' ? 2 : 1;
            } catch (Exception $e) {}

            switch (get_class($message))
            {
                case "Scalr_Messaging_Msg_HostInitResponse":

                    $dbMsrInfo = Scalr_Db_Msr_Info::init($dbFarmRole, $dbServer, $this->behavior);
                    $message->addDbMsrInfo($dbMsrInfo);

                    $config = $dbFarmRole->GetServiceConfiguration2($this->behavior);
                    if (!empty($config)) {
                        $message->{$this->behavior}->preset = array();
                        foreach ($config as $filename => $cfg) {
                            $file = new stdClass();
                            $file->file = new stdClass();
                            $file->file->name = $filename;
                            $file->file->settings = array();

                            foreach ($cfg as $k => $v) {
                                $setting = new stdClass();
                                $setting->setting = new stdClass();
                                $setting->setting->name = $k;
                                $setting->setting->value = $v;

                                $file->file->settings[] = $setting;
                            }
                            $message->{$this->behavior}->preset[] = $file;
                        }
                    }

                    if ($storageGeneration == 2) {

                        $message->volumeConfig = null;
                        $message->snapshotConfig = null;

                        $message->{$this->behavior}->volumeConfig = null;
                        $message->{$this->behavior}->snapshotConfig = null;

                        // Create volume configuration
                        $message->{$this->behavior}->volume = new stdClass();
                        $message->{$this->behavior}->volume->type = 'lvm';

                        switch ($dbFarmRole->Platform) {
                            case SERVER_PLATFORMS::EC2:
                                $diskType = 'ec2_ephemeral';
                                break;
                            case SERVER_PLATFORMS::GCE:
                                $diskType = 'gce_ephemeral';
                                break;

                            case SERVER_PLATFORMS::RACKSPACENG_UK:
                            case SERVER_PLATFORMS::RACKSPACENG_US:
                            case SERVER_PLATFORMS::OPENSTACK:
                                $diskType = 'loop';
                                break;
                        }

                        $message->{$this->behavior}->volume->pvs = array();
                        if ($diskType == 'loop') {
                            $message->{$this->behavior}->volume->pvs[] = array('type' => $diskType, 'size' => '75%root');
                        } else {
                            $volumes = $dbFarmRole->GetSetting(self::ROLE_DATA_STORAGE_LVM_VOLUMES);
                            $v = json_decode($volumes);
                            foreach ($v as $name => $size) {
                                $message->{$this->behavior}->volume->pvs[] = array('type' => $diskType, 'name' => $name);
                            }
                        }

                        $fs = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_FSTYPE);
                        if (!$fs)
                            $fs = 'ext3';

                        $message->{$this->behavior}->volume->vg = $this->behavior;
                          $message->{$this->behavior}->volume->name = 'data';
                          $message->{$this->behavior}->volume->size = '100%VG';
                          $message->{$this->behavior}->volume->fstype = $fs;

                        // Add restore configuration
                        $restore = $this->db->GetRow("SELECT manifest FROM storage_restore_configs WHERE farm_roleid = ? ORDER BY id DESC", array($dbFarmRole->ID));
                        if ($restore) {
                            $message->{$this->behavior}->restore = new stdClass();
                            $message->{$this->behavior}->restore->type = 'xtrabackup';
                            $message->{$this->behavior}->restore->cloudfsSource = $restore['manifest'];
                        }

                        switch($dbFarmRole->Platform) {
                            case SERVER_PLATFORMS::EC2:
                                $driver = 's3';
                                break;

                            case SERVER_PLATFORMS::GCE:
                                $driver = 'gcs';
                                break;
                            case SERVER_PLATFORMS::RACKSPACENG_US:
                            case SERVER_PLATFORMS::RACKSPACENG_UK:
                            case SERVER_PLATFORMS::OPENSTACK:
                                $driver = 'swift';
                                break;
                        }

                        // Add backup configuration
                        if (!$message->{$this->behavior}->restore) {
                            $message->{$this->behavior}->backup = new stdClass();
                            $message->{$this->behavior}->backup->type = 'xtrabackup';
                            $message->{$this->behavior}->backup->backupType = 'full';
                            $message->{$this->behavior}->backup->compressor = $dbFarmRole->GetSetting(self::ROLE_DATA_BUNDLE_COMPRESSION);
                            $message->{$this->behavior}->backup->cloudfsTarget = sprintf(
                                "%s://scalr-%s-%s-%s/data-bundles/%s/%s/",
                                $driver,
                                SCALR_ID,
                                $dbServer->envId,
                                $dbServer->GetCloudLocation(),
                                $dbServer->farmId,
                                $this->behavior
                            );
                        }
                    }

                    break;

                case "Scalr_Messaging_Msg_DbMsr_PromoteToMaster":

                    $dbMsrInfo = Scalr_Db_Msr_Info::init($dbFarmRole, $dbServer, $this->behavior);
                    $message->addDbMsrInfo($dbMsrInfo);

                    // Reset Slaves data bundle
                    $dbFarmRole->SetSetting(self::ROLE_NO_DATA_BUNDLE_FOR_SLAVES, 1);

                    $noDataBundle = $dbFarmRole->GetSetting(self::ROLE_NO_DATA_BUDNLE_ON_PROMOTE);
                    if ($noDataBundle)
                        $message->{$this->behavior}->noDataBundle = 1;


                    // IDCF using Cloudstack 2.X with very unstable volumes implementation.
                    // To avoid 500 errors during volumes re-attach we need to promote slaves to master with their own data.
                    if ($dbServer->platform == SERVER_PLATFORMS::IDCF) {
                        $message->{$this->behavior}->volumeConfig = null;
                        $message->{$this->behavior}->snapshotConfig = null;
                    }

                    if ($storageGeneration == 2) {

                        $message->volumeConfig = null;
                        $message->snapshotConfig = null;

                        $message->{$this->behavior}->volumeConfig = null;
                        $message->{$this->behavior}->snapshotConfig = null;

                        switch($dbFarmRole->Platform) {
                            case SERVER_PLATFORMS::EC2:
                                $driver = 's3';
                                break;

                            case SERVER_PLATFORMS::GCE:
                                $driver = 'gcs';
                                break;
                            case SERVER_PLATFORMS::RACKSPACENG_US:
                            case SERVER_PLATFORMS::RACKSPACENG_UK:
                            case SERVER_PLATFORMS::OPENSTACK:
                                $driver = 'swift';
                                break;
                        }

                        $message->{$this->behavior}->backup = new stdClass();
                        $message->{$this->behavior}->backup->type = 'xtrabackup';
                        $message->{$this->behavior}->backup->backupType = 'full';
                        $message->{$this->behavior}->backup->compressor = $dbFarmRole->GetSetting(self::ROLE_DATA_BUNDLE_COMPRESSION);
                        $message->{$this->behavior}->backup->cloudfsTarget = sprintf(
                            "%s://scalr-%s-%s-%s/data-bundles/%s/%s/",
                            $driver,
                            SCALR_ID,
                            $dbServer->envId,
                            $dbServer->GetCloudLocation(),
                            $dbServer->farmId,
                            $this->behavior
                        );
                    }

                    break;

                case "Scalr_Messaging_Msg_DbMsr_NewMasterUp":

                    $dbMsrInfo = Scalr_Db_Msr_Info::init($dbFarmRole, $dbServer, $this->behavior);
                    $message->addDbMsrInfo($dbMsrInfo);

                    if ($storageGeneration == 2) {

                        $message->{$this->behavior}->volumeConfig = null;
                        $message->{$this->behavior}->snapshotConfig = null;

                        $restore = $this->db->GetRow("SELECT manifest FROM storage_restore_configs WHERE farm_roleid = ? ORDER BY id DESC", array($dbFarmRole->ID));
                        if ($restore) {
                            $message->{$this->behavior}->restore = new stdClass();
                            $message->{$this->behavior}->restore->type = 'xtrabackup';
                            $message->{$this->behavior}->restore->cloudfsSource = $restore['manifest'];
                        }
                    }

                    break;
            }

            return $message;
        }

        private function updateBackupHistory(DBServer $dbServer, $operation, $status, $error = "")
        {
            $this->db->Execute("INSERT INTO services_db_backups_history SET
                `farm_role_id` = ?,
                `operation` = ?,
                `date` = NOW(),
                `status` = ?,
                `error` = ?
            ", array(
                $dbServer->farmRoleId,
                $operation,
                $status,
                $error
            ));

            $minId = $this->db->Execute("SELECT MIN(id) FROM services_db_backups_history WHERE farm_role_id = ? AND `operation` = ? ORDER BY id DESC LIMIT 0, 10", array(
                $dbServer->farmRoleId,
                $operation,
            ));
            if ($minId) {
                $this->db->Execute("DELETE FROM services_db_backups_history WHERE farm_role_id = ? AND `operation` = ? AND id < ?", array(
                    $dbServer->farmRoleId,
                    $operation,
                    $minId
                ));
            }
        }

        public function handleMessage(Scalr_Messaging_Msg $message, DBServer $dbServer)
        {
            parent::handleMessage($message, $dbServer);

            try {
                $dbFarmRole = $dbServer->GetFarmRoleObject();
                $storageType = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE);
                $storageGeneration = $storageType == 'lvm' ? 2 : 1;
            } catch (Exception $e) {}

            switch (get_class($message))
            {
                case "Scalr_Messaging_Msg_HostUp":

                    if ($message->dbType && in_array($message->dbType, array(ROLE_BEHAVIORS::REDIS, ROLE_BEHAVIORS::POSTGRESQL, ROLE_BEHAVIORS::MYSQL2, ROLE_BEHAVIORS::PERCONA, ROLE_BEHAVIORS::MARIADB)))
                    {
                        $dbMsrInfo = Scalr_Db_Msr_Info::init($dbFarmRole, $dbServer, $message->dbType);
                           $dbMsrInfo->setMsrSettings($message->{$message->dbType});
                        if ($message->{$message->dbType}->snapshotConfig) {
                            $dbFarmRole->SetSetting(self::ROLE_NO_DATA_BUNDLE_FOR_SLAVES, 0);
                        }

                        if ($message->{$message->dbType}->restore) {
                            $this->db->GetRow("INSERT INTO storage_restore_configs SET farm_roleid = ?, dtadded=NOW(), manifest = ?", array(
                                $dbFarmRole->ID,
                                $message->{$message->dbType}->restore->cloudfsSource
                            ));
                            $dbFarmRole->SetSetting(self::ROLE_NO_DATA_BUNDLE_FOR_SLAVES, 0);
                            $dbFarmRole->SetSetting(Scalr_Db_Msr::DATA_BUNDLE_LAST_TS, time());
                            $dbFarmRole->SetSetting(Scalr_Db_Msr::DATA_BUNDLE_IS_RUNNING, 0);
                        }

                        if ($message->{$message->dbType}->masterPassword) {
                            $dbFarmRole->SetSetting(self::ROLE_MASTER_PASSWORD, $message->{$message->dbType}->masterPassword);
                        }
                    }

                    break;

                case "Scalr_Messaging_Msg_DbMsr_PromoteToMasterResult":

                    if ($message->{$message->dbType}->restore) {
                        $this->db->GetRow("INSERT INTO storage_restore_configs SET farm_roleid = ?, dtadded=NOW(), manifest = ?", array(
                            $dbFarmRole->ID,
                            $message->{$message->dbType}->restore->cloudfsSource
                        ));

                        $dbFarmRole->SetSetting(self::ROLE_NO_DATA_BUNDLE_FOR_SLAVES, 0);
                    }

                    if (Scalr_Db_Msr::onPromoteToMasterResult($message, $dbServer)) {

                        if ($message->{$this->behavior}->snapshotConfig) {
                            $dbFarmRole->SetSetting(self::ROLE_NO_DATA_BUNDLE_FOR_SLAVES, 0);
                        }

                           Scalr::FireEvent($dbServer->farmId, new NewDbMsrMasterUpEvent($dbServer));
                    }

                    break;

                case "Scalr_Messaging_Msg_DbMsr_CreateDataBundleResult":

                    if ($message->status == "ok") {
                        if ($message->{$message->dbType}->restore) {
                                if ($message->{$message->dbType}->restore->backupType == 'incremental') {
                                    $parentManifest = $this->db->GetOne("SELECT manifest FROM storage_restore_configs WHERE farm_roleid = ? ORDER BY id DESC", array($dbFarmRole->ID));
                                }

                                $this->db->GetRow("INSERT INTO storage_restore_configs SET farm_roleid = ?, dtadded=NOW(), manifest = ?, type = ?, parent_manifest = ?", array(
                                $dbFarmRole->ID,
                                $message->{$message->dbType}->restore->cloudfsSource,
                                $message->{$message->dbType}->restore->backupType,
                                $parentManifest
                            ));
                        }

                        $dbFarmRole->SetSetting(self::ROLE_NO_DATA_BUNDLE_FOR_SLAVES, 0);

                           Scalr_Db_Msr::onCreateDataBundleResult($message, $dbServer);
                       }
                       else {
                           $dbFarmRole->SetSetting(Scalr_Db_Msr::DATA_BUNDLE_IS_RUNNING, 0);
                               //TODO: store last error
                       }

                       $this->updateBackupHistory($dbServer, 'bundle', $message->status, $message->lastError);

                    break;

                case "Scalr_Messaging_Msg_DbMsr_CreateBackupResult":

                    if ($message->status == "ok")
                           Scalr_Db_Msr::onCreateBackupResult($message, $dbServer);
                       else {
                           $dbFarmRole->SetSetting(Scalr_Db_Msr::DATA_BACKUP_IS_RUNNING, 0);

                       }

                       $this->updateBackupHistory($dbServer, 'backup', $message->status, $message->lastError);

                    break;
            }
        }
    }
