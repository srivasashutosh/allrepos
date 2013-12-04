<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * PrivateIpAddressesSetRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 */
class PrivateIpAddressesSetRequestData extends AbstractEc2DataType
{

    /**
     * Whether the private IP address is the primary private IP address
     * @var bool
     */
    public $primary;

    /**
     * @var string
     */
    public $privateIpAddress;
}