<?php
    define("NO_TEMPLATES",1);

    require_once(dirname(__FILE__).'/../src/prepend.inc.php');

    set_time_limit(0);

    $ScalrUpdate = new Update20121211();
    $ScalrUpdate->Run();

    class Update20121211
    {
        function Run()
        {
            global $db;

            $time = microtime(true);

            $db->Execute("ALTER TABLE  `role_scripts` ADD  `hash` VARCHAR( 12 ) NULL");

            $roleScripts = $db->Execute("SELECT id FROM role_scripts WHERE `hash` IS NULL");
            while ($script = $roleScripts->FetchRow()) {
                $hash = Scalr_Util_CryptoTool::sault(12);
                $db->Execute("UPDATE role_scripts SET `hash` = ? WHERE id = ?", array($hash, $script['id']));
            }


            //$db->Execute("ALTER TABLE  `farm_role_scripting_params` DROP FOREIGN KEY  `farm_role_scripting_params_ibfk_2` ;");
            $db->Execute("ALTER TABLE  `farm_role_scripting_params` DROP KEY `uniq`");
            $db->Execute("ALTER TABLE  `farm_role_scripting_params` ADD  `hash` VARCHAR( 12 ) NULL");


            $farmRoleParams = $db->Execute("SELECT * FROM farm_role_scripting_params");
            while ($p = $farmRoleParams->FetchRow()) {
                $hash = $db->GetOne("SELECT hash FROM role_scripts WHERE id = ?", array($p['role_script_id']));
                $db->Execute("UPDATE farm_role_scripting_params SET hash = ?, role_script_id = '0' WHERE id = ?", array($hash, $p['id']));
            }


            $db->Execute("ALTER TABLE  `scalr`.`farm_role_scripting_params` ADD UNIQUE  `uniq` (  `farm_role_id` ,  `hash` ( 12 ) ,  `farm_role_script_id` )");


            $db->Execute("ALTER TABLE  `servers` CHANGE  `platform`  `platform` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");

            print "Done.\n";

            $t = round(microtime(true)-$time, 2);

            print "Upgrade process took {$t} seconds\n\n\n";
        }

        function migrate()
        {

        }
    }
?>
