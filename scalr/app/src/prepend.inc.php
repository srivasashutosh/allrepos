<?php

define("TRANSACTION_ID", uniqid("tran"));
define("DEFAULT_LOCALE", "en_US");

@date_default_timezone_set(@date_default_timezone_get());

// Attempt to normalize settings
@error_reporting(E_ALL ^E_NOTICE ^E_USER_NOTICE ^E_DEPRECATED);

@ini_set('session.bug_compat_42', '0');
@ini_set('session.bug_compat_warn', '0');

// Increase execution time limit
set_time_limit(180);

// Locale init
$locale = DEFAULT_LOCALE;
define("LOCALE", $locale);

// Environment stuff
$base = dirname(__FILE__);
define("SRCPATH", $base);
define("APPPATH", "{$base}/..");
define("CACHEPATH", "$base/../cache");
define("SCALR_CONFIG_FILE", APPPATH . '/etc/config.yml');
define("SCALR_CONFIG_CACHE_FILE", CACHEPATH . '/.config');
define("SCALR_VERSION", trim(@file_get_contents(APPPATH . "/etc/version")));

$ADODB_CACHE_DIR = CACHEPATH . "/adodb";

define("SCALR_TEMPLATES_PATH", APPPATH . "/templates/" . LOCALE);

// Require autoload definition
$classpath[] = $base;
$classpath[] = $base . "/externals/ZF-1.10.8";
set_include_path(get_include_path() . PATH_SEPARATOR . join(PATH_SEPARATOR, $classpath));

require_once SRCPATH . "/autoload.inc.php";

spl_autoload_register("__autoload");

//Container witn adodb service needs to be defined in the first turn, as much depends on it.
Scalr::initializeContainer();

$idFilePath = APPPATH . '/etc/id';
$id = trim(@file_get_contents($idFilePath));
if (!$id) {
    $uuid = Scalr::GenerateUID();
    $id = dechex(abs(crc32($uuid)));

    $res = @file_put_contents($idFilePath, $id);
    if (!$res)
        exit("ERROR: Unable to write ID file ({$idFilePath}).");
}

define("SCALR_ID", $id);

// Define log4php contants
define("LOG4PHP_DIR", SRCPATH . '/externals/apache-log4php-2.0.0-incubating/src/main/php');
require_once LOG4PHP_DIR . '/Logger.php';

require_once SRCPATH . "/class.TaskQueue.php";
require_once SRCPATH . "/class.FarmTerminationOptions.php";
require_once SRCPATH . "/class.DataForm.php";
require_once SRCPATH . "/class.DataFormField.php";
require_once SRCPATH . "/queue_tasks/abstract.Task.php";
require_once SRCPATH . "/queue_tasks/class.FireDeferredEventTask.php";
require_once SRCPATH . '/externals/adodb5-18/adodb-exceptions.inc.php';
require_once SRCPATH . '/externals/adodb5-18/adodb.inc.php';

$cfg = Scalr::getContainer()->config;
set_error_handler("Scalr::errorHandler");

try {
    $db = Scalr::getDb();
} catch (Exception $e) {
    throw new Exception("Service is temporary not available. Please try again in a minute. [DB]");
}

//TODO [SCALRCORE-375] Use new config.yml. This section will be completely removed by the first of January 2014.
//This is needed to ensure operability of migrating script /bin/migrate_config.php
if (Scalr::getContainer()->get('config.type') == 'ini') {
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
                define("CF_{$key}", $vv);
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
            define("CF_{$nk}", $v);
        }
    }
    unset($cfg);
    unset($ConfigReflection);

    CONFIG::$HTTP_PROTO = (CONFIG::$HTTP_PROTO) ? CONFIG::$HTTP_PROTO : "http";
    CONFIG::$SYSDNS_SYSTEM = 0;
}

require_once SRCPATH . '/class.LoggerAppenderScalr.php';
require_once SRCPATH . '/class.LoggerPatternLayoutScalr.php';
require_once SRCPATH . '/class.FarmLogMessage.php';
require_once SRCPATH . '/class.ScriptingLogMessage.php';
require_once SRCPATH . '/class.LoggerPatternParserScalr.php';
require_once SRCPATH . '/class.LoggerBasicPatternConverterScalr.php';
require_once SRCPATH . '/class.LoggerFilterCategoryMatch.php';

Logger::configure(APPPATH.'/etc/log4php.xml', 'LoggerConfiguratorXml');
$Logger = Logger::getLogger('Application');

// Require observer interfaces
require_once APPPATH . '/observers/interface.IDeferredEventObserver.php';
require_once APPPATH . '/observers/interface.IEventObserver.php';

//FIXME Tender integration should be placed to config file
define("TENDER_APIKEY", "");
define("TENDER_SITEKEY", "");

//FIXME this stuff must be removed to config
define('SCALR_PMA_KEY', '');

//FIXME Recaptcha keys must be part of the config
define('SCALR_RECAPTCHA_PUBLIC_KEY', '');
define('SCALR_RECAPTCHA_PRIVATE_KEY', '');
