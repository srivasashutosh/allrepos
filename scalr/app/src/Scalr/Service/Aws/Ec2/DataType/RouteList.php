<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * RouteList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    04.04.2012
 *
 * @property string $routeTableId
 *           The ID of the associated Route Table
 *
 * @method   RouteTableList setRouteTableId()
 *           setRouteTableId($routeTableId)
 *           Set the ID of the associated Route Table
 */
class RouteList extends Ec2ListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('routeTableId');

    /**
     * Constructor
     *
     * @param array|RouteData  $aListData List of RouteData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'destinationCidrBlock', __NAMESPACE__ . '\\RouteData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Routes', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}