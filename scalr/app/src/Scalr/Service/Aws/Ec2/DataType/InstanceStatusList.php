<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceStatusList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 *
 * @method   string getNextToken() getNextToken()
 *           Gets a next token.
 *
 * @method   string getRequestId() getRequestId()
 *           Gets an ID of the request.
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\InstanceStatusData get()
 *           get(int $index)
 *           Gets InstanceStatusData object for the specified position in the list.
 */
class InstanceStatusList extends Ec2ListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('nextToken', 'requestId');

    /**
     * Constructor
     *
     * @param array|InstanceStatusData  $aListData List of InstanceStatusData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'instanceId', __NAMESPACE__ . '\\InstanceStatusData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'InstanceStatusList', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}