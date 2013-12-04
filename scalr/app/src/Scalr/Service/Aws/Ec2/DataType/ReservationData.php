<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * ReservationData (ReservationInfoType)
 *
 * Describes a reservation.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\GroupList         $groupSet          A list of security groups.
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceList      $instancesSet      A list of instances.
 */
class ReservationData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('groupSet', 'instancesSet');

    /**
     * The ID of the reservation.
     * @var string
     */
    public $reservationId;

    /**
     * The ID of the AWS account that owns the reservation.
     * @var string
     */
    public $ownerId;

    /**
     * The ID of the requester that launched the instances on your behalf
     * (for example, AWS Management Console or Auto Scaling).
     * @var string
     */
    public $requesterId;
}