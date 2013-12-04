<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InternetGatewayData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    03.04.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\InternetGatewayAttachmentList $attachmentSet
 *           Any VPCs attached to the Internet gateway
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList $tagSet
 *           The list of the tags
 */
class InternetGatewayData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('tagSet', 'attachmentSet');

    /**
     * The ID of the Internet gateway.
     *
     * @var string
     */
    public $internetGatewayId;

    /**
     * Constructor
     * @param   string     $internetGatewayId optional The ID of the internet gateway
     */
    public function __construct($internetGatewayId = null)
    {
        parent::__construct();
        $this->internetGatewayId = $internetGatewayId;
    }

    /**
     * Sets tag list
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array  $tagSet The list of the Tags
     * @return  InternetGatewayData
     */
    public function setTagSet($tagSet)
    {
        if ($tagSet !== null && !($tagSet instanceof ResourceTagSetList)) {
            $tagSet = new ResourceTagSetList($tagSet);
        }
        return $this->__call(__FUNCTION__, array($tagSet));
    }

    /**
     * Sets attachemt list
     *
     * @param   InternetGatewayAttachmentList|InternetGatewayAttachmentData|array  $attachmentSet The list of the Tags
     * @return  InternetGatewayData
     */
    public function setAttachmentSet($attachmentSet)
    {
        if ($attachmentSet !== null && !($attachmentSet instanceof InternetGatewayAttachmentList)) {
            $attachmentSet = new InternetGatewayAttachmentList($attachmentSet);
        }
        return $this->__call(__FUNCTION__, array($attachmentSet));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->internetGatewayId === null) {
            throw new Ec2Exception(sprintf(
                'internetGatewayId has not been initialized for the "%s" yet.',
                get_class($this)
            ));
        }
    }

    /**
     * DescribeInternetGateways
     *
     * Refreshes current object using Amazon request
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  InternetGatewayData Returns InternetGatewayData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->internetGateway->describe($this->internetGatewayId)->get(0);
    }

    /**
     * DeleteInternetGateway action
     *
     * Deletes an Internet gateway from your AWS account.
     * The gateway must not be attached to a VPC
     *
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->internetGateway->delete($this->internetGatewayId);
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
        return $this->getEc2()->tag->create($this->internetGatewayId, $tagList);
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
        return $this->getEc2()->tag->delete($this->internetGatewayId, $tagList);
    }

    /**
     * AttachInternetGateway action
     *
     * Attaches an Internet gateway to a VPC, enabling connectivity between the Internet and the VPC
     *
     * @param   string     $vpcId             The ID of the VPC
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attach($vpcId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->internetGateway->attach($this->internetGatewayId, $vpcId);
    }

    /**
     * DetachInternetGateway action
     *
     * Detaches an Internet gateway from a VPC, disabling connectivity between the Internet and the VPC
     *
     * @param   string     $vpcId             The ID of the VPC
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detach($vpcId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->internetGateway->detach($this->internetGatewayId, $vpcId);
    }
}