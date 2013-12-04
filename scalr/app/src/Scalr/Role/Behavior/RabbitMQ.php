<?php
    class Scalr_Role_Behavior_RabbitMQ extends Scalr_Role_Behavior implements Scalr_Role_iBehavior
    {
        /** DBFarmRole settings **/
        const ROLE_DATA_STORAGE_ENGINE 	= 'rabbitmq.data_storage.engine';
        const ROLE_COOKIE_NAME  		= 'rabbitmq.cookieName';
        const ROLE_NODES_RATIO 			= 'rabbitmq.nodes_ratio';
        const ROLE_PASSWORD				= 'rabbitmq.password';
        const ROLE_MASTER_PASSWORD      = 'rabbitmq.master_password';

        const ROLE_CP_SERVER_ID  		= 'rabbitmq.cp.server_id';
        const ROLE_CP_REQUESTED  		= 'rabbitmq.cp.isrequested';
        const ROLE_CP_REQUEST_TIME  	= 'rabbitmq.cp.request_time';
        const ROLE_CP_ERROR_MSG  		= 'rabbitmq.cp.error_msg';
        const ROLE_CP_URL  				= 'rabbitmq.cp.url';

        // For EBS storage
        const ROLE_DATA_STORAGE_EBS_SIZE = 'rabbitmq.data_storage.ebs.size';

        /** DBServer settings **/
        const SERVER_NODE_TYPE = 'rabbitmq.node_type';

        const NODE_TYPE_RAM = 'ram';
        const NODE_TYPE_DISK = 'disk';

        public function __construct($behaviorName)
        {
            parent::__construct($behaviorName);
        }

        public function onFarmSave(DBFarm $dbFarm, DBFarmRole $dbFarmRole)
        {
            if (!$dbFarmRole->GetSetting(self::ROLE_COOKIE_NAME))
            {
                $cookie = substr(sha1(microtime(true).rand(0,100000)), 0, 20);
                $dbFarmRole->SetSetting(self::ROLE_COOKIE_NAME, $cookie);
            }
        }

        public function getSecurityRules()
        {
            return array(
                "tcp:4369:4369:0.0.0.0/0",
                "tcp:5672:5672:0.0.0.0/0",
                "tcp:55672:55672:0.0.0.0/0",
                "tcp:15672:55672:0.0.0.0/0"
            );
        }

        public function listDnsRecords(DBServer $dbServer)
        {
            $records = array();

            array_push($records, array(
                "name" 		=> "int-rabbitmq",
                "value"		=> $dbServer->localIp,
                "type"		=> "A",
                "ttl"		=> 90,
                "server_id"	=> $dbServer->serverId,
                "issystem"	=> '1'
            ));

            array_push($records, array(
                "name" 		=> "ext-rabbitmq",
                "value"		=> $dbServer->remoteIp,
                "type"		=> "A",
                "ttl"		=> 90,
                "server_id"	=> $dbServer->serverId,
                "issystem"	=> '1'
            ));

            $nodeType = $dbServer->GetProperty(self::SERVER_NODE_TYPE);

            array_push($records, array(
                "name" 		=> "int-rabbitmq-{$nodeType}",
                "value"		=> $dbServer->localIp,
                "type"		=> "A",
                "ttl"		=> 90,
                "server_id"	=> $dbServer->serverId,
                "issystem"	=> '1'
            ));

            array_push($records, array(
                "name" 		=> "ext-rabbitmq-{$nodeType}",
                "value"		=> $dbServer->remoteIp,
                "type"		=> "A",
                "ttl"		=> 90,
                "server_id"	=> $dbServer->serverId,
                "issystem"	=> '1'
            ));

            return $records;
        }

        public function handleMessage(Scalr_Messaging_Msg $message, DBServer $dbServer)
        {
            parent::handleMessage($message, $dbServer);

            if (!$message->rabbitmq)
                return;

            switch (get_class($message))
            {
                case "Scalr_Messaging_Msg_HostUp":

                    if ($message->rabbitmq->volumeConfig)
                        $this->setVolumeConfig($message->rabbitmq->volumeConfig, $dbServer->GetFarmRoleObject(), $dbServer);
                    else
                        throw new Exception("Received hostUp message from RabbitMQ server without volumeConfig");

                    $dbServer->GetFarmRoleObject()->SetSetting(self::ROLE_PASSWORD, $message->rabbitmq->password);

                    if ($message->rabbitmq->masterPassword)
                        $dbServer->GetFarmRoleObject()->SetSetting(self::ROLE_MASTER_PASSWORD, $message->rabbitmq->masterPassword);

                    break;
            }
        }

        public function extendMessage(Scalr_Messaging_Msg $message, DBServer $dbServer)
        {
            $message = parent::extendMessage($message, $dbServer);

            switch (get_class($message))
            {
                case "Scalr_Messaging_Msg_HostInitResponse":

                    $message->rabbitmq = new stdClass();
                    $message->rabbitmq->cookie = $dbServer->GetFarmRoleObject()->GetSetting(self::ROLE_COOKIE_NAME);
                    $message->rabbitmq->volumeConfig = $this->getVolumeConfig($dbServer->GetFarmRoleObject(), $dbServer);
                    $message->rabbitmq->nodeType = $this->getNodeType($dbServer->GetFarmRoleObject(), $dbServer);
                    $message->rabbitmq->password = $dbServer->GetFarmRoleObject()->GetSetting(self::ROLE_PASSWORD);

                    $dbServer->SetProperty(self::SERVER_NODE_TYPE, $message->rabbitmq->nodeType);

                    break;
            }

            return $message;
        }

        private function getNodeType(DBFarmRole $dbFarmRole, DBServer $dbServer)
        {
            if ($dbServer->index == 1)
                return self::NODE_TYPE_DISK;

            $ramServers = 0;
            $diskServers = 0;
            foreach ($dbFarmRole->GetServersByFilter(array('status' => array(SERVER_STATUS::RUNNING, SERVER_STATUS::INIT))) as $server) {
                if ($server->GetProperty(self::SERVER_NODE_TYPE) == self::NODE_TYPE_DISK)
                    $diskServers++;
                elseif($server->GetProperty(self::SERVER_NODE_TYPE) == self::NODE_TYPE_RAM)
                    $ramServers++;

            }

            $totalServers = ($ramServers+$diskServers);

            $currentRatio = ($totalServers != 0) ? $diskServers / $totalServers * 100 : 0;
            $sRatio = (int)trim($dbFarmRole->GetSetting(self::ROLE_NODES_RATIO), "%");


            //$this->logger->error(new FarmLogMessage($dbFarmRole->FarmID, "Total: {$totalServers}, Disk: {$diskServers}, Ram: {$ramServers}, Ratio: {$currentRatio}, sRatio: {$sRatio}"));

            if ($currentRatio <= $sRatio)
                return self::NODE_TYPE_DISK;
            else
                return self::NODE_TYPE_RAM;
        }

        public function setVolumeConfig($volumeConfig, DBFarmRole $dbFarmRole, DBServer $dbServer)
        {
            try {
                $volume = Scalr_Storage_Volume::init()->loadByFarmRoleServer(
                    $dbFarmRole->ID,
                    $dbServer->index,
                    $this->behavior
                );

                if ($volume->id != $volumeConfig->id)
                    $volume->delete();

            } catch (Exception $e) {}

            try {
                $storageVolume = Scalr_Storage_Volume::init();
                try {
                    $storageVolume->loadById($volumeConfig->id);
                    $storageVolume->setConfig($volumeConfig);
                    $storageVolume->save();
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'not found')) {
                        $storageVolume->loadBy(array(
                            'id'			=> $volumeConfig->id,
                            'client_id'		=> $dbFarmRole->GetFarmObject()->ClientID,
                            'env_id'		=> $dbFarmRole->GetFarmObject()->EnvID,
                            'name'			=> sprintf("'%s' data volume", $this->behavior),
                            'type'			=> $dbFarmRole->GetSetting(static::ROLE_DATA_STORAGE_ENGINE),
                            'platform'		=> $dbFarmRole->Platform,
                            'size'			=> $volumeConfig->size,
                            'fstype'		=> $volumeConfig->fstype,
                            'farm_roleid'   => $dbFarmRole->ID,
                            'server_index'  => $dbServer->index,
                            'purpose'		=> $this->behavior
                        ));
                        $storageVolume->setConfig($volumeConfig);
                        $storageVolume->save(true);
                    }
                    else
                        throw $e;
                }
            }
            catch(Exception $e) {
                $this->logger->error(new FarmLogMessage($dbFarmRole->FarmID, "Cannot save storage volume: {$e->getMessage()}"));
            }
        }

        public function getVolumeConfig(DBFarmRole $dbFarmRole, DBServer $dbServer)
        {
            try {
                $volume = Scalr_Storage_Volume::init()->loadByFarmRoleServer(
                    $dbFarmRole->ID,
                    $dbServer->index,
                    $this->behavior
                );

                $volumeConfig = $volume->getConfig();
            } catch (Exception $e) {}

            if (!$volumeConfig)
            {
                $volumeConfig = new stdClass();
                $volumeConfig->type = $dbFarmRole->GetSetting(static::ROLE_DATA_STORAGE_ENGINE);

                switch ($volumeConfig->type) {
                	case MYSQL_STORAGE_ENGINE::EPH:
                	    if ($dbFarmRole->Platform == SERVER_PLATFORMS::RACKSPACE) {
                            $storageProvider = 'cf';

                            $volumeConfig->disk = new stdClass();
                            $volumeConfig->disk->type = 'loop';
                            $volumeConfig->disk->size = '75%root';
                        } elseif (in_array($dbFarmRole->Platform, array(SERVER_PLATFORMS::OPENSTACK, SERVER_PLATFORMS::RACKSPACENG_UK, SERVER_PLATFORMS::RACKSPACENG_US))) {
                            $storageProvider = 'swift';

                            $volumeConfig->disk = new stdClass();
                            $volumeConfig->disk->type = 'loop';
                            $volumeConfig->disk->size = '75%root';
                        } elseif ($dbFarmRole->Platform == SERVER_PLATFORMS::GCE) {
                            $storageProvider = 'gcs';

                            $volumeConfig->disk = array(
                                'type' => 'gce_ephemeral',
                                'name' => 'ephemeral-disk-0'
                            );
                            $volumeConfig->size = "80%";
                        } elseif ($dbFarmRole->Platform == SERVER_PLATFORMS::EC2) {
                            /*
                             * NOT SUPPORTED
                            $storageProvider = 's3';

                            $volumeConfig->disk = $this->dbFarmRole->GetSetting(Scalr_Db_Msr::DATA_STORAGE_EPH_DISK);
                            $volumeConfig->size = "80%";
                            */
                        }

                        $volumeConfig->snap_backend = sprintf("%s://scalr-%s-%s/data-bundles/%s/%s",
                            $storageProvider,
                            $dbFarmRole->GetFarmObject()->EnvID,
                            $dbFarmRole->CloudLocation,
                            $dbFarmRole->FarmID,
                            $this->behavior
                        );
                        $volumeConfig->vg = $this->behavior;
                	    break;

                	default:
                	    $volumeConfig->size = $dbFarmRole->GetSetting(static::ROLE_DATA_STORAGE_EBS_SIZE);
                	    break;
                }
            }

            return $volumeConfig;
        }
    }