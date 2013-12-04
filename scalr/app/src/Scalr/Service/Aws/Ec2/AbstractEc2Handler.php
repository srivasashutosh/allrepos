<?php
namespace Scalr\Service\Aws\Ec2;

use Scalr\Service\Aws;
use Scalr\Service\Aws\AbstractHandler;

/**
 * AbstractEc2Handler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     26.12.2012
 * @property  Aws\Ec2   $ec2      An Amazon Ec2 instance
 * @method    void                     __constructor()  __constructor(\Scalr\Service\Aws\Ec2 $ec2)
 */
abstract class AbstractEc2Handler extends AbstractHandler
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractHandler::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_EC2);
    }

    /**
     * Sets Amazon Ec2 service interface instance
     *
     * @param   Aws\Ec2 $ec2 Ec2 service instance
     * @return  AbstractEc2Handler
     */
    public function setEc2(Aws\Ec2 $ec2 = null)
    {
        $this->_services[Aws::SERVICE_INTERFACE_EC2] = $ec2;
        return $this;
    }

    /**
     * Gets Ec2 service interface instance
     *
     * @return  Aws\Ec2 Returns Ec2 service interface instance
     */
    public function getEc2()
    {
        return isset($this->_services[Aws::SERVICE_INTERFACE_EC2]) ? $this->_services[Aws::SERVICE_INTERFACE_EC2] : null;
    }
}