<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * MonitoringInstanceData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 */
class MonitoringInstanceData extends AbstractEc2DataType
{

    /**
     * Whether monitoring is enabled for the instance.
     * @var boolean
     */
    public $enabled;

    /**
     * Constructor
     *
     * @param   boolean    $enabled   Whether monitoring is enabled for the instance.
     */
    public function __construct($enabled = null)
    {
        parent::__construct();
        $this->enabled = $enabled;
    }
}