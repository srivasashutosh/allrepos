<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceMonitoringStateData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 */
class InstanceMonitoringStateData extends AbstractEc2DataType
{

    const STATE_DISABLED = 'disabled';

    const STATE_DISABLING = 'disabling';

    const STATE_ENABLED  = 'enabled';

    const STATE_PENDING  = 'pending';

    /**
     * The state of monitoring for the instance.The disabled state means
     * that Detailed Monitoring is disabled for the instance. The enabled
     * state means that Detailed Monitoring is enabled for the instance.The
     * pending state means that the instance is launching or that you
     * recently enabled Detailed Monitoring for the instance.
     * disabled | enabled | pending
     *
     * @var string
     */
    public $state;
}