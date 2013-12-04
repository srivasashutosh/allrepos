<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * AddressData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    29.01.2013
 */
class AddressData extends AbstractEc2DataType
{

    const DOMAIN_STANDARD = 'standard';

    const DOMAIN_VPC = 'vpc';

    /**
     * The ID of the instance the address is associated with (if any).
     *
     * @var string
     */
    public $instanceId;

    /**
     * The public IP address
     *
     * @var string
     */
    public $publicIp;

    /**
     * The ID representing the association of a VPC Elastic IP address with
     * an instance in a VPC.
     *
     * @var string
     */
    public $associationId;

    /**
     * The ID representing the allocation of the address for use with Amazon VPC.
     *
     * @var string
     */
    public $allocationId;

    /**
     * Whether this Elastic IP address is for EC2 instances (i.e., standard) or VPC instances
     * Valid values: standard | vpc
     *
     * @var string
     */
    public $domain;

    /**
     * The ID of the network interface.
     *
     * @var string
     */
    public $networkInterfaceId;

    /**
     * The ID of the AWS account that owns the network interface
     *
     * @var string
     */
    public $networkInterfaceOwnerId;

    /**
     * Private IP Address
     *
     * @var string
     */
    public $privateIpAddress;
}