<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * SecurityGroupIdSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 */
class SecurityGroupIdSetList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|SecurityGroupIdSetData  $aListData List of SecurityGroupIdSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'groupId', __NAMESPACE__ . '\\SecurityGroupIdSetData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'SecurityGroupId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}