<?php

/**
 * TODO [SCALRCORE-375] Use new config.yml. This will be completely removed by the first of January 2014.
 * @deprecated since 14.06.2013
 */
final class CONFIG
{
    public static $DB_DRIVER;
    public static $DB_HOST;
    public static $DB_NAME;
    public static $DB_USER;
    public static $DB_PASS;

    public static $DEBUG_PROFILING;
    public static $DEBUG_APP;
    public static $DEBUG_LEVEL;
    public static $DEBUG_DB;

    public static $AUDITLOG_ENABLED;
    public static $AUDITLOG_DSN;

    public static $CLOUDYN_MASTER_EMAIL;
    public static $CLOUDYN_ENVIRONMENT;

    public static $PHPUNIT_SKIP_FUNCTIONAL_TESTS = true;
    public static $PHPUNIT_TEST_USERID;
    public static $PHPUNIT_TEST_ENVID;

    public static $STATISTICS_RRD_DEFAULT_FONT_PATH;
    public static $STATISTICS_RRD_DB_DIR;
    public static $STATISTICS_RRD_STATS_URL;
    public static $STATISTICS_RRD_GRAPH_STORAGE_PATH;

    public static $INSTANCES_CONNECTION_POLICY = 'public'; // allowed values: public | local | auto

    //ec2,openstack,cloudstack,idcf,gce,eucalyptus,rackspace,rackspacenguk,rackspacengus,nimbula
    public static $GENERAL_ALLOWED_CLOUDS = 'ec2,openstack,cloudstack,idcf,gce,eucalyptus,rackspace,rackspacenguk,rackspacengus,nimbula';

    public static $GENERAL_AUTH_MODE = 'scalr'; /* scalr | ldap */

    /**
     * Encrypted registrar CP password
     *
     * @staticvar string
     */

    public static $CRYPTOKEY;

    public static $CRYPTO_ALGO;

    public static $EMAIL_ADDRESS;

    public static $EMAIL_NAME;
    public static $TEAM_EMAILS;

    /******* DNS ****************/

    public static $DEF_SOA_OWNER;
    public static $DEF_SOA_TTL = 14400;
    public static $DEF_SOA_REFRESH = 14400;
    public static $DEF_SOA_RETRY = 7200;
    public static $DEF_SOA_EXPIRE = 3600000;
    public static $DEF_SOA_MINTTL = 300;
    public static $DEF_SOA_PARENT;

    public static $DNS_TEST_DOMAIN_NAME = 'scalr.ws';
    public static $SYSDNS_SYSTEM = 0;

    /*******************************/

    public static $EVENTHANDLER_URL;

    public static $SECGROUP_PREFIX;

    public static $EMAIL_DSN;

    public static $NAMEDCONFTPL;

    public static $S3CFG_TEMPLATE;

    public static $SNMPTRAP_PATH;

    /**
     * Cache lifetimes
     */
    public static $EVENTS_RSS_CACHE_LIFETIME = 300;
    public static $EVENTS_TIMELINE_CACHE_LIFETIME = 300;
    public static $AJAX_PROCESSLIST_CACHE_LIFETIME = 120;

    public static $HTTP_PROTO = "http";

    public static $PMA_INSTANCE_IP_ADDRESS = '184.73.181.141';

    //**** Statistics and monitoring *******//
    public static $MONITORING_SERVER_URL = 'http://monitoring.scalr.net';
    public static $MONITORING_GRAPHS_URL = 'https://monitoring-graphs.scalr.net';

    public static $CRON_PROCESSES_NUMBER = 5;

    public static $SYNC_TIMEOUT = 300; // Minutes

    public static $SYNCHRONOUS_SCRIPT_TIMEOUT = 180; // seconds
    public static $ASYNCHRONOUS_SCRIPT_TIMEOUT = 1200; // seconds

    /**
     * List all available properties through reflection
     *
     * @return Array or names
     * @deprecated
     */
    public static function GetKeys()
    {
        $retval = array();
        $ReflectionClassThis = new ReflectionClass(__CLASS__);
        foreach($ReflectionClassThis->getStaticProperties() as $Property)
        {
            $retval[] = $Property->name;
        }
        return($retval);
    }

    /**
     * Get all values
     *
     * @param  $key Key name
     * @return array Array or values
     * @deprecated
     */
    public static function GetValues($key)
    {
        return get_class_vars(__CLASS__);
    }

    /**
     * Get value of property by it's name
     *
     * @param  $key Key name
     * @return string
     * @deprecated
     */
    public static function GetValue($key)
    {
        //property_exists
        $ReflectionClassThis = new ReflectionClass(__CLASS__);
        if ($ReflectionClassThis->hasProperty($key))
        {
            return $ReflectionClassThis->getStaticPropertyValue($key);
        }
        else
        {
            throw new Exception(sprintf(_("Called %s::GetValue('{$key}') for non-existent property {$key}"), __CLASS__));
        }
    }
}