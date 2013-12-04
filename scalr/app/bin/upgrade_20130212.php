<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130212();
$ScalrUpdate->Run();

class Update20130212
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $db->Execute("
            CREATE TABLE IF NOT EXISTS `auditlog` (
                `id` VARCHAR(36) NOT NULL,
                `sessionid` VARCHAR(36) NOT NULL,
                `accountid` INT(11) DEFAULT NULL,
                `userid` INT(11) DEFAULT NULL,
                `email` VARCHAR(100) DEFAULT NULL,
                `envid` INT(11) DEFAULT NULL,
                `ip` INT UNSIGNED DEFAULT NULL,
                `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `message` VARCHAR(255) DEFAULT NULL,
                `datatype` VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `sessionid` (`sessionid`),
                KEY `accountid` (`accountid`),
                KEY `userid` (`userid`),
                KEY `envid` (`envid`),
                KEY `time` (`time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");

        $db->Execute("
            CREATE TABLE IF NOT EXISTS `auditlog_tags` (
                `logid` VARCHAR(36) NOT NULL,
                `tag` VARCHAR(36) NOT NULL,
                PRIMARY KEY (`logid`, `tag`),
                KEY `tag` (`tag`),
                CONSTRAINT `FK_auditlog_tags_logid`
                    FOREIGN KEY (`logid`)
                    REFERENCES `auditlog` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
        ");

        $db->Execute("
            CREATE TABLE IF NOT EXISTS `auditlog_data` (
                `logid` VARCHAR(36) NOT NULL,
                `key` VARCHAR(255) NOT NULL,
                `value` text,
                PRIMARY KEY (`logid`,`key`),
                KEY `key` (`key`),
                CONSTRAINT `FK_auditlog_data_logid`
                    FOREIGN KEY (`logid`)
                    REFERENCES `auditlog` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}