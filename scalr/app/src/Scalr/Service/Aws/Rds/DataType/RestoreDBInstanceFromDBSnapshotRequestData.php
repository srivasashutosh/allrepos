<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * RestoreDBInstanceFromDBSnapshotRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.03.2013
 */
class RestoreDBInstanceFromDBSnapshotRequestData extends AbstractRdsDataType
{

    /**
     * Indicates that minor version upgrades will be applied automatically to the DB Instance during the
     * maintenance window. Changing this parameter does not result in an outage except in the following
     * case and the change is asynchronously applied as soon as possible. An outage will result if this
     * parameter is set to true during the maintenance window, and a newer minor version is available,
     * and RDS has enabled auto patching for that engine version
     *
     * @var bool
     */
    public $autoMinorVersionUpgrade;

    /**
     * The EC2 Availability Zone that the database instance will be created in.
     *
     * Default: A random, system-chosen Availability Zone.
     * Constraint:You cannot specify the AvailabilityZone parameter if the MultiAZ parameter is set to true.
     *
     * @var string
     */
    public $availabilityZone;

    /**
     * The new compute and memory capacity of the DB Instance.
     * To determine the instance classes that are available for a particular DB engine,
     * use the DescribeOrderableDBInstanceOptions action.
     * Passing a value for this parameter causes an outage during the change and is applied during the
     * next maintenance window, unless the ApplyImmediately parameter is specified as true for this
     * request.
     *
     * Valid Values: db.t1.micro | db.m1.small | db.m1.medium | db.m1.large |
     * db.m1.xlarge | db.m2.xlarge |db.m2.2xlarge | db.m2.4xlarge
     *
     * @var string
     */
    public $dBInstanceClass;

    /**
     * The DB Instance identifier. This value is stored as a lowercase string.
     *
     * Constraints:
     * Must be the identifier for an existing DB Instance
     * Must contain from 1 to 63 alphanumeric characters or hyphens
     * First character must be a letter
     * Cannot end with a hyphen or contain two consecutive hyphens
     *
     * @var string
     */
    public $dBInstanceIdentifier;

    /**
     * The database name for the restored DB Instance.
     * NOTE! This parameter doesn't apply to the MySQL engine
     *
     * @var string
     */
    public $dbName;

    /**
     * Name of the DB Instance to create from the DB Snapshot.
     * This parameter isn't case sensitive
     *
     * @var string
     */
    public $dBSnapshotIdentifier;

    /**
     * The DB Subnet Group name to use for the new instance
     *
     * @var string
     */
    public $dBSubnetGroupName;

    /**
     * The database engine to use for the new instance
     *
     * Default: The same as source
     * Constraint: Must be compatible with the engine of the source
     *
     * @var string
     */
    public $engine;

    /**
     * The new Provisioned IOPS (I/O operations per second) value for the RDS instance.
     * Changing this parameter does not result in an outage and the change is applied
     * during the next maintenance window unless the ApplyImmediately parameter
     * is set to true for this request.
     *
     * Constraints: Value supplied must be at least 10% greater than the current value.
     * Values that are not at least 10% greater than the existing value are rounded up
     * so that they are 10% greater than the current value.
     *
     * @var int
     */
    public $iops;

    /**
     * License model information for the restored DB Instance
     * Valid values: license-included | bring-your-own-license | general-public-license
     *
     * @var string
     */
    public $licenseModel;

    /**
     * Specifies if the DB Instance is a Multi-AZ deployment.
     * Changing this parameter does not result in an outage
     * and the change is applied during the next maintenance window unless the
     * ApplyImmediately parameter is set to true for this request.
     *
     * Constraints: Cannot be specified if the DB Instance is a read replica
     *
     * @var bool
     */
    public $multiAZ;

    /**
     * Indicates that the DB Instance should be associated with the specified option group.
     * Changing this parameter does not result in an outage except in the following case
     * and the change is applied during the next maintenance window unless
     * the ApplyImmediately parameter is set to true for this request.
     * If the parameter change results in an option group that enables OEM, this change can cause
     * a brief (sub-second) period during which new connections are rejected but existing connections are
     * not interrupted.
     *
     * @var string
     */
    public $optionGroupName;

    /**
     * The port number on which the database accepts connections.
     * Default: The same port as the original DB Instance
     * Constraints: Value must be 1150-65535
     *
     * @var int
     */
    public $port;

    /**
     * @var bool
     */
    public $publiclyAccessible;

    /**
     * Constructor
     *
     * @param   string     $dBInstanceIdentifier A user-supplied database identifier
     * @param   string     $dBSnapshotIdentifier A snapshot Identifier
     */
    public function __construct($dBInstanceIdentifier, $dBSnapshotIdentifier)
    {
        parent::__construct();
        $this->dBInstanceIdentifier = (string) $dBInstanceIdentifier;
        $this->dBSnapshotIdentifier = (string) $dBSnapshotIdentifier;
    }
}