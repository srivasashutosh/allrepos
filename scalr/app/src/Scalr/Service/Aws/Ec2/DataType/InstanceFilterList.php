<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceFilterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    08.01.2013
 */
class InstanceFilterList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|InstanceFilterData  $aListData InstanceFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'value'), __NAMESPACE__ . '\\InstanceFilterData');
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