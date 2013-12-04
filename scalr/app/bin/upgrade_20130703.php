#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130703();
$ScalrUpdate->Run();

class Update20130703
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $roles = $db->Execute("SELECT * FROM roles WHERE origin='SHARED'");
        while ($role = $roles->FetchRow()) {
            if (!stristr($role['behaviors'], 'chef')) {
                $dbRole = DBRole::loadById($role['id']);
                $behaviors = $dbRole->getBehaviors();
                $behaviors[] = 'chef';
                $dbRole->setBehaviors($behaviors);
                $dbRole->save();
            }
        }
    }
}
