<?php
namespace Scalr\Service\Aws;

/**
 * Amazon CloudWatch web service interface
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 */
abstract class AbstractServiceRelatedType
{
    /**
     * Holds an services instances.
     *
     * @var array
     */
    protected  $_services = array();

    /**
     * Gets the list of supported service interfaces.
     *
     * This method is supposed to be overridden.
     *
     * @return array Returns the list of supported service interfaces.
     *               Array should look like array(Aws::SERVICE_INTERFACE_ELB, ... )
     */
    abstract public function getServiceNames();

    /**
     * Gets the service interface instance
     *
     * @param  string   $name   Service Name
     * @throws \RuntimeException
     */
    public function __get($serviceName)
    {
        if (in_array($serviceName, $this->getServiceNames())) {
            return isset($this->_services[$serviceName]) ? $this->_services[$serviceName] : null;
        }
        throw new \RuntimeException(sprintf('Unknown property %s for the class %s', $serviceName, get_class($this)));
    }

    /**
     * Gets or sets an service interface instance
     *
     * @param   string    $name
     * @param   mixed     $arguments
     * @throws  \InvalidArgumentException
     * @throws  \RuntimeException
     */
    public function __call($name, $arguments)
    {
        $token = array(substr($name, 0, 3), substr($name, 3));
        if ($token[0] == 'get' || $token[0] == 'set') {
            $serviceName = lcfirst($token[1]);
            if (in_array($serviceName, $this->getServiceNames())) {
                if ($token[0] == 'get') {
                    return isset($this->_services[$serviceName]) ? $this->_services[$serviceName] : null;
                } else {
                    //Set is expected to be here
                    $this->_services[$serviceName] = $arguments[0];
                    return $this;
                }
            }
        }
        throw new \RuntimeException(sprintf('Unknown method %s for the class %s', $name, get_class($this)));
    }
}