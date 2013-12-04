<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * RouteTableFilterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    05.04.2013
 */
class RouteTableFilterList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|RouteTableFilterData  $aListData RouteTableFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'value'), __NAMESPACE__ . '\\RouteTableFilterData');
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