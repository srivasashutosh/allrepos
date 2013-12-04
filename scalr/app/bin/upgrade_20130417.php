<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130417();
$ScalrUpdate->Run();

class Update20130417
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $images = $db->Execute("SELECT * FROM role_images WHERE architecture IS NULL AND platform = 'ec2' ORDER BY id DESC");
        while ($image = $images->FetchRow()) {
            $role = DBRole::loadById($image['role_id']);

            if ($role->clientId == 0)
                continue;

            $environemnt = Scalr_Environment::init()->loadById($role->envId);

            try {
                $acrh = $environemnt->aws($image['cloud_location'])->ec2->image->describe($image['image_id'])->get(0)->architecture;
                $db->Execute("UPDATE role_images SET architecture = ? WHERE id = ?", array($acrh, $image['id']));
            } catch (Exception $e) {
                if (stristr($e->getMessage(), "does not exist") && stristr($e->getMessage(), 'The image id')) {
                    //$db->Execute("DELETE FROM role_images WHERE id = ?", array($image['id']));
                    print "Removed {$image['image_id']} because: {$e->getMessage()}\n";
                    continue;
                } elseif (stristr($e->getMessage(), "AWS was not able to validate the provided access credentials")) {
                    continue;
                } elseif (stristr($e->getMessage(), "You are not subscribed to this service")) {
                    continue;
                } elseif (stristr($e->getMessage(), "Invalid id")) {
                    $db->Execute("DELETE FROM role_images WHERE id = ?", array($image['id']));
                    continue;
                }

                var_dump($e->getMessage());
                exit();
            }
        }

        $db->Execute("ALTER TABLE  `roles` DROP  `is_stable` , DROP  `approval_state` , DROP  `szr_version` ;");
        $db->Execute("ALTER TABLE  `roles` DROP  `architecture` ;");
        $db->Execute("ALTER TABLE  `roles` ADD  `os_family` VARCHAR( 30 ) NULL ,
            ADD  `os_generation` VARCHAR( 10 ) NULL ,
            ADD  `os_version` VARCHAR( 10 ) NULL
        ");


        //SSL certs refactoring
        $db->Execute("ALTER TABLE  `apache_vhosts` ADD  `ssl_cert_id` INT( 11 ) NULL");

        $db->Execute("CREATE TABLE IF NOT EXISTS `services_ssl_certs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `env_id` int(11) NOT NULL,
          `name` varchar(40) NOT NULL,
          `ssl_pkey` text NULL,
          `ssl_cert` text NULL,
          `ssl_cabundle` text NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

        $vhosts = $db->Execute("SELECT * FROM apache_vhosts WHERE is_ssl_enabled = '1'");
        while ($vhost = $vhosts->FetchRow()) {
            $db->Execute("INSERT INTO services_ssl_certs SET
                env_id = ?,
                name = ?,
                ssl_pkey = ?,
                ssl_cert = ?,
                ssl_cabundle = ?
            ", array(
                $vhost['env_id'],
                $vhost['name'],
                $vhost['ssl_key'],
                $vhost['ssl_cert'],
                $vhost['ca_cert']
            ));
            $certId = $db->Insert_ID();

            $db->Execute("UPDATE apache_vhosts SET ssl_cert_id = ? WHERE id = ?", array($certId, $vhost['id']));
        }

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}