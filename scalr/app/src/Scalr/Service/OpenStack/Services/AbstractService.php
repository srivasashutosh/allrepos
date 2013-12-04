<?php
namespace Scalr\Service\OpenStack\Services;

use Scalr\Service\OpenStack\OpenStack;
use Scalr\Service\OpenStack\Exception\ServiceException;

/**
 * OpenStack abstract service interface class
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    04.12.2012
 */
abstract class AbstractService
{
    /**
     * Conventional service name.
     * @var array
     */
    private static $serviceName = array();

    /**
     * Api handler for the service.
     * @var object
     */
    private $apiHandler;

    /**
     * @var OpenStack
     */
    private $openstack;

    /**
     * @var array
     */
    private $availableHandlers;

    /**
     * Misc. cache
     *
     * @var array
     */
    private $cache;

    /**
     * Constructor
     *
     * @param OpenStack $openstack
     */
    public function __construct(OpenStack $openstack)
    {
        $this->openstack = $openstack;
    }

    /**
     * Gets an OpenStack instance
     *
     * @return OpenStack Returns OpenStack instance
     */
    public function getOpenStack()
    {
        return $this->openstack;
    }

    /**
     * Gets service interface name.
     *
     * Returned name must start with the lower case letter.
     *
     * @return string Returns service interface name.
     */
    public static function getName()
    {
        $class = get_called_class();
        if (!isset(self::$serviceName[$class])) {
            $name = self::getOriginalServiceName($class);
            if ($name !== null) {
                self::$serviceName[$class] = lcfirst($name);
            } else {
                throw new ServiceException(sprintf(
                    'Invalid service interface class name "%s". It should end with "Service".', $class
                ));
            }
        }
        return self::$serviceName[$class];
    }

    /**
     * Gets an original service name
     *
     * @param   string    $class A Service class name
     * @return  string    Returns service name or NULL if class is not a service.
     */
    protected static function getOriginalServiceName($class)
    {
        if (preg_match('#(?<=\\\\|^)([^\\\\]+)Service$#', $class, $m)) {
            $name = $m[1];
        } else {
            $name = null;
        }
        return $name;
    }

    /**
     * Gets endpoint url.
     *
     * @return string Returns Endpoint url without trailing slash
     */
    public function getEndpointUrl()
    {
        $type = $this->getType();
        $cfg = $this->getOpenStack()->getConfig();
        $region = $cfg->getRegion();
        if ($cfg->getAuthToken() === null) {
            $url = $cfg->getIdentityEndpoint();
        } else {
            if (!isset($this->cache['endpoint'])) {
                $version = substr($this->getVersion(), 1);
                $this->cache['endpoint'] = $cfg->getAuthToken()->getEndpointUrl($type, $region, $version);
            }
            $url = $this->cache['endpoint'];
        }
        return $url;
    }

    /**
     * Gets an API Handler for the service
     *
     * @return  object Returns an API Handler for the service
     */
    public function getApiHandler()
    {
        if ($this->apiHandler === null) {
            //This method is declared in the ServiceInterface and must be defined in children classes.
            $ver = $this->getVersion();
            $class = get_class($this);
            $name = self::getOriginalServiceName($class);
            if ($name === null) {
                throw new ServiceException(sprintf(
                    'Invalid service interface class name "%s". It should end with "Service".', $class
                ));
            }
            $apiClass = __NAMESPACE__ . '\\' . $name . '\\' . $ver . '\\' . $name . 'Api';
            $this->apiHandler = new $apiClass($this);
        }
        return $this->apiHandler;
    }

    /**
     * Gets the list of available handlers
     *
     * @return  array Returns the list of available handlers
     */
    public function getAvailableHandlers()
    {
        if (!isset($this->availableHandlers)) {
            $this->availableHandlers = array();
            $class = get_class($this);
            $name = self::getOriginalServiceName($class);
            $list = @glob(__DIR__ . '/' . $name . '/Handler/*Handler.php');
            if (!empty($list)) {
                foreach ($list as $filename) {
                    if (preg_match('#[\\\\|/]([a-z0-9]+)Handler\.php$#i', $filename, $m)) {
                        $this->availableHandlers[lcfirst($m[1])] = null;
                    }
                }
            }
        }
        return $this->availableHandlers;
    }

    /**
     * Used to retrieve service handlers
     * @param   string    $name
     */
    public function __get($name)
    {
        $available = $this->getAvailableHandlers();
        if (array_key_exists($name, $available)) {
            if ($this->availableHandlers[$name] instanceof AbstractServiceHandler) {
                return $this->availableHandlers[$name];
            } else {
                $class = __NAMESPACE__
                    . '\\' . self::getOriginalServiceName(get_class($this))
                    . '\\Handler\\' . ucfirst($name) . 'Handler';
                $this->availableHandlers[$name] = new $class ($this);
                return $this->availableHandlers[$name];
            }
        }
    }
}