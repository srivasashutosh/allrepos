<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DescribeEventRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.03.2013
 */
class DescribeEventRequestData extends AbstractRdsDataType
{

    /**
     * The number of minutes to retrieve events for
     * Default: 60
     *
     * @var int
     */
    public $duration;

    /**
     * The beginning of the time interval to retrieve events for, specified in ISO 8601 format.
     *
     * @var DateTime
     */
    public $startTime;

    /**
     * The end of the time interval for which to retrieve events, specified in ISO 8601 format.
     *
     * @var DateTime
     */
    public $endTime;

    /**
     * Specifies the category for the event.
     *
     * @var array
     */
    public $eventCategories;

    /**
     * Provides the text of this event
     *
     * @var string
     */
    public $marker;

    /**
     * The maximum number of records to include in the response. If more records exist than the specified
     * MaxRecords value, a pagination token called a marker is included in the response so that the
     * remaining results may be retrieved.
     * Default: 100
     * Constraints: minimum 20, maximum 100
     *
     * @var int
     */
    public $maxRecords;

    /**
     * The identifier of the event source for which events will be returned.
     * If not specified, then all sources are included in the response.
     * Constraints:
     * If SourceIdentifier is supplied, SourceType must also be provided.
     * If the source type is DBInstance, then a DBInstanceIdentifier must be supplied.
     * If the source type is DBSecurityGroup, a DBSecurityGroupName must be supplied.
     * If the source type is DBParameterGroup, a DBParameterGroupName must be supplied.
     * If the source type is DBSnapshot, a DBSnapshotIdentifier must be supplied.
     * Cannot end with a hyphen or contain two consecutive hyphens.
     *
     * @var string
     */
    public $sourceIdentifier;

    /**
     * Specifies the source type for this event
     *
     * @var string
     */
    public $sourceType;

}