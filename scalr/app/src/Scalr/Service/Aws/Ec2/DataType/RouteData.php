<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * RouteData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    04.04.2013
 *
 * @property string $routeTableId
 *           The ID of the associated Route Table
 *
 * @method   string getRouteTableId()
 *           getRouteTableId()
 *           Gets the ID of the associated Route Table
 *
 * @method   RouteData setRouteTableId()
 *           setRouteTableId($routeTableId)
 *           Sets the ID of the associated Route Table
 */
class RouteData extends AbstractEc2DataType
{

    const STATE_ACTIVE = 'active';
    const STATE_BLACKHOLE = 'blackhole';

    const ORIGIN_CREATE_ROUTE_TABLE = 'CreateRouteTable';
    const ORIGIN_CREATE_ROUTE = 'CreateRoute';
    const ORIGIN_ENABLE_VGW_ROUTE_PROPAGATION = 'EnableVgwRoutePropagation';

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('routeTableId');

    /**
     * The CIDR address block used for the destination match.
     * For example: 0.0.0.0/0
     *
     * @var string
     */
    public $destinationCidrBlock;

    /**
     * The ID of a gateway attached to your VPC
     *
     * @var string
     */
    public $gatewayId;

    /**
     * The ID of a NAT instance in your VPC
     *
     * @var string
     */
    public $instanceId;

    /**
     * The owner of the instance.
     *
     * @var string
     */
    public $instanceOwnerId;

    /**
     * The network interface ID.
     *
     * @var string
     */
    public $networkInterfaceId;

    /**
     * The state of the route.The blackhole state indicates that the route's
     * target isn't available (e.g., the specified gateway isn't attached to the
     * VPC, the specified NAT instance has been terminated, etc.).
     * Valid values: active | blackhole
     *
     * @var string
     */
    public $state;

    /**
     * Describes how the route was created.
     *
     * Valid values: Valid values: CreateRouteTable | CreateRoute | EnableVgwRoutePropagation
     *
     * CreateRouteTable indicates that route was automatically created when the route table was create.
     * CreateRoute indicates that the route was manually added to the route table.
     * EnableVgwRoutePropagation indicates that the route was propagated by route propagation.
     *
     * @var string
     */
    public $origin;

    /**
     * DeleteRoute action
     *
     * Deletes a route table from a VPC. The route table must not be associated
     * with a subnet. You can't delete the main route table
     *
     * @return  bool         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        if ($this->getRouteTableId() === null) {
            throw new Ec2Exception(sprintf(
                'routeTableId has not been initialized for the "%s" yet.', get_class($this)
            ));
        }
        return $this->getEc2()->routeTable->deleteRoute($this->getRouteTableId(), $this->destinationCidrBlock);
    }

    /**
     * ReplaceRoute action
     *
     * Replaces an existing route within a route table in a VPC
     *
     * Condition:You must provide only one of the following:
     * a GatewayId, InstanceId, or NetworkInterfaceId.
     *
     * @param   string     $gatewayId            optional The ID of a gataway attached to your VPC.
     * @param   string     $instanceId           optional The ID of a NAT instance in your VPC.
     * @param   string     $networkInterfaceId   optional Allows the routing of network interface IDs.
     *                                           Exactly one interface must be attached when
     *                                           specifying an instance ID or it fails.
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function replace($gatewayId = null, $instanceId = null,
                            $networkInterfaceId = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($this->getRouteTableId() === null) {
            throw new Ec2Exception(sprintf(
                'routeTableId has not been initialized for the "%s" yet.', get_class($this)
            ));
        }
        return $this->getEc2()->routeTable->replaceRoute(
            $this->getRouteTableId(), $this->destinationCidrBlock, $gatewayId, $instanceId, $networkInterfaceId
        );
    }
}