<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * PolicyAttributeData
 *
 * The PolicyAttribute data type. This data type contains a key/value pair that defines properties of
 * a specific policy.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class PolicyAttributeData extends AbstractElbDataType
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