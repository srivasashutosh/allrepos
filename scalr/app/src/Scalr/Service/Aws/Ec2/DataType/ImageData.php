<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * AWS Ec2 ImageData (DescribeImagesResponseItemType)
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    23.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ProductCodeSetList     $productCodes       A product codes associated with the AMI.
 * @property \Scalr\Service\Aws\Ec2\DataType\StateReasonData        $stateReason        The reason for the state change.
 * @property \Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingList $blockDeviceMapping Any block device mapping entries.
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList     $tagSet             Any tags assigned to the resource.
 */
class ImageData extends AbstractEc2DataType
{

    const STATE_AVAILABLE = 'available';

    const STATE_PENDING   = 'pending';

    const STATE_FAILED    = 'failed';

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('productCodes', 'stateReason', 'blockDeviceMapping', 'tagSet');

    /**
     * The ID of the AMI (Amazon Machine Image)
     *
     * @var string
     */
    public $imageId;

    /**
     * The name of the AMI that was provided during image creation
     *
     * @var string
     */
    public $name;

    /**
     * The description of the AMI that was provided during image creation
     *
     * @var string
     */
    public $description;

    /**
     * The Location of the AMI
     *
     * @var string
     */
    public $imageLocation;

    /**
     * Current state of the AMI. If the operation returns available, the
     * image is successfully registered and available for launching.
     * Valid values: available | pending | failed
     *
     * @var string
     */
    public $imageState;

    /**
     * AWS account ID of the image owner.
     *
     * @var string
     */
    public $imageOwnerId;

    /**
     * Whether the image has public launch permissions. The value is true
     * if this image has public launch permissions or false if it has only
     * implicit and explicit launch permissions
     *
     * @var bool
     */
    public $isPublic;

    /**
     * The architecture of the image.
     *
     * @var string
     */
    public $architecture;

    /**
     * The type of image (machine, kernel, or RAM disk).
     *
     * @var string
     */
    public $imageType;

    /**
     * The kernel associated with the image, if any. Only applicable for machine images.
     *
     * @var string
     */
    public $kernelId;

    /**
     * The RAM disk associated with the image, if any. Only applicable for machine images.
     *
     * @var string
     */
    public $ramdiskId;

    /**
     * The value is Windows for Windows AMIs; otherwise blank.
     *
     * @var string
     */
    public $platform;

    /**
     * The AWS account alias (e.g., amazon, self, etc.) or AWS account ID that owns the AMI.
     *
     * @var string
     */
    public $imageOwnerAlias;

    /**
     * The type of root device used by the AMI.The AMI can use an Amazon
     * EBS volume or an instance store volume.
     * ebs | instance-store
     *
     * @var string
     */
    public $rootDeviceType;

    /**
     * The device name of the root device (e.g., /dev/sda1, or xvda).
     * @var string
     */
    public $rootDeviceName;

    /**
     * The type of virtualization of the AMI.
     * paravirtual | hvm
     * @var string
     */
    public $virtualizationType;

    /**
     * The image's hypervisor type
     * ovm | xen
     * @var string
     */
    public $hypervisor;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        if ($this->imageId === null) {
            throw new Ec2Exception(sprintf(
                'imageId property has not been initialized for the "%s" yet', get_class($this)
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
        return $this->getEc2()->tag->create($this->imageId, $tagList);
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
        return $this->getEc2()->tag->delete($this->imageId, $tagList);
    }

    /**
     * DescribeImages action
     *
     * Refreshes description of the object using request to Amazon api
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  ImageData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->image->describe($this->imageId)->get(0);
    }

    /**
     * DeregisterImage action
     *
     * Deregisters the specified AMI. Once deregistered, the AMI cannot be used to launch new instances.
     * Note! This command does not delete the AMI.
     *
     * @return  bool        Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deregister()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->image->deregister($this->imageId);
    }

    /**
     * CopyImage action
     *
     * Initiates the copy of an AMI from the specified source region to the region in which the API call is executed
     *
     * @param   string     $srcRegion     The ID of the AWS region that contains the AMI to be copied.
     * @param   string     $name          optional The name of the new Amazon EC2 AMI.
     * @param   string     $description   optional A description of the new EC2 AMI in the destination region.
     * @param   string     $clientToken   optional Unique, case-sensitive identifier you provide to ensure
     *                                             idempotency of the request
     * @param   string     $destRegion    optional The ID of the destination region.
     * @return  string     Returns ID of the created image on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function copy($srcRegion, $name = null, $description = null,
                         $clientToken = null, $destRegion = null)
    {
        return $this->getEc2()->image->copy(
            $srcRegion, $this->imageId, $name, $description, $clientToken, $destRegion
        );
    }
}