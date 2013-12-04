<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * VpcFilterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.03.2013
 */
class VpcFilterList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|VpcFilterData  $aListData VpcFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'value'), __NAMESPACE__ . '\\VpcFilterData');
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