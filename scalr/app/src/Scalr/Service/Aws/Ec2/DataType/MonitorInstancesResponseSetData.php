<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * MonitorInstancesResponseSetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.04.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceMonitoringStateData $monitoring
 *           The monitoring information.
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\MonitorInstancesResponseSetData setMonitoring()
 *           setMonitoring(\Scalr\Service\Aws\Ec2\DataType\InstanceMonitoringStateData $monitoring = null)
 *           Sets monitoring information
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\InstanceMonitoringStateData getMonitoring()
 *           getMonitoring()
 *           Gets monitoring information
 */
class MonitorInstancesResponseSetData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('monitoring');

    /**
     * The ID of the Running Instance
     *
     * @var string
     */
    public $instanceId;

    /**
     * Constructor
     *
     * @param   string    $instanceId The ID for the running instance
     */
    public function __construct($instanceId = null)
    {
        parent::__construct();
        $this->instanceId = $instanceId;
    }
}