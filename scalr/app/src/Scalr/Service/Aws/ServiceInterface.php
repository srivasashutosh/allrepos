<?php
namespace Scalr\Service\Aws;

use Scalr\Service\AwsException;

/**
 * ServiceInterface
 *
 * Descrbies common interface for all Amazon AWS services such as EC2, ELB
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     20.09.2012
 */
interface ServiceInterface
{

    /**
     * Gets url for current region
     *
     * @return string Returns url for Query API for current region
     */
    public function getUrl();

    /**
     * Gets API Version that is being used in current version of the soft.
     *
     * @return  string  Returns current API Version in YYYYMMDD format
     */
    public function getCurrentApiVersion();

    /**
     * Gets API Version
     *
     * @return  string  Returns API Version
     */
    public function getApiVersion();

    /**
     * Sets API version
     *
     * @param   string   $apiVersion  API Version
     * @throws  ElbException
     */
    public function setApiVersion($apiVersion);

    /**
     * Gets available API versions.
     *
     * @return  array  Returns array of available API versions.
     */
    public function getAvailableApiVersions();

    /**
     * Gets an EntityManager
     *
     * @return  EntityManager
     */
    public function getEntityManager();

    /**
     * Gets a list of entities.
     *
     * Gets a list of allowed entities that handle API requests for associated objects.
     *
     * @return  array Returns the names of the allowed entities.
     */
    public function getAllowedEntities();

    /**
     * Gets an Aws instance which is associated with this service.
     *
     * @return  \Scalr\Service\Aws  Returns Aws instance which is associated with this service.
     */
    public function getAws();

    /**
     * Disables an entity manager to work with entities.
     *
     * It can be used to decrease latency.
     *
     * @return  void
     */
    public function disableEntityManager();

    /**
     * Enables an entity manager
     *
     * @return  void
     */
    public function enableEntityManager();

    /**
     * Whether an entity manager is enabled or not.
     *
     * @return  bool Returns true if it is enabled.
     */
    public function getEntityManagerEnabled();

    /**
     * Retrieves API Client
     *
     * @return  ClientInterface
     * @throws  AwsException
     */
    public function getApiClient();

    /**
     * Gets API client type
     *
     * @return  string Returns API client type. ("Query" by default)
     */
    public function getApiClientType();

    /**
     * Sets API client type
     *
     * @return  ServiceInterface
     * @throws  AwsException
     */
    public function setApiClientType($apiClientType);
}