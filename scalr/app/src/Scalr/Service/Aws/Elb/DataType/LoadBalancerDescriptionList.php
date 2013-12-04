<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * LoadBalancerDescriptionList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    02.10.2012
 * @method   LoadBalancerDescriptionData get() get($position = null) Gets LoadBalancerDescriptionData at specified position
 *                                                                   in the list.
 */
class LoadBalancerDescriptionList extends AbstractElbListDataType
{

    /**
     * Constructor
     *
     * @param array|LoadBalancerDescriptionList  $aListData  Load Balancer Description list
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('loadBalancerName'), 'Scalr\\Service\\Aws\\Elb\\DataType\\LoadBalancerDescriptionData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'LoadBalancerNames', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}