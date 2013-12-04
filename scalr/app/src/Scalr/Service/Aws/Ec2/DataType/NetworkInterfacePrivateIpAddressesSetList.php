<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * NetworkInterfacePrivateIpAddressesSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    02.04.2013
 */
class NetworkInterfacePrivateIpAddressesSetList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|NetworkInterfacePrivateIpAddressesSetData  $aListData NetworkInterfacePrivateIpAddressesSetData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('privateIpAddress', 'primary'), __NAMESPACE__ . '\\NetworkInterfacePrivateIpAddressesSetData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'PrivateIpAddresses', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}