<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstancePrivateIpAddressesSetData
 *
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 *
 * @property InstanceNetworkInterfaceAssociationData $association       The association information for an Elastic IP associated with the network interface.
 */
class InstancePrivateIpAddressesSetData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('association');

    /**
     * The private IP address of the network interface
     * @var string
     */
    public $privateIpAddress;

    /**
     * Whether this IP address is the primary private IP address of the network interface.
     * @var bool
     */
    public $primary;
}