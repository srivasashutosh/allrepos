<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * PropagatingVgwList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    04.04.2013
 */
class PropagatingVgwList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|PropagatingVgwData  $aListData List of PropagatingVgwData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'gatewayId', __NAMESPACE__ . '\\PropagatingVgwData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'GatewayId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}