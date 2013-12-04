<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceNetworkInterfaceSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 */
class InstanceNetworkInterfaceSetList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|InstanceNetworkInterfaceSetData  $aListData List of InstanceNetworkInterfaceSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('networkInterfaceId'), __NAMESPACE__ . '\\InstanceNetworkInterfaceSetData');
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