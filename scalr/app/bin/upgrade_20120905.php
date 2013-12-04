<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20120905();
    $ScalrUpdate->Run();

    class Update20120905
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $idFilePath = dirname(__FILE__)."/../etc/id";

            if (!@file_get_contents($idFilePath)) {
                $uuid = Scalr::GenerateUID();
                $id = dechex(abs(crc32($uuid)));

                $res = @file_put_contents($idFilePath, $id);
                if (!$res)
                    exit("ERROR: Unable to write ID file ({$idFilePath}).");
            }


            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
