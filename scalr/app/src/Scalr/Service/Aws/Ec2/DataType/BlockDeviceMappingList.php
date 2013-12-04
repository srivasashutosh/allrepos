<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * BlockDeviceMappingList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingData get()
 *           get(int $index)
 *           Gets BlockDeviceMappingData from the specified position in the list.
 */
class BlockDeviceMappingList extends Ec2ListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array();

    /**
     * Constructor
     *
     * @param array|GroupData  $aListData List of GroupData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('deviceName', 'virtualName', 'noDevice', 'ebs'), __NAMESPACE__ . '\\BlockDeviceMappingData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'BlockDeviceMapping', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}