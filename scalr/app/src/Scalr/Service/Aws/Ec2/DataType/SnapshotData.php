<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * AWS Ec2 SnapshotData (DescribeSnapshotsSetItemResponseType)
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since   30.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList        $tagSet        Any tags assigned to the resource
 */
class SnapshotData extends AbstractEc2DataType
{

    const STATUS_PENDING = 'pending';

    const STATUS_COMPLETED = 'completed';

    const STATUS_ERROR = 'error';

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('tagSet');

    /**
     * The ID of the snapshot.
     *
     * @var string
     */
    public $snapshotId;

    /**
     * The ID of the volume.
     *
     * @var string
     */
    public $volumeId;

    /**
     * The snapshot state
     * pending | completed | error
     *
     * @var string
     */
    public $status;

    /**
     * The time stamp when the snapshot was initiated
     *
     * @var DateTime
     */
    public $startTime;

    /**
     * The progress of the snapshot, as a percentage
     *
     * @var string
     */
    public $progress;

    /**
     * The ID of the AWS account that owns the snapshot
     *
     * @var string
     */
    public $ownerId;

    /**
     * The size of the volume, in GiB.
     *
     * @var string
     */
    public $volumeSize;

    /**
     * The description of the snapshot
     *
     * @var string
     */
    public $description;

    /**
     * The AWS account alias (amazon, self, etc.) or AWS account ID that owns the AMI.
     *
     * @var string
     */
    public $ownerAlias;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->snapshotId === null) {
            throw new Ec2Exception(sprintf('snapshotId has not been initialized for the "%s" yet!', get_class($this)));
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
        return $this->getEc2()->tag->create($this->snapshotId, $tagList);
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
        return $this->getEc2()->tag->delete($this->snapshotId, $tagList);
    }

    /**
     * DescribeSnapshots action
     *
     * Refreshes current object using API request to AWS
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  SnapshotData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->snapshot->describe($this->snapshotId)->get(0);
    }

    /**
     * DeleteSnapshot action
     *
     * Deletes a snapshot of an Amazon EBS volume.
     * Note! If you make periodic snapshots of a volume, the snapshots are incremental so that only the
     * blocks on the device that have changed since your last snapshot are incrementally saved in the
     * new snapshot. Even though snapshots are saved incrementally, the snapshot deletion process
     * is designed so that you need to retain only the most recent snapshot in order to restore the
     * volume.
     *
     * @return  bool         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->snapshot->delete($this->snapshotId);
    }

    /**
     * CopySnapshot action
     *
     * Copies a point-in-time snapshot of an Amazon Elastic Block Store (Amazon EBS) volume and stores it
     * in Amazon Simple Storage Service (Amazon S3).You can copy the snapshot within the same region or
     * from one region to another.You can use the snapshot to create new Amazon EBS volumes or Amazon
     * Machine Images (AMIs)
     *
     * @param   string     $srcRegion     The ID of the AWS region that contains the snapshot to be copied.
     * @param   string     $description   optional A description of the new Amazon EBS snapshot.
     * @param   string     $destRegion    optional The ID of the destination region.
     * @return  string     Returns ID of the created snapshot on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function copy($srcRegion, $description = null, $destRegion = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->snapshot->copy($srcRegion, $this->snapshotId, $description, $destRegion);
    }
}