<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * AddressFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    29.01.2013
 */
class AddressFilterNameType extends StringType
{
    /**
     * Indicates whether the address is a EC2 address, or a VPC address.
     * Valid values: standard | vpc
     */
    const TYPE_DOMAIN = 'domain';

    /**
     * The instance the address is associated with (if any).
     */
    const TYPE_INSTANCE_ID = 'instance-id';

    /**
     * The Elastic IP address.
     */
    const TYPE_PUBLIC_IP = 'public-ip';

    /**
     * The allocation ID for the address (VPC addresses only).
     */
    const TYPE_ALLOCATION_ID = 'allocation-id';

    /**
     * The association ID for the address (VPC addresses only).
     */
    const TYPE_ASSOCIATION_ID = 'association-id';

    /**
     * The network interface (if any) that the address is associated with. (for VPC addresses only).
     */
    const TYPE_NETWORK_INTERFACE_ID = 'network-interface-id';

    /**
     * The owner ID
     */
    const TYPE_NETWORK_INTERFACE_OWNER_ID = 'network-interface-owner-id';

    /**
     * The private IP address associated with the Elastic IP address (for VPC addresses only).
     */
    const TYPE_PRIVATE_IP_ADDRESS = 'private-ip-address';

    public static function getPrefix()
    {
        return 'TYPE_';
    }
}