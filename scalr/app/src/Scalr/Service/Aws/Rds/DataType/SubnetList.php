<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * SubnetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    11.03.2013
 */
class SubnetList extends RdsListDataType
{

    /**
     * Constructor
     *
     * @param array|SubnetData  $aListData List of SubnetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('subnetIdentifier'), __NAMESPACE__ . '\\SubnetData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Subnets', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}