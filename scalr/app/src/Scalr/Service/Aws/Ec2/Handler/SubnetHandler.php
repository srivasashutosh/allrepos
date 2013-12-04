<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\SubnetList;
use Scalr\Service\Aws\Ec2\DataType\SubnetFilterData;
use Scalr\Service\Aws\Ec2\DataType\SubnetFilterList;
use Scalr\Service\Aws\Ec2\DataType\SubnetData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * EC2 Subnet service interface handler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     31.01.2013
 */
class SubnetHandler extends AbstractEc2Handler
{

    /**
     * Gets SubnetData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string     $subnetId  An unique identifier.
     * @return  SubnetData|null Returns SubnetData if it does exist in the cache or NULL otherwise.
     */
    public function get($subnetId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:Subnet')->find($subnetId);
    }

    /**
     * DescribeSubnets action
     *
     * Describes one or more of your subnets.
     *
     * @param   ListDataType|array|string               $subnetIdList optional A subnet ID list.
     * @param   SubnetFilterList|SubnetFilterData|array $filter       optional The list of the filters.
     * @return  SubnetList       Returns the list of found subnets on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($subnetIdList = null, $filter = null)
    {
        if ($subnetIdList !== null && !($subnetIdList instanceof ListDataType)) {
            $subnetIdList = new ListDataType($subnetIdList);
        }
        if ($filter !== null && !($filter instanceof SubnetFilterList)) {
            $filter = new SubnetFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeSubnets($subnetIdList, $filter);
    }

    /**
     * CreateSubnet action
     *
     * Creates a subnet in an existing VPC. You can create up to 20 subnets in a VPC. If you add more than
     * one subnet to a VPC, they're set up in a star topology with a logical router in the middle.
     *
     * @param   string     $vpcId            The ID of the VPC.
     * @param   string     $cidrBlock        The CIDR block for the subnet to cover (for example, 10.0.0.0/24).
     * @param   string     $availabilityZone optional The Availability Zone for the subnet. By default AWS
     *                                       selects a zone for you (recommended)
     * @return  SubnetData                   Returns the SubnetData on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($vpcId, $cidrBlock, $availabilityZone = null)
    {
        return $this->getEc2()->getApiHandler()->createSubnet($vpcId, $cidrBlock, $availabilityZone);
    }

    /**
     * DeleteSubnet action
     *
     * Deletes a subnet from a VPC. You must terminate all running instances in the subnet before deleting it,
     * otherwise Amazon VPC returns an error
     *
     * @param   string       $subnetId   The ID of the subnet.
     * @return  bool         Returns the TRUE on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($subnetId)
    {
        return $this->getEc2()->getApiHandler()->deleteSubnet($subnetId);
    }
}