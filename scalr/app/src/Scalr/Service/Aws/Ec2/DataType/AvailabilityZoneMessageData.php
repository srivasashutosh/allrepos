<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * AvailabilityZoneMessageData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.12.2012
 */
class AvailabilityZoneMessageData extends AbstractEc2DataType
{

    /**
     * The message about the Availability Zone.
     * @var string
     */
    public $message;
}