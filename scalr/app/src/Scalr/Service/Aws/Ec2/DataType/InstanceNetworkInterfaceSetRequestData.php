<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceNetworkInterfaceSetRequestData
 *
 * This is used for running new instances
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\SecurityGroupIdSetList                  $securityGroupId       The group IDs for use by the network interface.
 * @property \Scalr\Service\Aws\Ec2\DataType\PrivateIpAddressesSetRequestList        $privateIpAddresses    The private IP addresses associated with the network interface.
 */
class InstanceNetworkInterfaceSetRequestData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('securityGroupId', 'privateIpAddresses');

    /**
     * The ID of the network interface.
     *
     * @var string
     */
    public $networkInterfaceId;

    /**
     * Required. The index of the device on the instance for the network interface attachment.
     * @var int
     */
    public $deviceIndex;

    /**
     * The Id of the subnet
     *
     * @var string
     */
    public $subnetId;

    /**
     * The description
     *
     * @var string
     */
    public $description;

    /**
     * The IP address of the network interface within the subnet.
     *
     * @var string
     */
    public $privateIpAddress;

    /**
     * If set to true, the interface is deleted when the instance is terminated
     *
     * @var bool
     */
    public $deleteOnTermination;

    /**
     * The number of secondary private IP addresses.
     * You cannot specify this option with privateIpAddressSet.
     *
     * @var int
     */
    public $secondaryPrivateIpAddressCount;

    /**
     * Sets SecurityGroupId list
     *
     * @param   SecurityGroupIdSetList|SecurityGroupIdSetData|array $securityGroupId
     * @return  RunInstancesRequestData
     */
    public function setSecurityGroupId($securityGroupId = null)
    {
        if ($securityGroupId !== null && !($securityGroupId instanceof SecurityGroupIdSetList)) {
            $securityGroupId = new SecurityGroupIdSetList($securityGroupId);
        }
        return $this->__call(__FUNCTION__, array($securityGroupId));
    }


    /**
     * Sets PrivateIpAddresses list
     *
     * @param   PrivateIpAddressesSetRequestList|PrivateIpAddressesSetRequestData|array $privateIpAddresses
     * @return  RunInstancesRequestData
     */
    public function setPrivateIpAddresses($privateIpAddresses = null)
    {
        if ($privateIpAddresses !== null && !($privateIpAddresses instanceof PrivateIpAddressesSetRequestList)) {
            $privateIpAddresses = new PrivateIpAddressesSetRequestList($privateIpAddresses);
        }
        return $this->__call(__FUNCTION__, array($privateIpAddresses));
    }
}