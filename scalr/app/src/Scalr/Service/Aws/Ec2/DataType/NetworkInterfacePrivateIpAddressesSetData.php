<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * NetworkInterfacePrivateIpAddressesSetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.04.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAssociationData $association
 *           The association information for an Elastic IP associated with the network interface.
 *
 * @method   NetworkInterfacePrivateIpAddressesSetData setAssociation()
 *           setAssociation(NetworkInterfaceAssociationData $association)
 *           Sets an association information for an Elastic IP associated with the network interface
 *
 * @method   NetworkInterfaceAssociationData getAssociation() getAssociation()
 *           Gets an association information for an Elastic IP associated with the network interface
 */
class NetworkInterfacePrivateIpAddressesSetData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'association',
    );

    /**
     * The private IP address of the network interface.
     *
     * @var string
     */
    public $privateIpAddress;

    /**
     * The private DNS name.
     *
     * @var string
     */
    public $privateDnsName;

    /**
     * Whether this IP address is the primary private IP address of the network interface.
     *
     * @var bool
     */
    public $primary;
}