<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * InstanceStatusDetailsSetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    15.01.2013
 */
class InstanceStatusDetailsSetData extends AbstractEc2DataType
{

    /**
     * The type of instance status detail. Valid values: reachability
     * @var string
     */
    public $name;

    /**
     * The status. Valid values: passed | failed | insufficient-data
     * @var string
     */
    public $status;

    /**
     * The time when a status check failed. For an instance that was
     * launched and impaired, this is the time when the instance was
     * launched.
     * @var DateTime
     */
    public $impairedSince;
}