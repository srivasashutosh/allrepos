<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * SubnetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList        $tagSet        Any tags assigned to the resource
 */
class SubnetData extends AbstractEc2DataType
{

    const STATE_PENDING = 'pending';

    const STATE_AVAILABLE = 'available';

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('tagSet');

    /**
     * The ID of the subnet.
     *
     * @var string
     */
    public $subnetId;

    /**
     * The current state of the subnet.
     * Valid values: pending | available
     *
     * @var string
     */
    public $state;

    /**
     * The ID of the VPC the subnet is in
     *
     * @var string
     */
    public $vpcId;

    /**
     * The CIDR block assigned to the subnet.
     *
     * @var string
     */
    public $cidrBlock;

    /**
     * The number of unused IP addresses in the subnet (the IP addresses
     * for any stopped instances are considered unavailable).
     *
     * @var int
     */
    public $availableIpAddressCount;

    /**
     * The Availability Zone of the subnet.
     *
     * @var string
     */
    public $availabilityZone;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->subnetId === null) {
            throw new Ec2Exception(sprintf('subnetId has not been initialized for the "%s" yet!', get_class($this)));
        }
    }

    /**
     * DescribeSubnets action
     *
     * Refreshes current object using API request to the AWS.
     *
     * @return  SubnetData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        parent::throwExceptionIfNotInitialized();
        return $this->getEc2()->subnet->describe($this->subnetId)->get(0);
    }

    /**
     * DeleteSubnet action
     *
     * Deletes a subnet from a VPC. You must terminate all running instances in the subnet before deleting it,
     * otherwise Amazon VPC returns an error
     *
     * @return  bool         Returns the TRUE on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        parent::throwExceptionIfNotInitialized();
        return $this->getEc2()->subnet->delete($this->subnetId);
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
        return $this->getEc2()->tag->create($this->subnetId, $tagList);
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
        return $this->getEc2()->tag->delete($this->subnetId, $tagList);
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
     * @return  string     Returns the ID of the association on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function associateRouteTable($routeTableId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->associate($routeTableId, $this->subnetId);
    }
}