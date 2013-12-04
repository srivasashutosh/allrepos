<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * DBSecurityGroupMembershipList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 */
class DBSecurityGroupMembershipList extends RdsListDataType
{

    /**
     * Constructor
     *
     * @param array|DBSecurityGroupMembershipData  $aListData List of DBSecurityGroupMembershipData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('dBSecurityGroupName', 'status'), __NAMESPACE__ . '\\DBSecurityGroupMembershipData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'DBSecurityGroups', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}