<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * EC2SecurityGroupList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    22.03.2013
 */
class EC2SecurityGroupList extends RdsListDataType
{

    /**
     * Constructor
     *
     * @param array|EC2SecurityGroupData  $aListData List of EC2SecurityGroupData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('eC2SecurityGroupId', 'eC2SecurityGroupName', 'eC2SecurityGroupOwnerId', 'status'),
            __NAMESPACE__ . '\\EC2SecurityGroupData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'EC2SecurityGroups', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}