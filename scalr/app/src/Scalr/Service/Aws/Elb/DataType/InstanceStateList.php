<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * InstanceStateList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    11.10.2012
 *
 * @method   \Scalr\Service\Aws\Elb\DataType\InstanceStateData get() get($position = null) Gets InstanceStateData at specified position
 *                                                                                         in the list.
 */
class InstanceStateList extends AbstractElbListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName'
    );

    /**
     * Constructor
     *
     * @param array|InstanceStateData  $aListData  InstanceState List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('instanceId'), 'Scalr\\Service\\Aws\\Elb\\DataType\\InstanceStateData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Instances', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}