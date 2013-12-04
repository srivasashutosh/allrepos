<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceStateChangeData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    18.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceStateData $currentState    The current state of the instance.
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceStateData $previousState   The current state of the instance.
 */
class InstanceStateChangeData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('currentState', 'previousState');

    /**
     * The instance ID.
     * @var string
     */
    public $instanceId;
}