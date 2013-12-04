<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * DBParameterGroupStatusList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    11.03.2013
 */
class DBParameterGroupStatusList extends RdsListDataType
{

    /**
     * Constructor
     *
     * @param array|DBParameterGroupStatusData  $aListData List of DBParameterGroupStatusData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('dBParameterGroupName', 'parameterApplyStatus'), __NAMESPACE__ . '\\DBParameterGroupStatusData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'DBParameterGroups', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}