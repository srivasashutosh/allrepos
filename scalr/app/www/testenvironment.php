<?php

$windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$sapi_type = php_sapi_name();
$phpBranch = substr(PHP_VERSION, 0, 3);

$PHPSITE = 'http://php.net/manual/en';

if (substr($sapi_type, 0, 3) == 'cli') {
    $cli = true;
} else {
    $cli = false;
}

$err = array();

if (!$windows) {
    // Check POSIX
    if (!function_exists('posix_getpid')) {
        $err[] = "Cannot find posix_getpid function. Make sure that POSIX Functions enabled. Look at $PHPSITE/posix.installation.php";
    }
    // Check PCNTL
    if ($cli && !function_exists('pcntl_fork')) {
        $err[] = "Cannot find pcntl_fork function. Make sure that PCNTL Functions enabled. Look at $PHPSITE/pcntl.installation.php";
    }

    // Check SYSVMSG and System V semaphore
    if (!function_exists('shm_attach') || !function_exists('msg_get_queue')) {
        $err[] = "System V semaphore must be enabled. Look at $PHPSITE/sem.installation.php";
    }

    // Check SNMP
    if (!function_exists('snmpget')) {
        $err[] = "Cannot find SNMP functions. Make sure that SNMP Functions enabled. Look at $PHPSITE/snmp.installation.php";
    }

    // Check RRDTool
//     if (class_exists('RRDUpdater')) {
//         $err[] = "rrdtool extension must be installed. Look at http://oss.oetiker.ch/rrdtool/pub/contrib/";
//     }
}

// Check PECL_HTTP
if (!class_exists('HTTPRequest')) {
    $err[] = "Cannot find PECL_HTTP functions. Make sure that PECL_HTTP Functions enabled. Look at $PHPSITE/http.install.php";
} else if (version_compare(phpversion('http'), '1.7.4', '<')) {
    $err[] = 'Version of the Pecl_Http extension must be >= 1.7.4.';
}

//SSH2
if (!function_exists('ssh2_exec')) {
    $err[] = "Ssh2 pecl extension must be installed. Look at $PHPSITE/ssh2.installation.php";
}

//cURL
if (!function_exists('curl_exec')) {
    $err[] = "cURL extension is mandatory and must be installed. Look at $PHPSITE/curl.installation.php";
}

//Socket
if (!function_exists('socket_create')) {
    $err[] = "Sockets must be enabled. Look at $PHPSITE/sockets.installation.php";
}

//YAML
if (!function_exists('yaml_parse')) {
    $err[] = "Yaml extension is required for the application. Look at $PHPSITE/yaml.installation.php";
}

// Check DOM
if (!class_exists('DOMDocument')) {
    $err[] = "Cannot find DOM functions. Make sure that DOM Functions enabled. Look at $PHPSITE/dom.installation.php";
}

// Check SimpleXML
if (!function_exists('simplexml_load_string')) {
    $err[] = "Cannot find simplexml_load_string function. Make sure that SimpleXML Functions enabled. Look at $PHPSITE/simplexml.setup.php";
}

// Check MySQLi
if (!function_exists('mysqli_connect')) {
    $err[] = "Cannot find mysqli_connect function. Make sure that MySQLi Functions enabled. Look at $PHPSITE/mysqli.installation.php";
}

// Check GetText
if (!function_exists('gettext')) {
    $err[] = "Cannot find gettext function. Make sure that GetText Functions enabled. Look at $PHPSITE/gettext.installation.php";
}

// Check MCrypt
if (!function_exists('mcrypt_encrypt')) {
    $err[] = "Cannot find mcrypt_encrypt function. Make sure that mCrypt Functions enabled. Look at $PHPSITE/mcrypt.installation.php";
}

// Check MHash
if (!function_exists('hash')) {
    $err[] = "Cannot find mhash function. Make sure that HASH Functions enabled.";
}

if (!function_exists('json_encode')) {
    $err[] = "Cannot find JSON functions. Make sure that JSON Functions enabled.";
}

// Check OpenSSL
if (!function_exists('openssl_verify')) {
    $err[] = "Cannot find OpenSSL functions. Make sure that OpenSSL Functions enabled.";
}

// Check SOAP
if (!class_exists('SoapClient')) {
    $err[] = "Cannot find SoapClient class. Make sure that SoapClient Extension enabled. Look at $PHPSITE/soap.installation.php";
}

// Checks php sessings
if (ini_get('safe_mode') == 1)
    $err[] = "PHP safe mode enabled. Please disable it.";

if (ini_get('register_gloabls') == 1)
    $err[] = "PHP register globals enabled. Please disable it.";

if (version_compare($phpBranch, '5.3', '<') ||
    $phpBranch == '5.3' && version_compare(PHP_VERSION, '5.3.16', '<') ||
    $phpBranch == '5.4' && version_compare(PHP_VERSION, '5.4.5', '<')) {
    //look into phpunit test app/src/Scalr/Tests/SoftwareDependencyTest.php
    $err[] = "You have " . phpversion() . " PHP version. It must be >= 5.3.16 for 5.3 branch or >= 5.4.5 for 5.4 branch";
}

// If all extensions installed
if (count($err) == 0) {
    $cryptokeyPath = __DIR__ . "/../etc/.cryptokey";
    if (!file_exists($cryptokeyPath) || filesize($cryptokeyPath) == 0) {
        if ($windows) {
            $key = '';
            for ($i = 0; $i < 13; ++$i) {
                $key .= sha1(uniqid());
            }
        } else {
            $key = file_get_contents('/dev/urandom', null, null, 0, 512);
        }

        if (strlen($key) < 500) {
            throw new Exception("Null key generated");
        }

        $key = substr(base64_encode($key), 0, 512);
        $res = file_put_contents($cryptokeyPath, $key);
        if ($res == 0) {
            $err[] = "Unable to create etc/.cryptokey file. Please create empty etc/.cryptokey and chmod it to 0777.";
        }
    }

    //Checks cache folder
    $cachePath = __DIR__ . '/../cache';
    if (!is_dir($cachePath)) {
        //Tries to create cache directory automatically
        if (@mkdir($cachePath) === false) {
            $err[] = sprintf(
                'Could not create %s folder automatically. ' .
                'Please create this folder manually with read/write access permissions for webserver and crontab actors.',
                realpath($cachePath)
            );
        }
    }

    if (count($err) == 0) {
        try {
            require_once __DIR__ . '/../src/prepend.inc.php';

            $container = \Scalr::getContainer();
            $config = $container->config;
            $db = $container->adodb;
        } catch (\Exception $e) {
            $err[] = "Could not initialize bootstrap. " . $e->getMessage();
        }
    }
}

$congrats = "Congratulations, your environment settings match Scalr requirements!";
$worningWin = "Please pay attention to the fact that Windows system is not allowed for production environment!";

if (!$cli) {
    if (count($err) == 0) {
        print "<span style='color:green'>" . $congrats . "</span><br>\n";
        if ($windows) {
            print "<span style='color:orange'>" . $worningWin . "</span>\n";
        }
    } else {
        print "<span style='color:red'>Errors:</span><br>";
        foreach ($err as $e)
            print "<span style='color:red'>&bull; {$e}</span><br>";
    }
} else {
    if (count($err) == 0) {
        print "\033[32m" . $congrats . "\033[0m\n";
        if ($windows) {
            print "033[31m" . $worningWin . "\033[0m\n";
        }
    } else {
        print "\033[31mErrors:\033[0m\n";
        foreach ($err as $e)
            print "\033[31m- {$e}\033[0m\n";
    }
}

