<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * SubnetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 *
 * @property \Scalr\Service\Aws\Rds\DataType\AvailabilityZoneData $subnetAvailabilityZone
 *           Contains Availability Zone information.
 */
class SubnetData extends AbstractRdsDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'subnetAvailabilityZone',
    );

    /**
     * Specifies the identifier of the subnet.
     *
     * @var string
     */
    public $subnetIdentifier;

    /**
     * Specifies the status of the subnet.
     *
     * @var string
     */
    public $subnetStatus;
}