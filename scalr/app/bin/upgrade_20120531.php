<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20120531();
    $ScalrUpdate->Run();

    class Update20120531
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("CREATE TABLE IF NOT EXISTS `ui_errors` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `tm` datetime NOT NULL,
                    `file` varchar(255) NOT NULL,
                    `lineno` varchar(255) NOT NULL,
                    `url` varchar(255) NOT NULL,
                    `short` varchar(255) NOT NULL,
                    `message` text NOT NULL,
                    `browser` varchar(255) NOT NULL,
                    `cnt` int(11) NOT NULL DEFAULT '1',
                    `account_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `file` (`file`,`lineno`,`short`)
                ) ENGINE=MyISAM ;
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
