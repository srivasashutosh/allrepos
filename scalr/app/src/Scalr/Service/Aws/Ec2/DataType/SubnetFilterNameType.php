<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * SubnetFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.01.2013
 */
class SubnetFilterNameType extends StringType
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
     * The number of IP addresses in the subnet that are available
     */
    const TYPE_AVAILABLE_IP_ADDRESS_COUNT = 'available-ip-address-count';

    /**
     * The CIDR block of the subnet. The CIDR block you specify must exactly
     * match the subnet's CIDR block for information to be returned for the subnet.
     * Constraints: Must contain the slash followed by one or two digits (for example,/28)
     */
    const TYPE_CIDR = 'cidr';

    /**
     * The state of the subnet.
     * Valid values: pending | available
     */
    const TYPE_STATE = 'state';

    /**
     * The ID of the subnet
     */
    const TYPE_SUBNET_ID = 'subnet-id';

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
     * The ID of the VPC the subnet is in.
     */
    const TYPE_VPC_ID = 'vpc-id';

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