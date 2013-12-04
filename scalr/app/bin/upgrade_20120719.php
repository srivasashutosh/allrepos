<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20120719();
    $ScalrUpdate->Run();

    class Update20120719
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("ALTER TABLE  `services_db_backups` ADD  `cloud_location` VARCHAR( 50 ) NULL");
            $db->Execute("ALTER TABLE  `role_images` CHANGE  `os_family`  `os_family` VARCHAR( 25 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
            $db->Execute("UPDATE `role_images` SET os_family = 'red hat enterprise linux' WHERE `os_family` LIKE '%red hat%'");


            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
