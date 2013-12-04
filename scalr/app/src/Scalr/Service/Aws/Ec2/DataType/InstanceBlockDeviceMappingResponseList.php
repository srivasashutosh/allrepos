<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceBlockDeviceMappingResponseList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    08.01.2013
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\InstanceBlockDeviceMappingResponseData get()
 *           get(int $index)
 *           Returns InstanceBlockDeviceMappingResponseData object from the specified position in the list.
 */
class InstanceBlockDeviceMappingResponseList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|InstanceBlockDeviceMappingResponseData  $aListData InstanceBlockDeviceMappingResponseData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('deviceName', 'ebs'), __NAMESPACE__ . '\\InstanceBlockDeviceMappingResponseData');
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