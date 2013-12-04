<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * VpcSecurityGroupMembershipList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 */
class VpcSecurityGroupMembershipList extends RdsListDataType
{

    /**
     * Constructor
     *
     * @param array|VpcSecurityGroupMembershipData  $aListData List of VpcSecurityGroupMembershipData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('vpcSecurityGroupId', 'status'), __NAMESPACE__ . '\\VpcSecurityGroupMembershipData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'VpcSecurityGroups', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}