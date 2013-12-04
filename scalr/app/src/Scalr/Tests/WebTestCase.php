<?php
namespace Scalr\Tests;

use \Scalr_UI_Request;
use \Scalr_UI_Response;
use \Scalr_UI_Controller;
use \CONFIG;

/**
 * WebTestCase class which is used for functional testing of the interface
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     21.02.2013
 */
abstract class WebTestCase extends TestCase
{
    /**
     * ID of the user which is used in the functional test
     * @var int
     */
    protected $_testUserId;

    /**
     * ID of the user's environment
     * @var int
     */
    protected $_testEnvId;

    /**
     * Scalr_Environment instance
     * @var \Scalr_Environment
     */
    private $env;

    /**
     * Error report level
     * @var int
     */
    private $errorLevel;

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $this->errorLevel = error_reporting();
        if (\Scalr::config('scalr.phpunit.skip_functional_tests')) {
            self::markTestSkipped();
        }
        if (\Scalr::config('scalr.phpunit.userid')) {
            $this->_testUserId = \Scalr::config('scalr.phpunit.userid');
        }
        if (\Scalr::config('scalr.phpunit.envid')) {
            $this->_testEnvId = \Scalr::config('scalr.phpunit.envid');
            $this->env = \Scalr_Environment::init()->loadById($this->_testEnvId);
        }
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->env = null;
        error_reporting($this->errorLevel);
        parent::tearDown();
    }

    /**
     * Makes a request to site
     *
     * @param   striing    $uri         A request uri
     * @param   array      $parameters  optional Request parameters
     * @param   string     $method      optional HTTP Request method
     * @param   array      $server      optional Additional server options
     * @param   array      $files       optional Uploaded files array
     * @return  array|string            Returns array which represents returned json object or raw body content in the
     *                                  case if the responce is not a json.
     */
    protected function request($uri, array $parameters = array(), $method = 'GET', array $server = array(), array $files = array())
    {
        //TODO implement logic for $_FILES
        $level = error_reporting(E_ERROR | E_RECOVERABLE_ERROR | E_USER_ERROR);
        $method = strtoupper($method);
        $aUrl = parse_url($uri);
        $_SERVER['QUERY_STRING'] = isset($aUrl['query']) ? $aUrl['query'] : '';
        $_SERVER['REQUEST_URI'] = (isset($aUrl['path']) ? $aUrl['path'] : '/')
          . (isset($aUrl['query']) ? '?' . $aUrl['query'] : '')
          . (isset($aUrl['fragment']) ? '#' . $aUrl['fragment'] : '');
        $path = trim(str_replace("?{$_SERVER['QUERY_STRING']}", "", $_SERVER['REQUEST_URI']), '/');
        if (!empty($_SERVER['QUERY_STRING'])) {
            foreach(explode('&', $_SERVER['QUERY_STRING']) as $v) {
                $v = array_map('html_entity_decode', explode('=', $v));
                $parameters[$v[0]] = isset($v[1]) ? $v[1] : null;
            }
        }
        foreach ($server as $k => $v) {
            $_SERVER[$k] = $v;
        }
        @ob_start();
        Scalr_UI_Response::getInstance()->resetResponse();
        Scalr_UI_Request::initializeInstance(
            Scalr_UI_Request::REQUEST_TYPE_UI, $this->_testUserId, $this->_testEnvId
        );

        Scalr_UI_Controller::handleRequest(explode('/', $path), $parameters);
        $content = @ob_get_contents();
        @ob_end_clean();
        $arr = @json_decode($content, true);
        error_reporting($level);
        return $arr === null ? $content : $arr;
    }

    /**
     * Asserts that response data array has necessary data keys.
     *
     * @param   array $keys         Array of the keys or Index array that looks like array($key => $constraint)
     * @param   array $responseData Response array
     * @param   bool  $checkAll     optional Whether it should check all data array or only the first.
     */
    protected function assertResponseDataHasKeys($keys, $responseData, $checkAll = false)
    {
        $this->assertInternalType('array', $responseData);
        if (isset($responseData['success']) && $responseData['success'] === false &&
            isset($responseData['errorMessage'])) {
            echo "\n" . $responseData['errorMessage'] . "\n";
        }
        $this->assertArrayHas(true, 'success', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        if (!empty($responseData['data'])) {
            $this->assertInternalType('array', $responseData['data']);
            foreach ($responseData['data'] as $obj) {
                $this->assertNotEmpty($obj);
                $this->assertInternalType('array', $obj);
                foreach ($keys as $key => $val) {
                    if (is_numeric($key)) {
                        $this->assertArrayHasKey($val, $obj);
                    } else {
                        $this->assertArrayHasKey($key, $obj);
                        $this->assertThat($obj[$key], $val);
                    }
                }
                if (!$checkAll) break;
            }
        }
    }

    /**
     * Gets an Scalr_Environment instance
     *
     * @return  \Scalr_Environment Returns environment instance
     */
    protected function getEnvironment()
    {
        return $this->env;
    }

    /**
     * Skip test if platform does not enabled
     *
     * @param   string    $platform
     */
    protected function skipIfPlatformDisabled($platform)
    {
        if (!$this->getEnvironment() || !$this->getEnvironment()->isPlatformEnabled($platform)) {
            $this->markTestSkipped($platform . ' platform is not enabled');
        }
    }
}