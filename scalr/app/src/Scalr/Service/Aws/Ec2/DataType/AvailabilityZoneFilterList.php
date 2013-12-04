<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * AvailabilityZoneFilterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.12.2012
 */
class AvailabilityZoneFilterList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|AvailabilityZoneFilterData  $aListData AvailabilityZoneFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'value'), __NAMESPACE__ . '\\AvailabilityZoneFilterData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Filter', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}