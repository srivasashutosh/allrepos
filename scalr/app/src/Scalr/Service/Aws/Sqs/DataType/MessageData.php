<?php
namespace Scalr\Service\Aws\Sqs\DataType;

use Scalr\Service\Aws\SqsException;
use Scalr\Service\Aws\Sqs\AbstractSqsDataType;
use Scalr\Service\Aws\Sqs\DataType\QueueAttributeList;

/**
 * MessageData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     08.11.2012
 */
class MessageData extends AbstractSqsDataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('queueName', 'queueUrl');

    /**
     * An element containing the message ID of the message sent to the queue.
     *
     * @var string
     */
    public $messageId;

    /**
     * An MD5 digest of the non-URL-encoded message body string.
     * You can use this to verify that SQS received the message correctly. SQS first URL decodes
     * the message before creating the MD5 digest.
     *
     * @var string
     */
    public $md5OfMessageBody;

}