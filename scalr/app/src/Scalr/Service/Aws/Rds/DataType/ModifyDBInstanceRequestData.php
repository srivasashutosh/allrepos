<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * ModifyDBInstanceRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    20.03.2013
 *
 * @property \Scalr\Service\Aws\DataType\ListDataType $dBSecurityGroups
 *           A list of DB Security Groups to associate with this DB Instance.
 *           Default: The default DB Security Group for the database engine
 *
 * @property \Scalr\Service\Aws\DataType\ListDataType $vpcSecurityGroupIds
 *           A list of EC2 VPC Security Groups to associate with this DB Instance.
 */
class ModifyDBInstanceRequestData extends AbstractRdsDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('dBSecurityGroups', 'vpcSecurityGroupIds');

    /**
     * Specifies the allocated storage size specified in gigabytes.
     *
     * Mysql: Constraints: Must be an integer from 5 to 1024.
     *
     * Oracle: Constraints: Must be an integer from 10 to 1024.
     *
     * SQL Server: Constraints: Must be an integer from 200 to 1024
     * (Standard Edition and Enterprise Edition) or from 30 to 1024
     * (Express Edition and Web Edition)
     *
     * @var int
     */
    public $allocatedStorage;

    /**
     * Indicates that major version upgrades are allowed.
     *
     * Changing this parameter does not result in an
     * outage and the change is asynchronously applied as soon as possible.
     *
     * Constraints: This parameter must be set to true when specifying a value for the EngineVersion
     * parameter that is a different major version than the DB Instance's current version.
     *
     * @var bool
     */
    public $allowMajorVersionUpgrade;

    /**
     * Specifies whether or not the modifications in this request
     * and any pending modifications are asynchronously applied as soon as possible,
     * regardless of the PreferredMaintenanceWindow setting for the DB Instance.
     *
     * If this parameter is passed as false, changes to the DB Instance are applied on the next call to
     * RebootDBInstance , the next maintenance reboot, or the next failure reboot, whichever occursfirst.
     * See each parameter to determine when a change is applied.
     *
     * Default: false
     *
     * @var bool
     */
    public $applyImmediately;

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
     * The number of days to retain automated backups.
     * Setting this parameter to a positive number enables backups.
     * Setting this parameter to 0 disables automated backups.
     * Changing this parameter can result in an outage if you change from 0 to a non-zero value or from a
     * non-zero value to 0. These changes are applied during the next maintenance window unless the
     * ApplyImmediately parameter is set to true for this request.
     * If you change the parameter from one non-zero value to another non-zero value,
     * the change is asynchronously applied as soon as possible.
     *
     * Constraints:
     * Must be a value from 0 to 8
     * Cannot be set to 0 if the DB Instance is a master instance with read replicas
     * or if the DB Instance is a read replica
     *
     * @var int
     */
    public $backupRetentionPeriod;

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
     * The name of the DB Parameter Group to apply to this DB Instance.
     * Changing this parameter does not result in an outage and the change
     * is applied during the next maintenance window unless the
     * ApplyImmediately parameter is set to true for this request.
     *
     * Constraints: The DB Parameter Group must be in the same
     * DB Parameter Group family as this DB Instance.
     *
     * @var string
     */
    public $dBParameterGroupName;

    /**
     * The version number of the database engine to upgrade to.
     *
     * Changing this parameter results in an outage and the change is applied during
     * the next maintenance window unless the ApplyImmediately parameter is set to true for this request.
     * For major version upgrades, if a nondefault DB Parameter Group is currently in use, a new DB
     * Parameter Group in the DB Parameter Group Family for the new engine version must be specified.
     * The new DB Parameter Group can be the default for that DB Parameter Group Family.
     *
     * @var string
     */
    public $engineVersion;

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
     * The new password for the DB Instance master user.
     * Can be any printable ASCII character except "/", "\", or "@".
     * Changing this parameter does not result in an outage and the change is asynchronously applied as
     * soon as possible. Between the time of the request and the completion of the request, the
     * MasterUserPassword element exists in the PendingModifiedValues element of the operation
     * response
     *
     * MySQL Constraints: Must contain from 8 to 41 alphanumeric characters.
     * Oracle Constraints: Must contain from 8 to 30 alphanumeric characters.
     * SQL Server Constraints: Must contain from 8 to 128 alphanumeric characters.
     *
     * @var string
     */
    public $masterUserPassword;

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
     * The new DB Instance identifier for the DB Instance when renaming a DB Instance.
     * This value is stored as a lowercase string.
     *
     * @var string
     */
    public $newDBInstanceIdentifier;

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
     * The daily time range during which automated backups
     * are created if automated backups are enabled,
     * using the BackupRetentionPeriod parameter.
     *
     * Default: A 30-minute window selected at random from an 8-hour block of time per region.
     * The following list shows the time blocks for each region from
     * which the default backup windows are assigned.
     *
     * US-East (Northern Virginia) Region: 03:00-11:00 UTC
     * US-West (Northern California) Region: 06:00-14:00 UTC
     * EU (Ireland) Region: 22:00-06:00 UTC
     * Asia Pacific (Singapore) Region: 14:00-22:00 UTC
     * Asia Pacific (Tokyo) Region: 17:00-03:00 UTC
     *
     * Constraints: Must be in the format hh24:mi-hh24:mi.
     * Times should be Universal Time Coordinated (UTC).
     * Must not conflict with the preferred maintenance window.
     * Must be at least 30 minutes.
     *
     * @var string
     */
    public $preferredBackupWindow;

    /**
     * The weekly time range (in UTC) during which system maintenance can occur.
     *
     * Format: ddd:hh24:mi-ddd:hh24:mi
     * Default: A 30-minute window selected at random from
     * an 8-hour block of time per region, occurring
     * on a random day of the week.
     * The following list shows the time blocks for each region from which
     * the default maintenance windows are assigned.
     *
     * US-East (Northern Virginia) Region: 03:00-11:00 UTC
     * US-West (Northern California) Region: 06:00-14:00 UTC
     * EU (Ireland) Region: 22:00-06:00 UTC
     * Asia Pacific (Singapore) Region: 14:00-22:00 UTC
     * Asia Pacific (Tokyo) Region: 17:00-03:00 UTC
     *
     * Valid Days: Mon, Tue, Wed, Thu, Fri, Sat, Sun
     * Constraints: Minimum 30-minute window.
     *
     * @var string
     */
    public $preferredMaintenanceWindow;

    /**
     * Constructor
     *
     * @param   string     $dBInstanceIdentifier A user-supplied database identifier
     */
    public function __construct($dBInstanceIdentifier)
    {
        parent::__construct();
        $this->dBInstanceIdentifier = (string) $dBInstanceIdentifier;
    }

    /**
     * Sets DBSecurityGroups list
     *
     * @param   ListDataType|array|string $dBSecurityGroups
     *          A list of DB Security Groups to associate with this DB Instance.
     *          Default: The default DB Security Group for the database engine
     * @return  CreateDBInstanceRequestData
     */
    public function setDBSecurityGroups($dBSecurityGroups = null)
    {
        if ($dBSecurityGroups !== null && !($dBSecurityGroups instanceof ListDataType)) {
            $dBSecurityGroups = new ListDataType($dBSecurityGroups);
        }
        return $this->__call(__FUNCTION__, array($dBSecurityGroups));
    }

    /**
     * Sets VpcSecurityGroupIds list
     *
     * @param   ListDataType|array|string $vpcSecurityGroupIds
     *          A list of EC2 VPC Security Groups to associate with this DB Instance.
     * @return  CreateDBInstanceRequestData
     */
    public function setVpcSecurityGroupIds($vpcSecurityGroupIds = null)
    {
        if ($vpcSecurityGroupIds !== null && !($vpcSecurityGroupIds instanceof ListDataType)) {
            $vpcSecurityGroupIds = new ListDataType($vpcSecurityGroupIds);
        }
        return $this->__call(__FUNCTION__, array($vpcSecurityGroupIds));
    }
}