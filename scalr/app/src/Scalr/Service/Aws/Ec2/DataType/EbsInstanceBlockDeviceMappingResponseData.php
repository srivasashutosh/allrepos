<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * EbsInstanceBlockDeviceMappingResponseData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 */
class EbsInstanceBlockDeviceMappingResponseData extends AbstractEc2DataType
{

    /**
     * The ID of the Amazon EBS volume.
     * @var string
     */
    public $volumeId;

    /**
     * The attachment state.
     * attaching | attached | detaching | detached
     * @var string
     */
    public $status;

    /**
     * The time stamp when the attachment initiated.
     * @var \DateTime
     */
    public $attachTime;

    /**
     * Whether the Amazon EBS volume is deleted on instance termination.
     * @var bool
     */
    public $deleteOnTermination;

}