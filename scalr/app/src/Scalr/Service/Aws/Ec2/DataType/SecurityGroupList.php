<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * SecurityGroupList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 *
 * @property string      $requestId    Request ID
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\SecurityGroupData get()
 *           get(int $index)
 *           Gets SecurityGroupData object for the specified position in the list.
 */
class SecurityGroupList extends Ec2ListDataType
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
     * @param array|SecurityGroupData  $aListData List of SecurityGroupData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('groupId'), __NAMESPACE__ . '\\SecurityGroupData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Group', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}