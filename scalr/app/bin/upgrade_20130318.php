<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130318();
$ScalrUpdate->Run();

class Update20130318
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $row = $db->GetRow("
            DESC `auditlog_data` `value`
        ");

        if (!empty($row)) {
            $db->Execute("DELETE FROM `auditlog`");
            $db->Execute("
                ALTER TABLE `auditlog_data`
                CHANGE `value` `old_value` TEXT
            ");
            $db->Execute("
                ALTER TABLE `auditlog_data`
                ADD `new_value` TEXT
            ");
            $db->Execute("
                ALTER TABLE `auditlog_data`
                ADD INDEX idx_old_value (`old_value` (8) ASC)
            ");
            $db->Execute("
                ALTER TABLE `auditlog_data`
                ADD INDEX idx_new_value (`new_value` (8) ASC)
            ");
        }
        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}