<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    08.01.2013
 */
class InstanceFilterNameType extends StringType
{

    /**
     * Filters the response based on a specific tag/value combination
     *
     * To instantiate the object with the tag:Anything we need to use following construction
     * InstanceFilterNameType::tag('Anything');
     */
    const TYPE_TAG_NAME = 'tag:Name';

    /**
     * The instance architecture.
     * i386 | x86_64
     */
    const TYPE_ARCHITECTURE = 'architecture';

    /**
     * The Availability Zone of the instance.
     */
    const TYPE_AVAILABILITY_ZONE = 'availability-zone';

    /**
     * The attach time for an Amazon EBS volume mapped to the instance (for example, 2010-09-15T17:15:20.000Z)
     */
    const TYPE_BLOCK_DEVICE_MAPPING_ATTACH_TIME = 'block-device-mapping.attach-time';

    /**
     * Whether the Amazon EBS volume is deleted on instance termination.
     */
    const TYPE_BLOCK_DEVICE_MAPPING_DELETE_ON_TERMINATION = 'block-device-mapping.delete-on-termination';

    /**
     * The device name (for example, /dev/sdh) for the Amazon EBS volume.
     */
    const TYPE_BLOCK_DEVICE_MAPPING_DEVICE_NAME = 'block-device-mapping.device-name';

    /**
     * The status for the Amazon EBS volume
     * attaching | attached | detaching | detached
     */
    const TYPE_BLOCK_DEVICE_MAPPING_STATUS = 'block-device-mapping.status';

    /**
     * The volume ID of the Amazon EBS volume.
     */
    const TYPE_BLOCK_DEVICE_MAPPING_VOLUME_ID = 'block-device-mapping.volume-id';

    /**
     * The idempotency token you provided when you launched the instance.
     */
    const TYPE_CLIENT_TOKEN = 'client-token';

    /**
     * The public DNS name of the instance.
     */
    const TYPE_DNS_NAME = 'dns-name';

    /**
     * The ID of a EC2 security group the instance is in.
     * This filter does not work for VPC security groups
     * (instead, use instance.group-id).
     */
    const TYPE_GROUP_ID = 'group-id';

    /**
     * The name of a EC2 security group the instance is in.
     * This filter does not work for VPC security groups
     * (instead, use instance.group-name).
     */
    const TYPE_GROUP_NAME = 'group-name';

    /**
     * The ID of the image used to launch the instance
     */
    const TYPE_IMAGE_ID = 'image-id';

    /**
     * The ID of the instance.
     */
    const TYPE_INSTANCE_ID = 'instance-id';

    /**
     * Indicates whether this is a Spot Instance.
     */
    const TYPE_INSTANCE_LIFECYCLE = 'instance-lifecycle';

    /**
     * A code representing the state of the instance.
     * The high byte is an opaque internal value and should
     * be ignored. The low byte is set based on the state represented
     * Type: Integer (16-bit unsigned integer)
     * Valid values: 0 (pending) | 16 (running) | 32 (shutting-down) | 48 (terminated) | 64 (stopping) | 80 (stopped)
     */
    const TYPE_INSTANCE_STATE_CODE = 'instance-state-code';

    /**
     * The state of the instance.
     * Valid values: pending | running | shutting-down | terminated | stopping | stopped
     */
    const TYPE_INSTANCE_STATE_NAME = 'instance-state-name';

    /**
     * The type of instance (for example, m1.small).
     */
    const TYPE_INSTANCE_TYPE = 'instance-type';

    /**
     * The ID of a VPC security group the instance is in.
     * This filter does not work for EC2 security groups (instead, use group-id).
     */
    const TYPE_INSTANCE_GROUP_ID = 'instance.group-id';

    /**
     * The name of a VPC security group the instance is in.
     * This filter does not work for EC2 security groups (instead, use group-name).
     */
    const TYPE_INSTANCE_GROUP_NAME = 'instance.group-name';

    /**
     * The public IP address of the instance.
     */
    const TYPE_IP_ADDRESS = 'ip-address';

    /**
     * The kernel ID.
     */
    const TYPE_KERNEL_ID = 'kernel-id';

    /**
     * The name of the key pair used when the instance
     * was launched
     */
    const TYPE_KEY_NAME = 'key-name';

    /**
     * When launching multiple instances, this is the index
     * for the instance in the launch group (for example, 0, 1, 2, and so on).
     */
    const TYPE_LAUNCH_INDEX = 'launch-index';

    /**
     * The time the instance was launched (for example, 2010-08-07T11:54:42.000Z).
     */
    const TYPE_LAUNCH_TIME = 'launch-time';

    /**
     * Indicates whether monitoring is enabled for the instance.
     * Valid values: disabled | enabled
     */
    const TYPE_MONITORING_STATE = 'monitoring-state';

    /**
     * The AWS account ID of the instance owner.
     */
    const TYPE_OWNER_ID = 'owner-id';

    /**
     * The name of the placement group the instance is in.
     */
    const TYPE_PLACEMENT_GROUP_NAME = 'placement-group-name';

    /**
     * The platform. Use windows if you have Windows
     * based instances; otherwise, leave blank.
     * Valid value: windows
     */
    const TYPE_PLATFORM = 'platform';

    /**
     * The private DNS name of the instance
     */
    const TYPE_PRIVATE_DNS_NAME = 'private-dns-name';

    /**
     * The private IP address of the instance.
     */
    const TYPE_PRIVATE_IP_ADDRESS = 'private-ip-address';

    /**
     * The product code associated with the AMI used to launch the instance.
     */
    const TYPE_PRODUCT_CODE = 'product-code';

    /**
     * The type of product code.
     * Valid values: devpay | marketplace
     */
    const TYPE_PRODUCT_CODE_TYPE = 'product-code.type';

    /**
     * The RAM disk ID.
     */
    const TYPE_RAMDISK_ID = 'ramdisk-id';

    /**
     * The reason for the current state of the instance
     * (for example, shows "User Initiated [date]" when you
     * stop or terminate the instance).
     * Similar to the state-reason-code filter.
     */
    const TYPE_REASON = 'reason';

    /**
     * The ID of the entity that launched the instance on
     * your behalf (for example, AWS Management Console, Auto Scaling, and so on)
     */
    const TYPE_REQUESTER_ID = 'requester-id';

    /**
     * The ID of the instance's reservation. A reservation ID is created any time you launch an instance.
     * A reservation ID has a one-to-one relationship with an instance launch request, but can be associated
     * with more than one instance if you launch multiple instances using the same launch request.
     * For example, if you launch one instance, you'll get one reservation ID.
     * If you launch ten instances using the same launch request, you'll also get one reservation ID.
     */
    const TYPE_RESERVATION_ID = 'reservation-id';

    /**
     * The name of the root device for the instance (for example, /dev/sda1).
     */
    const TYPE_ROOT_DEVICE_NAME = 'root-device-name';

    /**
     * The type of root device the instance uses.
     * Valid values: ebs | instance-store
     */
    const TYPE_ROOT_DEVICE_TYPE = 'root-device-type';

    /**
     * Indicates whether the instance performs source/destination checking. A value of true
     * means checking is enabled, and false means checking is disabled.
     * The value must be false for the instance to perform Network Address
     * Translation (NAT) in your VPC.
     * true | false
     */
    const TYPE_SOURCE_DEST_CHECK = 'source-dest-check';

    /**
     * The ID of the Spot Instance request.
     */
    const TYPE_SPOT_INSTANCE_REQUEST_ID = 'spot-instance-request-id';

    /**
     * The reason code for the state change.
     */
    const TYPE_STATE_REASON_CODE = 'state-reason-code';

    /**
     * A message that describes the state change.
     */
    const TYPE_STATE_REASON_MESSAGE = 'state-reason-message';

    /**
     * The ID of the subnet the instance is in (if using Amazon Virtual Private Cloud).
     */
    const TYPE_SUBNET_ID = 'subnet-id';

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
     * The virtualization type of the instance.
     * Valid values: paravirtual | hvm
     */
    const TYPE_VIRTUALIZATION_TYPE = 'virtualization-type';

    /**
     * The ID of the VPC the instance is in (if using Amazon Virtual Private Cloud).
     */
    const TYPE_VPC_ID = 'vpc-id';

    /**
     * The hypervisor type of the instance.
     * Valid values: ovm | xen
     */
    const TYPE_HYPERVISOR = 'hypervisor';

    /**
     * The description of the network interface (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_DESCRIPTION = 'network-interface.description';

    /**
     * The ID of the subnet of the network interface (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_SUBNET_ID = 'network-interface.subnet-id';

    /**
     * The ID of the Amazon VPC of the network interface. (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_VPC_ID = 'network-interface.vpc-id';

    /**
     * The ID of the network interface(available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_NETWORK_INTERFACE_ID = 'network-interface.network-interface.id';

    /**
     * The ID of the owner of the network interface (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_OWNER_ID = 'network-interface.owner-id';

    /**
     * The availability zone of the network interface (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_AVAILABILITY_ZONE = 'network-interface.availability-zone';

    /**
     * The requester ID of the network interface(available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_REQUESTER_ID = 'network-interface.requester-id';

    /**
     * Indicates whether the network interface is being managed by an AWS service (for example,
     * AWSManagement Console, Auto Scaling, and so on).
     * This filter is available only in Amazon Virtual Private Cloud.
     */
    const TYPE_NETWORK_INTERFACE_REQUESTER_MANAGED = 'network-interface.requester-managed';

    /**
     * The status of the network interface (available only in Amazon Virtual Private Cloud).
     * Valid values: available | in-use
     */
    const TYPE_NETWORK_INTERFACE_STATUS = 'network-interface.status';

    /**
     * The MAC address of the network interface (available only in Amazon Virtual Private Cloud).
     * Valid values: available | in-use
     */
    const TYPE_NETWORK_INTERFACE_MAC_ADDRESS = 'network-interface.mac-address';

    /**
     * The private DNS name of the network interface
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_PRIVATE_DNS_NAME = 'network-interface-private-dns-name';

    /**
     * Whether the network interface performs source/destination checking. A value of true means
     * checking is enabled, and false means checking is disabled. The value must be false for the network
     * interface to perform Network Address Translation (NAT) in your VPC (available only in Amazon
     * Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_SOURCE_DESTINATION_CHECK = 'network-interface.source-destination-check';

    /**
     * The ID of a VPC security group associated with the network interface
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_GROUP_ID = 'network-interface.group-id';

    /**
     * The name of a VPC security group associated with the network interface
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_GROUP_NAME = 'network-interface.group-name';

    /**
     * The ID of the interface attachment (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ATTACHMENT_ATTACHMENT_ID = 'network-interface.attachment.attachment-id';

    /**
     * The ID of the instance to which the network interface is attached
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ATTACHMENT_INSTANCE_ID = 'network-interface.attachment.instance-id';

    /**
     * The owner ID of the instance to which the network interface is attached
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ATTACHMENT_INSTANCE_OWNER_ID = 'network-interface.attachment.instance-owner-id';

    /**
     * The private IP address associated with the network interface
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ADDRESSES_PRIVATE_IP_ADDRESS = 'network-interface.addresses.private-ip-address';

    /**
     * The device index to which the network interface is attached
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ATTACHMENT_DEVICE_INDEX = 'network-interface.attachment.device-index';

    /**
     * The status of the attachment. (available only in Amazon Virtual Private Cloud).
     * Valid values: attaching | attached | detaching | detached
     */
    const TYPE_NETWORK_INTERFACE_ATTACHMENT_STATUS = 'network-interface.attachment.status';

    /**
     * The time that the network interface was attached to an instance
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ATTACHMENT_ATTACH_TIME = 'network-interface.attachment.attach-time';

    /**
     * Specifies whether the attachment is deleted when an instance is terminated
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ATTACHMENT_DELETE_ON_TERMINATION = 'network-interface.attachment.delete-on-termination';

    /**
     * Specifies whether the IP address of the network
     * interface is the primary private IP address (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ADDRESSES_PRIMARY = 'network-interface.addresses.primary';

    /**
     * The ID representing the association of a VPC Elastic IP address with a network interface
     * in a VPC(available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ADDRESSES_ASSOCIATION_PUBLIC_IP = 'network-interface.addresses.association.public-ip';

    /**
     * The owner ID of the private IP address associated with the network interface
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_NETWORK_INTERFACE_ADDRESSES_ASSOCIATION_IP_OWNER_ID = 'network-interface.addresses.association.ip-owner-id';

    /**
     * The address of the Elastic IP address bound to the
     * network interface (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_ASSOCIATION_PUBLIC_IP = 'association.public-ip';

    /**
     * The owner of the Elastic IP address associated with the network interface
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_ASSOCIATION_IP_OWNER_ID = 'association.ip-owner-id';

    /**
     * The allocation ID that AWS returned when you allocated the Elastic IP address for your network
     * interface (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_ASSOCIATION_ALLOCATION_ID = 'association.allocation-id';

    /**
     * The association ID returned when the network interface was associated with an IP address
     * (available only in Amazon Virtual Private Cloud).
     */
    const TYPE_ASSOCIATION_ASSOCIATION_ID = 'association.association-id';

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