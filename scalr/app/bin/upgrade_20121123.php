<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20121123();
    $ScalrUpdate->Run();

    class Update20121123
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("ALTER TABLE  `farm_role_scripts` ADD  `debug` VARCHAR( 255 ) NULL");

            $db->Execute('ALTER TABLE  `scripting_log` ADD  `event_server_id` VARCHAR( 36 ) NULL ,
                ADD  `script_name` VARCHAR( 50 ) NULL'
            );

            $db->Execute("ALTER TABLE  `scripting_log` ADD  `exec_time` INT( 11 ) NULL ,
                ADD  `exec_exitcode` INT( 11 ) NULL"
            );

            $db->Execute("ALTER TABLE  `scripting_log` ADD  `event_id` VARCHAR( 36 ) NULL ,
                ADD INDEX (  `event_id` )"
            );

            $db->Execute("ALTER TABLE  `events` ADD  `event_server_id` VARCHAR( 36 ) NULL ,
                ADD INDEX (  `event_server_id` )"
            );

            $db->Execute("CREATE TABLE IF NOT EXISTS `event_definitions` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `account_id` int(11) NOT NULL,
                  `env_id` int(11) NOT NULL,
                  `name` varchar(25) NOT NULL,
                  `description` text NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
            ");

            $db->Execute("ALTER TABLE  `scalr`.`scripting_log` ADD INDEX  `event_server_id` (  `event_server_id` )");


            $db->Execute("CREATE TABLE IF NOT EXISTS `server_alerts` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `env_id` int(11) DEFAULT NULL,
                  `farm_id` int(11) DEFAULT NULL,
                  `farm_roleid` int(11) DEFAULT NULL,
                  `server_index` int(11) DEFAULT NULL,
                  `server_id` varchar(36) DEFAULT NULL,
                  `metric` varchar(20) DEFAULT NULL,
                  `dtoccured` datetime DEFAULT NULL,
                  `dtlastcheck` datetime DEFAULT NULL,
                  `dtsolved` datetime DEFAULT NULL,
                  `details` varchar(255) DEFAULT NULL,
                  `status` enum('resolved','failed') DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `main2` (`server_id`,`metric`,`status`),
                  KEY `env_id` (`env_id`),
                  KEY `farm_role` (`farm_id`,`farm_roleid`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
            ");

            $db->Execute("ALTER TABLE `server_alerts`
                  ADD CONSTRAINT `server_alerts_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                  ADD CONSTRAINT `server_alerts_ibfk_2` FOREIGN KEY (`env_id`) REFERENCES `client_environments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
              ");


            $db->Execute("CREATE TABLE IF NOT EXISTS `farm_role_scripting_params` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `farm_role_id` int(11) DEFAULT NULL,
              `role_script_id` int(11) DEFAULT NULL,
              `farm_role_script_id` int(11) DEFAULT NULL,
              `params` text,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq` (`farm_role_id`,`role_script_id`,`farm_role_script_id`),
              KEY `farm_roleid` (`farm_role_id`),
              KEY `role_script_id` (`role_script_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
            ");

            $db->Execute("ALTER TABLE `farm_role_scripting_params`
              ADD CONSTRAINT `farm_role_scripting_params_ibfk_3` FOREIGN KEY (`farm_role_id`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
              ADD CONSTRAINT `farm_role_scripting_params_ibfk_2` FOREIGN KEY (`role_script_id`) REFERENCES `role_scripts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
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
