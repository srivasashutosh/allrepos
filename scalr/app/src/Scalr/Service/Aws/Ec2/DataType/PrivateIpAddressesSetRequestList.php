<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * PrivateIpAddressesSetRequestList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 */
class PrivateIpAddressesSetRequestList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|PrivateIpAddressesSetRequestData  $aListData List of PrivateIpAddressesSetRequestData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('primary', 'privateIpAddress'), __NAMESPACE__ . '\\PrivateIpAddressesSetRequestData');
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