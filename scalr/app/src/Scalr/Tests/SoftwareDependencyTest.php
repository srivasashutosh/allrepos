<?php
namespace Scalr\Tests;

/**
 * Software dependency test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     30.10.2012
 */
class SoftwareDependencyTest extends TestCase
{
    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Here we should add assertions for all php dependencies which is usded by Scalr.
     *
     * @test
     */
    public function testDependencies()
    {
        $windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $phpBranch = substr(PHP_VERSION, 0, 3);

        $this->assertTrue(
            !($phpBranch == '5.3' && version_compare(PHP_VERSION, '5.3.16', '<')) &&
            !($phpBranch == '5.4' && version_compare(PHP_VERSION, '5.4.5', '<')) &&
            version_compare($phpBranch, '5.3', '>=') ,
            sprintf('You have %s PHP version. It must be >= 5.3.16 for 5.3 branch or >= 5.4.5 for 5.4 branch', PHP_VERSION)
        );

        $this->assertTrue(
            function_exists('hash'),
            'Cannot find mhash function. Make sure that HASH Functions enabled.'
        );

        $this->assertTrue(
            function_exists('json_encode'),
            'Cannot find JSON function. Make sure that JSON Functions enabled.'
        );

        $this->assertTrue(
            function_exists('openssl_verify'),
            'Cannot find OpenSSL functions. Make sure that OpenSSL Functions enabled.'
        );

        $this->assertTrue(
            class_exists('HttpRequest'),
            'Pecl_Http extension is required for the application. '
          . 'Please install it http://www.php.net/manual/en/http.install.php'
        );

        $this->assertTrue(
            version_compare(phpversion('http'), '1.7.4', '>='),
            'Version of the Pecl_Http extension must be greater than or equal 1.7.4.'
        );

        $this->assertTrue(
            function_exists('yaml_parse'),
            'Yaml extension is required for the application. '
          . 'Please install it http://php.net/manual/en/yaml.installation.php'
        );

        $this->assertTrue(
            class_exists('mysqli'),
            'Mysqli database driver is mandatory and must be installed. '
          . 'Look at http://php.net/manual/en/mysqli.installation.php'
        );

        $this->assertTrue(
            function_exists('curl_exec'),
            'cURL extension is mandatory and must be installed. '
          . 'Look at http://ua1.php.net/manual/en/curl.installation.php'
        );

        $this->assertTrue(
            function_exists('mcrypt_encrypt'),
            'mcrypt extension is mandatory and must be installed. '
          . 'Look at http://ua1.php.net/manual/en/mcrypt.installation.php'
        );

        $this->assertTrue(
            class_exists('SoapClient'),
            'SOAP must be enabled. '
          . 'Look at http://www.php.net/manual/en/soap.installation.php'
        );

        $this->assertTrue(
            function_exists('socket_create'),
            'Sockets must be enabled. '
          . 'Look at http://php.net/manual/en/sockets.installation.php'
        );

        $this->assertTrue(
            function_exists('gettext'),
            'Gettext must be enabled. '
          . 'Look at http://php.net/manual/en/gettext.installation.php'
        );

        $this->assertTrue(
            function_exists('simplexml_load_string'),
            'SimpleXML must be enabled. '
          . 'Look at http://ua1.php.net/manual/en/simplexml.setup.php'
        );

        $this->assertTrue(
            function_exists('ssh2_exec'),
            'Ssh2 pecl extension must be installed. '
          . 'Look at http://ua1.php.net/manual/en/ssh2.installation.php'
        );

        $this->assertTrue(
            class_exists('DOMDocument'),
            'DOM must be enabled. '
          . 'Look at http://ua1.php.net/manual/en/dom.installation.php'
        );

        if (!$windows) {
            $this->assertTrue(
                function_exists('shm_attach'),
                'System V semaphore must be enabled. '
              . 'Look at http://www.php.net/manual/en/sem.installation.php'
            );

            $this->assertTrue(
                function_exists('pcntl_fork'),
                'PCNTL extension is mandatory and must be installed. '
              . 'Look at http://www.php.net/manual/en/pcntl.installation.php'
            );

            $this->assertTrue(
                function_exists('posix_getgid'),
                'POSIX must be enabled. '
              . 'Look at http://www.php.net/manual/en/posix.installation.php'
            );

            $this->assertTrue(
                class_exists('SNMP'),
                'SNMP must be enabled. '
              . 'Look at http://ua1.php.net/manual/en/snmp.installation.php'
            );

//             $this->assertTrue(
//                 class_exists('RRDUpdater'),
//                 'rrdtool extension must be installed.'
//               . 'Look at http://oss.oetiker.ch/rrdtool/pub/contrib/'
//             );
        }

        /*
        $this->assertTrue(
            class_exists('Mongo'),
            'Mongo extension is required for the application. '
          . 'Please install it http://www.php.net/manual/en/mongo.installation.php'
        );

        $this->assertTrue(
            version_compare(phpversion('mongo'), '1.2.12', '>='),
            'Version of mongodb driver must be greater than or equal 1.2.12'
        );
        */

        //Please add assertions here
    }
}