<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * CreateNetworkInterfaceRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    02.04.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\SecurityGroupIdSetList $securityGroupId
 *           A list of group IDs for use by the network interface.
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\NetworkInterfacePrivateIpAddressesSetList $privateIpAddresses
 *           The private IP addresses associated with the network interface
 */
class CreateNetworkInterfaceRequestData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'securityGroupId', 'privateIpAddresses'
    );

    /**
     * The ID of the subnet
     *
     * @var string
     */
    public $subnetId;

    /**
     * The primary private IP address of the network interface.
     *
     * @var string
     */
    public $privateIpAddress;

    /**
     * The number of secondary private IP addresses
     * to assign to a network interface. When you
     * specify a number of secondary IP addresses,
     * AWS automatically assigns these IP addresses
     * within the subnet's range.
     * The number of IP addresses you can assign
     * to a network interface varies by instance type.
     *
     * For a single network interface, you cannot
     * specify this option and specify more than one
     * private IP address using
     * PrivateIpAddress.n.
     *
     * @var int
     */
    public $secondaryPrivateIpAddressCount;

    /**
     * A description
     *
     * @var string
     */
    public $description;

    public function __construct($subnetId)
    {
        parent::__construct();
        $this->subnetId = $subnetId;
    }

    /**
     * Sets a SecurityGroupId list
     *
     * @param   SecurityGroupIdSetList|SecurityGroupIdSetData|array $securityGroupIdList The List of Security Group ID.
     * @return  CreateNetworkInterfaceRequestData
     */
    public function setSecurityGroupId($securityGroupIdList = null)
    {
        if ($securityGroupIdList !== null) {
            if (!($securityGroupIdList instanceof SecurityGroupIdSetList)) {
                $securityGroupIdList = new SecurityGroupIdSetList($securityGroupIdList);
            }
        }
        return $this->__call(__FUNCTION__, array($securityGroupIdList));
    }

    /**
     * Sets a PrivateIpAddresses list
     *
     * @param   NetworkInterfacePrivateIpAddressesSetList|NetworkInterfacePrivateIpAddressesSetData|array
     *          $privateIpAddresses The private IP address of the specified network interface.
     *
     * @return  CreateNetworkInterfaceRequestData
     */
    public function setPrivateIpAddresses($privateIpAddresses = null)
    {
        if ($privateIpAddresses !== null) {
            if (!($privateIpAddresses instanceof NetworkInterfacePrivateIpAddressesSetList)) {
                $privateIpAddresses = new NetworkInterfacePrivateIpAddressesSetList($privateIpAddresses);
            }
        }
        return $this->__call(__FUNCTION__, array($privateIpAddresses));
    }
}