<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130326();
$ScalrUpdate->Run();

class Update20130326
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $db->Execute("ALTER TABLE  `farm_role_scripts` ENGINE = INNODB");
        $db->Execute("CREATE TABLE IF NOT EXISTS `farm_role_scripting_targets` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `farm_role_script_id` int(11) DEFAULT NULL,
          `target_type` enum('farmrole','behavior') DEFAULT NULL,
          `target` varchar(20) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `farm_role_script_id` (`farm_role_script_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
        $db->Execute("ALTER TABLE `farm_role_scripting_targets`
          ADD CONSTRAINT `farm_role_scripting_targets_ibfk_3` FOREIGN KEY (`farm_role_script_id`) REFERENCES `farm_role_scripts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");


        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}