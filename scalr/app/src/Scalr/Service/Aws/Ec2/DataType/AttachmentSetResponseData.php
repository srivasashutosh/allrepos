<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * AttachmentSetResponseData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    21.01.2013
 */
class AttachmentSetResponseData extends AbstractEc2DataType
{

    const STATUS_ATTACHING = 'attaching';

    const STATUS_ATTACHED = 'attached';

    const STATUS_DETACHING = 'detaching';

    const STATUS_DETACHED = 'detached';

    /**
     * The ID of the volume
     * @var string
     */
    public $volumeId;

    /**
     * The ID of the instance.
     * @var string
     */
    public $instanceId;

    /**
     * The device name exposed to the instance (e.g., /dev/sdh).
     * @var string
     */
    public $device;

    /**
     * The attachment state.
     * attaching | attached | detaching | detached
     * @var string
     */
    public $status;

    /**
     * The time stamp when the attachment initiated.
     * @var DateTime
     */
    public $attachTime;

    /**
     * Whether the Amazon EBS volume is deleted on instance termination
     * @var bool
     */
    public $deleteOnTermination;
}

