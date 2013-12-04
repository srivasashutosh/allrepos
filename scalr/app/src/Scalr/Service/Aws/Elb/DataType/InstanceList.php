<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * InstanceList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.10.2012
 *
 * @method   InstanceData get() get($position = null) Gets InstanceData at specified position
 *                                                    in the list.
 */
class InstanceList extends AbstractElbListDataType
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
     * @param array|InstanceData  $aListData  Instance List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('instanceId'), 'Scalr\\Service\\Aws\\Elb\\DataType\\InstanceData');
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