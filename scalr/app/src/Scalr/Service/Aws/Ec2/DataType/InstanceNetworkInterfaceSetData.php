<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceNetworkInterfaceSetData
 *
 * Describes a network interface.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\GroupList                               $groupSet              A security group list.
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceAttachmentData  $attachment            The network interface attachment.
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceAssociationData $association           The association information for an Elastic IP associated with the network interface.
 * @property \Scalr\Service\Aws\Ec2\DataType\InstancePrivateIpAddressesSetList       $privateIpAddressesSet The private IP addresses associated with the network interface.
 */
class InstanceNetworkInterfaceSetData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('groupSet', 'attachment', 'association', 'privateIpAddressesSet');

    /**
     * The ID of the network interface.
     * @var string
     */
    public $networkInterfaceId;

    /**
     * The Id of the subnet
     * @var string
     */
    public $subnetId;

    /**
     * The ID of the VPC
     * @var string
     */
    public $vpcId;

    /**
     * The description
     * @var string
     */
    public $description;

    /**
     * The ID of the customer who created the network interface.
     * @var string
     */
    public $ownerId;

    /**
     * The network interface's status (available or in-use).
     * @var string
     */
    public $status;

    /**
     * The IP address of the network interface within the subnet.
     * @var string
     */
    public $privateIpAddress;

    /**
     * The private DNS name.
     * @var string
     */
    public $privateDnsName;

    /**
     * Whether to validate network traffic to or from this network interface.
     * @var bool
     */
    public $sourceDestCheck;
}