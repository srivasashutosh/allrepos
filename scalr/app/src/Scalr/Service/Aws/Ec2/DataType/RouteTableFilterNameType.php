<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * RouteTableFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    05.04.2013
 */
class RouteTableFilterNameType extends StringType
{
    /**
     * Filters the response based on a specific tag/value combination
     *
     * To instantiate the object with the tag:Anything we need to use following construction
     * InstanceFilterNameType::tag('Anything');
     */
    const TYPE_TAG_NAME = 'tag:Name';

    /**
     * The ID of an association ID for the route table.
     */
    const TYPE_ASSOCIATION_ROUTE_TABLE_ASSOCIATION_ID = 'association.route-table-association-id';

    /**
     * The ID of the route table involved in the association
     */
    const TYPE_ASSOCIATION_ROUTE_TABLE_ID = 'association.route-table-id';

    /**
     * The ID of the subnet involved in the association
     */
    const TYPE_ASSOCIATION_SUBNET_ID = 'association.subnet-id';

    /**
     * Indicates whether the route table is the main route table for the VPC.
     * Type:boolean
     */
    const TYPE_ASSOCIATION_MAIN = 'association.main';

    /**
     * The ID of the route table
     */
    const TYPE_ROUTE_TABLE_ID = 'route-table-id';

    /**
     * The CIDR range specified in a route in the table
     */
    const TYPE_ROUTE_DESTINATION_CIDR_BLOCK = 'route.destination-cidr-block';

    /**
     * The ID of a gateway specified in a route in the table.
     */
    const TYPE_ROUTE_GATEWAY_ID = 'route.gateway-id';

    /**
     * The ID of an instance specified in a route in the table.
     */
    const TYPE_ROUTE_INSTANCE_ID = 'route.instance-id';

    /**
     * Describes how the route was created.
     */
    const TYPE_ROUTE_ORIGIN = 'route.origin';

    /**
     * The state of a route in the route table. The
     * blackhole state indicates that the route's target
     * isn't available
     */
    const TYPE_ROUTE_STATE = 'route.state';

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
     * The ID of the VPC for the route table.
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