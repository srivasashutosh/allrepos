<?php
namespace Scalr\Service\Aws\Sqs\DataType;

use Scalr\Service\Aws\Sqs\AbstractSqsDataType;

/**
 * QueueAttributeData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 */
class QueueAttributeData extends AbstractSqsDataType
{
    /**
     * The length of time (in seconds) that a message received from a queue will be
     * invisible to other receiving components when they ask to receive messages.
     */
    const ATTR_VISIBILITY_TIMEOUT = 'VisibilityTimeout';

    /**
     * The formal description of the permissions for a resource.
     */
    const ATTR_POLICY = 'Policy';

    /**
     * The limit of how many bytes a message can contain before Amazon SQS rejects it.
     */
    const ATTR_MAXIMUM_MESSAGE_SIZE = 'MaximumMessageSize';

    /**
     * The number of seconds Amazon SQS retains a message.
     */
    const ATTR_MESSAGE_RETENTION_PERIOD = 'MessageRetentionPeriod';

    /**
     * The time in seconds that the delivery of all messages in the queue will be delayed.
     */
    const ATTR_DELAY_SECONDS = 'DelaySeconds';

    /**
     * Attribute name.
     *
     * The name of the attribute you want to set.
     * Allowed values:
     * VisibilityTimeout|Policy|MaximumMessageSize|MessageRetentionPeriod|DelaySeconds
     *
     * @var string
     */
    public $name;

    /**
     * Attribute value.
     *
     * The value of the attribute you want to set.
     *
     * Constraints: Constraints are specific for each value.
     * VisibilityTimeout—An integer from 0 to 43200 (12hours).
     * The default for this attribute is 30.
     *
     * Policy—A valid form-url-encoded policy.
     *
     * MaximumMessageSize—An integer from 1024 bytes (1 KiB) up to 65536 bytes (64 KiB).
     * The default for this attribute is 65536 (64 KiB).
     *
     * MessageRetentionPeriod—Integer representing seconds, from 60 (1 minute) to 1209600 (14 days).
     * The default for this attribute is 345600 (4 days).
     *
     * DelaySeconds—An integer from 0 to 900 (15minutes).
     * The default for this attribute is 0 (zero).
     *
     * @var string|numeric
     */
    public $value;

    /**
     * Convenient constructor
     *
     * @param   string     $name  optional An attribute name.
     * @param   mixed      $value optional An attribute value.
     */
    public function __construct($name = null, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets all available attributes
     *
     * @return array Returns the list of the all available attributes.
     */
    public static function getAvailableAttributes()
    {
        return array(
            'ApproximateNumberOfMessages',
            'ApproximateNumberOfMessagesNotVisible',
            'ApproximateNumberOfMessagesDelayed',
            'VisibilityTimeout',
            'CreatedTimestamp',
            'LastModifiedTimestamp',
            'Policy',
            'MaximumMessageSize',
            'MessageRetentionPeriod',
            'QueueArn',
            'DelaySeconds'
        );
    }
}