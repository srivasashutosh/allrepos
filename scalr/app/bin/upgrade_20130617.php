#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130617();
$ScalrUpdate->Run();

class Update20130617
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $db->Execute("ALTER TABLE  `services_ssl_certs` ADD  `ssl_pkey_password` TEXT NULL ;");
    }
}
