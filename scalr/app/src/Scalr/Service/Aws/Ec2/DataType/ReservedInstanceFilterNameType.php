<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * ReservedInstanceFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    14.01.2013
 */
class ReservedInstanceFilterNameType extends StringType
{

    /**
     * Filters the response based on a specific tag/value combination
     *
     * To instantiate the object with the tag:Anything we need to use following construction
     * InstanceFilterNameType::tag('Anything');
     */
    const TYPE_TAG_NAME = 'tag:Name';

    /**
     * The Availability Zone of the instance.
     */
    const TYPE_AVAILABILITY_ZONE = 'availability-zone';

    /**
     * The duration of the Reserved Instance (one year or three years), in seconds.
     */
    const TYPE_DURATION = 'duration';

    /**
     * The purchase price of the Reserved Instance (for example, 9800.0)
     */
    const TYPE_FIXED_PRICE = 'fixed-price';

    /**
     * The instance type on which the Reserved Instance can be used
     */
    const TYPE_INSTANCE_TYPE = 'instance-type';

    /**
     * The product description of the Reserved Instance.
     * Linux/UNIX | Linux/UNIX (Amazon VPC) | Windows | Windows (Amazon VPC)
     */
    const TYPE_PRODUCT_DESCRIPTION = 'product-description';

    /**
     * The ID of the instance.
     */
    const TYPE_RESERVED_INSTANCES_ID = 'reserved-instances-id';

    /**
     * The time at which the Reserved Instance purchase request was
     * placed (for example, 2010-08-07T11:54:42.000Z).
     */
    const TYPE_START = 'start';

    /**
     * The state of the Reserved Instance.
     * pending-payment | active | payment-failed | retired
     */
    const TYPE_STATE = 'state';

    /**
     * The key of a tag assigned to the resource.
     * This filter is independent of the tag-value filter.
     * For example, if you use both the filter "tag-key=Purpose" and the filter "tag-value=X",
     * you get any resources assigned both the tag key Purpose (regardless of what the tag's value is),
     * and the tag value X (regardless of what the tag's key is). If you want to list only resources where
     * Purpose is X, see the tag:key filter
     */
    const TYPE_TAG_KEY = 'tag-key';

    /**
     * The value of a tag assigned to the resource. This filter is independent of the tag-key filter.
     */
    const TYPE_TAG_VALUE = 'tag-value';

    /**
     * The usage price of the Reserved Instance, per hour (for example, 0.84)
     */
    const TYPE_USAGE_PRICE = 'usage-price';

    public static function getPrefix()
    {
        return 'TYPE_';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::validate()
     */
    protected function validate()
    {
        return preg_match('#^tag\:.+#', $this->value) ?: parent::validate();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::__callstatic()
     */
    public static function __callStatic($name, $args)
    {
        $class = __CLASS__;
        if ($name == 'tag') {
            if (!isset($args[0])) {
                throw new \InvalidArgumentException(sprintf(
                    'Tag name must be provided! Please use %s::tag("symbolic-name")', $class
                ));
            }
            return new $class('tag:' . $args[0]);
        }
        return parent::__callStatic($name, $args);
    }
}