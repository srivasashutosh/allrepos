<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20121219();
    $ScalrUpdate->Run();

    class Update20121219
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("CREATE TABLE IF NOT EXISTS `farm_role_storage_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_role_id` int(11) DEFAULT NULL,
  `index` tinyint(3) DEFAULT NULL,
  `type` varchar(15) DEFAULT NULL,
  `fs` varchar(15) DEFAULT NULL,
  `re_use` tinyint(1) DEFAULT NULL,
  `mount` tinyint(1) DEFAULT NULL,
  `mountpoint` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farm_role_id` (`farm_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

            $db->Execute("CREATE TABLE IF NOT EXISTS `farm_role_storage_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_role_id` int(11) DEFAULT NULL,
  `server_index` tinyint(4) DEFAULT NULL,
  `storage_config_id` int(11) DEFAULT NULL,
  `config` text,
  `storage_id` varchar(36) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `storage_id` (`storage_id`),
  KEY `storage_config_id` (`storage_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

            $db->Execute("CREATE TABLE IF NOT EXISTS `farm_role_storage_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_config_id` int(11) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `storage_config` (`storage_config_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

            $db->Execute("ALTER TABLE `farm_role_storage_devices`
  ADD CONSTRAINT `farm_role_storage_devices_ibfk_1` FOREIGN KEY (`storage_config_id`) REFERENCES `farm_role_storage_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

            $db->Execute("ALTER TABLE `farm_role_storage_settings`
  ADD CONSTRAINT `farm_role_storage_settings_ibfk_1` FOREIGN KEY (`storage_config_id`) REFERENCES `farm_role_storage_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

            $db->Execute("CREATE TABLE IF NOT EXISTS `storage_restore_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `manifest` text,
  `type` enum('full','incremental') NOT NULL,
  `parent_manifest` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

            $db->Execute("ALTER TABLE  `farm_role_storage_config` ADD  `status` VARCHAR( 20 ) NULL");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
