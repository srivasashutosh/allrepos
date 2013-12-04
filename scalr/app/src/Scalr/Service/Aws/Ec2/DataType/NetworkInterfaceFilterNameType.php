<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * NetworkInterfaceFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.04.2013
 */
class NetworkInterfaceFilterNameType extends StringType
{
    /**
     * Filters the response based on a specific tag/value combination
     *
     * To instantiate the object with the tag:Anything we need to use following construction
     * InstanceFilterNameType::tag('Anything');
     */
    const TYPE_TAG_NAME = 'tag:Name';

    /**
     * The private IP addresses associated with the network interface
     */
    const TYPE_ADDRESSES_PRIVATE_IP_ADDRESS = 'addresses.private-ip-address';

    /**
     * Whether the private IP address is the primary IP
     * address associated with the network interface.
     * Valid values: true | false
     */
    const TYPE_ADDRESSES_PRIMARY = 'addresses.primary';

    /**
     * The association ID returned when the network
     * interface was associated with the Elastic IP
     * address.
     */
    const TYPE_ADDRESSES_ASSOCIATION_PUBLIC_IP = 'addresses.association.public-ip';

    /**
     * The owner ID of the addresses associated with the network interface.
     */
    const TYPE_ADDRESSES_ASSOCIATION_OWNER_ID = 'addresses.association.owner-id';

    /**
     * The association ID returned when the network
     * interface was associated with an IP address
     */
    const TYPE_ASSOCIATION_ASSOCIATION_ID = 'association.association-id';

    /**
     * The allocation ID that AWS returned when you
     * allocated the Elastic IP address for your network
     * interface.
     */
    const TYPE_ASSOCIATION_ALLOCATION_ID = 'association.allocation-id';

    /**
     * The owner of the Elastic IP address associated
     * with the network interface
     */
    const TYPE_ASSOCIATION_IP_OWNER_ID = 'association.ip-owner-id';

    /**
     * The address of the Elastic IP address bound to the
     * network interface.
     */
    const TYPE_ASSOCIATION_PUBLIC_IP = 'association.public-ip';

    /**
     * The ID of the interface attachment
     */
    const TYPE_ATTACHMENT_ATTACHMENT_ID = 'attachment.attachment-id';

    /**
     * The ID of the instance to which the network interface is attached
     */
    const TYPE_ATTACHMENT_INSTANCE_ID = 'attachment.instance-id';

    /**
     * The owner ID of the instance to which the network interface is attached
     */
    const TYPE_ATTACHMENT_INSTANCE_OWNER_ID = 'attachment.instance-owner-id';

    /**
     * The device index to which the network interface is attached.
     */
    const TYPE_ATTACHMENT_DEVICE_INDEX = 'attachment.device-index';

    /**
     * The status of the attachment.
     * Valid values: attaching | attached | detaching | detached
     */
    const TYPE_ATTACHMENT_STATUS = 'attachment.status';

    /**
     * The time that the network interface was attached to an instance.
     * Type: DateTime
     */
    const TYPE_ATTACHMENT_ATTACH_TIME = 'attachment.attach.time';

    /**
     * Indicates whether the attachment is deleted when an instance is terminated.
     */
    const TYPE_ATTACHMENT_DELETE_ON_TERMINATION = 'attachment.delete-on-termination';

    /**
     * The Availability Zone of the network interface
     */
    const TYPE_AVAILABILITY_ZONE = 'availability-zone';

    /**
     * The description of the network interface
     */
    const TYPE_DESCRIPTION = 'description';

    /**
     * The ID of a security group associated with the network interface.
     */
    const TYPE_GROUP_ID = 'group-id';

    /**
     * The name of a security group associated with the network interface.
     */
    const TYPE_GROUP_NAME = 'group-name';

    /**
     * The MAC address of the network interface
     */
    const TYPE_MAC_ADDRESS = 'mac-address';

    /**
     * The ID of the network interface
     */
    const TYPE_NETWORK_INTERFACE_ID = 'network-interface-id';

    /**
     * The AWS account ID of the network interface owner.
     */
    const TYPE_OWNER_ID = 'owner-id';

    /**
     * The private IP address or addresses of the network interface.
     */
    const TYPE_PRIVATE_IP_ADDRESS = 'private-ip-address';

    /**
     * The private DNS name of the network interface
     */
    const TYPE_PRIVATE_DNS_NAME = 'private-dns-name';

    /**
     * The ID of the entity that launched the instance on
     * your behalf (for example, AWS Management
     * Console, Auto Scaling, and so on).
     */
    const TYPE_REQUESTER_ID = 'requester-id';

    /**
     * Indicates whether the network interface is being
     * managed by an AWS service (for example, AWS
     * Management Console, Auto Scaling, and so on).
     * Type: Boolean
     */
    const TYPE_REQUESTER_MANAGED = 'requester-managed';

    /**
     * Indicates whether the network interface performs
     * source/destination checking. A value of true
     * means checking is enabled, and false means
     * checking is disabled. The value must be false for
     * the network interface to perform Network Address
     * Translation (NAT) in your VPC.
     * Type: Boolean
     */
    const TYPE_SOURCE_DEST_CHECK = 'source-dest-check';

    /**
     * The status of the network interface. If the network
     * interface is not attached to an instance, the status
     * shows available; if a network interface is
     * attached to an instance the status shows in-use.
     * Valid values: available | in-use
     */
    const TYPE_STATUS = 'status';

    /**
     * The key of a tag assigned to the resource.
     * This filter is independent of the tag-value filter.
     * For example, if you use both the filter "tag-key=Purpose" and the filter "tag-value=X",
     * you get any resources assigned both the tag key Purpose (regardless of what the tag's value is),
     * and the tag value X (regardless of what the tag's key is). If you want to list only resources where
     * Purpose is X, see the tag:key filter
     */
    const TYPE_TAG_KEY = 'tag-key';

    /**
     * The value of a tag assigned to the resource. This filter is independent of the tag-key filter.
     */
    const TYPE_TAG_VALUE = 'tag-value';

    /**
     * The ID of the VPC for the network interface..
     */
    const TYPE_VPC_ID = 'vpc-id';

    public static function getPrefix()
    {
        return 'TYPE_';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::validate()
     */
    protected function validate()
    {
        return preg_match('#^tag\:.+#', $this->value) ?: parent::validate();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::__callstatic()
     */
    public static function __callStatic($name, $args)
    {
        $class = __CLASS__;
        if ($name == 'tag') {
            if (!isset($args[0])) {
                throw new \InvalidArgumentException(sprintf(
                    'Tag name must be provided! Please use %s::tag("symbolic-name")', $class
                ));
            }
            return new $class('tag:' . $args[0]);
        }
        return parent::__callStatic($name, $args);
    }
}