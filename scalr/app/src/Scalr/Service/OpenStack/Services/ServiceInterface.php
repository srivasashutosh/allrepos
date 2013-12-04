<?php
namespace Scalr\Service\OpenStack\Services;

use Scalr\Service\OpenStack\Exception\OpenStackException;

/**
 * OpenStack service interface
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    04.12.2012
 */
interface ServiceInterface
{
    /**
     * Gets a service name.
     *
     * Returned name must start with the lower case letter.
     *
     * @return  string Returns service interface name.
     */
    public static function getName();

    /**
     * Gets a service type.
     *
     * @return  string Returns service interface type.
     */
    public static function getType();

    /**
     * Gets andpoint url
     *
     * @return  string Returns endpoint url without trailing slash
     * @throws  OpenStackException
     */
    public function getEndpointUrl();

    /**
     * Gets a version number
     *
     * @return  string Returns version of the interface
     */
    public function getVersion();

    /**
     * Gets an API handler for the appropriated version
     *
     * @return  object Returns Api handler
     */
    public function getApiHandler();

    /**
     * Gets the list of available handlers
     *
     * @return  array Returns the list of available handlers
     */
    public function getAvailableHandlers();

}