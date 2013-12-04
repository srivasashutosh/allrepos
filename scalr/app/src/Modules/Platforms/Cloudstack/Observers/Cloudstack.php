<?php
    class Modules_Platforms_Cloudstack_Observers_Cloudstack extends EventObserver
    {
        public $ObserverName = 'Cloudstack';

        function __construct()
        {
            parent::__construct();
        }

        private function getCloudStackClient($environment, $cloudLoction=null, $platformName)
        {
            $platform = PlatformFactory::NewPlatform($platformName);

            return Scalr_Service_Cloud_Cloudstack::newCloudstack(
                $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_URL, $environment),
                $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_KEY, $environment),
                $platform->getConfigVariable(Modules_Platforms_Cloudstack::SECRET_KEY, $environment),
                $platformName
            );
        }

        public function OnHostInit(HostInitEvent $event)
        {
            if (!in_array($event->DBServer->platform, array(SERVER_PLATFORMS::CLOUDSTACK, SERVER_PLATFORMS::IDCF, SERVER_PLATFORMS::UCLOUD)))
                return;

            if ($event->DBServer->farmRoleId) {
                $dbFarmRole = $event->DBServer->GetFarmRoleObject();
                $networkType = $dbFarmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_NETWORK_TYPE);
                $networkId = $dbFarmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_NETWORK_ID);
                if ($networkType == 'Direct' || !$networkId)
                    return true;

                $sharedIpId = $dbFarmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_SHARED_IP_ID);
            }

            $platform = PlatformFactory::NewPlatform($event->DBServer->platform);

            try {
                $environment = $event->DBServer->GetEnvironmentObject();
                $cloudLocation = $event->DBServer->GetCloudLocation();

                if (!$sharedIpId)
                    $sharedIpId = $platform->getConfigVariable(Modules_Platforms_Cloudstack::SHARED_IP_ID.".{$cloudLocation}", $environment, false);

                $cs = $this->getCloudStackClient(
                    $environment,
                    $cloudLocation,
                    $event->DBServer->platform
                );

                // Create port forwarding rules for scalarizr
                $port = $platform->getConfigVariable(Modules_Platforms_Cloudstack::SZR_PORT_COUNTER.".{$cloudLocation}.{$sharedIpId}", $environment, false);
                if (!$port) {
                    $port1 = 30000;
                    $port2 = 30001;
                    $port3 = 30002;
                    $port4 = 30003;
                }
                else {
                    $port1 = $port+1;
                    $port2 = $port1+1;
                    $port3 = $port2+1;
                    $port4 = $port3+1;
                }

                $result2 = $cs->createPortForwardingRule($sharedIpId, 8014, "udp", $port1, $event->DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));

                $result1 = $cs->createPortForwardingRule($sharedIpId, 8013, "tcp", $port1, $event->DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));

                $result3 = $cs->createPortForwardingRule($sharedIpId, 8010, "tcp", $port3, $event->DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));
                $result4 = $cs->createPortForwardingRule($sharedIpId, 8008, "tcp", $port2, $event->DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));

                $result5 = $cs->createPortForwardingRule($sharedIpId, 22, "tcp", $port4, $event->DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID));

                $event->DBServer->SetProperties(array(
                    SERVER_PROPERTIES::SZR_CTRL_PORT => $port1,
                    SERVER_PROPERTIES::SZR_SNMP_PORT => $port1,

                    SERVER_PROPERTIES::SZR_API_PORT => $port3,
                    SERVER_PROPERTIES::SZR_UPDC_PORT => $port2,
                    SERVER_PROPERTIES::CUSTOM_SSH_PORT => $port4
                ));

                $platform->setConfigVariable(array(Modules_Platforms_Cloudstack::SZR_PORT_COUNTER.".{$cloudLocation}.{$sharedIpId}" => $port4), $environment, false);
            } catch (Exception $e) {
                $this->Logger->warn(new FarmLogMessage($this->FarmID,
                    sprintf(_("Cloudstack handler failed: %s."), $e->getMessage())
                ));
            }
        }
    }
?>