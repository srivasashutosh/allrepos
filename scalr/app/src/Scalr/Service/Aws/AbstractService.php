<?php
namespace Scalr\Service\Aws;

use Scalr\Service\Aws\Client\ClientInterface;
use Scalr\Service\AwsException;
use Scalr\Service\Aws\Client\QueryClient;
use Scalr\Service\Aws\Client\QueryClient\S3QueryClient;

/**
 * AbstractService
 *
 * Ensures to provide common properties and behaviour for all Services.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     10.10.2012
 */
abstract class AbstractService
{

    /**
     * AWS Instance
     *
     * @var \Scalr\Service\Aws
     */
    protected $aws;

    /**
     * Entity Manager
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * API Version
     *
     * @var string
     */
    protected $apiVersion;

    /**
     * API Client Type
     * ('Query', 'Soap')
     *
     * @var string
     */
    private $apiClientType = \Scalr\Service\Aws::CLIENT_QUERY;

    /**
     * Stores API clients objects
     *
     * @var array
     */
    private $apiClients = array();

    /**
     * Child class name
     *
     * @var string
     */
    private $class;

    /**
     * Misc. instances
     *
     * @var array
     */
    private $instances;

    /**
     * ELB API Handler intance.
     *
     * @var ElbApi
     */
    protected $apiHandler;

    /**
     * Whether an entity manager is enabled.
     *
     * @var bool
     */
    private $entityManagerEnabled = false;

    /**
     * Gets an API version
     *
     * @return string Returns an API Version
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Gets low-level api handler for AWS Service
     *
     * @return   mixed Returns low-level api handler
     */
    public function getApiHandler()
    {
        if (!isset($this->apiHandler)) {
            $class = get_class($this);
            $serviceName = preg_replace('/^.+\\\\([^\\\\]+)$/', '\\1', $class);
            $client = $this->getApiClient();
            $apiHandlerClass = $class . '\\V' . $this->getApiVersion() . '\\' . $serviceName . 'Api';
            $this->apiHandler = new $apiHandlerClass($this, $client);
        }
        return $this->apiHandler;
    }

    /**
     * Retrieves API Client
     *
     * @return  ClientInterface
     * @throws  AwsException
     */
    public function getApiClient()
    {
        $apiClientType = $this->getApiClientType();
        if (!isset($this->apiClients[$apiClientType])) {
            $class = get_class($this);
            $serviceName = preg_replace('/^.+\\\\([^\\\\]+)$/', '\\1', $class);
            $clientClass = __NAMESPACE__ . '\\Client\\' . $apiClientType . 'Client';

            //Some services, like Simple Storage Service, may use different query client.
            if (file_exists(__DIR__ . '/Client/' .  $apiClientType . 'Client/' . $serviceName . $apiClientType . 'Client.php')) {
                $clientClass = $clientClass . '\\' . $serviceName . $apiClientType . 'Client';
            }
            if ($apiClientType == \Scalr\Service\Aws::CLIENT_SOAP) {
                $wsdlPath = __DIR__ . '/' . $serviceName . '/V' . $this->getApiVersion() . '/' . $serviceName . '.wsdl';
                if (!file_exists($wsdlPath)) {
                    throw new AwsException(sprintf('Could not find wsdl "%s" for the service "%s"', $wsdlPath, $serviceName));
                }
                $client = new $clientClass(
                    $this->aws->getAccessKeyId(), $this->aws->getSecretAccessKey(),
                    $this->getApiVersion(), $this->getUrl(), $wsdlPath
                );
                $client->setCertificate($this->aws->getCertificate());
                $client->setPrivateKey($this->aws->getPrivateKey());
            } else {
                $client = new $clientClass(
                    $this->aws->getAccessKeyId(), $this->aws->getSecretAccessKey(),
                    $this->getApiVersion(), $this->getUrl()
                );
            }
            $client->setAws($this->getAws());
            $this->apiClients[$apiClientType] = $client;
        }
        return $this->apiClients[$apiClientType];
    }

    /**
     * Sets API version
     *
     * @param    string   $apiVersion  API Version
     * @throws   ElbException
     */
    public function setApiVersion($apiVersion)
    {
        if (!in_array($apiVersion, $this->getAvailableApiVersions())) {
            throw new AwsException(sprintf('Version %d is not supported yet.', $apiVersion));
        }
        $this->apiVersion = $apiVersion;
    }

    /**
     * Constructor
     *
     * @param    \Scalr\Service\Aws   $aws  AWS Instance for the specified region and
     *                                      that is associated with this service.
     * @throws   ElbException
     */
    public function __construct(\Scalr\Service\Aws $aws)
    {
        $this->aws = $aws;
        $this->class = get_class($this);
        $this->setApiVersion($this->getCurrentApiVersion());
        $this->em = $aws->getEntityManager();
    }

    /**
     * Gets an AWS entity manager
     *
     * @return \Scalr\Service\Aws\EntityManager Returns an AWS entity manager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Ensures getting datatype handlers
     *
     * @param   string   $entityname
     */
    public function __get($entityname)
    {
        if (in_array($entityname, $this->getAllowedEntities())) {
            $class = $this->class . '\\Handler\\' . ucfirst($entityname) . 'Handler';
            if (!isset($this->instances[$class])) {
                $this->instances[$class] = new $class($this);
            }
            return $this->instances[$class];
        }
        return null;
    }

    /**
     * Gets an Aws instance
     *
     * @return \Scalr\Service\Aws Returns an AWS instance
     */
    public function getAws()
    {
        return $this->aws;
    }

    /**
     * Disables an entity manager to work with entities.
     *
     * It can be used to decrease latency.
     *
     * @return  void
     */
    public function disableEntityManager()
    {
        $this->entityManagerEnabled = false;
    }

    /**
     * Enables an entity manager
     *
     * @return  void
     */
    public function enableEntityManager()
    {
        $this->entityManagerEnabled = true;
    }

    /**
     * Whether an entity manager is enabled or not.
     *
     * @return  bool Returns true if it is enabled.
     */
    public function getEntityManagerEnabled()
    {
        return $this->entityManagerEnabled;
    }

    /**
     * Gets API client type
     *
     * @return  string Returns API client type. (Scalr\Service\Aws::CLIENT_QUERY by default)
     */
    public function getApiClientType()
    {
        return $this->apiClientType;
    }

    /**
     * Sets API client type
     *
     * @param   AbstractService
     * @throws  AwsException
     */
    public function setApiClientType($apiClientType)
    {
        if ($apiClientType !== \Scalr\Service\Aws::CLIENT_QUERY &&
            $apiClientType !== \Scalr\Service\Aws::CLIENT_SOAP) {
            throw new AwsException(sprintf('Invalid API client type "%s"', $apiClientType));
        }
        $this->apiClientType = $apiClientType;
        $this->getApiHandler()->setClient($this->getApiClient());
        return $this;
    }
}
