<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130425();
$ScalrUpdate->Run();

class Update20130425
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        // Strip literal tags from apache vhosts
        $apacheVhosts = $db->Execute("SELECT * FROM apache_vhosts");
        while ($vhost = $apacheVhosts->FetchRow()) {
            $httpd_conf = str_replace(array("{literal}", "{/literal}"), array("", ""), $vhost['httpd_conf']);
            $httpd_conf_ssl = str_replace(array("{literal}", "{/literal}"), array("", ""), $vhost['httpd_conf_ssl']);

            $db->Execute("UPDATE apache_vhosts SET httpd_conf = ?, httpd_conf_ssl = ? WHERE id = ?", array(
                $httpd_conf, $httpd_conf_ssl, $vhost['id']
            ));
        }

        // Strip literal tags from nginx_vhost
        $templates = $db->Execute("SELECT id, value FROM farm_role_options WHERE hash = 'nginx_https_vhost_template'");
        while ($t = $templates->FetchRow()) {
            $v = str_replace(array("{literal}", "{/literal}"), array("", ""), $t['value']);
            $db->Execute("UPDATE farm_role_options SET value = ? WHERE id = ?", array($v, $t['id']));
        }

        $templates = $db->Execute("SELECT id, defval FROM role_parameters WHERE hash = 'nginx_https_vhost_template'");
        while ($t = $templates->FetchRow()) {
            $v = str_replace(array("{literal}", "{/literal}"), array("", ""), $t['defval']);
            $db->Execute("UPDATE role_parameters SET defval = ? WHERE id = ?", array($v, $t['id']));
        }

        $db->Execute("ALTER TABLE  `role_images` CHANGE  `agent_version`  `agent_version` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }
}