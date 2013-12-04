<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20120328();
    $ScalrUpdate->Run();

    class Update20120328
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("CREATE TABLE IF NOT EXISTS `role_scripts` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `role_id` int(11) DEFAULT NULL,
                  `event_name` varchar(50) DEFAULT NULL,
                  `target` varchar(15) DEFAULT NULL,
                  `script_id` int(11) DEFAULT NULL,
                  `version` varchar(10) DEFAULT NULL,
                  `timeout` int(5) DEFAULT NULL,
                  `issync` tinyint(1) DEFAULT NULL,
                  `params` text,
                  `order_index` int(11) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `role_id` (`role_id`),
                  KEY `script_id` (`script_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
            ");

            $db->Execute("ALTER TABLE `role_scripts`
                  ADD CONSTRAINT `role_scripts_ibfk_2` FOREIGN KEY (`script_id`) REFERENCES `scripts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                  ADD CONSTRAINT `role_scripts_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
              ");

            $db->Execute("ALTER TABLE  `farms` ADD FOREIGN KEY (  `clientid` ) REFERENCES  `scalr`.`clients` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION ;");

            $db->Execute("ALTER TABLE  `server_operations` ADD  `timestamp` INT( 11 ) NULL AFTER  `id`");
            $db->Execute("ALTER TABLE  `server_operations` ADD  `status` VARCHAR( 20 ) NULL AFTER  `timestamp`");

            $db->Execute("ALTER TABLE  `scalr`.`server_operations` DROP INDEX  `server_id` , ADD INDEX  `server_id` (  `server_id` ,  `name` ( 20 ) )");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
