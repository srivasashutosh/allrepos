<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130311();
$ScalrUpdate->Run();

class Update20130311
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $db->Execute('ALTER TABLE  `messages` ADD  `ipaddress` VARCHAR( 15 ) NULL');

        $db->Execute("CREATE  TABLE IF NOT EXISTS `global_variables` (
              `env_id` INT NOT NULL ,
              `role_id` INT NOT NULL ,
              `farm_id` INT NOT NULL ,
              `farm_role_id` INT NOT NULL ,
              `name` VARCHAR(30) NOT NULL ,
              `value` TEXT NULL ,
              `flag_final` TINYINT(1) NULL DEFAULT 0 ,
              `flag_required` TINYINT(1) NULL DEFAULT 0 ,
              `scope` ENUM('env','role','farm','farmrole') NULL ,
              PRIMARY KEY (`env_id`, `role_id`, `farm_id`, `farm_role_id`, `name`) ,
              INDEX `name` (`name` ASC) ,
              INDEX `role_id` (`role_id` ASC) ,
              INDEX `farm_id` (`farm_id` ASC) ,
              INDEX `farm_role_id` (`farm_role_id` ASC) )
            ENGINE = InnoDB
        ");

        $db->Execute("ALTER TABLE  `global_variables` ADD FOREIGN KEY (  `env_id` ) REFERENCES `client_environments` (
            `id`
            ) ON DELETE CASCADE ON UPDATE NO ACTION ;
        ");

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}