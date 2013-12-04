<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DBInstanceData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    11.03.2013
 *
 * @property \Scalr\Service\Aws\Rds\DataType\DBParameterGroupStatusList $dBParameterGroups
 *           Provides the list of DB Parameter Groups applied to this DB Instance
 *
 * @property \Scalr\Service\Aws\Rds\DataType\DBSecurityGroupMembershipList $dBSecurityGroups
 *           Provides List of DB Security Group elements
 *
 * @property \Scalr\Service\Aws\Rds\DataType\EndpointData $endpoint
 *           Specifies the connection endpoint
 *
 * @property \Scalr\Service\Aws\Rds\DataType\OptionGroupMembershipData $optionGroupMembership
 *           Specifies the name and status of the option group that this instance belongs to.
 *
 * @property \Scalr\Service\Aws\Rds\DataType\PendingModifiedValuesData $pendingModifiedValues
 *           Specifies that changes to the DB Instance are pending.
 *           This element is only included when changes are pending.
 *
 * @property \Scalr\Service\Aws\Rds\DataType\VpcSecurityGroupMembershipList $vpcSecurityGroups
 *           Provides List of VPC security group elements that the DB Instance belongs to.
 */
class DBInstanceData extends AbstractRdsDataType
{

    const STATUS_AVAILABLE = 'available';
    const STATUS_CREATING = 'creating';
    const STATUS_DELETING = 'deleting';
    const STATUS_REBOOTING = 'rebooting';
    const STATUS_BACKING_UP = 'backing-up';
    const STATUS_FAILED = 'failed';
    const STATUS_STORAGE_FULL = 'storage-full';
    const STATUS_INCOMPATIBLE_OPTION_GROUP = 'incompatible-option-group';
    const STATUS_INCOMPATIBLE_PARAMETERS = 'incompatible-parameters';
    const STATUS_INCOMPATIBLE_RESTORE = 'incompatible-restore';
    const STATUS_INCOMPATIBLE_NETWORK = 'incompatible-network';

    const LICENSE_LICENSE_INCLUDED = 'license-included';
    const LICENSE_BRING_YOUR_OWN_LICENSE = 'bring-your-own-license';
    const LICENSE_GENERAL_PUBLIC_LICENSE = 'general-public-license';

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'dBParameterGroups', 'dBSecurityGroups', 'dBSubnetGroup', 'endpoint',
        'optionGroupMembership', 'pendingModifiedValues', 'vpcSecurityGroups'
    );

    /**
     * Specifies the allocated storage size specified in gigabytes.
     *
     * @var int
     */
    public $allocatedStorage;

    /**
     * Indicates that minor version patches are applied automatically
     *
     * @var bool
     */
    public $autoMinorVersionUpgrade;

    /**
     * Specifies the name of the Availability Zone the DB Instance is located in.
     *
     * @var string
     */
    public $availabilityZone;

    /**
     * Specifies the number of days for which automatic DB Snapshots are retained
     *
     * @var int
     */
    public $backupRetentionPeriod;

    /**
     * If present, specifies the name of the character set
     * that this instance is associated with
     *
     * @var string
     */
    public $characterSetName;

    /**
     * Contains the name of the compute and memory capacity class of the DB Instance
     *
     * @var string
     */
    public $dBInstanceClass;

    /**
     * Contains a user-supplied database identifier.
     * This is the unique key that identifies a DB Instance
     *
     * @var string
     */
    public $dBInstanceIdentifier;

    /**
     * Specifies the current state of this database
     *
     * @var string
     */
    public $dBInstanceStatus;

    /**
     * The meaning of this parameter differs according to the database engine you use.
     *
     * MySQL
     * Contains the name of the initial database of this instance
     * that was provided at create time, if one was
     * specified when the DB Instance was created. This same name
     * is returned for the life of the DB Instance.
     *
     * Oracle
     * Contains the Oracle System ID (SID) of the created DB Instance
     *
     * @var string
     */
    public $dBName;

    /**
     * Provides the name of the database engine to be used for this DB Instance.
     *
     * @var string
     */
    public $engine;

    /**
     * Indicates the database engine version.
     *
     * @var string
     */
    public $engineVersion;

    /**
     * Provides the date and time the DB Instance was created.
     *
     * @var DateTime
     */
    public $instanceCreateTime;

    /**
     * Specifies the Provisioned IOPS (I/O operations per second) value
     *
     * @var int
     */
    public $iops;

    /**
     * Specifies the latest time to which a database
     * can be restored with point-in-time restore
     *
     * @var DateTime
     */
    public $latestRestorableTime;

    /**
     * License model information for this DB Instance
     *
     * @var string
     */
    public $licenseModel;

    /**
     * Contains the master username for the DB Instance
     *
     * @var string
     */
    public $masterUsername;

    /**
     * Specifies if the DB Instance is a Multi-AZ deployment.
     *
     * @var bool
     */
    public $multiAZ;

    /**
     * Specifies the daily time range during which automated
     * backups are created if automated backups are enabled,
     * as determined by the BackupRetentionPeriod
     *
     * @var string
     */
    public $preferredBackupWindow;

    /**
     * Specifies the weekly time range (in UTC)
     * during which system maintenance can occur
     *
     * @var string
     */
    public $preferredMaintenanceWindow;

    /**
     * publiclyAccessible
     * @var bool
     */
    public $publiclyAccessible;

    /**
     * Contains one or more identifiers of the
     * Read Replicas associated with this DB Instance
     *
     * @var array
     */
    public $readReplicaDBInstanceIdentifiers;

    /**
     * Contains the identifier of the source DB Instance
     * if this DB Instance is a Read Replica
     *
     * @var string
     */
    public $readReplicaSourceDBInstanceIdentifier;

    /**
     * If present, specifies the name of the secondary
     * Availability Zone for a DB instance with multi-AZ
     * support.
     *
     * @var string
     */
    public $secondaryAvailabilityZone;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Rds.AbstractRdsDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->dBInstanceIdentifier === null) {
            throw new RdsException(sprintf(
                'DBInstanceIdentifier has not been initialized for "%s" yet', get_class($this)
            ));
        }
    }

    /**
     * DescribeDBInstances action
     *
     * Refreshes description of the object using request to Amazon.
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * If not, solution is to use $object = object->refresh() instead.
     *
     * @return  DBInstanceData  Return refreshed object
     * @throws  ClientException
     * @throws  RdsException
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbInstance->describe($this->dBInstanceIdentifier)->get(0);
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
     * @param   bool         $skipFinalSnapshot         optional Determines whether a final DB Snapshot is created
     *                                                  before the DB Instance is deleted
     * @param   string       $finalDBSnapshotIdentifier optional The DBSnapshotIdentifier of the new DBSnapshot
     *                                                  created when SkipFinalSnapshot is set to false
     * @return  DBInstanceData  Returns created DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function delete($skipFinalSnapshot = null, $finalDBSnapshotIdentifier = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbInstance->delete($this->dBInstanceIdentifier, $skipFinalSnapshot, $finalDBSnapshotIdentifier);
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
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbInstance->modify($request);
    }

    /**
     * Creates and returns new ModifyDbInstanceRequestData object for this DBInstance
     *
     * @return ModifyDBInstanceRequestData Returns new ModifyDbInstanceRequestData object for this DBInstance
     */
    public function getModifyRequest()
    {
        $this->throwExceptionIfNotInitialized();
        return new ModifyDBInstanceRequestData($this->dBInstanceIdentifier);
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
     * @param   bool       $forceFailover        optional When true, the reboot will be conducted through
     *                                           a MultiAZ failover. You cannot specify true if the instance
     *                                           is not configured for MultiAZ.
     * @return  DBInstanceData  Returns DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function reboot($forceFailover = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbInstance->reboot($this->dBInstanceIdentifier, $forceFailover);
    }

    /**
     * CreateDBSnapshot
     *
     * Creates a DBSnapshot. The source DBInstance must be in "available" state.
     *
     * @param   string     $dBSnapshotIdentifier The identifier for the DB Snapshot.
     * @return  DBSnapshotData Returns DBSnapshotData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function createSnapshot($dBSnapshotIdentifier)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbSnapshot->create($this->dBInstanceIdentifier, $dBSnapshotIdentifier);
    }

    /**
     * Gets a new RestoreDBInstanceFromDBSnapshotRequestData object for this DB Instance
     *
     * @param   string      $dbSnapshotIdentifier  The DB Snapshot Identifier.
     * @return  RestoreDBInstanceFromDBSnapshotRequestData
     */
    public function getRestoreFromSnapshotRequest($dbSnapshotIdentifier)
    {
        $this->throwExceptionIfNotInitialized();
        return new RestoreDBInstanceFromDBSnapshotRequestData($this->dBInstanceIdentifier, $dbSnapshotIdentifier);
    }

    /**
     * RestoreDBInstanceFromDBSnapshot action
     *
     * Creates a new DB Instance from a DB snapshot.The target database is created from the source database
     * restore point with the same configuration as the original source database, except that the new RDS
     * instance is created with the default security group.
     *
     * @param   RestoreDBInstanceFromDBSnapshotRequestData|string $request The request object or DB Snapshot Identifier
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