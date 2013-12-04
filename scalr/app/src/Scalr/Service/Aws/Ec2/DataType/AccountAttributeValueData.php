<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * AccountAttributeValueData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.03.2013
 */
class AccountAttributeValueData extends AbstractEc2DataType
{

    /**
     * The value of the attribute
     * @var string
     */
    public $attributeValue;
}