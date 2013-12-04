<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * AvailabilityZoneData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.12.2012
 * @property AvailabilityZoneMessageList  $messageSet  Any messages about the Availability Zone
 */
class AvailabilityZoneData extends AbstractEc2DataType
{
    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('messageSet');

    /**
     * The name of the Availability Zone.
     * @var string
     */
    public $zoneName;

    /**
     * The state of the Availability Zone.
     * @var string
     */
    public $zoneState;

    /**
     * The name of the Region.
     * @var string
     */
    public $regionName;
}