<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * InstanceStatusEventTypeData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    15.01.2013
 */
class InstanceStatusEventTypeData extends AbstractEc2DataType
{

    /**
     * The associated code of the event.
     * Valid parameters: instance-reboot | system-reboot | instance-retirement
     * @var string
     */
    public $code;

    /**
     * A description of the event.
     * @var string
     */
    public $description;

    /**
     * The earliest scheduled start time for the event.
     * @var DateTime
     */
    public $notBefore;

    /**
     * The latest scheduled end time for the event.
     * @var DateTime
     */
    public $notAfter;
}