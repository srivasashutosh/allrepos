<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DBSnapshotData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.03.2013
 */
class DBSnapshotData extends AbstractRdsDataType
{

    const STATUS_AVAILABLE = 'available';

    const STATUS_DELETING = 'deleting';

    /**
     * Specifies the allocated storage size in gigabytes (GB).
     *
     * @var int
     */
    public $allocatedStorage;

    /**
     * Specifies the name of the Availability Zone the DB Instance
     * was located in at the time of the DB Snapshot.
     *
     * @var string
     */
    public $availabilityZone;

    /**
     * Specifies the the DBInstanceIdentifier
     * of the DB Instance this DB Snapshot was created from
     *
     * @var string
     */
    public $dBInstanceIdentifier;

    /**
     * Specifies the identifier for the DB Snapshot.
     *
     * @var string
     */
    public $dBSnapshotIdentifier;

    /**
     * Specifies the name of the database engine
     *
     * @var string
     */
    public $engine;

    /**
     * Specifies the version of the database engine
     *
     * @var string
     */
    public $engineVersion;

    /**
     * Specifies the time (UTC) when the snapshot was taken
     *
     * @var DateTime
     */
    public $instanceCreateTime;

    /**
     * Specifies the Provisioned IOPS (I/O operations per second)
     * value of the DB Instance at the time of the snapshot
     *
     * @var int
     */
    public $iops;

    /**
     * License model information for the restored DB Instance
     *
     * @var string
     */
    public $licenseModel;

    /**
     * Provides the master username for the DB Instance
     *
     * @var string
     */
    public $masterUsername;

    /**
     * Specifies the port that the database engine
     * was listening on at the time of the snapshot
     *
     * @var int
     */
    public $port;

    /**
     * Provides the time (UTC) when the snapshot was taken.
     *
     * @var DateTime
     */
    public $snapshotCreateTime;

    /**
     * Provides the type of the DB Snapshot
     *
     * @var string
     */
    public $snapshotType;

    /**
     * Specifies the status of this DB Snapshot
     *
     * @var string
     */
    public $status;

    /**
     * Provides the Vpc Id associated with the DB Snapshot
     *
     * @var string
     */
    public $vpcId;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Rds.AbstractRdsDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->dBSnapshotIdentifier === null) {
            throw new RdsException(sprintf(
                'dbSnapshotIdentifier has not been initialized for "%s" yet.', get_class($this)
            ));
        }
    }

    /**
     * DescribeDBSnapshots action
     *
     * Refreshes description of the object using request to Amazon.
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * If not, solution is to use $object = object->refresh() instead.
     *
     * @return  DBSnapshotData Returns DBSnapshotData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbSnapshot->describe(null, $this->dBSnapshotIdentifier)->get(0);
    }

    /**
     * DeleteDBSnapshot action
     *
     * Deletes a DBSnapshot.
     * Note! The DBSnapshot must be in the available state to be deleted
     *
     * @param   string     $dBSnapshotIdentifier The Identifier for the DB Snapshot to delete.
     * @return  DBSnapshotData Returns DBSnapshotData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbSnapshot->delete($this->dBSnapshotIdentifier);
    }

    /**
     * Gets a new RestoreDBInstanceFromDBSnapshotRequestData object for this DB Instance
     *
     * @param   string      $dbInstanceIdentifier  The DB Instance Identifier.
     * @return  RestoreDBInstanceFromDBSnapshotRequestData
     */
    public function getRestoreFromSnapshotRequest($dbInstanceIdentifier)
    {
        $this->throwExceptionIfNotInitialized();
        return new RestoreDBInstanceFromDBSnapshotRequestData($dbInstanceIdentifier, $this->dBSnapshotIdentifier);
    }

    /**
     * RestoreDBInstanceFromDBSnapshot action
     *
     * Creates a new DB Instance from a DB snapshot.The target database is created from the source database
     * restore point with the same configuration as the original source database, except that the new RDS
     * instance is created with the default security group.
     *
     * @param   RestoreDBInstanceFromDBSnapshotRequestData|string $request The request object or DB Instance Identifier
     * @return  DBInstanceData Returns DBInstanceData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function restoreFromSnapshot($request)
    {
        $this->throwExceptionIfNotInitialized();
        if (!is_object($request)) {
            $request = $this->getRestoreFromSnapshotRequest($request);
        }
        if (empty($request) || !($request instanceof RestoreDBInstanceFromDBSnapshotRequestData)) {
            throw new \BadFunctionCallException('Invalid request argument for restoreFromSnapshot() method');
        }
        return $this->getRds()->dbInstance->restoreFromSnapshot($request);
    }
}