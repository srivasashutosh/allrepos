<?php
namespace Scalr\Service\Aws\Rds\Handler;

use Scalr\Service\Aws\Rds\DataType\RestoreDBInstanceFromDBSnapshotRequestData;
use Scalr\Service\Aws\Rds\DataType\DBSnapshotData;
use Scalr\Service\Aws\Rds\DataType\ModifyDBInstanceRequestData;
use Scalr\Service\Aws\Rds\DataType\CreateDBInstanceRequestData;
use Scalr\Service\Aws\Rds\DataType\DBInstanceList;
use Scalr\Service\Aws\Rds\DataType\DBInstanceData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsHandler;

/**
 * Amazon RDS DbInstanceHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     07.03.2013
 */
class DbInstanceHandler extends AbstractRdsHandler
{

    /**
     * Gets InstanceData object from the EntityManager.
     *
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string              $dBInstanceIdentifier.
     * @return  DBInstanceData|null Returns DBInstanceData if it does exist in the cache or NULL otherwise.
     */
    public function get($dBInstanceIdentifier)
    {
        return $this->getRds()->getEntityManager()->getRepository('Rds:DBInstance')->find($dBInstanceIdentifier);
    }

    /**
     * DescribeDBInstances action
     *
     * Returns information about provisioned RDS instances. This API supports pagination
     *
     * @param   string          $dbInstanceIdentifier optional The user-specified instance identifier.
     * @param   string          $marker               optional The response includes only records beyond the marker.
     * @param   int             $maxRecords           optional The maximum number of records to include in the response.
     * @return  DBInstanceList  Returns the list of DB Instances
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describe($dbInstanceIdentifier = null, $marker = null, $maxRecords = null)
    {
        return $this->getRds()->getApiHandler()->describeDBInstances($dbInstanceIdentifier, $marker, $maxRecords);
    }

    /**
     * CreateDBInstance action
     *
     * Creates a new DB instance.
     *
     * @param   CreateDBInstanceRequestData $request
     * @return  DBInstanceData  Returns created DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function create(CreateDBInstanceRequestData $request)
    {
        return $this->getRds()->getApiHandler()->createDBInstance($request);
    }

    /**
     * CreateDBSnapshot
     *
     * Creates a DBSnapshot. The source DBInstance must be in "available" state.
     *
     * @param   string     $dBInstanceIdentifier The DB Instance Identifier
     * @param   string     $dBSnapshotIdentifier The identifier for the DB Snapshot.
     * @return  DBSnapshotData Returns DBSnapshotData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function createSnapshot($dBInstanceIdentifier, $dBSnapshotIdentifier)
    {
        return $this->getRds()->dbSnapshot->create($dBInstanceIdentifier, $dBSnapshotIdentifier);
    }

    /**
     * DeleteDBInstance action
     *
     * The DeleteDBInstance API deletes a previously provisioned RDS instance.
     * A successful response from the web service indicates the request
     * was received correctly. If a final DBSnapshot is requested the status
     * of the RDS instance will be "deleting" until the DBSnapshot is created.
     * DescribeDBInstance is used to monitor the status of this operation.
     * This cannot be canceled or reverted once submitted
     *
     * @param   string       $dBInstanceIdentifier      The DB Instance identifier for the DB Instance to be deleted.
     * @param   bool         $skipFinalSnapshot         optional Determines whether a final DB Snapshot is created
     *                                                  before the DB Instance is deleted
     * @param   string       $finalDBSnapshotIdentifier optional The DBSnapshotIdentifier of the new DBSnapshot
     *                                                  created when SkipFinalSnapshot is set to false
     * @return  DBInstanceData  Returns created DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function delete($dBInstanceIdentifier, $skipFinalSnapshot = null, $finalDBSnapshotIdentifier = null)
    {
        return $this->getRds()->getApiHandler()->deleteDBInstance(
            $dBInstanceIdentifier, $skipFinalSnapshot, $finalDBSnapshotIdentifier
        );
    }

    /**
     * ModifyDBInstance action
     *
     * Modify settings for a DB Instance.
     * You can change one or more database configuration parameters by
     * specifying these parameters and the new values in the request.
     *
     * @param   ModifyDBInstanceRequestData $request Modify DB Instance request object
     * @return  DBInstanceData  Returns modified DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function modify(ModifyDBInstanceRequestData $request)
    {
        return $this->getRds()->getApiHandler()->modifyDBInstance($request);
    }

    /**
     * RebootDBInstance action
     *
     * Reboots a previously provisioned RDS instance. This API results in the application of modified
     * DBParameterGroup parameters with ApplyStatus of pending-reboot to the RDS instance. This action is
     * taken as soon as possible, and results in a momentary outage to the RDS instance during which the RDS
     * instance status is set to rebooting. If the RDS instance is configured for MultiAZ, it is possible that the
     * reboot will be conducted through a failover. A DBInstance event is created when the reboot is completed.
     *
     * @param   string     $dBInstanceIdentifier The DB Instance identifier.
     *                                           This parameter is stored as a lowercase string
     * @param   bool       $forceFailover        optional When true, the reboot will be conducted through
     *                                           a MultiAZ failover. You cannot specify true if the instance
     *                                           is not configured for MultiAZ.
     * @return  DBInstanceData  Returns DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function reboot($dBInstanceIdentifier, $forceFailover = null)
    {
        return $this->getRds()->getApiHandler()->rebootDBInstance($dBInstanceIdentifier, $forceFailover);
    }

    /**
     * RestoreDBInstanceFromDBSnapshot action
     *
     * Creates a new DB Instance from a DB snapshot.The target database is created from the source database
     * restore point with the same configuration as the original source database, except that the new RDS
     * instance is created with the default security group.
     *
     * @param   RestoreDBInstanceFromDBSnapshotRequestData $request The request object.
     * @return  DBInstanceData Returns DBInstanceData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function restoreFromSnapshot(RestoreDBInstanceFromDBSnapshotRequestData $request)
    {
        return $this->getRds()->getApiHandler()->restoreDBInstanceFromDBSnapshot($request);
    }
}