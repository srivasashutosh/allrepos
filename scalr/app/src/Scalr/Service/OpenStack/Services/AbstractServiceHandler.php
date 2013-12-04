<?php
namespace Scalr\Service\OpenStack\Services;

use Scalr\Service\OpenStack\Exception\ServiceHandlerException;
use Scalr\Service\OpenStack\Exception\OpenStackException;

/**
 * OpenStack AbstractServiceHandler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    14.12.2012
 */
abstract class AbstractServiceHandler
{
    /**
     * Service interface instance
     * @var ServiceInterface
     */
    private $service;

    /**
     * Constructor
     * @param   ServiceInterface $service A Service interface instance
     */
    public function __construct(ServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Gets a service intance
     *
     * @return  ServiceInterface Returns service interface
     */
    public function getService()
    {
        return $this->service;
    }

    public function __call($method, $arguments)
    {
        $aliases = $this->getServiceMethodAliases();
        if (array_key_exists($method, $aliases)) {
            $name = $aliases[$method];
            if (method_exists($this->service, $name)) {
                $ref = new \ReflectionMethod($this->service, $name);
                if ($ref->isPublic()) {
                    return $ref->invokeArgs($this->service, $arguments);
                }
            }
            throw new ServiceHandlerException(sprintf(
                'Cannot find method "%s" for the service "%s"',
                $name, get_class($this->service)
            ));
        }
        throw new ServiceHandlerException(sprintf(
            'Cannot find alias method "%s" for the service handler "%s". Check getServiceMethodAliases()',
            $method, get_class($this)
        ));
    }
}