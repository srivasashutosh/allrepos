<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstancePrivateIpAddressesSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 */
class InstancePrivateIpAddressesSetList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|InstancePrivateIpAddressesSetData  $aListData List of InstancePrivateIpAddressesSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('privateIpAddress', 'primary', 'association'), __NAMESPACE__ . '\\InstancePrivateIpAddressesSetData');
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