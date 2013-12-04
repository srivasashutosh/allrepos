#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130627();
$ScalrUpdate->Run();

class Update20130627
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $db->Execute('ALTER TABLE  `account_users` CHANGE  `loginattempts`  `loginattempts` INT( 4 ) NULL DEFAULT NULL');
    }
}
