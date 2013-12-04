<?php
namespace Scalr\Service\Aws\Elb;

use Scalr\Service\Aws;
use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\AbstractDataType;

/**
 * AbstractElbDataType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    03.10.2012
 * @method   \Scalr\Service\Aws\Elb getElb()               getElb()                            Gets an Amazon ELB instance
 * @method   AbstractElbDataType    setElb()               setElb(\Scalr\Service\Aws\Elb $elb) Sets an Amazon ELB instance
 * @method   string                 getLoadBalancerName()  getLoadBalancerName()               Gets an loadBalancer name that is associated with entity.
 * @method   AbstractElbDataType    setLoadBalancerName()  setLoadBalancerName(string $name)   Sets an loadBalancer name that is associated with entity.
 */
abstract class AbstractElbDataType extends AbstractDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_ELB);
    }

    /**
     * Throws an exception if this object was not initialized.
     *
     * @throws ElbException
     */
    protected function throwExceptionIfNotInitialized()
    {
        $lbname = $this->getLoadBalancerName();
        if (empty($lbname)) {
            throw new ElbException(get_class($this) . ' has not been initialized with loadBalancerName.');
        }
        if (!($this->getElb() instanceof \Scalr\Service\Aws\Elb)) {
            throw new ElbException(get_class($this) . ' has not been initialized with Elb yet.');
        }
    }
}