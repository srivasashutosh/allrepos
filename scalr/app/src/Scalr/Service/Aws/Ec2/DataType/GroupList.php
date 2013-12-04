<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * GroupList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\GroupData get()
 *           get(int $index)
 *           Gets the GroupData object from the specified position in the list.
 */
class GroupList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|GroupData  $aListData List of GroupData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('groupId', 'groupName'), __NAMESPACE__ . '\\GroupData');
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