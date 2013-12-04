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

            $db->Execute("ALTER TABLE  `services_db_backups` CHANGE  `size`  `size` BIGINT( 20 ) NULL DEFAULT NULL");
            $db->Execute("ALTER TABLE  `roles` ADD  `is_devel` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `is_stable`");
            $db->Execute("ALTER TABLE  `bundle_tasks` CHANGE  `snapshot_id`  `snapshot_id` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
