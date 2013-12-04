<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * UserIdGroupPairList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 */
class UserIdGroupPairList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|UserIdGroupPairData  $aListData List of UserIdGroupPairData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('userId', 'groupId', 'groupName'), __NAMESPACE__ . '\\UserIdGroupPairData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Groups', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}