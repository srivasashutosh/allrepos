<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * RouteTableList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    04.04.2012
 */
class RouteTableList extends Ec2ListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('requestId');

    /**
     * Constructor
     *
     * @param array|RouteTableData  $aListData List of RouteTableData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'routeTableId', __NAMESPACE__ . '\\RouteTableData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'RouteTableId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}