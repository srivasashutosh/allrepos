<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceStatusFilterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    15.01.2013
 */
class InstanceStatusFilterList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|InstanceStatusFilterData  $aListData InstanceStatusFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'value'), __NAMESPACE__ . '\\InstanceStatusFilterData');
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