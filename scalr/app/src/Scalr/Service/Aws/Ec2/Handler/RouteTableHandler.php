<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\RouteTableFilterData;
use Scalr\Service\Aws\Ec2\DataType\RouteTableFilterList;
use Scalr\Service\Aws\Ec2\DataType\RouteTableList;
use Scalr\Service\Aws\Ec2\DataType\RouteTableData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * RouteTableHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     05.04.2013
 */
class RouteTableHandler extends AbstractEc2Handler
{

    /**
     * Gets RouteTableData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string       $routeTableId  Unique Identifier.
     * @return  RouteTableData|null Returns RouteTableData if it does exist in the cache or NULL otherwise.
     */
    public function get($routeTableId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:RouteTable')->find($routeTableId);
    }

    /**
     * DescribeRouteTables action
     *
     * Describes one or more of your route tables.
     *
     * @param   ListDataType|array|string $routeTableIdList optional
     *          The list of Route Table IDs
     *
     * @param   RouteTableFilterList|RouteTableFilterData|array $filter optional
     *          The filter list
     *
     * @return  RouteTableList Returns the list of the RouteTableData objects on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($routeTableIdList = null, $filter = null)
    {
        if ($routeTableIdList !== null && !($routeTableIdList instanceof ListDataType)) {
            $routeTableIdList = new ListDataType($routeTableIdList);
        }
        if ($filter !== null && !($filter instanceof RouteTableFilterList)) {
            $filter = new RouteTableFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeRouteTables($routeTableIdList, $filter);
    }

    /**
     * CreateRouteTable action
     *
     * Creates a route table within a VPC. After you create a new route table,
     * you can add routes and associate the table with a subnet
     *
     * @param   string     $vpcId The ID of the VPC
     * @return  RouteTableData Returns the RouteTableData object on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($vpcId)
    {
        return $this->getEc2()->getApiHandler()->createRouteTable($vpcId);
    }

    /**
     * DeleteRouteTable action
     *
     * Deletes a route table from a VPC. The route table must not be associated
     * with a subnet. You can't delete the main route table
     *
     * @param   string       $routeTableId The ID of the Route Table
     * @return  bool         Returns the RouteTableData object on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($routeTableId)
    {
        return $this->getEc2()->getApiHandler()->deleteRouteTable($routeTableId);
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
     * @param   string     $routeTableId The ID of the Route Table.
     * @param   string     $subnetId     The ID of the subnet.
     * @return  string     Returns the ID of the association on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function associate($routeTableId, $subnetId)
    {
        return $this->getEc2()->getApiHandler()->associateRouteTable($routeTableId, $subnetId);
    }

    /**
     * DisassociateRouteTable action
     *
     * Disassociates a subnet from a route table.
     * After you perform this action, the subnet no longer uses the routes in the route table.
     * Instead, it uses the routes in the VPC's main route table.
     *
     * @param   string     $associationId The association ID representing the current association between
     *                                    the route table and subnet.
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function disassociate($associationId)
    {
        return $this->getEc2()->getApiHandler()->disassociateRouteTable($associationId);
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
     * @param   string     $routeTableId  The ID of the new route table to associate with the subnet.
     * @param   string     $associationId The ID representing the current association
     *                                    between the original route table and the subnet.
     * @return  string     Returns the ID of the new association on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function replaceAssociation($routeTableId, $associationId)
    {
        return $this->getEc2()->getApiHandler()->replaceRouteTableAssociation($routeTableId, $associationId);
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
     * @param   string     $routeTableId         The ID of the route table where the route will be added.
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
    public function createRoute($routeTableId, $destinationCidrBlock, $gatewayId = null, $instanceId = null,
                                $networkInterfaceId = null)
    {
        return $this->getEc2()->getApiHandler()->createRoute(
            $routeTableId, $destinationCidrBlock, $gatewayId, $instanceId, $networkInterfaceId
        );
    }

    /**
     * ReplaceRoute action
     *
     * Replaces an existing route within a route table in a VPC
     *
     * Condition:You must provide only one of the following:
     * a GatewayId, InstanceId, or NetworkInterfaceId.
     *
     * @param   string     $routeTableId         The ID of the route table where the route will be added.
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
    public function replaceRoute($routeTableId, $destinationCidrBlock, $gatewayId = null, $instanceId = null,
                                 $networkInterfaceId = null)
    {
        return $this->getEc2()->getApiHandler()->replaceRoute(
            $routeTableId, $destinationCidrBlock, $gatewayId, $instanceId, $networkInterfaceId
        );
    }

    /**
     * DeleteRoute action
     *
     * Deletes a route table from a VPC. The route table must not be associated
     * with a subnet. You can't delete the main route table
     *
     * @param   string       $routeTableId         The ID of the Route Table.
     * @param   string       $destinationCidrBlock The CIDR range for the route to delete.
     * @return  bool         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteRoute($routeTableId, $destinationCidrBlock)
    {
        return $this->getEc2()->getApiHandler()->deleteRoute($routeTableId, $destinationCidrBlock);
    }
}