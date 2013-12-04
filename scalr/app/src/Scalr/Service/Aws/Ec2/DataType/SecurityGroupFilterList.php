<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * SecurityGroupFilterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.12.2012
 */
class SecurityGroupFilterList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|SecurityGroupFilterData  $aListData SecurityGroupFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'value'), __NAMESPACE__ . '\\SecurityGroupFilterData');
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