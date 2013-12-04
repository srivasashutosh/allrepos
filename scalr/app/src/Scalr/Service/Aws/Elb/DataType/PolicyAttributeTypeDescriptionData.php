<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * PolicyAttributeTypeDescriptionData
 *
 * The PolicyAttributeTypeDescription data type. This data type is used to describe values that are
 * acceptable for the policy attribute
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class PolicyAttributeTypeDescriptionData extends AbstractElbDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName',
        'policyTypeName'
    );

    /**
     * The name of the attribute associated with the policy type.
     *
     * @var string
     */
    public $attributeName;

    /**
     * The type of attribute. For example, Boolean, Integer, etc.
     *
     * @var string
     */
    public $attributeType;

    /**
     * The cardinality of the attribute.
     *
     * Valid Values:
     * ONE(1) : Single value required
     * ZERO_OR_ONE(0..1) : Up to one value can be supplied
     * ZERO_OR_MORE(0..*) : Optional. Multiple values are allowed
     * ONE_OR_MORE(1..*0) : Required. Multiple values are allowed
     *
     * @var string
     */
    public $cardinality;

    /**
     * The default value of the attribute, if applicable.
     *
     * @var string
     */
    public $defaultValue;

    /**
     * A human-readable description of the attribute.
     *
     * @var string
     */
    public $description;
}