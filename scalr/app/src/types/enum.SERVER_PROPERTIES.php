<?
    class SERVER_PROPERTIES
    {
        /** SCALARIZR PROPERTIES */
        const SZR_KEY			= 'scalarizr.key';
        // permanent, one-time
        const SZR_KEY_TYPE		= 'scalarizr.key_type';

        const SZR_ONETIME_KEY_EXPIRED = 'scalarizr.onetime_key_expired';

        // 0.5 or 0.2-139
        const SZR_VESION		= 'scalarizr.version';
        const SZR_UPD_CLIENT_VERSION = 'scalarizr.update_client.version';

        // Import properties
        const SZR_IMPORTING_ROLE_NAME = 'scalarizr.import.role_name';
        const SZR_IMPORTING_BEHAVIOR = 'scalarizr.import.behaviour';
        const SZR_IMPORTING_LAST_LOG_MESSAGE = 'scalarizr.import.last_log_msg';
        const SZR_IMPORTING_BUNDLE_TASK_ID = 'scalarizr.import.bundle_task_id';
        const SZR_IMPORTING_OS_FAMILY = 'scalarizr.import.os_family';
        const SZR_IMPORTING_LEAVE_ON_FAIL = 'scalarizr.import.leave_on_fail';
        const SZR_DEV_SCALARIZR_BRANCH = 'scalarizr.dev.scalarizr.branch';

        const SZR_IMPORTING_CHEF_SERVER_ID = 'scalarizr.import.chef.server_id';
        const SZR_IMPORTING_CHEF_ENVIRONMENT = 'scalarizr.import.chef.environment';
        const SZR_IMPORTING_CHEF_ROLE_NAME = 'scalarizr.import.chef.role_name';


        const SZR_IS_INIT_FAILED = 'scalarizr.is_init_failed';
        const SZR_IS_INIT_ERROR_MSG = 'scalarizr.init_error_msg';
        const LAUNCH_ERROR = 'system.launch.error';
        const SUB_STATUS 		= 'system.sub-status';

        const SZR_IMPORTING_MYSQL_SERVER_TYPE = 'scalarizr.import.mysql_server_type';

        const SZR_SNMP_PORT = 'scalarizr.snmp_port';
        const SZR_CTRL_PORT = 'scalarizr.ctrl_port';
        const SZR_API_PORT = 'scalarizr.api_port';
        const SZR_UPDC_PORT = 'scalarizr.updc_port';
        const CUSTOM_SSH_PORT = 'scalarizr.ssh_port';

        /** DATABASE PROPERTIES */
        const DB_MYSQL_MASTER	= 'db.mysql.master';
        const DB_MYSQL_REPLICATION_STATUS = 'db.mysql.replication_status';


        /** DNS PROPERTIES */
        const EXCLUDE_FROM_DNS	= 'dns.exclude_instance';


        /** System PROPERTIES **/
        const ARCHITECTURE = "system.architecture";
        const REBOOTING = "system.rebooting";
        const INITIALIZED_TIME = "system.date.initialized";

        /** Healthcheck PROPERTIES **/
        const HEALTHCHECK_FAILED = "system.healthcheck.failed";
        const HEALTHCHECK_TIME = "system.healthcheck.time";

        /** Statistics **/
        const STATISTICS_BW_IN 	= "statistics.bw.in";
        const STATISTICS_BW_OUT	= "statistics.bw.out";
        const STATISTICS_LAST_CHECK_TS	= "statistics.lastcheck_ts";

    }
?>
