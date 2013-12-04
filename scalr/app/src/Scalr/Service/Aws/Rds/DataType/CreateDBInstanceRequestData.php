<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * CreateDBInstanceRequestData
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
class CreateDBInstanceRequestData extends AbstractRdsDataType
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
     * Indicates that minor engine upgrades will be applied automatically
     * to the DB Instance during the maintenance window
     *
     * Default: true
     *
     * @var bool
     */
    public $autoMinorVersionUpgrade;

    /**
     * Specifies the name of the Availability Zone the DB Instance is located in.
     *
     * Constraint: The AvailabilityZone parameter cannot be specified if the MultiAZ parameter is set to
     * true. The specified Availability Zone must be in the same region as the current endpoint
     *
     * @var string
     */
    public $availabilityZone;

    /**
     * Specifies the number of days for which automatic DB Snapshots are retained
     *
     * Constraints:
     * Must be a value from 0 to 8
     * Cannot be set to 0 if the DB Instance is a master instance with read replicas
     * Default: 1
     *
     * @var int
     */
    public $backupRetentionPeriod;

    /**
     * For supported engines, indicates that the DB Instance
     * should be associated with the specified CharacterSet.
     *
     * @var string
     */
    public $characterSetName;

    /**
     * Contains the name of the compute and memory capacity class of the DB Instance
     *
     * Valid Values: db.t1.micro | db.m1.small | db.m1.medium | db.m1.large |
     * db.m1.xlarge | db.m2.xlarge |db.m2.2xlarge | db.m2.4xlarge
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
     * The meaning of this parameter differs according to the database engine you use.
     *
     * MySQL
     * The name of the database to create when the DB Instance is created. If this parameter is not specified,
     * no database is created in the DB Instance.
     * Constraints:
     *     Must contain 1 to 64 alphanumeric characters
     *     Cannot be a word reserved by the specified database engine
     *
     * Oracle
     * The Oracle System ID (SID) of the created DB Instance.
     * Default: ORCL
     * Constraints:
     *     Cannot be longer than 8 characters
     *
     * SQL Server
     * Not applicable. Must be null.
     *
     * @var string
     */
    public $dBName;

    /**
     * The name of the DB Parameter Group to associate with this DB instance. If this argument is omitted,
     * the default DBParameterGroup for the specified engine will be used.
     *
     * Constraints:
     * Must be 1 to 255 alphanumeric characters
     * First character must be a letter
     * Cannot end with a hyphen or contain two consecutive hyphens
     *
     * @var string
     */
    public $dBParameterGroupName;

    /**
     * A DB Subnet Group to associate with this DB Instance.
     * If there is no DB Subnet Group, then it is a non-VPC DB instance.
     *
     * @var string
     */
    public $dBSubnetGroupName;

    /**
     * Provides the name of the database engine to be used for this DB Instance.
     *
     * Valid Values: MySQL | oracle-se1 | oracle-se | oracle-ee | sqlserver-ee |
     * sqlserver-se | sqlserver-ex | sqlserver-web
     *
     * @var string
     */
    public $engine;

    /**
     * Indicates the database engine version.
     *
     * MySQL Example: 5.1.42
     * Oracle Example: 11.2.0.2.v2
     * SQL Server Example: 10.50.2789.0.v1
     *
     * @var string
     */
    public $engineVersion;

    /**
     * Specifies the Provisioned IOPS (I/O operations per second) value
     *
     * Constraints: Must be an integer greater than 1000.
     *
     * @var int
     */
    public $iops;

    /**
     * License model information for this DB Instance
     *
     * Valid values: license-included | bring-your-own-license | general-public-license
     *
     * @var string
     */
    public $licenseModel;

    /**
     * The password for the master database user. Can be any printable ASCII character except "/", "\", or "@".
     *
     * MySQL Constraints: Must contain from 8 to 41 alphanumeric characters.
     * Oracle Constraints: Must contain from 8 to 30 alphanumeric characters.
     * SQL Server Constraints: Must contain from 8 to 128 alphanumeric characters.
     *
     * @var string
     */
    public $masterUserPassword;

    /**
     * Contains the master username for the DB Instance
     *
     * MySQL
     * Constraints:
     * Must be 1 to 16 alphanumeric characters.
     * First character must be a letter.
     * Cannot be a reserved word for the chosen database engine.
     *
     * Oracle
     * Constraints:
     * Must be 1 to 30 alphanumeric characters.
     * First character must be a letter.
     * Cannot be a reserved word for the chosen database engine.
     *
     * SQL Server
     * Constraints:
     * Must be 1 to 128 alphanumeric characters.
     * First character must be a letter.
     * Cannot be a reserved word for the chosen database engine.
     *
     * @var string
     */
    public $masterUsername;

    /**
     * Specifies if the DB Instance is a Multi-AZ deployment.
     * You cannot set the AvailabilityZone parameter
     * if the MultiAZ parameter is set to true.
     *
     * @var bool
     */
    public $multiAZ;

    /**
     * Indicates that the DB Instance should be associated with the specified option group.
     *
     * @var string
     */
    public $optionGroupName;

    /**
     * The port number on which the database accepts connections.
     *
     * MySQL Default: 3306 Valid Values: 1150-65535
     * Oracle Default: 1521 Valid Values: 1150-65535
     * SQL Server Default: 1433 Valid Values: 1150-65535 except for 1434 and 3389.
     *
     * @var int
     */
    public $port;

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
     * publiclyAccessible
     *
     * @var bool
     */
    public $publiclyAccessible;

    /**
     * A list of EC2 VPC Security Groups to associate with this DB Instance.
     *
     * Default: The default EC2 VPC Security Group for the DB Subnet group's VPC.
     *
     * @var array
     */
    public $vpcSecurityGroupIds;

    /**
     * Constructor
     *
     * @param   string     $dBInstanceIdentifier A user-supplied database identifier
     * @param   int        $allocatedStorage     The allocated storage size specified in gigabytes
     * @param   string     $dBInstanceClass      The name of the compute and memory capacity class of the DB Instance
     * @param   string     $engine               The name of the database engine to be used for this DB Instance
     * @param   string     $masterUsername       The master username
     * @param   string     $masterUserPassword   The password
     */
    public function __construct($dBInstanceIdentifier, $allocatedStorage, $dBInstanceClass,
                                $engine, $masterUsername, $masterUserPassword)
    {
        parent::__construct();
        $this->dBInstanceIdentifier = (string) $dBInstanceIdentifier;
        $this->allocatedStorage = (int) $allocatedStorage;
        $this->dBInstanceClass = (string) $dBInstanceClass;
        $this->engine = (string) $engine;
        $this->masterUsername = (string) $masterUsername;
        $this->masterUserPassword = (string) $masterUserPassword;
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