<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * NetworkInterfaceList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    02.04.2013
 *
 * @property string $requestId The Request ID
 */
class NetworkInterfaceList extends Ec2ListDataType
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
     * @param array|NetworkInterfaceData  $aListData List of NetworkInterfaceData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'newtorkInterfaceId', __NAMESPACE__ . '\\NetworkInterfaceData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'NetworkInterfaceId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}