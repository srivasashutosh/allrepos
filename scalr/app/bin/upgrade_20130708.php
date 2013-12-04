#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130708();
$ScalrUpdate->Run();

class Update20130708
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $db->Execute("ALTER TABLE  `farm_role_scripts` ADD  `script_path` VARCHAR( 255 ) NULL ;");
        $db->Execute("ALTER TABLE  `apache_vhosts` DROP INDEX  `ix_name` , ADD UNIQUE  `ix_name` (  `name` ,  `env_id` ,  `farm_id` ) ;");
    }
}
