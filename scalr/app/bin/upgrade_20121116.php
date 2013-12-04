<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20121116();
    $ScalrUpdate->Run();

    class Update20121116
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute('ALTER TABLE `ui_debug_log` DROP `watch_client_id`, DROP `watch_client_hash`;');
            $db->Execute('ALTER TABLE `ui_debug_log` ADD  `user_id` INT( 11 ) NULL DEFAULT NULL');
            $db->Execute('ALTER TABLE `ui_debug_log` CHANGE  `client_id`  `account_id` INT( 11 ) NULL DEFAULT NULL');

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
