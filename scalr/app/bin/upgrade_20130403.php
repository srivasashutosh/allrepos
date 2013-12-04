<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130403();
$ScalrUpdate->Run();

class Update20130403
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $db->Execute("CREATE TABLE IF NOT EXISTS `services_db_backups_history` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `farm_role_id` int(11) NOT NULL,
            `operation` enum('backup','bundle') NOT NULL,
            `date` datetime NOT NULL,
            `status` enum('ok','error') NOT NULL,
            `error` varchar(255) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `main` (`farm_role_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
        ");
        $db->Execute("ALTER TABLE `services_db_backups_history`
            ADD CONSTRAINT `services_db_backups_history_ibfk_1` FOREIGN KEY (`farm_role_id`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        $db->Execute("ALTER TABLE  `services_db_backups_history` CHANGE  `error`  `error` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}