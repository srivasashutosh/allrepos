<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20130206();
    $ScalrUpdate->Run();

    class Update20130206
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            //$db->Execute("ALTER TABLE  `farms` ADD  `changed_by_id` INT( 11 ) NOT NULL , ADD  `changed_time` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
            $db->Execute("ALTER TABLE  `farm_role_storage_config` DROP PRIMARY KEY , ADD UNIQUE ( `id` )");
            $db->Execute("ALTER TABLE  `farm_role_storage_settings` DROP FOREIGN KEY  `farm_role_storage_settings_ibfk_1`");
            $db->Execute("ALTER TABLE  `farm_role_storage_devices` DROP FOREIGN KEY  `farm_role_storage_devices_ibfk_1`");
            $db->Execute("ALTER TABLE  `farm_role_storage_config` CHANGE  `id`  `id` VARCHAR( 36 ) NOT NULL");
            $db->Execute("ALTER TABLE  `farm_role_storage_devices` CHANGE  `storage_config_id`  `storage_config_id` VARCHAR( 36 ) NULL DEFAULT NULL");
            $db->Execute("ALTER TABLE  `farm_role_storage_devices` ADD FOREIGN KEY (  `storage_config_id` ) REFERENCES  `farm_role_storage_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");
            $db->Execute("ALTER TABLE  `farm_role_storage_settings` CHANGE  `storage_config_id`  `storage_config_id` VARCHAR( 36 ) NULL DEFAULT NULL");
            $db->Execute("ALTER TABLE  `farm_role_storage_settings` ADD FOREIGN KEY (  `storage_config_id` ) REFERENCES  `farm_role_storage_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");

            $db->Execute("CREATE TABLE IF NOT EXISTS `account_user_vars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_name` (`user_id`,`name`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
");
            $db->Execute("ALTER TABLE  `account_user_vars` ADD FOREIGN KEY (  `user_id` ) REFERENCES `account_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION ;");

            $db->Execute("ALTER TABLE  `clients` ADD  `priority` INT( 1 ) NULL DEFAULT  '0'");

            $db->Execute("ALTER TABLE  `farms` ADD  `changed_by_id` INT( 11 ) NULL , ADD  `changed_time` VARCHAR( 32 ) NULL");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
