#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130629();
$ScalrUpdate->Run();

class Update20130629
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $services = $db->Execute("SELECT * FROM farm_role_cloud_services");
        while ($service = $services->FetchRow()) {
            $dbFarmRole = DBFarmRole::LoadByID($service['farm_role_id']);
            if (!$dbFarmRole->GetSetting(DBFarmRole::SETTING_AWS_ELB_ENABLED)) {
                $dbFarmRole->SetSetting(DBFarmRole::SETTING_AWS_ELB_ENABLED, 1);
                $dbFarmRole->SetSetting(DBFarmRole::SETTING_AWS_ELB_ID, $service['id']);
            }
        }
    }
}
