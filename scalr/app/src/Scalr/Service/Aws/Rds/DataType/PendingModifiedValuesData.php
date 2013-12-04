<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * PendingModifiedValuesData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 */
class PendingModifiedValuesData extends AbstractRdsDataType
{

    /**
     * Contains the new AllocatedStorage size
     * for the DB Instance that will be applied or is in progress
     *
     * @var int
     */
    public $allocatedStorage;

    /**
     * Specifies the pending number of days for
     * which automated backups are retained
     *
     * @var int
     */
    public $backupRetentionPeriod;

    /**
     * Contains the new DBInstanceClass for the
     * DB Instance that will be applied or is in progress
     *
     * @var string
     */
    public $dBInstanceClass;

    /**
     * Contains the new DBInstanceIdentifier for the
     * DB Instance that will be applied or is in progress
     *
     * @var string
     */
    public $dBInstanceIdentifier;

    /**
     * Indicates the database engine version
     *
     * @var string
     */
    public $engineVersion;

    /**
     * Specifies the new Provisioned IOPS value for the
     * DB Instance that will be applied or is being applied
     *
     * @var int
     */
    public $iops;

    /**
     * Contains the pending or in-progress change
     * of the master credentials for the DB Instance
     *
     * @var string
     */
    public $masterUserPassword;

    /**
     * Indicates that the Single-AZ DB Instance
     * is to change to a Multi-AZ deployment
     *
     * @var bool
     */
    public $multiAZ;

    /**
     * Specifies the pending port for the DB Instance
     *
     * @var int
     */
    public $port;
}