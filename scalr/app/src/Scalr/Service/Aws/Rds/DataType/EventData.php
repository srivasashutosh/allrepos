<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * EventData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.03.2013
 */
class EventData extends AbstractRdsDataType
{

    const SOURCE_TYPE_DB_INSTANCE = 'db-instance';
    const SOURCE_TYPE_DB_PARAMETER_GROUP = 'db-parameter-group';
    const SOURCE_TYPE_DB_SECURITY_GROUP = 'db-security-group';
    const SOURCE_TYPE_DB_SNAPSHOT = 'db-snapshot';

    /**
     * Specifies the date and time of the event.
     *
     * @var DateTime
     */
    public $date;

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
    public $message;

    /**
     * Provides the identifier for the source of the event
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