<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * RouteTableData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    04.04.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList $tagSet
 *           The list of the tags
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\RouteList $routeSet
 *           A list of routes in the route table
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\RouteTableAssociationList $associationSet
 *           A list of associations between the route table and one or more subnets
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\PropagatingVgwList $propagatingVgwSet
 *           The IDs of any virtual private gateways (VGW) propagating routes
 *
 * @method   RouteTableData setTagSet()
 *           setTagSet(ResourceTagSetList $tagSet)
 *           Sets the list of the tags
 *
 * @method   RouteTableData setRouteSet()
 *           setRouteSet(RouteList $routeSet)
 *           Sets a list of routes in the route table
 *
 * @method   RouteTableData setAssociationSet()
 *           setAssociationSet(RouteTableAssociationList $associationSet)
 *           Sets a list of associations between the route table and one or more subnets
 *
 * @method   RouteTableData setPropagatingVgwSet()
 *           setPropagatingVgwSet(PropagatingVgwList $propagatingVgwSet)
 *           Sets the IDs of any virtual private gateways (VGW) propagating routes
 */
class RouteTableData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('routeSet', 'associationSet', 'propagatingVgwSet', 'tagSet');

    /**
     * The route table's ID.
     *
     * @var string
     */
    public $routeTableId;

    /**
     * The ID of the VPC.
     *
     * @var string
     */
    public $vpcId;

    /**
     * Constructor
     *
     * @param   string     $routeTableId optional The route table's ID
     * @param   string     $vpcId        optional The ID of the VPC
     */
    public function __construct($routeTableId = null, $vpcId = null)
    {
        parent::__construct();
        $this->routeTableId = $routeTableId;
        $this->vpcId = $vpcId;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->routeTableId === null) {
            throw new Ec2Exception(sprintf(
                'routeTableId has not been initialized for the "%s" yet.', get_class($this)
            ));
        }
    }

    /**
     * CreateTags action
     *
     * Adds or overwrites one or more tags for the specified EC2 resource or resources. Each resource can
     * have a maximum of 10 tags. Each tag consists of a key and optional value. Tag keys must be unique per
     * resource.
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createTags($tagList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->tag->create($this->routeTableId, $tagList);
    }

    /**
     * DeleteTags action
     *
     * Deletes a specific set of tags from a specific set of resources. This call is designed to follow a
     * DescribeTags call. You first determine what tags a resource has, and then you call DeleteTags with
     * the resource ID and the specific tags you want to delete.
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteTags($tagList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->tag->delete($this->routeTableId, $tagList);
    }

    /**
     * DescribeRouteTables action
     *
     * Refreshes current object using Amazon request
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  RouteTableData Returns the RouteTableData object
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->describe($this->routeTableId)->get(0);
    }

    /**
     * DeleteRouteTable action
     *
     * Deletes a route table from a VPC. The route table must not be associated
     * with a subnet. You can't delete the main route table
     *
     * @return  bool         Returns the RouteTableData object on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->delete($this->routeTableId);
    }

    /**
     * AssociateRouteTable action
     *
     * Associates a subnet with a route table. The subnet and route table must be in the same VPC.
     * This association causes traffic originating from the subnet to be routed
     * according to the routes in the route table. The action returns an association ID,
     * which you need if you want to disassociate the route table
     * from the subnet later. A route table can be associated with multiple subnets.
     *
     * @param   string     $subnetId     The ID of the subnet.
     * @return  string     Returns the ID of the association on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function associate($subnetId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->associate($this->routeTableId, $subnetId);
    }

    /**
     * ReplaceRouteTableAssociation action
     *
     * Changes the route table associated with a given subnet in a VPC.
     * After you execute this action, the subnet uses the routes
     * in the new route table it's associated with.
     *
     * You can also use this action to change which table is the main route table in the VPC.
     * You just specify the main route table's association ID and the route table that you
     * want to be the new main route table.
     *
     * @param   string     $associationId The ID representing the current association
     *                                    between the original route table and the subnet.
     * @return  string     Returns the ID of the new association on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function replaceAssociation($associationId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->replaceAssociation($this->routeTableId, $associationId);
    }

    /**
     * DeleteRoute action
     *
     * Deletes a route table from a VPC. The route table must not be associated
     * with a subnet. You can't delete the main route table
     *
     * @param   string       $destinationCidrBlock The CIDR range for the route to delete.
     * @return  bool         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteRoute($destinationCidrBlock)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->deleteRoute($this->routeTableId, $destinationCidrBlock);
    }

    /**
     * ReplaceRoute action
     *
     * Replaces an existing route within a route table in a VPC
     *
     * Condition:You must provide only one of the following:
     * a GatewayId, InstanceId, or NetworkInterfaceId.
     *
     * @param   string     $destinationCidrBlock The CIDR address block used for the destination match.
     * @param   string     $gatewayId            optional The ID of a gataway attached to your VPC.
     * @param   string     $instanceId           optional The ID of a NAT instance in your VPC.
     * @param   string     $networkInterfaceId   optional Allows the routing of network interface IDs.
     *                                           Exactly one interface must be attached when
     *                                           specifying an instance ID or it fails.
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function replaceRoute($destinationCidrBlock, $gatewayId = null, $instanceId = null,
                                 $networkInterfaceId = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->replaceRoute(
            $this->routeTableId, $destinationCidrBlock, $gatewayId, $instanceId, $networkInterfaceId
        );
    }

    /**
     * CreateRoute action
     *
     * Creates a route in a route table within a VPC.
     * The route's target can be either a gateway attached to the
     * VPC or a NAT instance in the VPC.
     * When determining how to route traffic, we use the route with the most specific match.
     * For example, let's say the traffic is destined for 192.0.2.3, and the route table
     * includes the following two routes:
     * 192.0.2.0/24 (goes to some target A)
     * 192.0.2.0/28 (goes to some target B)
     *
     * Both routes apply to the traffic destined for 192.0.2.3.
     * However, the second route in the list covers a
     * smaller number of IP addresses and is therefore more specific,
     * so we use that route to determine where to target the traffic.
     *
     * Condition:You must provide only one of the following:
     * a GatewayId, InstanceId, or NetworkInterfaceId.
     *
     * @param   string     $destinationCidrBlock The CIDR address block used for the destination match.
     * @param   string     $gatewayId            optional The ID of a gataway attached to your VPC.
     * @param   string     $instanceId           optional The ID of a NAT instance in your VPC.
     * @param   string     $networkInterfaceId   optional Allows the routing of network interface IDs.
     *                                           Exactly one interface must be attached when
     *                                           specifying an instance ID or it fails.
     * @return  bool     Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createRoute($destinationCidrBlock, $gatewayId = null, $instanceId = null,
                                $networkInterfaceId = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->createRoute(
            $this->routeTableId, $destinationCidrBlock, $gatewayId, $instanceId, $networkInterfaceId
        );
    }
}