<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * AvailabilityZoneMessageList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.12.2012
 */
class AvailabilityZoneMessageList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|AvailabilityZoneMessageData  $aListData  AvailabilityZoneMessageData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('message'), __NAMESPACE__ . '\\AvailabilityZoneMessageData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Message', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}