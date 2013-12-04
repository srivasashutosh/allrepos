<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\SnapshotList;
use Scalr\Service\Aws\Ec2\DataType\SnapshotFilterData;
use Scalr\Service\Aws\Ec2\DataType\SnapshotFilterList;
use Scalr\Service\Aws\Ec2\DataType\SnapshotData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * EC2 Snapshot service interface handler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     30.01.2013
 */
class SnapshotHandler extends AbstractEc2Handler
{

    /**
     * Gets SnapshotData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string    $snapshotId  An unique identifier.
     * @return  SnapshotData|null Returns SnapshotData if it does exist in the cache or NULL otherwise.
     */
    public function get($snapshotId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:Snapshot')->find($snapshotId);
    }

    /**
     * DescribeSnapshots action
     *
     * Describes one or more of the Amazon EBS snapshots available to you. Snapshots available to you include
     * public snapshots available for any AWS account to launch, private snapshots you own, and private
     * snapshots owned by another AWS account but for which you've been given explicit create volume
     * permissions.
     *
     * @param   ListDataType|array|string                   $snapshotIdList   optional One or more snapshot IDs. By default it describes snapshots
     *                                                                        for which you have launch permissions.
     * @param   ListDataType|array|string                   $ownerList        optional Returns the snapshots owned by the specified owner.
     *                                                                        Multiple owners can be specified.
     *                                                                        Valid values: self | amazon | AWS Account ID
     * @param   SnapshotFilterList|SnapshotFilterData|array $filter           optional The list of filters.
     * @param   ListDataType|array|string                   $restorableByList optional One or more AWS accounts IDs that can create volumes
     *                                                                        from the snapshot.
     * @return  SnapshotList Returns the list of snapshots on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($snapshotIdList = null, $ownerList = null, $filter = null, $restorableByList = null)
    {
        if ($snapshotIdList !== null && !($snapshotIdList instanceof ListDataType)) {
            $snapshotIdList = new ListDataType($snapshotIdList);
        }
        if ($ownerList !== null && !($ownerList instanceof ListDataType)) {
            $ownerList = new ListDataType($ownerList);
        }
        if ($restorableByList !== null && !($restorableByList instanceof ListDataType)) {
            $restorableByList = new ListDataType($restorableByList);
        }
        if ($filter !== null && !($filter instanceof SnapshotFilterList)) {
            $filter = new SnapshotFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeSnapshots($snapshotIdList, $ownerList, $filter, $restorableByList);
    }

    /**
     * CreateSnapshot action
     *
     * Creates a snapshot of an Amazon EBS volume and stores it in Amazon S3.You can use snapshots for
     * backups, to make copies of instance store volumes, and to save data before shutting down an instance.
     *
     * @param   string     $volumeId    The ID of the Amazon EBS volume.
     * @param   string     $description optional A description of the Amazon EBS snapshot. (Up to 255 characters)
     * @return  SnapshotData            Returns the SnapshotData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($volumeId, $description = null)
    {
        return $this->getEc2()->getApiHandler()->createSnapshot($volumeId, $description);
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
     * @param   string       $snapshotId The ID of the Amazon EBS snapshot.
     * @return  bool         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($snapshotId)
    {
        return $this->getEc2()->getApiHandler()->deleteSnapshot($snapshotId);
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
     * @param   string     $srcSnapshotId The ID of the Amazon EBS snapshot to copy.
     * @param   string     $description   optional A description of the new Amazon EBS snapshot.
     * @param   string     $destRegion    optional The ID of the destination region.
     * @return  string     Returns ID of the created snapshot on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function copy($srcRegion, $srcSnapshotId, $description = null, $destRegion = null)
    {
        return $this->getEc2()->getApiHandler()->copySnapshot($srcRegion, $srcSnapshotId, $description, $destRegion);
    }
}