<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * NetworkInterfaceData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.04.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\GroupList $groupSet
 *           The security group list
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAttachmentData $attachment
 *           The network interface attachment
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAssociationData $association
 *           The association information for an Elastic IP associated with the network interface.
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList $tagSet
 *           The tags assigned to the resource.
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\NetworkInterfacePrivateIpAddressesSetList $privateIpAddressesSet
 *           The private IP addresses associated with the network interface
 *
 *
 * @method   NetworkInterfaceData setGroupSet()
 *           setGroupSet(GroupList $groupSet)
 *           Sets the security group list
 *
 * @method   GroupList getGroupSet() getGroupSet()
 *           Gets the security group list
 *
 * @method   NetworkInterfaceData setAssociation()
 *           setAssociation(NetworkInterfaceAssociationData $association)
 *           Sets an association information for an Elastic IP associated with the network interface
 *
 * @method   NetworkInterfaceAssociationData getAssociation() getAssociation()
 *           Gets an association information for an Elastic IP associated with the network interface
 *
 * @method   NetworkInterfaceData setAttachment()
 *           setAttachment(NetworkInterfaceAttachmentData $attachment)
 *           Sets the network interface attachment
 *
 * @method   NetworkInterfaceAttachmentData getAttachment() getAttachment()
 *           Gets the network interface attachment
 *
 * @method   NetworkInterfaceData setTagSet()
 *           setTagSet(ResourceTagSetList $tagSet)
 *           Sets the tags assigned to the resource.
 *
 * @method   ResourceTagSetList getTagSet() getTagSet()
 *           Gets the tags assigned to the resource.
 *
 * @method   NetworkInterfaceData setPrivateIpAddressesSet()
 *           setPrivateIpAddressesSet(ResourceTagSetList $privateIpAddressesSet)
 *           Sets the private IP addresses associated with the network interface
 *
 * @method   NetworkInterfacePrivateIpAddressesSetList getPrivateIpAddressesSet() getPrivateIpAddressesSet()
 *           Gets the private IP addresses associated with the network interface
 */
class NetworkInterfaceData extends AbstractEc2DataType
{

    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_USE = 'in-use';

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'groupSet', 'attachment', 'association', 'tagSet', 'privateIpAddressesSet'
    );

    /**
     * The ID of the network interface
     *
     * @var string
     */
    public $networkInterfaceId;

    /**
     * The ID of the subnet
     *
     * @var string
     */
    public $subnetId;

    /**
     * The ID of the VPC.
     *
     * @var string
     */
    public $vpcId;

    /**
     * The Availability Zone.
     *
     * @var string
     */
    public $availabilityZone;

    /**
     * A description
     *
     * @var string
     */
    public $description;

    /**
     * The ID of the customer who created the interface
     *
     * @var string
     */
    public $ownerId;

    /**
     * The ID of the entity that launched the instance on your behalf (for
     * example, AWS Management Console or Auto Scaling)
     *
     * @var string
     */
    public $requesterId;

    /**
     * Whether the network interface is being managed by AWS.
     *
     * @var string
     */
    public $requesterManaged;

    /**
     * The status (available or in-use).
     *
     * @var string
     */
    public $status;

    /**
     * The MAC address
     *
     * @var string
     */
    public $macAddress;

    /**
     * The IP address of the interface within the subnet.
     *
     * @var string
     */
    public $privateIpAddress;

    /**
     * The private DNS name.
     *
     * @var string
     */
    public $privateDnsName;

    /**
     * Whether traffic to or from the instance is validated
     *
     * @var bool
     */
    public $sourceDestCheck;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->networkInterfaceId === null) {
            throw new Ec2Exception(sprintf(
                'networkInterfaceId has not been initialized for the "%s" yet.',
                get_class($this)
            ));
        }
    }

    /**
     * DescribeNetworkInterfaces action
     *
     * Refreshes current object using AWS API call.
     *
     * @return  NetworkInterfaceData Returns Network Interfaces object
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->networkInterface->describe($this->networkInterfaceId);
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
        return $this->getEc2()->tag->create($this->networkInterfaceId, $tagList);
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
        return $this->getEc2()->tag->delete($this->networkInterfaceId, $tagList);
    }

    /**
     * DeleteNetworkInterface action
     *
     * Deletes the specified network interface.
     *
     * @return  bool Returns true on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->networkInterface->delete($this->networkInterfaceId);
    }

    /**
     * AttachNetworkInterface action
     *
     * Attaches a network interface to an instance.
     *
     * @param   string       $instanceId         The ID of the instance to attach to the network interface
     * @param   int          $deviceIndex        The index of the device for the network interface attachment.
     * @return  string  Returns Attachment ID on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attach($instanceId, $deviceIndex)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->networkInterface->attach($this->networkInterfaceId, $instanceId, $deviceIndex);
    }

    /**
     * DescribeNetworkInterfaceAttribute action
     *
     * Describes a network interface attribute.You can specify only one attribute at a time.
     *
     * @param   NetworkInterfaceAttributeType|string $attribute          The attribute.
     * @return  mixed  Returns attribute value. It can be string, boolean, NetworkInterfaceAttachmentData or
     *                 GroupList depends on attribute value you provided.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeAttribute($attribute)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->networkInterface->describeAttribute($this->networkInterfaceId, $attribute);
    }

    /**
     * ModifyNetworkInterfaceAttribute action
     *
     * Modifies a network interface attribute.You can specify only one attribute at a time.
     *
     * @param   NetworkInterfaceAttributeType|string $attr               The attribute name.
     * @param   mixed                                $value              The attribute value.
     * @return  bool  Returns TRUE on success
     * @throws  \BadFunctionCallException
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function modifyAttribute($attr, $value)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->networkInterface->modifyAttribute($this->networkInterfaceId, $attr, $value);
    }

    /**
     * ResetNetworkInterfaceAttribute action
     *
     * Resets a network interface attribute.You can specify only one attribute at a time
     *
     * @param   NetworkInterfaceAttributeType|string $attr               The attribute name.
     * @return  bool  Returns TRUE on success
     * @throws  \BadFunctionCallException
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function resetAttribute($attr)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->networkInterface->resetAttribute($this->networkInterfaceId, $attr);
    }
}