#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130709();
$ScalrUpdate->Run();

class Update20130709
{
    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $db->Execute("DROP TABLE  `scheduler_tasks`;");
        $db->Execute("ALTER TABLE  `ui_errors` ADD  `user_id` INT( 11 ) NOT NULL");
        $db->Execute("ALTER TABLE  `ui_errors` DROP INDEX  `file` , ADD UNIQUE  `info` (  `file` ,  `lineno` ,  `short` ,  `account_id` ,  `user_id` )");

        $db->Execute("ALTER TABLE `client_environments` DROP  `is_system`, DROP  `color`");
    }
}
