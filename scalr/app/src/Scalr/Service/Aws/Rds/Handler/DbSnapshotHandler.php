<?php
namespace Scalr\Service\Aws\Rds\Handler;

use Scalr\Service\Aws\Rds\DataType\DBSnapshotList;
use Scalr\Service\Aws\Rds\DataType\DBSnapshotData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsHandler;

/**
 * Amazon RDS DbSnapshotHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     27.03.2013
 */
class DbSnapshotHandler extends AbstractRdsHandler
{

    /**
     * Gets DBSnapshotData object from the EntityManager.
     *
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string              $dBInstanceIdentifier.
     * @return  DBSnapshotData|null Returns DBSnapshotData if it does exist in the cache or NULL otherwise.
     */
    public function get($dBSnapshotIdentifier)
    {
        return $this->getRds()->getEntityManager()->getRepository('Rds:DBSnapshot')->find($dBSnapshotIdentifier);
    }

    /**
     * DescribeDBSnapshots action
     *
     * Returns the detailed parameter list for a particular DBParameterGroup.
     *
     * @param   string     $dBParameterGroupName The name of the DB Parameter Group.
     * @param   string     $source               optional The parameter types to return.
     * @param   string     $marker               optional An optional pagination token provided by a previous
     *                                           DescribeDBParameterGroups request. If this parameter is specified, the response includes
     *                                           only records beyond the marker, up to the value specified by MaxRecords.
     * @param   int        $maxRecords           optional The maximum number of records to include in the response.
     *                                           If more records exist than the specified MaxRecords value,
     *                                           a pagination token called a marker is included in the response so that the
     *                                           remaining results may be retrieved.
     * @return  DBSnapshotList Returns DBSnapshotList on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describe($dBInstanceIdentifier = null, $dBSnapshotIdentifier = null, $snapshotType = null,
                             $marker = null, $maxRecords = null)
    {
        return $this->getRds()->getApiHandler()->describeDBSnapshots(
            $dBInstanceIdentifier, $dBSnapshotIdentifier, $snapshotType, $marker, $maxRecords
        );
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
    public function create($dBInstanceIdentifier, $dBSnapshotIdentifier)
    {
        return $this->getRds()->getApiHandler()->createDBSnapshot($dBInstanceIdentifier, $dBSnapshotIdentifier);
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
    public function delete($dBSnapshotIdentifier)
    {
        return $this->getRds()->getApiHandler()->deleteDBSnapshot($dBSnapshotIdentifier);
    }
}