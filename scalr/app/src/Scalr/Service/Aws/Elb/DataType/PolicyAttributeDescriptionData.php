<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * PolicyAttributeDescriptionData
 *
 * The PolicyAttributeDescription data type. This data type is used to describe the attributes and
 * values associated with a policy.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class PolicyAttributeDescriptionData extends AbstractElbDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName',
        'policyName'
    );

    /**
     * The name of the attribute associated with the policy.
     *
     * @var string
     */
    public $attributeName;

    /**
     * The value of the attribute associated with the policy.
     *
     * @var string
     */
    public $attributeValue;
}