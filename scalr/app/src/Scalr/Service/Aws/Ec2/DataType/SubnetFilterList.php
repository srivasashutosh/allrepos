<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * SubnetFilterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.01.2013
 */
class SubnetFilterList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|SubnetFilterData  $aListData SubnetFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'value'), __NAMESPACE__ . '\\SubnetFilterData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Filter', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}