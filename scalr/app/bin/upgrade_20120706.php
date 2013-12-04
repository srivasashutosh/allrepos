<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20120706();
    $ScalrUpdate->Run();

    class Update20120706
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("ALTER TABLE  `farms` ADD  `created_by_id` INT( 11 ) NULL , ADD  `created_by_email` VARCHAR( 250 ) NULL");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
