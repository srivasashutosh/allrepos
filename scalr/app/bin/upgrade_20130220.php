<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20130220();
    $ScalrUpdate->Run();

    class Update20130220
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("ALTER TABLE  `farm_role_storage_devices` ADD  `placement` VARCHAR( 36 ) NULL AFTER  `server_index`");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
