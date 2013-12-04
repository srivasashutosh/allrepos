#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130610();
$ScalrUpdate->Run();

class Update20130610
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $db->Execute("ALTER TABLE  `scheduler` ADD  `comments` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `type`");
    }
}
