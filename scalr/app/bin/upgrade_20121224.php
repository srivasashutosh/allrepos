<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20121224();
    $ScalrUpdate->Run();

    class Update20121224
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("ALTER TABLE  `messages` ADD  `json_message` TEXT NULL AFTER  `message`");
            $db->Execute("ALTER TABLE  `messages` ADD  `in_processing` TINYINT( 1 ) NULL DEFAULT  '0'");

            $db->Execute("ALTER TABLE  `servers_history` ADD  `scu_used` FLOAT( 10, 4 ) NULL DEFAULT  '0', ADD  `scu_reported` FLOAT( 10, 4 ) NULL DEFAULT  '0', ADD  `scu_updated` TINYINT( 1 ) NULL DEFAULT  '0'");
            $db->Execute("ALTER TABLE  `servers_history` ADD  `scu_collecting` TINYINT( 1 ) NULL DEFAULT  '0'");

            $db->Execute("ALTER TABLE  `servers_history` CHANGE  `scu_used`  `scu_used` FLOAT( 11, 2 ) NULL DEFAULT  '0.00'");
            $db->Execute("ALTER TABLE  `servers_history` CHANGE  `scu_reported`  `scu_reported` FLOAT( 11, 2 ) NULL DEFAULT  '0.00'");

            $db->Execute("CREATE TABLE IF NOT EXISTS `farm_role_config_presets` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `farm_roleid` int(11) DEFAULT NULL,
              `behavior` varchar(25) DEFAULT NULL,
              `cfg_filename` varchar(25) DEFAULT NULL,
              `cfg_key` varchar(100) DEFAULT NULL,
              `cfg_value` text,
              PRIMARY KEY (`id`),
              KEY `main` (`farm_roleid`,`behavior`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
            ");

            $db->Execute("ALTER TABLE `farm_role_config_presets`
                ADD CONSTRAINT `farm_role_config_presets_ibfk_1` FOREIGN KEY (`farm_roleid`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
            ");

            $db->Execute("ALTER TABLE `account_groups` ADD `color` VARCHAR( 16 ) NOT NULL DEFAULT ''");

            $db->Execute("ALTER TABLE  `storage_snapshots` CHANGE  `id`  `id` VARCHAR( 36 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

            $db->Execute("ALTER TABLE  `account_groups` CHANGE  `color`  `color` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT  ''");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
