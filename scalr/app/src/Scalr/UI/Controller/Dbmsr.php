<?php
class Scalr_UI_Controller_Dbmsr extends Scalr_UI_Controller
{
    public static function getPermissionDefinitions()
    {
        return array();
    }

    public function xGrowStorageAction()
    {
        $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
        $this->user->getPermissions()->validate($dbFarmRole->GetFarmObject());

        $behavior = Scalr_Role_Behavior::loadByName($dbFarmRole->GetRoleObject()->getDbMsrBehavior());
        $master = $behavior->getMasterServer($dbFarmRole);
        if ($master) {
            $port = $master->GetProperty(SERVER_PROPERTIES::SZR_API_PORT);
            if (!$port)
                $port = 8010;

            try {
                $client = Scalr_Net_Scalarizr_Client::getClient($master, Scalr_Net_Scalarizr_Client::NAMESPACE_MYSQL, $port);

                $volume = Scalr_Storage_Volume::init()->loadById(
                    $dbFarmRole->GetSetting(Scalr_Db_Msr::VOLUME_ID)
                );

                if ($volume->type != MYSQL_STORAGE_ENGINE::EBS && $volume->type != MYSQL_STORAGE_ENGINE::RAID_EBS && $volume->type != 'raid')
                    throw new Exception("Grow feature available only for EBS and RAID storage types");

                if ($volume->size >= (int)$this->getParam('newSize'))
                    throw new Exception("New size should be greather than current one ({$volume->size} GB)");

                $volumeConfig = $volume->getConfig();
                $platformAccessData = PlatformFactory::NewPlatform($dbFarmRole->Platform)->GetPlatformAccessData($this->environment, $master);

                $result = $client->growStorage($volumeConfig, $this->getParam('newSize'), $platformAccessData);

                // Do not remove. We need to wait a bit before operation will be registered in scalr.
                sleep(2);

                $this->response->data(array('operationId' => $result));

            } catch (Exception $e) {
                throw new Exception("Cannot grow storage: {$e->getMessage()}");
            }
        } else
            throw new Exception("Impossible to increase storage size. No running master server.");
    }

    public function xSetupPmaAccessAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'farmRoleId' => array('type' => 'int')
        ));

        $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
        $this->user->getPermissions()->validate($dbFarm);

        $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
        if ($dbFarmRole->FarmID != $dbFarm->ID)
            throw new Exception("Role not found");

        $dbFarmRole->ClearSettings("mysql.pma");

        $masterDbServer = null;
        foreach ($dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING)) as $dbServer) {

            if ($dbFarmRole->GetRoleObject()->getDbMsrBehavior())
                $isMaster = $dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER);
            else
                $isMaster = $dbServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER);

            if ($isMaster) {
                $masterDbServer = $dbServer;
                break;
            }
        }

        if ($masterDbServer) {
            $time = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_PMA_REQUEST_TIME);
            if (!$time || $time+3600 < time()) {
                $msg = new Scalr_Messaging_Msg_Mysql_CreatePmaUser($dbFarmRole->ID, \Scalr::config('scalr.pma_instance_ip_address'));
                $masterDbServer->SendMessage($msg);

                $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_PMA_REQUEST_TIME, time());
                $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_PMA_REQUEST_ERROR, "");

                $this->response->success();
            }
            else
                throw new Exception("MySQL access credentials for PMA already requested. Please wait...");
        }
        else
            throw new Exception("There is no running MySQL master. Please wait until master starting up.");
    }

    public function xCancelBackupAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'farmRoleId' => array('type' => 'int')
        ));

        $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
        $this->user->getPermissions()->validate($dbFarm);

        $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
        if ($dbFarmRole->FarmID != $dbFarm->ID)
            throw new Exception("Role not found");

        if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
            throw new Exception("Operation not supported for this role");
        } else {
            if ($dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BACKUP_IS_RUNNING) == 1) {
                $bundleServerId = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BACKUP_SERVER_ID);

                $dbServer = DBServer::LoadByID($bundleServerId);
                $dbServer->SendMessage(new Scalr_Messaging_Msg_DbMsr_CancelBackup());
            }
        }

        $this->response->success('Backup cancelation successfully initiated');
        return;
    }

    public function xCancelDataBundleAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'farmRoleId' => array('type' => 'int')
        ));

        $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
        $this->user->getPermissions()->validate($dbFarm);

        $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
        if ($dbFarmRole->FarmID != $dbFarm->ID)
            throw new Exception("Role not found");

        if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
            throw new Exception("Operation not supported for this role");
        } else {
            if ($dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BUNDLE_IS_RUNNING) == 1) {
                $bundleServerId = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BUNDLE_SERVER_ID);

                $dbServer = DBServer::LoadByID($bundleServerId);
                $dbServer->SendMessage(new Scalr_Messaging_Msg_DbMsr_CancelDataBundle());
            }
        }

        $this->response->success('Data bundle cancelation successfully initiated');
        return;
    }

    public function xCreateDataBundleAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'farmRoleId' => array('type' => 'int'),
            'bundleType'
        ));

        $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
        $this->user->getPermissions()->validate($dbFarm);

        $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
        if ($dbFarmRole->FarmID != $dbFarm->ID)
            throw new Exception("Role not found");

        if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
            foreach ($dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING)) as $dbServer) {
                if ($dbServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER)) {

                    if ($dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_IS_BUNDLE_RUNNING) == 1)
                        throw new Exception("Data bundle already in progress");

                    $dbServer->SendMessage(new Scalr_Messaging_Msg_Mysql_CreateDataBundle());

                    $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_IS_BUNDLE_RUNNING, 1);
                    $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_BUNDLE_SERVER_ID, $dbServer->serverId);

                    $this->response->success('Data bundle successfully initiated');
                    return;
                }
            }
        } else {
            $behavior = Scalr_Role_Behavior::loadByName($dbFarmRole->GetRoleObject()->getDbMsrBehavior());
            $behavior->createDataBundle($dbFarmRole, array(
                'dataBundleType' => $this->getParam('bundleType'),
                'compressor' => $this->getParam('compressor'),
                'useSlave' => $this->getParam('useSlave'))
            );
            $this->response->success('Data bundle successfully initiated');
            return;
        }

        $this->response->failure('Scalr unable to initiate data bundle. No running replication master found.');
    }

    public function xCreateBackupAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'farmRoleId' => array('type' => 'int')
        ));

        $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
        $this->user->getPermissions()->validate($dbFarm);

        $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
        if ($dbFarmRole->FarmID != $dbFarm->ID)
            throw new Exception("Role not found");

        if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
            if ($dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_IS_BCP_RUNNING) == 1)
                throw new Exception("Backup already in progress");

            foreach ($dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING)) as $dbServer) {
                if (!$dbServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER))
                    $slaveDbServer = $dbServer;
                else
                    $masterDbServer = $dbServer;
            }

            if (!$slaveDbServer)
                $slaveDbServer = $masterDbServer;

            if ($slaveDbServer) {
                $slaveDbServer->SendMessage(new Scalr_Messaging_Msg_Mysql_CreateBackup($dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_ROOT_PASSWORD)));

                $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_IS_BCP_RUNNING, 1);
                $dbFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_BCP_SERVER_ID, $slaveDbServer->serverId);

                $this->response->success('Backup successfully initiated');
                return;
            }
        } else {
            $behavior = Scalr_Role_Behavior::loadByName($dbFarmRole->GetRoleObject()->getDbMsrBehavior());
            $behavior->createBackup($dbFarmRole);
            $this->response->success('Backup successfully initiated');
            return;
        }

        $this->response->failure('Scalr unable to initiate data backup. No running replication master found.');
    }

    private function getMySqlReplicationStatus($type, $ip, $username, $password)
    {
        $conn = NewADOConnection("mysqli");
        $conn->Connect($ip, $username, $password, null);
        $conn->SetFetchMode(ADODB_FETCH_ASSOC);

        $r = $conn->GetRow("SHOW {$type} STATUS");

        unset($conn);

        return $r;
    }

    public function statusAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'farmRoleId' => array('type' => 'int'),
            'type'
        ));

        $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
        $this->user->getPermissions()->validate($dbFarm);

        if ($this->getParam('farmRoleId')) {
            $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
            if ($dbFarmRole->FarmID != $dbFarm->ID)
                throw new Exception("Role not found");
        }
        elseif ($this->getParam('type')) {
            foreach ($dbFarm->GetFarmRoles() as $sDbFarmRole) {
                if ($sDbFarmRole->GetRoleObject()->hasBehavior($this->getParam('type'))) {
                    $dbFarmRole = $sDbFarmRole;
                    break;
                }
            }

            if (!$dbFarmRole)
                throw new Exception("Role not found");

        } else {
            throw new Scalr_UI_Exception_NotFound();
        }

        $data = array(
            'farmRoleId' => $dbFarmRole->ID,
            'farmHash' => $dbFarm->Hash,
            'pmaAccessConfigured' => false,
            'staticDnsSupported' => \Scalr::config('scalr.dns.static.enabled'),
        );

        $data['backupsNotSupported'] = in_array($dbFarmRole->Platform, array(
            SERVER_PLATFORMS::CLOUDSTACK,
            SERVER_PLATFORMS::IDCF,
            SERVER_PLATFORMS::UCLOUD
        ));

        if ($dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_PMA_USER))
            $data['pmaAccessConfigured'] = true;
        else
        {

            $errmsg = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_PMA_REQUEST_ERROR);
            if (!$errmsg)
            {
                $time = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_PMA_REQUEST_TIME);
                if ($time)
                {
                    if ($time+3600 < time())
                        $data['pmaAccessError'] = _("Scalr didn't receive auth info from MySQL instance. Please check that MySQL running and Scalr has access to it.");
                    else
                        $data['pmaAccessSetupInProgress'] = true;
                }
            } else
                $data['pmaAccessError'] = $errmsg;
        }

        //TODO: Legacy code. Move to DB_MSR
        if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
            $data['dbType'] = Scalr_Db_Msr::DB_TYPE_MYSQL;

            $data['dtLastBundle'] = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_LAST_BUNDLE_TS) ? Scalr_Util_DateTime::convertTz((int)$dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_LAST_BUNDLE_TS), 'd M Y \a\\t H:i:s') : 'Never';
            $data['dtLastBackup'] = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_LAST_BCP_TS) ? Scalr_Util_DateTime::convertTz((int)$dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_LAST_BCP_TS), 'd M Y \a\\t H:i:s') : 'Never';

            $data['additionalInfo']['MasterUsername'] = 'scalr';
            $data['additionalInfo']['MasterPassword'] = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_ROOT_PASSWORD);

            $slaveNumber = 0;

            foreach ($dbFarmRole->GetServersByFilter() as $dbServer) {
                if ($dbServer->status != SERVER_STATUS::RUNNING) {
                    //TODO:
                    continue;
                }

                if ($dbServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER) == 1)
                {
                    $data['isBundleRunning'] = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_IS_BUNDLE_RUNNING);
                    $data['bundleServerId'] = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_BUNDLE_SERVER_ID);

                    if ($data['isBundleRunning']) {
                        $opId = $this->db->GetOne("SELECT id FROM server_operations WHERE server_id = ? AND name = ? AND status = ? ORDER BY timestamp DESC", array(
                            $data['bundleServerId'], 'MySQL data bundle', 'running'
                        ));
                    } elseif ($data['bundleServerId']) {
                        $opId = $this->db->GetOne("SELECT id FROM server_operations WHERE server_id = ? AND name = ? ORDER BY timestamp DESC", array(
                            $data['bundleServerId'], 'MySQL data bundle'
                        ));
                    }

                    if ($opId)
                        $data['bundleOperationId'] = $opId;
                }

                   $data['isBackupRunning'] = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_IS_BCP_RUNNING);
                $data['backupServerId'] = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_BCP_SERVER_ID);

                if (!$data['backupOperationId']) {
                    if ($data['isBackupRunning']) {
                        $opId = $this->db->GetOne("SELECT id FROM server_operations WHERE server_id = ? AND name = ? AND status = ? ORDER BY timestamp DESC", array(
                            $data['backupServerId'], 'MySQL backup', 'running'
                        ));
                    } elseif ($data['backupServerId']) {
                        $opId = $this->db->GetOne("SELECT id FROM server_operations WHERE server_id = ? AND name = ? ORDER BY timestamp DESC", array(
                            $data['backupServerId'], 'MySQL backup'
                        ));
                    }

                    if ($opId)
                        $data['backupOperationId'] = $opId;
                }

                try
                   {
                       $isCloudstack = in_array($dbFarmRole->Platform, array(SERVER_PLATFORMS::CLOUDSTACK, SERVER_PLATFORMS::IDCF, SERVER_PLATFORMS::UCLOUD));
                       $isMaster = ($dbServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER) == 1);

                       if (!$isCloudstack) {
                           $rStatus = $this->getMySqlReplicationStatus($isMaster ? 'MASTER' : 'SLAVE', $dbServer->remoteIp, 'scalr_stat', $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_STAT_PASSWORD));
                       }

                    if ($isMaster) {
                           $MasterPosition = $rStatus['Position'];
                           $master_ip = $dbServer->remoteIp;
                           $master_iid = $dbServer->serverId;
                    }
                       else {
                           $num = ++$slaveNumber;
                           $SlavePosition = $rStatus['Exec_Master_Log_Pos'];
                       }

                       $d = array(
                           "serverId" => $dbServer->serverId,
                           "localIp" => $dbServer->localIp,
                           "remoteIp" => $dbServer->remoteIp,
                           "replicationRole" => $isMaster ? 'Master' : "Slave #{$num}"
                       );

                       if (!$isCloudstack) {
                           $d['data'] = $rStatus;
                           $d['masterPosition'] = $MasterPosition;
                           $d['slavePosition'] = $SlavePosition;
                       }

                       $data["replicationStatus"][] = $d;
                   }
                   catch(Exception $e)
                   {
                       $data["replicationStatus"][] = array(
                           "serverId" => $dbServer->serverId,
                           "localIp" => $dbServer->localIp,
                           "remoteIp" => $dbServer->remoteIp,
                           "error" => ($e->msg) ? $e->msg : $e->getMessage(),
                           "replicationRole" => $isMaster ? 'Master' : 'Slave'
                       );
                   }
            }

        } else {

            $data['dbType'] = $dbFarmRole->GetRoleObject()->getDbMsrBehavior();
            if (!$data['dbType'])
                $this->response->failure("Unknown db type");

            $behavior = Scalr_Role_Behavior::loadByName($data['dbType']);
            $masterServer = $behavior->getMasterServer($dbFarmRole);


            // Get Stoarge usage
            $size = array(
                'total' => -1,
                'used' => -1,
                'free' => -1
            );

            if ($masterServer) {
                try {
                    $port = $masterServer->GetProperty(SERVER_PROPERTIES::SZR_API_PORT);
                    if (!$port) $port = 8010;

                    $client = Scalr_Net_Scalarizr_Client::getClient($masterServer, Scalr_Net_Scalarizr_Client::NAMESPACE_SYSTEM, $port);

                    if ($data['dbType'] == ROLE_BEHAVIORS::REDIS)
                        $mpoint = '/mnt/redisstorage';
                    elseif ($data['dbType'] == ROLE_BEHAVIORS::POSTGRESQL)
                        $mpoint = '/mnt/pgstorage';
                    else
                        $mpoint = '/mnt/dbstorage';

                    $usage = (array)$client->statvfs(array($mpoint));
                    $size = (array)$usage[$mpoint];

                    if ($size['total']) {
                        $size['used'] = $size['total'] - $size['free'];

                        // Convert KB to GB
                        foreach ($size as $k=>$v) {
                            $size[$k] = round($v / 1024 / 1024, 2);
                        }
                    }

                } catch (Exception $e) {
                    $this->response->varDump($e->getMessage());
                }
            }

            $data['storage'] = array(
                'engine' => $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE),
                'id'     => $dbFarmRole->GetSetting(Scalr_Db_Msr::VOLUME_ID) ? $dbFarmRole->GetSetting(Scalr_Db_Msr::VOLUME_ID) : '',
                'fs'     => $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_FSTYPE),
                'size'   => $size
            );

            switch ($data['storage']['engine']) {
                case MYSQL_STORAGE_ENGINE::EBS:
                    $data['storage']['engineName'] = 'Single EBS volume';
                    break;
                case MYSQL_STORAGE_ENGINE::RAID_EBS:
                    $data['storage']['engineName'] = sprintf('RAID %s on %s EBS volumes',
                        $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_RAID_LEVEL),
                        $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_RAID_DISKS_COUNT)
                    );
                    break;
                case MYSQL_STORAGE_ENGINE::LVM:
                    $data['storage']['engineName'] = 'LVM on ephemeral device(s)';
                    break;
                case MYSQL_STORAGE_ENGINE::EPH:
                    $data['storage']['engineName'] = 'Ephemeral device';
                    break;
                case MYSQL_STORAGE_ENGINE::CSVOL:
                    $data['storage']['engineName'] = 'Single Cloudstack volume';
                    break;
                default:
                    $data['storage']['engineName'] = $data['storage']['engine'];
                    break;
            }

            $data['additionalInfo']['MasterUsername'] = 'scalr';

            if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::POSTGRESQL)) {
                $data['additionalInfo']['MasterPassword'] = $dbFarmRole->GetSetting(Scalr_Db_Msr_Postgresql::ROOT_PASSWORD);
                $name = 'PostgreSQL';
            } elseif ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::REDIS)) {

                $ports = @json_decode($dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::PORTS_ARRAY));
                $passwords = @json_decode($dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::PASSWD_ARRAY));

                if (!$ports && !$passwords)
                    $data['additionalInfo']['MasterPassword'] = $dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::MASTER_PASSWORD);
                else {
                    $data['additionalInfo']['MasterPassword'] = "";
                    foreach ($ports as $i=>$port) {
                        $data['additionalInfo']['MasterPassword'] .= "Port {$port}: {$passwords[$i]}<br>";
                    }
                }


                $name = 'Redis';
            } elseif ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL2)) {
                $data['additionalInfo']['MasterPassword'] = $dbFarmRole->GetSetting(Scalr_Db_Msr_Mysql2::ROOT_PASSWORD);
                $name = 'MySQL';
            } elseif ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::PERCONA)) {
                $data['additionalInfo']['MasterPassword'] = $dbFarmRole->GetSetting(Scalr_Db_Msr_Percona::ROOT_PASSWORD);
                $name = 'Percona Server';
            } elseif ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MARIADB)) {
                $data['additionalInfo']['MasterPassword'] = $dbFarmRole->GetSetting(Scalr_Db_Msr_Mariadb::ROOT_PASSWORD);
                $name = 'MariaDB';
            }

            if (!$data['additionalInfo']['MasterPassword'])
                $data['additionalInfo']['MasterPassword'] = "";

            $data['name'] = $name;


            if ($dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_ENGINE) == 'lvm') {
                $data['noDataBundleForSlaves'] = ($dbFarmRole->GetSetting(Scalr_Role_DbMsrBehavior::ROLE_NO_DATA_BUNDLE_FOR_SLAVES)) ? true : false;
            }

            $data['dtLastBackup'] = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BACKUP_LAST_TS) ? Scalr_Util_DateTime::convertTz((int)$dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BACKUP_LAST_TS), 'd M Y \a\\t H:i:s') : 'Never';
            $data['dtLastBundle'] = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BUNDLE_LAST_TS) ? Scalr_Util_DateTime::convertTz((int)$dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BUNDLE_LAST_TS), 'd M Y \a\\t H:i:s') : 'Never';

            $slaveNumber = 0;

            foreach ($dbFarmRole->GetServersByFilter() as $dbServer) {
                if ($dbServer->status != SERVER_STATUS::RUNNING) {
                    //TODO:
                    continue;
                }

                if ($dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER) == 1) {
                    $data['isBundleRunning'] = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BUNDLE_IS_RUNNING);
                    $data['bundleServerId'] = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BUNDLE_SERVER_ID);

                    if ($data['isBundleRunning']) {
                        $opId = $this->db->GetOne("SELECT id FROM server_operations WHERE server_id = ? AND name = ? AND status = ? ORDER BY timestamp DESC", array(
                            $data['bundleServerId'], "{$name} data bundle", 'running'
                        ));
                    } elseif ($data['bundleServerId']) {
                        $opId = $this->db->GetOne("SELECT id FROM server_operations WHERE server_id = ? AND name = ? ORDER BY timestamp DESC", array(
                            $data['bundleServerId'], "{$name} data bundle"
                        ));
                    }

                    if ($opId)
                        $data['bundleOperationId'] = $opId;
                }

                   $data['isBackupRunning'] = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BACKUP_IS_RUNNING);
                $data['backupServerId'] = $dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_BACKUP_SERVER_ID);

                if (!$data['backupOperationId']) {
                    if ($data['isBackupRunning']) {
                        $opId = $this->db->GetOne("SELECT id FROM server_operations WHERE server_id = ? AND name = ? AND status = ? ORDER BY timestamp DESC", array(
                            $data['backupServerId'], "{$name} backup", 'running'
                        ));
                    } elseif ($data['backupServerId']) {
                        $opId = $this->db->GetOne("SELECT id FROM server_operations WHERE server_id = ? AND name = ? ORDER BY timestamp DESC", array(
                            $data['backupServerId'], "{$name} backup"
                        ));
                    }

                    if ($opId)
                        $data['backupOperationId'] = $opId;
                }

                try {

                    $isCloudstack = in_array($dbFarmRole->Platform, array(SERVER_PLATFORMS::CLOUDSTACK, SERVER_PLATFORMS::IDCF, SERVER_PLATFORMS::UCLOUD));
                       $isMaster = ($dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER) == 1);

                       if (!$isCloudstack && in_array($this->getParam('type'), array(ROLE_BEHAVIORS::MYSQL2, ROLE_BEHAVIORS::PERCONA, ROLE_BEHAVIORS::MARIADB))) {
                           $password = $dbFarmRole->GetSetting(Scalr_Db_Msr_Mysql2::STAT_PASSWORD);
                           $rStatus = $this->getMySqlReplicationStatus($isMaster ? 'MASTER' : 'SLAVE', $dbServer->remoteIp, 'scalr_stat', $password);
                       }

                    if ($isMaster) {
                           $MasterPosition = $rStatus['Position'];
                           $master_ip = $dbServer->remoteIp;
                           $master_iid = $dbServer->serverId;
                    }
                       else {
                           $num = ++$slaveNumber;
                           $SlavePosition = $rStatus['Exec_Master_Log_Pos'];
                       }

                       $d = array(
                           "serverId" => $dbServer->serverId,
                           "localIp" => $dbServer->localIp,
                           "remoteIp" => $dbServer->remoteIp,
                           "replicationRole" => $isMaster ? 'Master' : "Slave #{$num}"
                       );

                       if (!$isCloudstack) {
                           $d['data'] = $rStatus;
                           $d['masterPosition'] = $MasterPosition;
                           $d['slavePosition'] = $SlavePosition;
                       }

                       $data["replicationStatus"][] = $d;
                   }
                   catch(Exception $e)
                   {
                       $data["replicationStatus"][] = array(
                           "serverId" => $dbServer->serverId,
                           "localIp" => $dbServer->localIp,
                           "remoteIp" => $dbServer->remoteIp,
                           "error" => ($e->msg) ? $e->msg : $e->getMessage(),
                           "replicationRole" => $isMaster ? 'Master' : 'Slave'
                       );
                   }
            }
        }

        $this->response->page('ui/dbmsr/status.js', $data);
    }
}
