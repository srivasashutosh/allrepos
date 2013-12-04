<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130320();
$ScalrUpdate->Run();

class Update20130320
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $db->Execute("ALTER TABLE  `scheduler` ADD  `target_server_index` INT( 11 ) NULL DEFAULT NULL AFTER  `target_id`");
        $rows = $db->GetAll('SELECT * FROM `scheduler` WHERE `target_type` = ?', array('instance'));
        foreach ($rows as $row) {
            $serverArgs = explode(':', $row['target_id']);
            $db->Execute('UPDATE `scheduler` SET target_id = ?, `target_server_index` = ? WHERE id = ?', array($serverArgs[0], $serverArgs[1], $row['id']));
        }

        $db->Execute("ALTER TABLE  `scheduler` CHANGE  `target_id`  `target_id` INT( 11 ) NULL DEFAULT NULL COMMENT  'id of farm, farm_role from other tables'");
        $db->Execute("ALTER TABLE  `scheduler` CHANGE  `type`  `type` ENUM(  'script_exec',  'terminate_farm',  'launch_farm' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
        $db->Execute("ALTER TABLE  `scheduler` CHANGE  `target_type`  `target_type` ENUM(  'farm',  'role',  'instance' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}