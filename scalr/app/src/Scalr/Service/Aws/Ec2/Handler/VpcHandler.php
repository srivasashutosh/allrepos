<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\VpcFilterData;
use Scalr\Service\Aws\Ec2\DataType\VpcFilterList;
use Scalr\Service\Aws\Ec2\DataType\VpcData;
use Scalr\Service\Aws\Ec2\DataType\VpcList;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * VpcHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.03.2013
 */
class VpcHandler extends AbstractEc2Handler
{

    /**
     * Gets VpcData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string       $vpcId  Unique Identifier.
     * @return  VpcData|null Returns VpcData if it does exist in the cache or NULL otherwise.
     */
    public function get($vpcId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:Vpc')->find($vpcId);
    }

    /**
     * DescribeVpcs action
     *
     * Describes one or more of your VPCs.
     *
     * @param   ListDataType|array|string         $vpcIdList optional The list of the VpcID
     * @param   VpcFilterList|VpcFilterData|array $filter    optional The filter list
     * @return  VpcList       Returns the list of the Vpcs
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($vpcIdList = null, $filter = null)
    {
        if ($vpcIdList !== null && !($vpcIdList instanceof ListDataType)) {
            $vpcIdList = new ListDataType($vpcIdList);
        }
        if ($filter !== null && !($filter instanceof VpcFilterList)) {
            $filter = new VpcFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeVpcs($vpcIdList, $filter);
    }

    /**
     * CreateVpc action
     *
     * Creates a VPC with the specified CIDR block.
     * The smallest VPC you can create uses a /28 netmask (16 IP addresses),
     * and the largest uses a /16 netmask (65,536 IP addresses).
     *
     * By default, each instance you launch in the VPC has the default DHCP options, which includes only a
     * default DNS server that we provide (AmazonProvidedDNS)
     *
     * @param   string     $cidrBlock       The CIDR block you want the VPC to cover (for example, 10.0.0.0/16).
     * @param   string     $instanceTenancy optional The supported tenancy options for instances
     *                                      launched into the VPC. A value of default means
     *                                      that instances can be launched with any tenancy;
     *                                      a value of dedicated means all instances are
     *                                      launched as dedicated tenancy instances
     *                                      regardless of the tenancy assigned to the instance
     *                                      at launch. Setting the instance tenancy to
     *                                      dedicated runs your instance on single-tenant
     *                                      hardware.
     * @return  VpcData Returns VpcData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($cidrBlock, $instanceTenancy = null)
    {
        return $this->getEc2()->getApiHandler()->createVpc($cidrBlock, $instanceTenancy);
    }

    /**
     * DeleteVpc action
     *
     * Deletes a VPC.You must detach or delete all gateways or
     * other objects that are dependent on the VPC
     * first. For example, you must terminate all running instances,
     * delete all security groups (except the default),
     * delete all the route tables (except the default), and so on.
     *
     * @param   string      $vpcId The ID of the VPC
     * @return  bool Returns true on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($vpcId)
    {
        return $this->getEc2()->getApiHandler()->deleteVpc($vpcId);
    }
}