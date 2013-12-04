<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InternetGatewayList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    03.04.2012
 */
class InternetGatewayList extends Ec2ListDataType
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
     * @param array|InternetGatewayData  $aListData List of InternetGatewayData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'internetGatewayId', __NAMESPACE__ . '\\InternetGatewayData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'InternetGatewayId', $member = true)
    {
        return array_filter(parent::getQueryArray($uriParameterName, $member), function ($val) {
            return $val !== null;
        });
    }
}