<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20120606();
    $ScalrUpdate->Run();

    class Update20120606
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("CREATE TABLE IF NOT EXISTS `services_db_backups` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `status` varchar(25) DEFAULT NULL,
              `env_id` int(11) DEFAULT NULL,
              `farm_id` int(11) DEFAULT NULL,
              `service` varchar(50) DEFAULT NULL,
              `platform` varchar(25) DEFAULT NULL,
              `provider` varchar(20) DEFAULT NULL,
              `dtcreated` datetime DEFAULT NULL,
              `size` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `env_id` (`env_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

            $db->Execute("CREATE TABLE IF NOT EXISTS `services_db_backup_parts` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `backup_id` int(11) DEFAULT NULL,
              `path` text,
              `size` int(11) DEFAULT NULL,
              `seq_number` int(5) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `backup_id` (`backup_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
            ");

            $db->Execute("ALTER TABLE `services_db_backup_parts`
  ADD CONSTRAINT `services_db_backup_parts_ibfk_1` FOREIGN KEY (`backup_id`) REFERENCES `services_db_backups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
            ");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
