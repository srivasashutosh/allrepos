<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * ListenerDescriptionList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.10.2012
 * @method   ListenerDescriptionData get() get($position = null) Gets ListenerDescriptionData at specified position
 *                                                                    in the list.
 */
class ListenerDescriptionList extends AbstractElbListDataType
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
        parent::__construct($aListData, 'listener', 'Scalr\\Service\\Aws\\Elb\\DataType\\ListenerDescriptionData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Listeners', $member = true)
    {
        //Returns listeners parameters
        $listenerList = new ListenerList($this->getComputed());
        return $listenerList->getQueryArray($uriParameterName);
    }
}