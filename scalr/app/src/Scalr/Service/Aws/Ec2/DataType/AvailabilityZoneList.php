<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * AvailabilityZoneList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.12.2012
 * @property string  $requestId  A request ID.
 */
class AvailabilityZoneList extends Ec2ListDataType
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
     * @param array|AvailabilityZoneData  $aListData List of AvailabilityZoneData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'zoneName', __NAMESPACE__ . '\\AvailabilityZoneData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'AvailabilityZones', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}