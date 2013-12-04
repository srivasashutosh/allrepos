<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * InstanceStateData
 *
 * The InstanceState data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class InstanceStateData extends AbstractElbDataType
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
     * Provides a description of the instance.
     *
     * @var string
     */
    public $description;

    /**
     * Provides an EC2 instance ID.
     *
     * @var string
     */
    public $instanceId;

    /**
     * Provides information about the cause of OutOfService instances. Specifically, it
     * indicates whether the cause is Elastic Load Balancing or the instance behind the
     * LoadBalancer.
     *
     * @var string
     */
    public $reasonCode;

    /**
     * Specifies the current status of the instance.
     *
     * @var string
     */
    public $state;
}