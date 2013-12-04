<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceStateData
 *
 * Describes the current state of the instance.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 */
class InstanceStateData extends AbstractEc2DataType
{
    const CODE_PENDING       = 0;

    const CODE_RUNNING       = 16;

    const CODE_SHUTTING_DOWN = 32;

    const CODE_TERMINATED    = 48;

    const CODE_STOPPING      = 64;

    const CODE_STOPPED       = 80;

    const NAME_PENDING       = 'pending';

    const NAME_RUNNING       = 'running';

    const NAME_SHUTTING_DOWN = 'shutting-down';

    const NAME_TERMINATED    = 'terminated';

    const NAME_STOPPING      = 'stopping';

    const NAME_STOPPED       = 'stopped';

    /**
     * The low byte represents the state.
     * The high byte is an opaque internal value and should be ignored.
     * @var int
     */
    public $code;

    /**
     * The current state of the instance.
     * @var string
     */
    public $name;

    /**
     * Convenient constructor
     *
     * @param   int        $code optional The low byte representes the code of the state.
     * @param   string     $name optional The current state of the instance.
     */
    public function __construct($code = null, $name = null)
    {
        parent::__construct();
        $this->code = $code;
        $this->name = $name;
    }
}