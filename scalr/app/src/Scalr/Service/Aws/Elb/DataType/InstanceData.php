<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * InstanceData
 *
 * The Instance data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class InstanceData extends AbstractElbDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName'
    );

    /**
     * Provides an EC2 instance ID.
     *
     * @var string
     */
    public $instanceId;

    /**
     * Constructor
     * @param string $instanceId
     */
    public function __construct($instanceId = null)
    {
        $this->instanceId = $instanceId;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Elb.AbstractElbDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if (empty($this->instanceId)) {
            throw new ElbException(get_class($this) . ' has not been initialized with instanceId.');
        }
    }

    /**
     * DescribeInstanceHealth action
     *
     * Returns the current state of the instances of the specified LoadBalancer. If no instances are specified,
     * the state of all the instances for the LoadBalancer is returned.
     *
     * @return  InstanceStateList Returns a list containing health information for the current instance.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function describeHealth()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->describeInstanceHealth($this->getLoadBalancerName(), $this);
    }
}