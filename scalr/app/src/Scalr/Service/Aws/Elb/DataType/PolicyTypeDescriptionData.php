<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * PolicyTypeDescriptionData
 *
 * The PolicyTypeDescription data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 * @property PolicyAttributeTypeDescriptionList $policyAttributeTypeDescriptions The description of the policy attributes associated with
 *                                                                               the LoadBalancer policies defined by the Elastic Load Balancing service.
 * @method   PolicyAttributeTypeDescriptionList getPolicyAttributeTypeDescriptions() getPolicyAttributeTypeDescriptions()
 * @method   PolicyTypeDescriptionData          setPolicyAttributeTypeDescriptions() setPolicyAttributeTypeDescriptions(PolicyAttributeTypeDescriptionList $policyAttributeTypeDescriptions)
 */
class PolicyTypeDescriptionData extends AbstractElbDataType
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
        'policyAttributeTypeDescriptions'
    );

    /**
     * A human-readable description of the policy type.
     *
     * @var string
     */
    public $description;

    /**
     * The name of the policy type.
     *
     * @var string
     */
    public $policyTypeName;
}