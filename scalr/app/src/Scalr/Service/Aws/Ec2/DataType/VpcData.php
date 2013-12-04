<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * VpcData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.03.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList $tagSet
 *           The list of the tags
 */
class VpcData extends AbstractEc2DataType
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
     * The ID of the VPC.
     *
     * @var string
     */
    public $vpcId;

    /**
     * The current state of the VPC.
     *
     * @var string
     */
    public $state;

    /**
     * The CIDR block for the VPC.
     *
     * @var string
     */
    public $cidrBlock;

    /**
     * The ID of the set of DHCP options you've associated with the VPC
     * (or default if the default options are associated with the VPC).
     *
     * @var string
     */
    public $dhcpOptionsId;

    /**
     * The allowed tenancy of instances launched into the VPC.
     *
     * @var string
     */
    public $instanceTenancy;

    /**
     * Indicates whether the VPC is the default VPC
     *
     * @var bool
     */
    public $isDefault;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->vpcId === null) {
            throw new Ec2Exception(sprintf(
                'VpcId has not been initialized for the "%s" yet', get_class($this)
            ));
        }
    }

    /**
     * DescribeVpcs action
     *
     * Refreshes current object using Amazon request
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  VpcData       Returns the VpcData object
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->vpc->describe($this->vpcId)->get(0);
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
        return $this->getEc2()->tag->create($this->vpcId, $tagList);
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
        return $this->getEc2()->tag->delete($this->vpcId, $tagList);
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
     * @return  bool Returns true on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->vpc->delete($this->vpcId);
    }

    /**
     * AttachInternetGateway action
     *
     * Attaches an Internet gateway to a VPC, enabling connectivity between the Internet and the VPC
     *
     * @param   string     $internetGatewayId The ID of the internet gateway
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attachInternetGateway($internetGatewayId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->internetGateway->attach($internetGatewayId, $this->vpcId);
    }

    /**
     * DetachInternetGateway action
     *
     * Detaches an Internet gateway from a VPC, disabling connectivity between the Internet and the VPC
     *
     * @param   string     $internetGatewayId The ID of the internet gateway
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detachInternetGateway($internetGatewayId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->internetGateway->detach($internetGatewayId, $this->vpcId);
    }

    /**
     * CreateSubnet action
     *
     * Creates a subnet in an existing VPC. You can create up to 20 subnets in a VPC. If you add more than
     * one subnet to a VPC, they're set up in a star topology with a logical router in the middle.
     *
     * @param   string     $cidrBlock        The CIDR block for the subnet to cover (for example, 10.0.0.0/24).
     * @param   string     $availabilityZone optional The Availability Zone for the subnet. By default AWS
     *                                       selects a zone for you (recommended)
     * @return  SubnetData                   Returns the SubnetData on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createSubnet($cidrBlock, $availabilityZone = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->subnet->create($this->vpcId, $cidrBlock, $availabilityZone);
    }

    /**
     * CreateRouteTable action
     *
     * Creates a route table within a VPC. After you create a new route table,
     * you can add routes and associate the table with a subnet
     *
     * @return  RouteTableData Returns the RouteTableData object on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createRouteTable()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->create($this->vpcId);
    }
}