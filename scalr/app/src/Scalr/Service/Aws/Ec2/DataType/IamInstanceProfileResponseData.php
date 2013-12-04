<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * IamInstanceProfileResponseData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 */
class IamInstanceProfileResponseData extends AbstractEc2DataType
{
    /**
     * The Amazon resource name (ARN) of the IAM Instance Profile (IIP) to associate with the instance.
     * @var string
     */
    public $arn;

    /**
     * The ID of the IAM Instance Profile ID (IIP) associated with the instance. (id)
     * @var string
     */
    public $iamInstanceProfileId;
}