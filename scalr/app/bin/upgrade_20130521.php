<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130521();
$ScalrUpdate->Run();

class Update20130521
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $db->Execute("ALTER TABLE  `elastic_ips` ADD  `allocation_id` VARCHAR( 30 ) NULL");

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }
}