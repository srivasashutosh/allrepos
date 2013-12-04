<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * KeyPairFilterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    08.02.2013
 */
class KeyPairFilterList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|KeyPairFilterData  $aListData KeyPairFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'value'), __NAMESPACE__ . '\\KeyPairFilterData');
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