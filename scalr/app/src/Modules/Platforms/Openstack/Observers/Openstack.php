<?php
    class Modules_Platforms_Openstack_Observers_Openstack extends EventObserver
    {
        public $ObserverName = 'Openstack';

        function __construct()
        {
            parent::__construct();
        }

        /**
         * @return \Scalr\Service\OpenStack\OpenStack
         */
        protected function getOsClient(Scalr_Environment $environment, $cloudLocation)
        {
            return $environment->openstack($this->platform, $cloudLocation);
        }


        public function OnBeforeHostTerminate(BeforeHostTerminateEvent $event)
        {
            if (!in_array($event->DBServer->platform, array(SERVER_PLATFORMS::OPENSTACK, SERVER_PLATFORMS::RACKSPACENG_UK, SERVER_PLATFORMS::RACKSPACENG_US)))
                return;

            try {
                $dbFarmRole = $event->DBServer->GetFarmRoleObject();
            } catch (Exception $e){}

            try {
                if ($dbFarmRole->GetSetting(DBFarmRole::SETTING_OPENSTACK_IP_POOL)) {
                    $environment = $event->DBServer->GetEnvironmentObject();

                    $osClient = $environment->openstack($event->DBServer->platform, $event->DBServer->GetCloudLocation());

                    $osClient->servers->removeFloatingIp($event->DBServer->GetCloudServerID(), $event->DBServer->remoteIp);
                }
            } catch (Exception $e) {
                Logger::getLogger("OpenStackObserver")->fatal("OpenStackObserver observer failed: " . $e->getMessage());
            }
        }
    }
?>