<?php

    require_once dirname(__FILE__).'/../../../externals/google-api-php-client-r558/src/Google_Client.php';
    require_once dirname(__FILE__).'/../../../externals/google-api-php-client-r558/src/contrib/Google_ComputeService.php';

    class Modules_Platforms_GoogleCE implements IPlatformModule
    {
        /** Properties **/
        const CLIENT_ID 			= 'gce.client_id';
        const SERVICE_ACCOUNT_NAME	= 'gce.service_account_name';
        const KEY					= 'gce.key';
        const PROJECT_ID			= 'gce.project_id';
        const ACCESS_TOKEN			= 'gce.access_token';

        const RESOURCE_BASE_URL = 'https://www.googleapis.com/compute/v1beta14/projects/';

        private $instancesListCache;

        public function getClient(Scalr_Environment $environment, $cloudLocation)
        {
            $client = new Google_Client();
            $client->setApplicationName("Scalr GCE");
            $client->setScopes(array('https://www.googleapis.com/auth/compute'));

            $key = base64_decode($environment->getPlatformConfigValue(self::KEY));
            $client->setAssertionCredentials(new Google_AssertionCredentials(
                $environment->getPlatformConfigValue(self::SERVICE_ACCOUNT_NAME),
                array('https://www.googleapis.com/auth/compute'),
                $key
            ));

            $client->setUseObjects(true);
            $client->setClientId($environment->getPlatformConfigValue(self::CLIENT_ID));

            $gce = new Google_ComputeService($client);

            //**** Store access token ****//
            $jsonAccessToken = $environment->getPlatformConfigValue(self::ACCESS_TOKEN);
            $accessToken = @json_decode($jsonAccessToken);
            if ($accessToken && $accessToken->created+$accessToken->expires_in > time())
                $client->setAccessToken($jsonAccessToken);
            else {
                $gce->zones->listZones($environment->getPlatformConfigValue(self::PROJECT_ID));
                $token = $client->getAccessToken();
                $environment->setPlatformConfig(array(
                    self::ACCESS_TOKEN => $token
                ));
            }

            return $gce;
        }

        public function __construct()
        {

        }

        public function getLocations()
        {
            try {
                $environment = Scalr_UI_Request::getInstance()->getEnvironment();
            }
            catch(Exception $e) {
                return array();
            }

            if (!$environment || !$environment->isPlatformEnabled(SERVER_PLATFORMS::GCE))
                return array();

            try {
                $client = $this->getClient($environment, "fakeRegion");

                $zones = $client->zones->listZones($environment->getPlatformConfigValue(self::PROJECT_ID));

                foreach ($zones->getItems() as $zone) {
                    if ($zone->status == 'UP')
                        $retval[$zone->getName()] = "GCE / {$zone->getName()}";
                }

            } catch (Exception $e) {
                return array();
            }

            return $retval;
        }

        public function getPropsList()
        {
            return array(
                self::CLIENT_ID	=> 'Client ID',
                self::SERVICE_ACCOUNT_NAME	=> 'E-mail',
                self::KEY	=> "Key",
                self::PROJECT_ID => "Project ID"
            );
        }

        public function GetServerCloudLocation(DBServer $DBServer)
        {
            return $DBServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION);
        }

        public function GetServerID(DBServer $DBServer)
        {
            return $DBServer->GetProperty(GCE_SERVER_PROPERTIES::SERVER_ID);
        }

        public function GetServerFlavor(DBServer $DBServer)
        {
            return $DBServer->GetProperty(GCE_SERVER_PROPERTIES::MACHINE_TYPE);
        }

        public function IsServerExists(DBServer $DBServer, $debug = false)
        {
            return in_array(
                $DBServer->serverId,
                array_keys($this->GetServersList($DBServer->GetEnvironmentObject(), $DBServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION), true))
            );
        }

        public function GetServerIPAddresses(DBServer $DBServer)
        {
            $gce = $this->getClient($DBServer->GetEnvironmentObject(), $DBServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION));

            $result = $gce->instances->get(
                $DBServer->GetEnvironmentObject()->getPlatformConfigValue(self::PROJECT_ID),
                $DBServer->GetCloudLocation(),
                $DBServer->serverId
            );

            $network = $result->getNetworkInterfaces();

            return array(
                'localIp'	=> $network[0]->networkIP,
                'remoteIp'	=> $network[0]->accessConfigs[0]->natIP
            );
        }

        public function GetServersList(Scalr_Environment $environment, $cloudLocation, $skipCache = false)
        {
            if (!$this->instancesListCache[$environment->id][$cloudLocation] || $skipCache)
            {
                $this->instancesListCache[$environment->id][$cloudLocation] = array();

                $gce = $this->getClient($environment, $cloudLocation);
                $pageToken = false;
                $cnt = 0;
                while (true) {
                    if ($pageToken)
                        $opts = array('pageToken' => $pageToken);
                    else
                        $opts = array();

                    $result = $gce->instances->listInstances($environment->getPlatformConfigValue(self::PROJECT_ID), $cloudLocation, $opts);
                    if (is_array($result->items))
                        foreach ($result->items as $server)
                            $this->instancesListCache[$environment->id][$cloudLocation][$server->name] = $server->status;

                    $pageToken = $result->getNextPageToken();
                    if (!$pageToken)
                        break;

                    $cnt++;

                    if ($cnt == 10)
                        throw new Exception("Deadloop detected in GCE module");
                }
            }

            return $this->instancesListCache[$environment->id][$cloudLocation];
        }

        public function GetServerRealStatus(DBServer $DBServer)
        {
            $cloudLocation = $DBServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION);
            $environment = $DBServer->GetEnvironmentObject();

            $iid = $DBServer->serverId;
            if (!$iid) {
                $status = 'not-found';
            }
            elseif (!$this->instancesListCache[$environment->id][$cloudLocation][$iid])
            {
                $gce = $this->getClient($environment, $cloudLocation);

                try {
                    $result = $gce->instances->get(
                        $DBServer->GetEnvironmentObject()->getPlatformConfigValue(self::PROJECT_ID),
                        $cloudLocation,
                        $DBServer->serverId
                    );
                    $status = $result->status;
                }
                catch(Exception $e)
                {
                    if (stristr($e->getMessage(), "not found"))
                        $status = 'not-found';
                    else
                        throw $e;
                }
            }
            else
            {
                $status = $this->instancesListCache[$environment->id][$cloudLocation][$DBServer->serverId];
            }

            return Modules_Platforms_GoogleCE_Adapters_Status::load($status);
        }

        public function TerminateServer(DBServer $DBServer)
        {
            $gce = $this->getClient($DBServer->GetEnvironmentObject(), $DBServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION));

            $gce->instances->delete(
                $DBServer->GetEnvironmentObject()->getPlatformConfigValue(self::PROJECT_ID),
                $DBServer->GetCloudLocation(),
                $DBServer->serverId
            );

            return true;
        }

        public function RebootServer(DBServer $DBServer)
        {
            //NOT_SUPPORTED
        }

        public function RemoveServerSnapshot(DBRole $DBRole)
        {
            foreach ($DBRole->getImageId(SERVER_PLATFORMS::GCE) as $location => $imageId) {

                $gce = $this->getClient($DBRole->GetEnvironmentObject(), $location);

                try {

                    $projectId = $DBRole->GetEnvironmentObject()->getPlatformConfigValue(self::PROJECT_ID);
                    $imageId = str_replace("{$projectId}/images/", "", $imageId);

                    $gce->images->delete($projectId, $imageId);
                }
                catch(Exception $e)
                {
                    throw $e;
                }
            }

            return true;
        }

        public function CheckServerSnapshotStatus(BundleTask $BundleTask)
        {

        }

        public function CreateServerSnapshot(BundleTask $BundleTask)
        {
            $DBServer = DBServer::LoadByID($BundleTask->serverId);
            $BundleTask->status = SERVER_SNAPSHOT_CREATION_STATUS::IN_PROGRESS;
            $BundleTask->bundleType = SERVER_SNAPSHOT_CREATION_TYPE::GCE_STORAGE;

            $msg = new Scalr_Messaging_Msg_Rebundle(
                $BundleTask->id,
                $BundleTask->roleName,
                array()
            );

            if (!$DBServer->SendMessage($msg))
            {
                $BundleTask->SnapshotCreationFailed("Cannot send rebundle message to server. Please check event log for more details.");
                return;
            }
            else
            {
                $BundleTask->Log(sprintf(_("Snapshot creating initialized (MessageID: %s). Bundle task status changed to: %s"),
                        $msg->messageId, $BundleTask->status
                ));
            }

            $BundleTask->setDate('started');
            $BundleTask->Save();
        }

        private function ApplyAccessData(Scalr_Messaging_Msg $msg)
        {


        }

        public function GetServerConsoleOutput(DBServer $DBServer)
        {
            $gce = $this->getClient($DBServer->GetEnvironmentObject(), $DBServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION));

            $retval = $gce->instances->getSerialPortOutput(
                $DBServer->GetEnvironmentObject()->getPlatformConfigValue(self::PROJECT_ID),
                $DBServer->GetCloudLocation(),
                $DBServer->serverId
            );

            return base64_encode($retval->contents);
        }

        private function getObjectUrl($objectName, $objectType, $projectName) {

            if ($objectType != 'images')
                return self::RESOURCE_BASE_URL."{$projectName}/global/{$objectType}/{$objectName}";
            else {
                if (!stristr($objectName, "/global"))
                    return str_replace($projectName, "{$projectName}/global", self::RESOURCE_BASE_URL."{$objectName}");
                else
                    return self::RESOURCE_BASE_URL."{$objectName}";
            }
        }

        private function getObjectName($objectURL)
        {
            return substr($objectURL, strrpos($objectURL, "/")+1);
        }

        public function GetServerExtendedInformation(DBServer $DBServer)
        {
            try
            {
                try	{
                    $gce = $this->getClient($DBServer->GetEnvironmentObject(), $DBServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION));

                    $info = $gce->instances->get(
                        $DBServer->GetEnvironmentObject()->getPlatformConfigValue(self::PROJECT_ID),
                        $DBServer->GetCloudLocation(),
                        $DBServer->serverId
                    );
                }
                catch(Exception $e){}

                if ($info) {
                    $network = $info->getNetworkInterfaces();

                    return array(
                        'Cloud Server ID'		=> $info->id,
                        'Image ID'				=> $this->getObjectName($info->image),
                        'Machine Type'			=> $this->getObjectName($info->machineType),
                        'Public IP'				=> $network[0]->accessConfigs[0]->natIP,
                        'Private IP'			=> $network[0]->networkIP,
                        'Status'				=> $info->status,
                        'Name'					=> $info->name,
                        'Zone'					=> $this->getObjectName($info->zone)
                    );
                }
            }
            catch(Exception $e){}

            return false;
        }

        public function LaunchServer(DBServer $DBServer, Scalr_Server_LaunchOptions $launchOptions = null)
        {
            $environment = $DBServer->GetEnvironmentObject();

            if (!$launchOptions)
            {
                $launchOptions = new Scalr_Server_LaunchOptions();
                $DBRole = DBRole::loadById($DBServer->roleId);

                $launchOptions->imageId = $DBRole->getImageId(SERVER_PLATFORMS::GCE, $DBServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION));

                $launchOptions->serverType = $DBServer->GetFarmRoleObject()->GetSetting(DBFarmRole::SETTING_GCE_MACHINE_TYPE);

                $launchOptions->cloudLocation = $DBServer->GetFarmRoleObject()->CloudLocation;

                $userData = $DBServer->GetCloudUserData();

                $launchOptions->architecture = 'x86_64';

                $networkName = $DBServer->GetFarmRoleObject()->GetSetting(DBFarmRole::SETTING_GCE_NETWORK);
            } else {
                $userData = array();
                $networkName = 'default';
            }

            if ($DBServer->status == SERVER_STATUS::TEMPORARY)
                $keyName = "SCALR-ROLESBUILDER-".SCALR_ID;
            else
                $keyName = "FARM-{$DBServer->farmId}-".SCALR_ID;

            $sshKey = Scalr_SshKey::init();
            if (!$sshKey->loadGlobalByName($keyName, "", $DBServer->envId, SERVER_PLATFORMS::GCE)) {
                $keys = $sshKey->generateKeypair();
                if ($keys['public']) {
                    $sshKey->farmId = $DBServer->farmId;
                    $sshKey->clientId = $DBServer->clientId;
                    $sshKey->envId = $DBServer->envId;
                    $sshKey->type = Scalr_SshKey::TYPE_GLOBAL;
                    $sshKey->cloudLocation = "";
                    $sshKey->cloudKeyName = $keyName;
                    $sshKey->platform = SERVER_PLATFORMS::GCE;
                    $sshKey->save();

                    $publicKey = $keys['public'];
                } else {
                    throw new Exception("Scalr unable to generate ssh keypair");
                }
            } else {
                $publicKey = $sshKey->getPublic();
            }

            $gce = $this->getClient($environment, $launchOptions->cloudLocation);

            //
            //
            // Check firewall
            $firewalls = $gce->firewalls->listFirewalls($environment->getPlatformConfigValue(self::PROJECT_ID));
            $firewallFound = false;
            foreach ($firewalls->getItems() as $f) {
                if ($f->getName() == 'scalr-system') {
                    $firewallFound = true;
                    break;
                }
            }

            // Create scalr firewall
            if (!$firewallFound) {
                $firewall = new Google_Firewall();
                $firewall->setName('scalr-system');
                $firewall->setNetwork($this->getObjectUrl(
                    $networkName,
                    'networks',
                    $environment->getPlatformConfigValue(self::PROJECT_ID)
                ));

                //Get scalr IP-pool IP list and set source ranges
                $firewall->setSourceRanges(\Scalr::config('scalr.aws.ip_pool'));

                // Set ports
                $tcp = new Google_FirewallAllowed();
                $tcp->setIPProtocol('tcp');
                $tcp->setPorts(array('1-65535'));
                $udp = new Google_FirewallAllowed();
                $udp->setIPProtocol('udp');
                $udp->setPorts(array('1-65535'));
                $firewall->setAllowed(array($tcp, $udp));

                // Set target tags
                $firewall->setTargetTags(array('scalr'));

                $gce->firewalls->insert(
                    $environment->getPlatformConfigValue(self::PROJECT_ID), $firewall
                );
            }
            ///
            ////
            //////




            $instance = new Google_Instance();
            $instance->setKind("compute#instance");

            $accessConfig = new Google_AccessConfig();
            $accessConfig->setName("External NAT");
            $accessConfig->setType("ONE_TO_ONE_NAT");

            $network = new Google_NetworkInterface();
            $network->setNetwork($this->getObjectUrl(
                $networkName,
                'networks',
                $environment->getPlatformConfigValue(self::PROJECT_ID)
            ));

            $network->setAccessConfigs(array($accessConfig));
            $instance->setNetworkInterfaces(array($network));

            $serviceAccount = new Google_ServiceAccount();
            $serviceAccount->setEmail("default");
            $serviceAccount->setScopes(array(
                "https://www.googleapis.com/auth/userinfo.email",
                "https://www.googleapis.com/auth/compute",
                "https://www.googleapis.com/auth/devstorage.full_control"
            ));
            $instance->setServiceAccounts(array($serviceAccount));

            if ($launchOptions->cloudLocation != 'x-scalr-custom') {
                $availZone = $launchOptions->cloudLocation;
            } else {
                $location = $DBServer->GetFarmRoleObject()->GetSetting(DBFarmRole::SETTING_GCE_CLOUD_LOCATION);

                $availZones = array();
                if (stristr($location, "x-scalr-custom")) {
                    $zones = explode("=", $location);
                    foreach (explode(":", $zones[1]) as $zone)
                        if ($zone != "")
                        array_push($availZones, $zone);
                }

                sort($availZones);
                $availZones = array_reverse($availZones);

                $servers = $DBServer->GetFarmRoleObject()->GetServersByFilter(array("status" => array(
                    SERVER_STATUS::RUNNING,
                    SERVER_STATUS::INIT,
                    SERVER_STATUS::PENDING
                )));
                $availZoneDistribution = array();
                foreach ($servers as $cDbServer) {
                    if ($cDbServer->serverId != $DBServer->serverId)
                        $availZoneDistribution[$cDbServer->GetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION)]++;
                }

                $sCount = 1000000;
                foreach ($availZones as $zone) {
                    if ((int)$availZoneDistribution[$zone] <= $sCount) {
                        $sCount = (int)$availZoneDistribution[$zone];
                        $availZone = $zone;
                    }
                }

                $aZones = implode(",", $availZones); // Available zones
                $dZones = ""; // Zones distribution
                foreach ($availZoneDistribution as $zone => $num)
                    $dZones .= "({$zone}:{$num})";

                $DBServer->SetProperty("tmp.gce.avail_zone.algo2", "[A:{$aZones}][D:{$dZones}][S:{$availZone}]");
            }

            $instance->setZone($this->getObjectUrl(
                $availZone,
                'zones',
                $environment->getPlatformConfigValue(self::PROJECT_ID)
            ));


            $instance->setMachineType($this->getObjectUrl(
                $launchOptions->serverType,
                'machineTypes',
                $environment->getPlatformConfigValue(self::PROJECT_ID)
            ));
            $instance->setImage($this->getObjectUrl(
                $launchOptions->imageId,
                'images',
                $environment->getPlatformConfigValue(self::PROJECT_ID)
            ));
            $instance->setName($DBServer->serverId);

            $tags = array(
                'scalr',
                "env-{$DBServer->envId}"
            );
            if ($DBServer->farmId)
                $tags[] = "farm-{$DBServer->farmId}";

            if ($DBServer->farmRoleId)
                $tags[] = "farmrole-{$DBServer->farmRoleId}";

            $gTags = new Google_Tags();
            $gTags->setItems($tags);

            $instance->setTags($gTags);

            $metadata = new Google_Metadata();
            $items = array();

            // Set user data
             foreach ($userData as $k=>$v)
                $uData .= "{$k}={$v};";
            $uData = trim($uData, ";");
            if ($uData) {
                $item = new Google_MetadataItems();
                $item->setKey('scalr');
                $item->setValue($uData);
                $items[] = $item;
            }

            // Add SSH Key
            $item = new Google_MetadataItems();
            $item->setKey("sshKeys");
            $item->setValue("scalr:{$publicKey}");
            $items[] = $item;

            $metadata->setItems($items);

            $instance->setMetadata($metadata);

            try {
                $result = $gce->instances->insert(
                    $environment->getPlatformConfigValue(self::PROJECT_ID),
                    $availZone,
                    $instance
                );
            } catch (Exception $e) {
                throw new Exception(sprintf(_("Cannot launch new instance. %s (%s, %s)"), $e->getMessage(), $launchOptions->imageId, $launchOptions->serverType));
            }

            if ($result->id) {

                $DBServer->SetProperty(GCE_SERVER_PROPERTIES::PROVISIONING_OP_ID, $result->name);
                $DBServer->SetProperty(GCE_SERVER_PROPERTIES::SERVER_NAME, $DBServer->serverId);
                $DBServer->SetProperty(GCE_SERVER_PROPERTIES::SERVER_ID, $DBServer->serverId);
                $DBServer->SetProperty(GCE_SERVER_PROPERTIES::CLOUD_LOCATION, $availZone);
                $DBServer->SetProperty(GCE_SERVER_PROPERTIES::MACHINE_TYPE, $launchOptions->serverType);
                $DBServer->SetProperty(SERVER_PROPERTIES::ARCHITECTURE, $launchOptions->architecture);

                return $DBServer;
            }
            else
                throw new Exception(sprintf(_("Cannot launch new instance. %s (%s, %s)"), serialize($result), $launchOptions->imageId, $launchOptions->serverType));
        }

        public function GetPlatformAccessData($environment, $DBServer) {
            $accessData = new stdClass();
            $accessData->clientId = $environment->getPlatformConfigValue(self::CLIENT_ID);
            $accessData->serviceAccountName = $environment->getPlatformConfigValue(self::SERVICE_ACCOUNT_NAME);
            $accessData->projectId = $environment->getPlatformConfigValue(self::PROJECT_ID);
            $accessData->key = $environment->getPlatformConfigValue(self::KEY);

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

        public function ClearCache ()
        {
            $this->instancesListCache = array();
        }
    }



?>
