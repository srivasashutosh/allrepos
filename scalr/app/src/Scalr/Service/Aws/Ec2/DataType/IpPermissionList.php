<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * IpPermissionList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\IpPermissionData get()
 *           get(int $index)
 *           Gets IpPermissionData object for the specified position in the list.
 */
class IpPermissionList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|IpPermissionData  $aListData List of IpPermissionData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('ipProtocol', 'fromPort', 'toPort', 'groups', 'ipRanges'), __NAMESPACE__ . '\\IpPermissionData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'IpPermissions', $member = true)
    {
        return array_filter(parent::getQueryArray($uriParameterName, $member), function ($val) {
            return $val !== null;
        });
    }
}