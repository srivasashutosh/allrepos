#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130620();
$ScalrUpdate->Run();

class Update20130620
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $farms = $db->Execute("SELECT id FROM farms");
        while ($farm = $farms->FetchRow()) {
            $dbFarm = DBFarm::LoadByID($farm['id']);
            if (!$dbFarm->GetSetting(DBFarm::SETTING_TIMEZONE)) {
                $env = $dbFarm->GetEnvironmentObject();
                $tz = $env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::TIMEZONE);
                $dbFarm->SetSetting(DBFarm::SETTING_TIMEZONE, $tz);
            }
        }
    }
}
