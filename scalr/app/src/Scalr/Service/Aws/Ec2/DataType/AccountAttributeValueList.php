<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * AccountAttributeValueList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.13.2013
 */
class AccountAttributeValueList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|AccountAttributeValueData  $aListData List of AccountAttributeValueData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('attributeValue'), __NAMESPACE__ . '\\AccountAttributeValueData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'AttributeValueSet', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}