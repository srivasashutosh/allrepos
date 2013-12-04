<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * AWS Ec2 VolumeData (DescribeVolumesSetItemResponseType)
 *
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    21.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList        $tagSet        Any tags assigned to the resource
 * @property \Scalr\Service\Aws\Ec2\DataType\AttachmentSetResponseList $attachmentSet Any volumes attached
 */
class VolumeData extends AbstractEc2DataType
{
    const STATUS_CREATING = 'creating';

    const STATUS_AVAILABLE = 'available';

    const STATUS_IN_USE = 'in-use';

    const STATUS_DELETING = 'deleting';

    const STATUS_DELETED = 'deleted';

    const STATUS_ERROR = 'error';


    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('attachmentSet', 'tagSet');

    /**
     * The ID of the volume.
     * @var string
     */
    public $volumeId;

    /**
     * The size of the volume, in GiBs.
     * @var string
     */
    public $size;

    /**
     * The snapshot from which the volume was created (optional).
     * @var string
     */
    public $snapshotId;

    /**
     * The Availability Zone in which the volume was created.
     * @var string
     */
    public $availabilityZone;

    /**
     * The state of the volume.
     * Valid values: creating | available | in-use | deleting | deleted | error
     * @var string
     */
    public $status;

    /**
     * The time stamp when volume creation was initiated.
     * @var DateTime
     */
    public $createTime;

    /**
     * The volume type
     * standard | io1
     * @var string
     */
    public $volumeType;

    /**
     * The number of I/O operations per second (IOPS) that the volume supports.
     * Valid values: Range is 100 to 2000.
     * Condition: Required when the volume type is io1; not used with
     * standard volumes.
     *
     * @var int
     */
    public $iops;

    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->volumeId === null) {
            throw new Ec2Exception(sprintf(
                'volumeId has not been provided for the "%s" yet.', get_class($this)
            ));
        }
    }

    /**
     * DescribeVolumes action
     *
     * Refreshes current object using Amazon request
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  VolumeData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->volume->describe($this->volumeId)->get(0);
    }

    /**
     * DeleteVolume action
     *
     * Deletes an Amazon EBS volume. The volume must be in the available state (not attached to an instance)
     *
     * @return  bool         Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->volume->delete($this->volumeId);
    }

    /**
     * AttachVolume action
     *
     * Attaches an Amazon EBS volume to a running instance and exposes it to the instance with the specified
     * device name.
     *
     * For a list of supported device names, see Attaching the Volume to an Instance. Any device names that
     * aren't reserved for instance store volumes can be used for Amazon EBS volumes.
     *
     * Note! If a volume has an AWS Marketplace product code:
     *  -The volume can only be attached to the root device of a stopped instance.
     *
     *  -You must be subscribed to the AWS Marketplace code that is on the volume.
     *
     *  -The configuration (instance type, operating system) of the instance must support that specific
     *   AWS Marketplace code. For example, you cannot take a volume from a Windows instance
     *   and attach it to a Linux instance.
     *
     *  -AWS Marketplace product codes are copied from the volume to the instance.
     *
     * @param   string     $instanceId  The ID of the Instance. The instance must be running.
     * @param   string     $device      The device name as exposed to the instance
     * @return  AttachmentSetResponseData Returns AttachmentSetResponseData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attach($instanceId, $device)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->volume->attach($this->volumeId, $instanceId, $device);
    }

    /**
     * DetachVolume action
     *
     * Detaches an Amazon EBS volume from an instance. Make sure to unmount any file systems on the
     * device within your operating system before detaching the volume. Failure to do so will result in volume
     * being stuck in "busy" state while detaching.
     *
     * Note! If an Amazon EBS volume is the root device of an instance, it cannot be detached while the
     * instance is in the "running" state. To detach the root volume, stop the instance first.
     * If the root volume is detached from an instance with an AWS Marketplace product code, then
     * the AWS Marketplace product codes from that volume are no longer associated with the instance.
     *
     * @param   string     $instanceId  optional The ID of the Instance.
     * @param   string     $device      optional The device name.
     * @param   bool       $force       optional Forces detachment if the previous detachment attempt did
     *                                  not occur cleanly (logging into an instance, unmounting
     *                                  the volume, and detaching normally). This option can lead
     *                                  to data loss or a corrupted file system. Use this option only
     *                                  as a last resort to detach a volume from a failed instance.
     *                                  The instance won't have an opportunity to flush file system
     *                                  caches or file system metadata. If you use this option, you
     *                                  must perform file system check and repair procedures.
     * @return  AttachmentSetResponseData Returns AttachmentSetResponseData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detach($instanceId = null, $device = null, $force = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->volume->detach($this->volumeId, $instanceId, $device, $force);
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
        return $this->getEc2()->tag->create($this->volumeId, $tagList);
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
        return $this->getEc2()->tag->delete($this->volumeId, $tagList);
    }

    /**
     * CreateSnapshot action
     *
     * Creates a snapshot of an Amazon EBS volume and stores it in Amazon S3.You can use snapshots for
     * backups, to make copies of instance store volumes, and to save data before shutting down an instance.
     *
     * @param   string     $description optional A description of the Amazon EBS snapshot. (Up to 255 characters)
     * @return  SnapshotData            Returns the SnapshotData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createSnapshot($description = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->snapshot->create($this->volumeId, $description);
    }
}