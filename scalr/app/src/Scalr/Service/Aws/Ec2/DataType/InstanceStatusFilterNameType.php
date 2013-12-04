<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceStatusFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    15.01.2013
 */
class InstanceStatusFilterNameType extends StringType
{

    /**
     * The Availability Zone of the instance.
     */
    const TYPE_AVAILABILITY_ZONE = 'availability-zone';

    /**
     * The code identifying the type of event.
     * instance-reboot | system-reboot | system-maintenance | instance-retirement
     */
    const TYPE_EVENT_CODE = 'event.code';

    /**
     * A description of the event.
     */
    const TYPE_EVENT_DESCRIPTION = 'event.description';

    /**
     * The public DNS name of the instance.
     */
    const TYPE_DNS_NAME = 'dns-name';

    /**
     * The latest end time for the scheduled event.
     */
    const TYPE_EVENT_NOT_AFTER = 'event.not-after';

    /**
     * The earliest start time for the scheduled event.
     */
    const TYPE_EVENT_NOT_BEFORE = 'event.not-before';

    /**
     * The state of the instance.
     * pending | running | shutting-down | terminated | stopping | stopped
     */
    const TYPE_INSTANCE_STATE_NAME = 'instance-state-name';

    /**
     * A code representing the state of the instance. The high byte is an opaque internal value and should be ignored.
     * The low byte is set based on the state represented
     * Type: Integer (16-bit unsigned integer)
     * Valid values: 0 (pending) | 16 (running) | 32 (shutting-down) | 48 (terminated) | 64 (stopping) | 80 (stopped)
     */
    const TYPE_INSTANCE_STATE_CODE = 'instance-state-code';

    /**
     * The system status of the instance.
     * Valid values: ok | impaired | initializing | insufficient-data | not-applicable
     */
    const TYPE_SYSTEM_STATUS_STATUS = 'system-status.status';

    /**
     * Filters on system status where the name is reachability.
     * Valid values: passed | failed | initializing | insufficient-data
     */
    const TYPE_SYSTEM_STATUS_REACHABILITY = 'system-status.reachability';

    /**
     * The status of the instance.
     * Valid values: ok | impaired | initializing | insufficient-data | not-applicable
     */
    const TYPE_INSTANCE_STATUS_STATUS = 'instance-status.status';

    /**
     * Filters on instance status where the name is reachability.
     * Valid values: passed | failed |initializing | insufficient-data
     */
    const TYPE_INSTANCE_STATUS_REACHABILITY = 'instance-status.reachability';

    public static function getPrefix()
    {
        return 'TYPE_';
    }
}