<?php
    class Modules_Platforms_Cloudstack extends Modules_Platform implements IPlatformModule
    {
        /** Properties **/
        const API_KEY 	= 'api_key';
        const SECRET_KEY	= 'secret_key';
        const API_URL = 'api_url';

        const ACCOUNT_NAME = 'account_name';
        const DOMAIN_NAME  = 'domain_name';
        const DOMAIN_ID  = 'domain_id';
        const SHARED_IP = 'shared_ip';
        const SHARED_IP_ID = 'shared_ip_id';
        const SHARED_IP_INFO = 'shared_ip_info';
        const SZR_PORT_COUNTER = 'szr_port_counter';

        private $instancesListCache;

        public function __construct($platform = 'cloudstack')
        {
            parent::__construct($platform);
        }

        /**
         *
         * @param unknown_type $environment
         * @param unknown_type $region
         * @return Scalr_Service_Cloud_Nimbula_Client
         */
        protected function getCloudStackClient($environment, $cloudLocation=null)
        {
            return Scalr_Service_Cloud_Cloudstack::newCloudstack(
                $this->getConfigVariable(self::API_URL, $environment),
                $this->getConfigVariable(self::API_KEY, $environment),
                $this->getConfigVariable(self::SECRET_KEY, $environment),
                $this->platform
            );
        }

        public function getLocations() {
            try {
                $environment = Scalr_UI_Request::getInstance()->getEnvironment();
            }
            catch(Exception $e) {
                return array();
            }

            if (!$environment || !$environment->isPlatformEnabled($this->platform))
                return array();

            try {
                $cs = Scalr_Service_Cloud_Cloudstack::newCloudstack(
                    $this->getConfigVariable(self::API_URL, $environment),
                    $this->getConfigVariable(self::API_KEY, $environment),
                    $this->getConfigVariable(self::SECRET_KEY, $environment),
                    $this->platform
                );

                foreach ($cs->listZones() as $zone)
                    $retval[$zone->name] = ucfirst($this->platform)." / {$zone->name}";

            } catch (Exception $e) {
                return array();
            }

            return $retval;
        }

        public function getPropsList()
        {
            return array(
                self::API_URL			=> 'API URL',
                self::API_KEY			=> 'API Key',
                self::SECRET_KEY		=> 'Secret Key'
            );
        }

        public function GetServerCloudLocation(DBServer $DBServer)
        {
            return $DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::CLOUD_LOCATION);
        }

        public function GetServerID(DBServer $DBServer)
        {
            return $DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID);
        }

        public function GetServerFlavor(DBServer $DBServer)
        {
            //return $DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::);
        }

        public function IsServerExists(DBServer $DBServer)
        {
            return in_array(
                $DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID),
                array_keys($this->GetServersList($DBServer->GetEnvironmentObject(), $this->GetServerCloudLocation($DBServer)))
            );
        }

        public function GetServerIPAddresses(DBServer $DBServer)
        {
            $cloudLocation = $this->GetServerCloudLocation($DBServer);
            $env = $DBServer->GetEnvironmentObject();
              $cs = $this->getCloudStackClient($env, $cloudLocation);
            try {
                $iinfo = $cs->listVirtualMachines($DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));
                $iinfo = (is_array($iinfo)) ? $iinfo[0] : null;
            } catch (Exception $e) {}

            if ($iinfo->id && $iinfo->id == $DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID))
            {
                $localIp = $iinfo->nic[0]->ipaddress;
                if ($iinfo->publicip)
                    $remoteIp = $iinfo->publicip;
            }

            return array(
                'localIp'   => $localIp,
                'remoteIp'  => $remoteIp
            );
        }

        private function GetServersList(Scalr_Environment $environment, $region, $skipCache = false)
        {
            if (!$region)
                return array();

            if (!$this->instancesListCache[$environment->id][$region] || $skipCache) {
                $cs = $this->getCloudStackClient($environment, $region);

                try {
                    $results = $cs->listVirtualMachines(null, $region);
                }
                catch(Exception $e) {
                    throw new Exception(sprintf("Cannot get list of servers for platfrom ec2: %s", $e->getMessage()));
                }


                if (count($results) > 0) {
                    foreach ($results as $item)
                        $this->instancesListCache[$environment->id][$region][$item->id] = $item->state;
                }
            }

            return $this->instancesListCache[$environment->id][$region];
        }

        public function GetServerRealStatus(DBServer $DBServer)
        {
            $region = $this->GetServerCloudLocation($DBServer);
            $iid = $DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID);

            if (!$iid || !$region) {
                $status = 'not-found';
            }
            elseif (!$this->instancesListCache[$DBServer->GetEnvironmentObject()->id][$region][$iid]) {

                $cs = $this->getCloudStackClient($DBServer->GetEnvironmentObject(), $region);

                try {
                    $iinfo = $cs->listVirtualMachines($iid);
                    $iinfo = (is_array($iinfo)) ? $iinfo[0] : false;

                    if ($iinfo)
                        $status = $iinfo->state;
                    else
                        $status = 'not-found';
                }
                catch(Exception $e) {
                    if (stristr($e->getMessage(), "Not Found"))
                        $status = 'not-found';
                }
            }
            else {
                $status = $this->instancesListCache[$DBServer->GetEnvironmentObject()->id][$region][$DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID)];
            }

            return Modules_Platforms_Cloudstack_Adapters_Status::load($status);
        }

        public function TerminateServer(DBServer $DBServer)
        {
            $cs = $this->getCloudStackClient($DBServer->GetEnvironmentObject(), $this->GetServerCloudLocation($DBServer));

            $cs->destroyVirtualMachine($DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));
            return true;
        }

        public function RebootServer(DBServer $DBServer)
        {
            $cs = $this->getCloudStackClient($DBServer->GetEnvironmentObject(), $this->GetServerCloudLocation($DBServer));
            $cs->rebootVirtualMachine($DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));
            return true;
        }

        public function RemoveServerSnapshot(DBRole $DBRole)
        {
            //TODO:
        }

        public function CheckServerSnapshotStatus(BundleTask $BundleTask)
        {
            //TODO:
        }

        public function CreateServerSnapshot(BundleTask $BundleTask)
        {
            $DBServer = DBServer::LoadByID($BundleTask->serverId);
            $BundleTask->status = SERVER_SNAPSHOT_CREATION_STATUS::IN_PROGRESS;
            $BundleTask->bundleType = SERVER_SNAPSHOT_CREATION_TYPE::CSTACK_DEF;

            $msg = new Scalr_Messaging_Msg_Rebundle(
                $BundleTask->id,
                $BundleTask->roleName,
                array()
            );

            if (!$DBServer->SendMessage($msg)) {
                $BundleTask->SnapshotCreationFailed("Cannot send rebundle message to server. Please check event log for more details.");
                return;
            }
            else {
                $BundleTask->Log(sprintf(_("Snapshot creating initialized (MessageID: %s). Bundle task status changed to: %s"),
                    $msg->messageId, $BundleTask->status
                ));
            }

            $BundleTask->setDate('started');
            $BundleTask->Save();
        }

        public function GetServerConsoleOutput(DBServer $DBServer)
        {
            //NOT SUPPORTED
        }

        public function GetServerExtendedInformation(DBServer $DBServer)
        {
            $cs = $this->getCloudStackClient($DBServer->GetEnvironmentObject(), $this->GetServerCloudLocation($DBServer));

            try {
                $iinfo = $cs->listVirtualMachines($DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));
                $iinfo = (is_array($iinfo)) ? $iinfo[0] : null;
            } catch (Exception $e) {}

            if ($iinfo->id /*&& $iinfo->id == $DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID)*/)
            {
                $retval = array(
                    'Cloud Server ID' => $iinfo->id,
                    'Name'			=> $iinfo->name,
                    'State'			=> $iinfo->state,
                    'Group'			=> $iinfo->group,
                    'Zone'			=> $iinfo->zonename,
                    'Template name' => $iinfo->templatename,
                    'Offering name' => $iinfo->serviceofferingname,
                    'Root device type' => $iinfo->rootdevicetype,
                    'Internal IP'	=> $iinfo->nic[0]->ipaddress,
                    'Hypervisor'    => $iinfo->hypervisor
                );

                if ($iinfo->publicip)
                    $retval['Public IP'] = $iinfo->publicip;

                if ($iinfo->securitygroup) {
                    $retval['Security groups'] = "";
                    foreach ($iinfo->securitygroup as $sg) {
                        $retval['Security groups'] .= "{$sg->name}, ";
                    }

                    $retval['Security groups'] = trim($retval['Security groups'], ", ");
                }

                return $retval;
            }

            return false;
        }

        public function LaunchServer(DBServer $DBServer, Scalr_Server_LaunchOptions $launchOptions = null)
        {
            $environment = $DBServer->GetEnvironmentObject();

            $farmRole = $DBServer->GetFarmRoleObject();

            if (!$launchOptions)
            {
                $launchOptions = new Scalr_Server_LaunchOptions();
                $dbRole = DBRole::loadById($DBServer->roleId);

                $launchOptions->imageId = $dbRole->getImageId($this->platform, $DBServer->GetFarmRoleObject()->CloudLocation);
                $launchOptions->serverType = $DBServer->GetFarmRoleObject()->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_SERVICE_OFFERING_ID);
                   $launchOptions->cloudLocation = $DBServer->GetFarmRoleObject()->CloudLocation;

                /*
                 * User Data
                 */
                foreach ($DBServer->GetCloudUserData() as $k=>$v)
                    $u_data .= "{$k}={$v};";

                $launchOptions->userData = trim($u_data, ";");

                $launchOptions->architecture = 'x86_64';
            }

            $cs = $this->getCloudStackClient(
                $environment,
                $launchOptions->cloudLocation
            );

            $diskOffering = null;
            $size = null;

            $diskOffering = $farmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_DISK_OFFERING_ID);
            if ($diskOffering === false || $diskOffering === null)
                $diskOffering = null;

            $sharedIp = $farmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_SHARED_IP_ID);
            if (!$sharedIp) {
                $networkType = $farmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_NETWORK_TYPE);
                $networkId = $farmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_NETWORK_ID);
                if ($networkId && ($networkType == 'Virtual' || $networkType == 'Isolated' || !$networkType)) {
                    $sharedIpId = $this->getConfigVariable(self::SHARED_IP_ID.".{$launchOptions->cloudLocation}", $environment, false);
                    if (!$sharedIpId)
                    {
                        $ipResult = $cs->associateIpAddress($launchOptions->cloudLocation);
                        $ipId = $ipResult->id;
                        if ($ipId) {
                            while (true) {
                                $ipInfo = $cs->listPublicIpAddresses($ipId);
                                $ipInfo = $ipInfo->publicipaddress[0];

                                if (!$ipInfo)
                                    throw new Exception("Cannot allocate IP address: listPublicIpAddresses -> failed");

                                if ($ipInfo->state == 'Allocated') {
                                    $this->setConfigVariable(array(self::SHARED_IP_ID.".{$launchOptions->cloudLocation}" => $ipId), $environment, false);
                                    $this->setConfigVariable(array(self::SHARED_IP.".{$launchOptions->cloudLocation}" => $ipInfo->ipaddress), $environment, false);
                                    $this->setConfigVariable(array(self::SHARED_IP_INFO.".{$launchOptions->cloudLocation}" => serialize($ipInfo)), $environment, false);

                                    $sharedIpId = $ipId;
                                    break;
                                } else if ($ipInfo->state == 'Allocating') {
                                    sleep(1);
                                } else {
                                    throw new Exception("Cannot allocate IP address: ipAddress->state = {$ipInfo->state}");
                                }
                            }
                        }
                        else
                            throw new Exception("Cannot allocate IP address: associateIpAddress -> failed");
                    }
                }
            }

            $keyName = "FARM-{$DBServer->farmId}-".SCALR_ID;

            $sshKey = Scalr_SshKey::init();
            try {
                if (!$sshKey->loadGlobalByName($keyName, "", $DBServer->envId, $this->platform))
                {
                    $result = $cs->createSSHKeyPair($keyName);
                    if ($result->keypair->privatekey)
                    {
                        $sshKey->farmId = $DBServer->farmId;
                        $sshKey->clientId = $DBServer->clientId;
                        $sshKey->envId = $DBServer->envId;
                        $sshKey->type = Scalr_SshKey::TYPE_GLOBAL;
                        $sshKey->cloudLocation = "";//$launchOptions->cloudLocation;
                        $sshKey->cloudKeyName = $keyName;
                        $sshKey->platform = $this->platform;

                        $sshKey->setPrivate($result->keypair->privatekey);
                        $sshKey->setPublic($sshKey->generatePublicKey());

                        $sshKey->save();
                    }
                }
            } catch (Exception $e) {
                Logger::getLogger("CloudStack")->error(new FarmLogMessage($DBServer->farmId, "Unable to generate keypair: {$e->getMessage()}"));
            }

            $roleName = $farmRole->GetRoleObject()->name;

            $vResult = $cs->deployVirtualMachine(
                $launchOptions->serverType,
                $launchOptions->imageId,
                $launchOptions->cloudLocation,
                null, //account
                $diskOffering, // diskoffering
                $DBServer->serverId, //displayName
                null, //domainId
                $roleName,
                null, //hostId
                null, //hypervisor
                $keyName,
                "",//$DBServer->serverId, //name
                $farmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_NETWORK_ID),
                null, //securityGroupIds
                null, //SecGroupNames
                $size, //size
                base64_encode($launchOptions->userData)
            );
            if ($vResult->id) {
                $DBServer->SetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID, $vResult->id);
                $DBServer->SetProperty(CLOUDSTACK_SERVER_PROPERTIES::CLOUD_LOCATION, $launchOptions->cloudLocation);
                $DBServer->SetProperty(CLOUDSTACK_SERVER_PROPERTIES::LAUNCH_JOB_ID, $vResult->jobid);

                try {
                    $res = $cs->queryAsyncJobResult($vResult->jobid);
                    $DBServer->SetProperty(CLOUDSTACK_SERVER_PROPERTIES::TMP_PASSWORD, $res->jobresult->virtualmachine->password);
                    $DBServer->SetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_NAME, $res->jobresult->virtualmachine->name);
                } catch (Exception $e) {
                    if ($DBServer->farmId)
                      Logger::getLogger("CloudStack")->error(new FarmLogMessage($DBServer->farmId, $e->getMessage()));
                }

                return $DBServer;
            } else
                throw new Exception(sprintf("Cannot launch new instance: %s", $vResult->errortext));
        }

        public function GetPlatformAccessData($environment, DBServer $DBServer) {
            $accessData = new stdClass();
            $accessData->apiKey = $this->getConfigVariable(self::API_KEY, $environment);
            $accessData->secretKey = $this->getConfigVariable(self::SECRET_KEY, $environment);
            $accessData->apiUrl = $this->getConfigVariable(self::API_URL, $environment);

            return $accessData;
        }

        public function PutAccessData(DBServer $DBServer, Scalr_Messaging_Msg $message)
        {
            $put = false;
            $put |= $message instanceof Scalr_Messaging_Msg_Rebundle;
            $put |= $message instanceof Scalr_Messaging_Msg_BeforeHostUp;
            $put |= $message instanceof Scalr_Messaging_Msg_HostInitResponse;
            $put |= $message instanceof Scalr_Messaging_Msg_Mysql_PromoteToMaster;
            $put |= $message instanceof Scalr_Messaging_Msg_Mysql_CreateDataBundle;
            $put |= $message instanceof Scalr_Messaging_Msg_Mysql_CreateBackup;
            $put |= $message instanceof Scalr_Messaging_Msg_BeforeHostTerminate;

            $put |= $message instanceof Scalr_Messaging_Msg_DbMsr_PromoteToMaster;
            $put |= $message instanceof Scalr_Messaging_Msg_DbMsr_CreateDataBundle;
            $put |= $message instanceof Scalr_Messaging_Msg_DbMsr_CreateBackup;
            $put |= $message instanceof Scalr_Messaging_Msg_DbMsr_NewMasterUp;

            if ($put) {
                $environment = $DBServer->GetEnvironmentObject();
                $message->platformAccessData = $this->GetPlatformAccessData($environment, $DBServer);
            }
        }

        public function ClearCache()
        {
            $this->instancesListCache = array();
        }
    }
