<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceStatusData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    15.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceStatusEventTypeData $eventsSet
 *           Extra information regarding events associated with the instance.
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceStateData $instanceState
 *           The intended state of the instance. Calls to DescribeInstanceStatus
 *           require that an instance be in the running state.
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceStatusTypeData $systemStatus
 *           Reports impaired functionality that stems from issues related to the
 *           systems that support an instance, such as hardware failures and
 *           network connectivity problems.
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceStatusTypeData $instanceStatus
 *           Reports impaired functionality that arises from problems internal
 *           to the instance.
 */
class InstanceStatusData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('eventsSet', 'instanceState', 'systemStatus', 'instanceStatus');

    /**
     * The ID of the instance
     * @var string
     */
    public $instanceId;

    /**
     * String
     * @var string
     */
    public $availabilityZone;
}