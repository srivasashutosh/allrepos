<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * AccountAttributeSetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.03.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\AccountAttributeValueList $attributeValueSet
 *           Describes the value of an account attribute.
 */
class AccountAttributeSetData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('attributeValueSet');

    /**
     * The name of the attribute.
     * @var string
     */
    public $attributeName;

}