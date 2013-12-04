<?php
namespace Scalr\Service\Aws\Sqs\DataType;

use Scalr\Service\Aws\SqsException;
use Scalr\Service\Aws\Sqs\AbstractSqsDataType;
use Scalr\Service\Aws\Sqs\DataType\QueueAttributeList;

/**
 * QueueData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 */
class QueueData extends AbstractSqsDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array();

    /**
     * The queue name
     *
     * @var string
     */
    public $queueName;

    /**
     * The queue URL for a queue
     *
     * @var string
     */
    public $queueUrl;

    /**
     * the approximate number of visible messages in a queue
     *
     * @var int
     */
    public $approximateNumberOfMessages;

    /**
     * the approximate number of messages that are not timed-out and not deleted
     *
     * @var int
     */
    public $approximateNumberOfMessagesNotVisible;

    /**
     * the approximate number of messages that are not visible because
     * you have set a positive delay value on the queue.
     *
     * @var int
     */
    public $approximateNumberOfMessagesDelayed;

    /**
     * The visibility timeout for the queue
     *
     * @var int
     */
    public $visibilityTimeout;

    /**
     * The time when the queue was created
     *
     * @var \DateTime
     */
    public $createdTimestamp;

    /**
     * The time when the queue was last changed
     *
     * @var \DateTime
     */
    public $lastModifiedTimestamp;

    /**
     * the queue's policy
     */
    public $policy;

    /**
     * returns the limit of how many bytes a message can contain before Amazon SQS rejects it
     *
     * @var int
     */
    public $maximumMessageSize;

    /**
     * returns the number of seconds Amazon SQS retains a message.
     *
     * @var int
     */
    public $messageRetentionPeriod;

    /**
     * eturns the queue's Amazon resource name (ARN)
     *
     * @var string
     */
    public $queueArn;

    /**
     * The time in seconds that the delivery of all messages in the queue will be delayed
     *
     * @var int
     */
    public $delaySeconds;

    /**
     * @var string
     */
    private $uriPath;


    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Sqs.AbstractSqsDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->queueName === null) {
            throw new SqsException(sprintf('queueName property has not been provided for the %s yet.', get_class($this)));
        }
    }

    /**
     * Gets queue url.
     *
     * This url is used for the api request.
     * If url has already been retrieved from AWS it will just return it saved value.
     *
     * @param   string     $queueOwnerAwsAccountId optional The AWS account ID of the account that created the queue.
     * @return  string     Returns QueueUrl
     * @throws  ClientException
     * @throws  SqsException
     */
    public function getUrl($queueOwnerAwsAccountId = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($this->queueUrl == null) {
            $queue = $this->getSqs()->queue->getUrl($this->queueName, $queueOwnerAwsAccountId);
            $this->queueUrl = $queue->queueUrl;
            unset($queue);
        }
        return $this->queueUrl;
    }

    /**
     * Gets uri path which is used for API calls.
     *
     * @return  string  Returns Uri Path which is used for making API calls.
     * @throws  ClientException
     * @throws  SqsException
     */
    public function getUriPath()
    {
        if (!isset($this->uriPath)) {
            $url = $this->getUrl();
            $this->uriPath = preg_replace('#^.+(/[^/]+)(/[^/]+)/?$#', '\\1\\2/', $url);
        }
        return $this->uriPath;
    }

    /**
     * The DeleteQueue action deletes the queue specified by the queue URL, regardless of whether the
     * queue is empty. If the specified queue does not exist, SQS returns a successful response.

     * @return  bool         Returns TRUE if queue does successfully remove.
     * @throws  ClientException
     * @throws  SqsException
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        $queueUrl = $this->getUrl();
        return $this->getSqs()->queue->delete($queueUrl);
    }

    /**
     * The GetQueueAttributes action fetches all attributes of a queue.
     *
     * @param   string             $queueName          The queue name.
     * @param   array|ListDataType $queueAttributeList optional The attribute list you want to get.
     * @return  QueueAttributeList  Returns QueueAttributeList
     * @throws  ClientException
     * @throws  SqsException
     */
    public function fetchAttributes()
    {
        $attributes = new QueueAttributeList();
        //Fetches attributes and stores its values directly to this object.
        $this->getSqs()->queue->getAttributes($this->queueName, null);
        foreach (QueueAttributeData::getAvailableAttributes() as $attr) {
            $getfn = 'get' . $attr;
            $attributes->append(new QueueAttributeData($attr, $this->$getfn()));
        }
        return $attributes;
    }

    /**
     * The SendMessage action delivers a message to the specified queue.
     *
     * The maximum allowed message size is 64 KB.
     * The following list shows the characters (in Unicode) allowed in your message, according to the
     * W3C XML specification (for more information, go to http://www.w3.org/TR/REC-xml/#charsets).
     * If you send any characters not included in the list, your request will be rejected.
     * #x9 | #xA | #xD | [#x20 to #xD7FF] | [#xE000 to #xFFFD] | [#x10000 to #x10FFFF]
     *
     * @param   string     $messageBody  The message to send.
     * @param   int        $delaySeconds optional The number of seconds to delay a specific message.
     *                                   Messages with a positive DelaySeconds value become available for
     *                                   processing after the delay time is finished.
     *                                   If you don't specify a value, the default value for the queue applies.
     * @return  MessageData              Returns MessageData object if success.
     * @throws  ClientException
     * @throws  SqsException
     */
    public function sendMessage($messageBody, $delaySeconds = null)
    {
        return $this->getSqs()->message->send($this->queueName, $messageBody, $delaySeconds);
    }
}