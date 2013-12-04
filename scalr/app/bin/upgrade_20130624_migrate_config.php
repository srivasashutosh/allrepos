#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new MigrateConfigToYaml1();
$ScalrUpdate->Run();

class ScriptYmlExport1
{
    public $data = array();

    public function set($name, $val)
    {
        $token = strtok($name, '.');
        $ptr =& $this->data;
        while ($token !== false) {
            if (!isset($ptr[$token])) {
                $ptr[$token] = array();
            }
            $t = strtok('.');
            if ($t === false) break;
            $ptr =& $ptr[$token];
            $token = $t;
        }
        $ptr[$token] = $val;
    }

    public function emit()
    {
        return yaml_emit($this->data, YAML_UTF8_ENCODING, YAML_LN_BREAK);
    }
}

class MigrateConfigToYaml1
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $yml = new ScriptYmlExport1();

        if (!file_exists(APPPATH . '/etc/config.ini')) {
            printf("/app/etc/config.ini does not exist, therefore nothing to migrate.\n");
            exit();
        }

        if (!is_readable(APPPATH . '/etc/config.ini')) {
            printf("/app/etc/config.ini is not readable. Please set permissions.\n");
            exit();
        }

        if (is_readable(APPPATH . '/etc/config.yml')) {
            $oldyml = yaml_parse_file(APPPATH . '/etc/config.yml');
            if (isset($oldyml['scalr']['connections']['mongo'])) {
                //mongo has been renamed to mongodb
                $oldyml['scalr']['connections']['mongodb'] = $oldyml['scalr']['connections']['mongo'];
                unset($oldyml['scalr']['connections']['mongo']);
            }
        }

        $cfg = parse_ini_file(APPPATH . '/etc/config.ini', true);

        // Select config from db
        foreach ($db->GetAll("SELECT * FROM config") as $rsk) {
            $cfg[$rsk["key"]] = $rsk["value"];
        }

        $ConfigReflection = new ReflectionClass("CONFIG");
        // Define Constants and paste config into CONFIG struct
        foreach ($cfg as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $kk => $vv) {
                    $key = strtoupper("{$k}_{$kk}");
                    if ($ConfigReflection->hasProperty($key)) {
                        CONFIG::$$key = $vv;
                    }
                    if (!defined("CF_{$key}")) {
                        define("CF_{$key}", $vv);
                    }
                }
            } else {
                if (is_array($k)) {
                    $nk = strtoupper("{$k[0]}_{$k[1]}");
                } else {
                    $nk = strtoupper("{$k}");
                }
                if ($ConfigReflection->hasProperty($nk)) {
                    CONFIG::$$nk = $v;
                }
                if (!defined("CF_{$nk}")) {
                    define("CF_{$nk}", $v);
                }
            }
        }

        $time = microtime(true);

        $mapping = array(
            'TEMPLATES_PATH' => 'null',
            'DEBUG_PROFILING' => 'null',
            'DEBUG_APP' => 'null',
            'DEBUG_LEVEL' => 'null',
            'DEBUG_DB' => 'null',
            'CRYPTOKEY' => 'null',
            'CRYPTO_ALGO' => 'null',
            'TEAM_EMAILS' => 'null',
            'DEF_SOA_OWNER' => 'null',
            'DEF_SOA_TTL' => 'null',
            'DEF_SOA_REFRESH' => 'null',
            'DEF_SOA_RETRY' => 'null',
            'DEF_SOA_EXPIRE' => 'null',
            'DEF_SOA_MINTTL' => 'null',
            'DEF_SOA_PARENT' => 'null',
            'EMAIL_DSN' => 'null',
            'NAMEDCONFTPL' => 'null',
            'SNMPTRAP_PATH' => 'null',
            'EVENTS_TIMELINE_CACHE_LIFETIME' => 'null',
            'AJAX_PROCESSLIST_CACHE_LIFETIME' => 'null',
            'MONITORING_GRAPHS_URL' => 'null',
            'CRON_PROCESSES_NUMBER' => 'null',
            'SYNC_TIMEOUT' => 'null',
            'AUDITLOG_DSN' => 'null',
            'S3CFG_TEMPLATE' => 'null', #
            'STATISTICS_RRD_DEFAULT_FONT_PATH' => 'null', #

            'SYSDNS_SYSTEM' => 'scalr.dns.static.enabled', #

            'DB_DRIVER' => 'scalr.connections.mysql.driver', #
            'DB_HOST' => 'scalr.connections.mysql.host:scalr.connections.mysql.port', #

            'DB_NAME' => 'scalr.connections.mysql.name', #
            'DB_USER' => 'scalr.connections.mysql.user', #
            'DB_PASS' => 'scalr.connections.mysql.pass', #

            'LDAP_HOST' => 'scalr.connections.ldap.host:scalr.connections.ldap.port', #

            'LDAP_USER' => 'scalr.connections.ldap.user', #
            'LDAP_PASS' => 'scalr.connections.ldap.pass', #
            'LDAP_BASE_DN' => 'scalr.connections.ldap.base_dn', #

            'INSTANCES_CONNECTION_POLICY' => 'scalr.instances_connection_policy', #
            'HTTP_PROTO' => 'scalr.endpoint.scheme', #
            'EVENTHANDLER_URL' => 'scalr.endpoint.host', #

            'AUDITLOG_ENABLED' => 'scalr.auditlog.enabled', #

            'CLOUDYN_MASTER_EMAIL' => 'scalr.cloudyn.master_email', #
            'CLOUDYN_ENVIRONMENT' => 'scalr.cloudyn.environment', #

            'PHPUNIT_SKIP_FUNCTIONAL_TESTS' => 'scalr.phpunit.skip_functional_tests', #
            'PHPUNIT_TEST_USERID' => 'scalr.phpunit.userid', #
            'PHPUNIT_TEST_ENVID' => 'scalr.phpunit.envid', #

            'STATISTICS_RRD_DB_DIR' => 'scalr.stats_poller.rrd_db_dir', #
            'STATISTICS_RRD_GRAPH_STORAGE_PATH' => 'scalr.stats_poller.images_path', #
            'STATISTICS_RRD_STATS_URL' => 'scalr.stats_poller.graphics_url', #

            'GENERAL_ALLOWED_CLOUDS' => 'scalr.allowed_clouds', #

            'EMAIL_ADDRESS' => 'scalr.email.address', #
            'EMAIL_NAME' => 'scalr.email.name', #

            'DNS_TEST_DOMAIN_NAME' => 'scalr.dns.global.default_domain_name', #

            'SECGROUP_PREFIX' => 'scalr.aws.security_group_prefix', #

            'EVENTS_RSS_CACHE_LIFETIME' => 'scalr.rss_cache_lifetime', #
            'PMA_INSTANCE_IP_ADDRESS' => 'scalr.pma_instance_ip_address', #
            'MONITORING_SERVER_URL' => 'scalr.monitoring.server_url', #

            'SYNCHRONOUS_SCRIPT_TIMEOUT' => 'scalr.script.timeout.sync', #
            'ASYNCHRONOUS_SCRIPT_TIMEOUT' => 'scalr.script.timeout.async', #

            'GENERAL_AUTH_MODE' => 'scalr.auth_mode', #
        );

        $list = array();

        $extension = new \Scalr\System\Config\Extension();
        $extension->load();

        //Collects the list of defined constants which start with "CF_..."
        $constants = get_defined_constants(true);
        if (empty($constants['user'])) {
            printf("User defined constants list is empty. Something went wrong.\n");
            exit();
        }
        foreach ($constants['user'] as $name => $value) {
            if (substr($name, 0, 3) !== 'CF_') continue;
            $list[substr($name, 3)] = $value;
        }

        //Collects CONFIG class defined constants
        $refl = new ReflectionClass('CONFIG');
        foreach ($refl->getStaticProperties() as $name => $value) {
            if (isset($list[$name]) && $value !== $list[$name]) {
                printf("Warning. Values mismatch for constant %s (%s != %s)!\n", $name, $value, $list[$name]);
            }
            $list[$name] = $value;
        }

        foreach ($list as $name => $value) {
            if (isset($mapping[$name])) {
                $key = $mapping[$name];
                if ($key === 'null') {
                    continue;
                }
                if (strpos($key, ':')) {
                    //two keys
                    $keys = explode(':', $key);
                    if (preg_match('/^(.+?)\:([0-9]+)$/', $value, $m)) {
                        $values = array($m[1], (isset($m[2]) ? (int)$m[2] : null));
                    } else {
                        $values = array($value, null);
                    }
                }
                if (!isset($keys)) {
                    $keys = array($key);
                    $values = array($value);
                }
                for ($i = 0; $i < count($keys); $i++) {
                    $key = $keys[$i];
                    $value = $values[$i];
                    //If default value is defined and equal to value from old config
                    //we do not publish it in the new config.
                    if ($extension->defined($key)) {
                        $obj = $extension($key);
                        if (property_exists($obj, 'default') && $obj->default == $value) {
                            continue;
                        }
                    }
                    if ($value === '0' || $value === "1" || $value === 0 || $value === 1) {
                        $value = (bool) $value;
                    }
                    if ($key === 'scalr.allowed_clouds') {
                        $value = array_filter(explode(",", $value));
                    }
                    $yml->set($key, $value);
                }
                unset($keys);
                unset($values);
            } else {
                printf("Warning! %s does not exist in mapping\n", $name);
            }
        }

        if (!empty($oldyml)) {
            //Migrating old yaml file to new
            if (!empty($oldyml['scalr']['msg_sender'])) {
                if (!empty($yml->data['scalr']['msg_sender'])) {
                    $yml->set('scalr.msg_sender', array_replace_recursive($oldyml['scalr']['msg_sender'], $yml->data['scalr']['msg_sender']));
                } else {
                    $yml->set('scalr.msg_sender', $oldyml['scalr']['msg_sender']);
                }
            }
            if (!empty($oldyml['scalr']['stats_poller'])) {
                if (!empty($yml->data['scalr']['stats_poller'])) {
                    $yml->set('scalr.stats_poller', array_replace_recursive($oldyml['scalr']['stats_poller'], $yml->data['scalr']['stats_poller']));
                } else {
                    $yml->set('scalr.stats_poller', $oldyml['scalr']['stats_poller']);
                }
            }
        }

        //Migrates dns config
        if (is_readable(APPPATH . '/etc/dns.ini')) {
            $dns = parse_ini_file(APPPATH . '/etc/dns.ini', true);

            $yml->set('scalr.dns.mysql', isset($dns['db']) ? $dns['db'] : null);
            $yml->set('scalr.dns.static.enabled', (isset($dns['static']['enabled']) ?
                (bool)$dns['static']['enabled'] : false));
            $nslist = array();
            foreach (range(1, 4) as $i) {
                if (isset($dns['static']['ns' . $i])) {
                    $nslist[] = $dns['static']['ns' . $i];
                }
            }
            $yml->set('scalr.dns.static.nameservers', $nslist);
            $yml->set('scalr.dns.static.domain_name', (isset($dns['static']['domain_name']) ?
                $dns['static']['domain_name'] : ''));

            $yml->set('scalr.dns.global.enabled', (isset($dns['global']['enabled']) ? (bool)$dns['global']['enabled'] : false));
            $nslist = array();
            foreach (range(1, 4) as $i) {
                if (isset($dns['global']['ns' . $i])) {
                    $nslist[] = $dns['global']['ns' . $i];
                }
            }
            $yml->set('scalr.dns.global.nameservers', $nslist);
        } else {
            printf("Warning! Could not access to dns config (%s).\n", APPPATH . '/etc/dns.ini');
            $dns = array();
        }

        //Migrates security.ini config
        if (is_readable(APPPATH . '/etc/security.ini')) {
            $security = parse_ini_file(APPPATH . '/etc/security.ini', true);

            $yml->set('scalr.aws.security_group_name', isset($security['ec2']['security_group_name']) ? $security['ec2']['security_group_name'] : 'scalr.ip-pool');
            $yml->set('scalr.aws.ip_pool', isset($security['ip-pool']) && is_array($security['ip-pool']) ?
                array_values($security['ip-pool']) : array());

        } else {
            printf("Warning! Could not access to security config (%s).\n", APPPATH . '/etc/security.ini');
            $security = array();
        }

        //Adds several new required parameters
        $yml->set('scalr.billing.enabled', true);
        if (!isset($yml->data['scalr']['instances_connection_policy'])) {
            $yml->set('scalr.instances_connection_policy', 'public');
        }
        if (!isset($yml->data['scalr']['auth_mode'])) {
            $yml->set('scalr.auth_mode', 'scalr');
        }

        $ret = $yml->emit();
        if ($ret == false) {
            printf("Error! Could not emit config.yml file.\n");
            exit();
        }

        if (isset($oldyml)) {
            if (file_exists(APPPATH . '/etc/config.yml.bak')) {
                @unlink(APPPATH . '/etc/config.yml.bak');
            }
            //Preserves old yaml config.
            @rename(APPPATH . '/etc/config.yml', APPPATH . '/etc/config.yml.bak');
        }

        //Publishes new yaml config
        $ret = file_put_contents(APPPATH . '/etc/config.yml', $ret);
        if ($ret === false) {
            printf("Error! Could not write to config file %s\n", APPPATH . '/etc/config.yml');
            exit();
        }

        //Removes ini config.
        //It is necessary to rename this file in order to code starts working with yaml.
        @rename(APPPATH . '/etc/config.ini', APPPATH . '/etc/config.ini.bak');

        //Also we should clear cache file for the best
        if (is_readable(SCALR_CONFIG_CACHE_FILE)) {
            @unlink(SCALR_CONFIG_CACHE_FILE);
        }

        $t = round(microtime(true) - $time, 2);

        printf("Done!\nMigration process took %0.2f seconds\n\n", $t);
        printf(
            "Please pay your attention to following parameters in config.yml:\n"
          . "\tscalr.billing.enabled: yes|no\n"
          . "\n\n"
        );
    }
}