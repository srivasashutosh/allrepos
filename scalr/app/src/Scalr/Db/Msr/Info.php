<?php
abstract class Scalr_Db_Msr_Info
{
    protected  $replicationMaster,
        $volumeConfig,
        $snapshotConfig;

    /**
     * @var DBServer
     */
    protected $dbServer;

    /**
     * @var DBFarmRole
     */
    protected $dbFarmRole;

    public $databaseType;

    public static function init(DBFarmRole $dbFarmRole, DBServer $dbServer, $dbType) {
        switch ($dbType) {
            /*
            case Scalr_Db_Msr::DB_TYPE_MYSQL:
                return new Scalr_Db_Msr_Mysql_Info($dbFarmRole, $dbServer);
                break;
            */

            case Scalr_Db_Msr::DB_TYPE_PERCONA:
            case Scalr_Db_Msr::DB_TYPE_MARIADB:
            case Scalr_Db_Msr::DB_TYPE_MYSQL2:
                return new Scalr_Db_Msr_Mysql2_Info($dbFarmRole, $dbServer, $dbType);
                break;

            case Scalr_Db_Msr::DB_TYPE_POSTGRESQL:
                return new Scalr_Db_Msr_Postgresql_Info($dbFarmRole, $dbServer);
                break;

            case Scalr_Db_Msr::DB_TYPE_REDIS:
                return new Scalr_Db_Msr_Redis_Info($dbFarmRole, $dbServer);
                break;
            default:
                throw new Exception("{$dbType} not supported by DbMsr system");
                break;
        }
    }

    public function setMsrSettings($settings)
    {
        if ($settings->volumeConfig) {
            try {
                $storageVolume = Scalr_Storage_Volume::init();
                try {
                    $storageVolume->loadById($settings->volumeConfig->id);
                    $storageVolume->setConfig($settings->volumeConfig);
                    $storageVolume->save();
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'not found')) {
                        $storageVolume->loadBy(array(
                            'id'			=> $settings->volumeConfig->id,
                            'client_id'		=> $this->dbServer->clientId,
                            'env_id'		=> $this->dbServer->envId,
                            'name'			=> "'{$this->databaseType}' data volume",
                            'type'			=> $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE),
                            'platform'		=> $this->dbServer->platform,
                            'size'			=> $settings->volumeConfig->size,
                            'fstype'		=> $settings->volumeConfig->fstype,
                            'purpose'		=> $this->databaseType,
                            'farm_roleid'	=> $this->dbFarmRole->ID,
                            'server_index'	=> $this->dbServer->index
                        ));
                        $storageVolume->setConfig($settings->volumeConfig);
                        $storageVolume->save(true);
                    }
                    else
                        throw $e;
                }

                $this->dbFarmRole->SetSetting(Scalr_Db_Msr::VOLUME_ID, $storageVolume->id);
            }
            catch(Exception $e) {
                $this->logger->error(new FarmLogMessage($this->dbServer->farmId, "Cannot save storage volume: {$e->getMessage()}"));
            }
        }

        if ($settings->snapshotConfig) {
            try {
                $storageSnapshot = Scalr_Model::init(Scalr_Model::STORAGE_SNAPSHOT);
                try {
                    $storageSnapshot->loadById($settings->snapshotConfig->id);
                    $storageSnapshot->setConfig($settings->snapshotConfig);
                    $storageSnapshot->save();
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'not found')) {
                        $storageSnapshot->loadBy(array(
                            'id'			=> $settings->snapshotConfig->id,
                            'client_id'		=> $this->dbServer->clientId,
                               'farm_id'		=> $this->dbServer->farmId,
                            'farm_roleid'	=> $this->dbServer->farmRoleId,
                            'env_id'		=> $this->dbServer->envId,
                            'name'			=> sprintf(_("'{$this->databaseType}' data bundle #%s"), $settings->snapshotConfig->id),
                            'type'			=> $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE),
                            'platform'		=> $this->dbServer->platform,
                            'description'	=> sprintf(_("'{$this->databaseType}' data bundle created on Farm '%s' -> Role '%s'"),
                                $this->dbFarmRole->GetFarmObject()->name,
                                $this->dbFarmRole->GetRoleObject()->name
                            ),
                            'service'		=> $this->databaseType
                        ));

                        $storageSnapshot->setConfig($settings->snapshotConfig);
                        $storageSnapshot->save(true);
                       }
                       else
                        throw $e;
                }

                $this->dbFarmRole->SetSetting(Scalr_Db_Msr::SNAPSHOT_ID, $storageSnapshot->id);
            }
            catch(Exception $e) {
                $this->logger->error(new FarmLogMessage($event->DBServer->farmId, "Cannot save storage snapshot: {$e->getMessage()}"));
            }
        }
    }

    public function __construct(DBFarmRole $dbFarmRole, DBServer $dbServer, $type = null) {
        $this->dbFarmRole = $dbFarmRole;
        $this->dbServer = $dbServer;
        $this->logger = Logger::getLogger(__CLASS__);

        $this->replicationMaster = (int)$dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER);

        $this->buildStorageSettings();
    }

    public function getMessageProperties() {
        $retval = new stdClass();
        $retval->replicationMaster = $this->replicationMaster;
        $retval->volumeConfig = $this->volumeConfig;
        $retval->snapshotConfig = $this->snapshotConfig;

        return $retval;
    }

    protected function getFreshVolumeConfig()
    {
        $volumeConfig = new stdClass();
        $volumeConfig->type = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE);

        $fsType = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_FSTYPE);
        if ($fsType) {
            $volumeConfig->fstype = $fsType;
        }

        // For any Block storage APIs
        if ($volumeConfig->type == MYSQL_STORAGE_ENGINE::RAID_EBS) {

            $volumeConfig->type = 'raid';
            $volumeConfig->vg = $this->databaseType;
            $volumeConfig->level = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_RAID_LEVEL);
            $volumeConfig->disks = array();

            for ($i = 1; $i <= $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_RAID_DISKS_COUNT); $i++) {
                $dsk = new stdClass();
                $dsk->type = 'ebs';
                $dsk->size = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_RAID_DISK_SIZE);

                if ($this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_RAID_EBS_DISK_TYPE) == 'io1') {
                    $dsk->volumeType = 'io1';
                    $dsk->iops = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_RAID_EBS_DISK_IOPS);
                }

                $volumeConfig->disks[] = $dsk;
            }

            $volumeConfig->snapPv = new stdClass();
            $volumeConfig->snapPv->type = 'ebs';
            $volumeConfig->snapPv->size = 1;

        } else if ($volumeConfig->type == MYSQL_STORAGE_ENGINE::CINDER) {

            $volumeConfig->size = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_CINDER_SIZE);

        } else if ($volumeConfig->type == MYSQL_STORAGE_ENGINE::GCE_PERSISTENT) {

            $volumeConfig->size = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_GCED_SIZE);

        } else if ($volumeConfig->type == MYSQL_STORAGE_ENGINE::EPH) {

            if ($this->dbFarmRole->Platform == SERVER_PLATFORMS::RACKSPACE) {
                $storageProvider = 'cf';

                $volumeConfig->disk = new stdClass();
                $volumeConfig->disk->type = 'loop';
                $volumeConfig->disk->size = '75%root';
            } elseif (in_array($this->dbFarmRole->Platform, array(SERVER_PLATFORMS::OPENSTACK, SERVER_PLATFORMS::RACKSPACENG_UK, SERVER_PLATFORMS::RACKSPACENG_US))) {
                $storageProvider = 'swift';

                $volumeConfig->disk = new stdClass();
                $volumeConfig->disk->type = 'loop';
                $volumeConfig->disk->size = '75%root';
            } elseif ($this->dbFarmRole->Platform == SERVER_PLATFORMS::GCE) {
                $storageProvider = 'gcs';

                $volumeConfig->disk = array(
                    'type' => 'gce_ephemeral',
                    'name' => 'ephemeral-disk-0'
                );
                $volumeConfig->size = "80%";
            } elseif ($this->dbFarmRole->Platform == SERVER_PLATFORMS::EC2) {
                $storageProvider = 's3';

                $volumeConfig->disk = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_EPH_DISK);
                $volumeConfig->size = "80%";
            }

            $volumeConfig->snap_backend = sprintf("%s://scalr-%s-%s/data-bundles/%s/%s",
                $storageProvider,
                $this->dbFarmRole->GetFarmObject()->EnvID,
                $this->dbFarmRole->CloudLocation,
                $this->dbFarmRole->FarmID,
                $this->databaseType
            );
            $volumeConfig->vg = $this->databaseType;

        } else {
            $volumeConfig->size = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_EBS_SIZE);

            if ($this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_EBS_TYPE) == 'io1') {
                $volumeConfig->volumeType = 'io1';
                $volumeConfig->iops = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_EBS_IOPS);
            }
        }

        return $volumeConfig;
    }

    protected function buildStorageSettings()
    {
        if ($this->dbFarmRole->GetSetting(Scalr_Db_Msr::VOLUME_ID) && $this->replicationMaster)
        {
            try {
                $volume = Scalr_Storage_Volume::init()->loadById(
                    $this->dbFarmRole->GetSetting(Scalr_Db_Msr::VOLUME_ID)
                );

                $this->volumeConfig = $volume->getConfig();
            } catch (Exception $e) {}
        }

        /***
        * For Rackspace we ALWAYS need snapsjot_config for mysql
        * ***/
        if ($this->dbFarmRole->GetSetting(Scalr_Db_Msr::SNAPSHOT_ID)) {
            try {
                $snapshotConfig = Scalr_Model::init(Scalr_Model::STORAGE_SNAPSHOT)->loadById(
                    $this->dbFarmRole->GetSetting(Scalr_Db_Msr::SNAPSHOT_ID)
                );

                $this->snapshotConfig = $snapshotConfig->getConfig();

                if ($this->snapshotConfig) {
                    if ($this->snapshotConfig->type == MYSQL_STORAGE_ENGINE::EPH) {
                        if ($this->dbFarmRole->Platform == SERVER_PLATFORMS::EC2) {
                            if (!isset($this->snapshotConfig->disk))
                                $this->snapshotConfig->disk = new stdClass();

                            $this->snapshotConfig->disk->device = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_EPH_DISK);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->logger->error(new FarmLogMessage($this->dbServer->farmId, "Cannot get snaphotConfig for hostInit message: {$e->getMessage()}"));
            }
        }

        //TODO:
        /** If new role and there is no volume, we need to create a new one **/
        if ($this->replicationMaster)
        {
            if (!$this->volumeConfig) {
                $this->volumeConfig = $this->getFreshVolumeConfig();
            } else {
                if ($this->volumeConfig->type == MYSQL_STORAGE_ENGINE::EPH) {
                    if ($this->dbFarmRole->Platform == SERVER_PLATFORMS::EC2) {
                        $this->volumeConfig->disk->device = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_EPH_DISK);
                    }
                }
            }
        } else {
            $this->volumeConfig = $this->getFreshVolumeConfig();
        }
    }
}