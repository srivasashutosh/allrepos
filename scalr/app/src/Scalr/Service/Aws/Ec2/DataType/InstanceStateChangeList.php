<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceStateChangeList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    18.01.2013
 */
class InstanceStateChangeList extends Ec2ListDataType
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
     * @param array|InstanceStateChangeData  $aListData List of InstanceStateChangeData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('instanceId', 'currentState', 'previousState'), __NAMESPACE__ . '\\InstanceStateChangeData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'InstanceStateChange', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}