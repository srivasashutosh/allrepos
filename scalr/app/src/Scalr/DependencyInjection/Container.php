<?php

namespace Scalr\DependencyInjection;

/**
 * DependencyInjection container.
 * Inspired by Fabien Potencier.
 *
 * @author   Vitaliy Demidov    <vitaliy@scalr.com>
 * @since    19.10.2012
 *
 * @property string $awsRegion
 *           The AWS region derived from user's environment.
 *
 * @property string $awsSecretAccessKey
 *           The AWS sercret access key taken from user's environment.
 *
 * @property string $awsAccessKeyId
 *           The Aws access key id taken from user's environment.
 *
 * @property string $awsAccountNumber
 *           The Aws account number.
 *
 * @property \Scalr_Session $session
 *           The Scalr Session isntance.
 *
 * @property \Scalr\Service\Cloudyn $cloudyn
 *           The Cloudyn instance for the current user
 *
 * @property \Scalr_Environment $environment
 *           Recently loaded Scalr_Environment instance.
 *
 * @property \Scalr\Service\Aws $aws
 *           The Aws instance for the last instantiated user's environment.
 *
 * @property \Scalr_UI_Request $request
 *           The Scalr_UI_Request instance.
 *
 * @property \Scalr_Account_User $user
 *           The Scalr_Account_User instance which is property for the request.
 *
 * @property \Scalr\Logger\AuditLog $auditLog
 *           The AuditLog.
 *
 * @property \Scalr\Logger\LoggerStorageInterface $auditLogStorage
 *           The AuditLogStorage
 *
 * @property \Scalr\SimpleMailer $mailer
 *           Returns the new instance of the SimpleMailer class.
 *           This is not a singletone.
 *
 * @property \ADODB_mysqli $adodb
 *           Gets an ADODB mysqli Connection object
 *
 * @property \ADODB_mysqli $dnsdb
 *           Gets an ADODB mysqli Connection to PDNS Database
 *
 * @property \Scalr\System\Config\Yaml $config
 *           Gets configuration
 *
 *
 * @method   mixed config()
 *           config(string $name)
 *           Gets config value for the dot notation access key
 *
 * @method   \Scalr\Service\Aws aws()
 *           aws(string|\DBServer|\DBFarmRole|\DBEBSVolume $awsRegion = null,
 *               string|\Scalr_Environment $awsAccessKeyId = null,
 *               string $awsSecretAccessKey = null,
 *               string $certificate = null,
 *               string $privateKey = null)
 *           Gets an Aws instance.
 *
 * @method   \Scalr\Service\OpenStack\OpenStack openstack()
 *           openstack($platform, $region)
 *           Gets an Openstack instance for the current environment
 *
 * @method   \ADODB_mysqli adodb()
 *           adodb()
 *           Gets an ADODB mysqli Connection object
 *
 * @method   \Scalr\Net\Ldap\LdapClient ldap()
 *           ldap($user, $password)
 *           Gets a  new instance of LdapClient. If user and pass are not specified for
 *           scalr.connections.ldap section in the config the user and password which are specified
 *           for calling this method will be used.
 */
class Container
{
    /**
     * @var Container
     */
    static private $instance;

    /**
     * Container of services
     *
     * @var array
     */
    protected $values = array();

    /**
     * Shared objects pseudo-static cache
     *
     * @var array
     */
    protected $shared = array();

    /**
     * Associated services for release memory
     *
     * @var array
     */
    protected $releasehooks = array();

    protected function __construct()
    {
    }

    private final function __clone()
    {
    }

    /**
     * Gets singleton instance of the Container
     *
     * @return Container
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Container();
        }
        return self::$instance;
    }

    /**
     * Resets singleton object.
     *
     * It can be used for phpunit testing purposes.
     */
    static public function reset()
    {
        self::$instance = null;
    }

    /**
     * @param   string           $id
     * @throws  RuntimeException
     * @return  mixed
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * @param   string     $id
     * @param   mixed      $value
     */
    public function __set($id, $value)
    {
        $this->set($id, $value);
    }

    /**
     * Sets parameter
     *
     * @param   string     $id     Service id
     * @param   mixed      $value  Value
     * @return  Container
     */
    public function set($id, $value)
    {
        $this->values[$id] = $value;
        if ($value === null) {
            $this->release($id);
        }
        return $this;
    }

    /**
     * Gets parameter
     *
     * @param   string $id Service Id
     * @throws  \RuntimeException
     * @return  mixed
     */
    public function get($id)
    {
        if (!isset($this->values[$id])) {
            throw new \RuntimeException(
                sprintf('Could not find the service "%s"' , $id)
            );
        }
        return is_callable($this->values[$id]) ? $this->values[$id]($this) : $this->values[$id];
    }

    /**
     * Invoker
     *
     * Gets possible to use $container($id) instead of $container->get($id)
     * @param   string   $id Service ID
     * @return  mixed
     */
    public function __invoke($id)
    {
        return $this->get($id);
    }

    /**
     * @param   string     $id
     * @param   array      $arguments
     * @throws  \RuntimeException
     */
    public function __call($id, $arguments)
    {
        if (!is_callable($this->values[$id])) {
            throw new \RuntimeException(sprintf(
                '%s() is not callable or does not exist.', $id
            ));
        }
        return $this->values[$id]($this, $arguments);
    }

    /**
     * Creates lambda function for making single instance of services.
     *
     * @param   callback   $callable
     * @return  Container
     */
    public function setShared($id, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf(
                'Second argument of the "%s" method must be callable.', __FUNCTION__
            ));
        }
        $ptr =& $this->shared;
        if (($t = strpos($id, '.')) !== false) {
            //We need to register release hook which is needed to remove all
            //associated objects from the memory.
            $parentid = substr($id, 0, $t);
            if (!isset($this->releasehooks[$parentid])) {
                $this->releasehooks[$parentid] = array();
            }
            $this->releasehooks[$parentid][$id] = true;
        }
        $this->values[$id] = function (Container $container, $arguments = null) use ($id, $callable, &$ptr) {
            if (!isset($ptr[$id])) {
                $ptr[$id] = $callable($container);
            }
            //Invokes magic method for the specified object if it does exist.
            if (!empty($arguments) && is_array($arguments) &&
                is_object($ptr[$id]) && method_exists($ptr[$id], '__invoke')) {
                return call_user_func_array(array($ptr[$id], '__invoke'), $arguments);
            }
            return $ptr[$id];
        };

        return $this;
    }

    /**
     * Releases shared object from the pseudo-static cache
     *
     * @param   string    $id  The ID of the service
     * @return  Container
     */
    public function release($id)
    {
        if (isset($this->shared[$id])) {
            if (is_object($this->shared[$id]) && method_exists($this->shared[$id], '__destruct')) {
                $this->shared[$id]->__destruct();
            }
            unset($this->shared[$id]);
        }
        //Releases all children shared objects
        if (!empty($this->releasehooks[$id])) {
            foreach ($this->releasehooks[$id] as $serviceid => $b) {
                $this->release($serviceid);
            }
            unset($this->releasehooks[$id]);
        }
        return $this;
    }

    /**
     * Checks, whether service with required id is initialized.
     *
     * @param   string   $id        Service id
     * @param   bool     $callable  optional If true it will check whether service is callable.
     * @return  bool     Returns true if required service is initialized or false otherwise.
     */
    public function initialized($id, $callable = false)
    {
        return isset($this->values[$id]) && (!$callable || is_callable($this->values[$id]));
    }
}