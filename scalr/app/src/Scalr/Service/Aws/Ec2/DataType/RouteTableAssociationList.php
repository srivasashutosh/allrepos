<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * RouteTableAssociationList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    04.04.2012
 */
class RouteTableAssociationList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|RouteTableAssociationData  $aListData List of RouteTableAssociationData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'RouteTableAssociationId', __NAMESPACE__ . '\\RouteTableAssociationData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'RouteTableAssociationId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}