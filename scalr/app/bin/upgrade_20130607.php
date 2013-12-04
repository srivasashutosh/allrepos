#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130607();
$ScalrUpdate->Run();

class Update20130607
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $db->Execute("CREATE TABLE IF NOT EXISTS `farm_role_cloud_services` (
          `id` varchar(36) NOT NULL,
          `type` varchar(10) NOT NULL,
          `env_id` int(11) NOT NULL,
          `farm_id` int(11) NOT NULL,
          `farm_role_id` int(11) NOT NULL,
          `platform` varchar(36) DEFAULT NULL,
          `cloud_location` varchar(36) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `farm_role_id` (`farm_role_id`),
          KEY `farm_id` (`farm_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");

        $db->Execute("ALTER TABLE `farm_role_cloud_services`
  ADD CONSTRAINT `farm_role_cloud_services_ibfk_1` FOREIGN KEY (`farm_role_id`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
        ");

    }
}