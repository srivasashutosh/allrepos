<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20120802();
    $ScalrUpdate->Run();

    class Update20120802
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("ALTER TABLE  `ec2_ebs` ADD  `type` ENUM(  'standard',  'io1' ) NOT NULL DEFAULT  'standard' AFTER  `snap_id` , ADD  `iops` INT( 4 ) NULL AFTER  `type`");
            $db->Execute("ALTER TABLE  `messages` CHANGE  `message`  `message` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
            $db->Execute("ALTER TABLE  `client_environment_properties` ADD  `cloud` VARCHAR( 20 ) NULL");

            $db->Execute("ALTER TABLE  `servers_history` ADD  `launch_reason` VARCHAR( 255 ) NULL AFTER  `dtterminated_scalr`");
            $db->Execute("ALTER TABLE  `servers_history` ADD  `env_id` INT( 11 ) NULL , ADD  `farm_id` INT( 11 ) NULL , ADD  `farm_roleid` INT( 11 ) NULL");
            $db->Execute("ALTER TABLE  `servers_history` ADD  `server_index` INT( 5 ) NULL");
            $db->Execute("ALTER TABLE  `servers_history` ADD  `shutdown_confirmed` TINYINT( 3 ) NOT NULL DEFAULT  '0'");


            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
