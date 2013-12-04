<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130328();
$ScalrUpdate->Run();

class Update20130328
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $db->Execute("ALTER TABLE `script_revisions` DROP INDEX  `scriptid_revision` , ADD UNIQUE  `scriptid_revision` (  `scriptid` ,  `revision` )");

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}