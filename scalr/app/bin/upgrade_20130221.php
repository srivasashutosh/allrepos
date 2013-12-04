<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130221();
$ScalrUpdate->Run();

class Update20130221
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $result = $db->getAll('SELECT a.id, cp.value FROM account_users AS a LEFT JOIN client_environments AS c ON c.client_id = a.account_id
            LEFT JOIN client_environment_properties AS cp ON cp.env_id = c.id WHERE cp.name = "timezone" GROUP BY a.id
        ');

        foreach ($result as $value) {
            $db->Execute('REPLACE INTO account_user_settings SET user_id = ?, name = ?, value = ?', array($value['id'], 'ui.timezone', $value['value']));
        }

        $db->Execute('ALTER TABLE  `ui_debug_log` CHANGE  `dtadded`  `dtadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}