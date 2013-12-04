<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * AccountAttributeSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.13.2013
 */
class AccountAttributeSetList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|AccountAttributeSetData  $aListData List of AccountAttributeSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('attributeName'), __NAMESPACE__ . '\\AccountAttributeSetData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'AccountAttributeSet', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}