<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * MonitorInstancesResponseSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.04.2013
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\MonitorInstancesResponseSetData get()
 *           get(int $index)
 *           Gets MonitorInstancesResponseSetData object from the specified position in the list.
 */
class MonitorInstancesResponseSetList extends Ec2ListDataType
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
     * @param array|MonitorInstancesResponseSetData  $aListData List of MonitorInstancesResponseSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            'instanceId',
            __NAMESPACE__ . '\\MonitorInstancesResponseSetData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'InstanceId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}