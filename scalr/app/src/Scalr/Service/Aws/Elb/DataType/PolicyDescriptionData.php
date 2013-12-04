<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * PolicyDescriptionData
 *
 * The PolicyDescription data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 * @property PolicyAttributeDescriptionList $policyAttributeDescriptions A list of policy attribute description structures.
 * @method   PolicyAttributeDescriptionList getPolicyAttributeDescriptions() getPolicyAttributeDescriptions()
 * @method   PolicyDescriptionData          setPolicyAttributeDescriptions() setPolicyAttributeDescriptions(PolicyAttributeDescriptionList $policyAttributeDescriptions)
 */
class PolicyDescriptionData extends AbstractElbDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName'
    );

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var array
     */
    protected $_properties = array(
        'policyAttributeDescriptions'
    );

    /**
     * The name of the policy associated with the LoadBalancer.
     *
     * @var string
     */
    public $policyName;

    /**
     * The name of the policy type associated with the LoadBalancer.
     *
     * @var string
     */
    public $policyTypeName;
}