<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceNetworkInterfaceSetRequestList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 */
class InstanceNetworkInterfaceSetRequestList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|InstanceNetworkInterfaceSetRequestData  $aListData List of InstanceNetworkInterfaceSetRequestData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array(
                'networkInterfaceId', 'deviceIndex', 'subnetId', 'description', 'privateIpAddress',
                'securityGroupId', 'deleteOnTermination', 'privateIpAddresses', 'secondaryPrivateIpAddressCount'
            ),
            __NAMESPACE__ . '\\InstanceNetworkInterfaceSetRequestData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'NetworkInterface', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}