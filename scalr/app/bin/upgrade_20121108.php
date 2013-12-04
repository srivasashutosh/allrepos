<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20121108();
    $ScalrUpdate->Run();

    class Update20121108
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("ALTER TABLE  `farm_role_scaling_metrics` ADD FOREIGN KEY (  `farm_roleid` ) REFERENCES  `scalr`.`farm_roles` (
                `id`
                ) ON DELETE CASCADE ON UPDATE NO ACTION
            ");

            $db->Execute("ALTER TABLE  `bundle_tasks` ADD  `os_family` VARCHAR( 20 ) NULL ,
                ADD  `os_name` VARCHAR( 255 ) NULL ,
                ADD  `os_version` VARCHAR( 10 ) NULL
            ");

            $db->Execute("ALTER TABLE  `farm_roles` ADD FOREIGN KEY (  `farmid` ) REFERENCES  `scalr`.`farms` (
                `id`
                ) ON DELETE CASCADE ON UPDATE NO ACTION ;
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
