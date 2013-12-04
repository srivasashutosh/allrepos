<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130418();
$ScalrUpdate->Run();

class Update20130418
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $db->Execute("ALTER TABLE  `roles` ADD  `cat_id` INT( 11 ) NULL AFTER  `env_id`");

        $db->Execute("CREATE TABLE IF NOT EXISTS `role_categories` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `env_id` int(11) NOT NULL,
          `name` varchar(30) NOT NULL,
          PRIMARY KEY (`id`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;
        ");

        $db->Execute("INSERT INTO `role_categories` (`id`, `env_id`, `name`) VALUES
            (1, 0, 'Base'),
            (2, 0, 'Databases'),
            (3, 0, 'Application Servers'),
            (4, 0, 'Load Balancers'),
            (5, 0, 'Message Queues'),
            (6, 0, 'Caches'),
            (7, 0, 'Cloudfoundry'),
            (8, 0, 'Mixed');
        ");

        $roles = $db->Execute("SELECT id, behaviors FROM roles");
        while ($role = $roles->FetchRow()) {
            $db->Execute("UPDATE roles SET cat_id = ? WHERE id = ?", array(ROLE_BEHAVIORS::GetCategoryId($role['behaviors']), $role['id']));
        }

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }
}