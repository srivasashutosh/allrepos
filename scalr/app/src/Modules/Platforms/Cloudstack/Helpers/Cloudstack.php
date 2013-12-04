<?php

    class Modules_Platforms_Cloudstack_Helpers_Cloudstack
    {
        public static function farmSave(DBFarm $DBFarm, array $roles)
        {
            foreach ($roles as $DBFarmRole)
            {
                if (!in_array($DBFarmRole->Platform, array(SERVER_PLATFORMS::CLOUDSTACK, SERVER_PLATFORMS::IDCF, SERVER_PLATFORMS::UCLOUD)))
                    continue;

                $location = $DBFarmRole->CloudLocation;

                $platform = PlatformFactory::NewPlatform($DBFarmRole->Platform);

                $cs = Scalr_Service_Cloud_Cloudstack::newCloudstack(
                    $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_URL, $DBFarm->GetEnvironmentObject()),
                    $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_KEY, $DBFarm->GetEnvironmentObject()),
                    $platform->getConfigVariable(Modules_Platforms_Cloudstack::SECRET_KEY, $DBFarm->GetEnvironmentObject()),
                    $DBFarmRole->Platform
                );

                $networkId = $DBFarmRole->GetSetting(DBFarmRole::SETTING_CLOUDSTACK_NETWORK_ID);
                $set = fasle;
                foreach ($cs->listNetworks("", "", "", $networkId) as $network) {
                    if ($network->id == $networkId) {
                        $DBFarmRole->SetSetting(DBFarmRole::SETTING_CLOUDSTACK_NETWORK_TYPE, $network->type);
                        $set = true;
                    }
                }

                if (!$set)
                    throw new Exception("Unable to get GuestIPType for Network #{$networkId}. Please try again later or choose another network offering.");
            }
        }


        public static function farmValidateRoleSettings($settings, $rolename)
        {
            if (!$settings[DBFarmRole::SETTING_CLOUDSTACK_SERVICE_OFFERING_ID])
                throw new Exception(sprintf(_("Service offering for '%s' cloudstack role should be selected on 'Cloudstack settings' tab"), $rolename));

            //if (!$settings[DBFarmRole::SETTING_CLOUDSTACK_NETWORK_ID])
            //    throw new Exception(sprintf(_("Network offering for '%s' cloudstack role should be selected on 'Cloudstack settings' tab"), $rolename));
        }

        public static function farmUpdateRoleSettings(DBFarmRole $DBFarmRole, $oldSettings, $newSettings)
        {

        }
    }

?>