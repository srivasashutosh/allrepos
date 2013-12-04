<?php
namespace Scalr\Service\Aws\Sqs\V20111001;

use Scalr\Service\Aws\AbstractApi;
use Scalr\Service\Aws\Sqs\DataType\MessageData;
use Scalr\Service\Aws\Sqs\DataType\QueueList;
use Scalr\Service\Aws\Sqs\DataType\QueueData;
use Scalr\Service\Aws\Sqs\DataType\QueueAttributeList;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\SqsException;
use Scalr\Service\Aws\Sqs;
use Scalr\Service\Aws\EntityManager;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Client\QueryClientResponse;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientInterface;

/**
 * Sqs Api messaging.
 *
 * Implements Sqs Low-Level API Actions.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 */
class SqsApi extends AbstractApi
{

    /**
     * @var Sqs
     */
    protected $sqs;

    /**
     * Constructor
     *
     * @param   Sqs             $sqs           Sqs instance
     * @param   ClientInterface $client        Client Interface
     */
    public function __construct(Sqs $sqs, ClientInterface $client)
    {
        $this->sqs = $sqs;
        $this->client = $client;
    }

    /**
     * The CreateQueue action creates a new queue.
     *
     * When you request CreateQueue, you provide a name for the queue.
     * To successfully create a new queue, you must provide a name that is unique within the scope of your own queues.
     *
     * Note! If you delete a queue, you must wait at least 60 seconds before creating a queue with the same name.
     *
     * If you provide the name of an existing queue, along with the exact names and values of all the queue's
     * attributes, CreateQueue returns the queue URL for the existing queue. If the queue name, attribute
     * names, or attribute values do not match an existing queue, CreateQueue returns an error.
     *
     * @param   string             $queueName           A Queue name
     * @param   QueueAttributeList $queueAttributeList  optional QueueAttributeList
     * @return  QueueData          Returns QueueData
     * @throws  ClientException
     * @throws  SqsException
     */
    public function createQueue ($queueName, QueueAttributeList $queueAttributeList = null)
    {
        $result = null;
        $options = array(
            'QueueName' => (string) $queueName,
        );
        if ($queueAttributeList !== null) {
            $options = array_merge($options, $queueAttributeList->getQueryArrayBare('Attribute'));
        }
        $response = $this->client->call('CreateQueue', $options, '/');
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->CreateQueueResult->QueueUrl)) {
                throw new SqsException('Unexpected response! ' . $response->getRawContent());
            }
            $queueUrl = (string) $sxml->CreateQueueResult->QueueUrl;

            if (($result = $this->sqs->queue->get($options['QueueName'])) !== null) {
                //This just updates queueUrl if queue already exists.
                $result->queueUrl = $queueUrl;
            } else {
                $em = $this->getEntityManager();
                $result = new QueueData();
                $result->setSqs($this->sqs);
                $result->queueName = $options['QueueName'];
                $result->queueUrl = $queueUrl;
                $em->attach($result);
            }
        }

        return $result;
    }

    /**
     * The GetQueueUrl action returns the Uniform Resource Locater (URL) of a queue. This action provides
     * a simple way to retrieve the URL of an SQS queue.
     *
     * To access a queue that belongs to another AWS account, use the QueueOwnerAWSAccountId parameter
     * to specify the account ID of the queue's owner. The queue's owner must grant you permission to access
     * the queue.
     *
     * @param   string     $queueName              The name of an existing queue.
     * @param   string     $queueOwnerAwsAccountId optional The AWS account ID of the account that created the queue.
     * @return  QueueData  Returns QueueData
     * @throws  ClientException
     * @throws  SqsException
     */
    public function getQueueUrl($queueName, $queueOwnerAwsAccountId = null)
    {
        $result = null;
        $options = array(
            'QueueName' => (string) $queueName,
        );
        if ($queueOwnerAwsAccountId !== null) {
            $options['QueueOwnerAWSAccountId'] = (string) $queueOwnerAwsAccountId;
        }
        $response = $this->client->call('GetQueueUrl', $options, '/');
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->GetQueueUrlResult->QueueUrl)) {
                throw new SqsException('Unexpected response! ' . $response->getRawContent());
            }
            $queueUrl = (string) $sxml->GetQueueUrlResult->QueueUrl;

            if (($result = $this->sqs->queue->get($options['QueueName'])) !== null) {
                //This just updates queueUrl if queue already exists.
                $result->queueUrl = $queueUrl;
            } else {
                $em = $this->getEntityManager();
                $result = new QueueData();
                $result->setSqs($this->sqs);
                $result->queueName = $options['QueueName'];
                $result->queueUrl = $queueUrl;
                $em->attach($result);
            }
        }

        return $result;
    }

    /**
     * The ListQueues action returns a list of your queues. The maximum number of queues that can be
     * returned is 1000. If you specify a value for the optional QueueNamePrefix parameter, only queues with
     * a name beginning with the specified value are returned.
     *
     * @param   string       $queueNamePrefix optional String to use for filtering the list results.
     *                                        Only those queues whose name begins with the specified string are returned.
     * @return  QueueList    Returns the list of available Queues
     * @throws  ClientException
     * @throws  SqsException
     */
    public function listQueues($queueNamePrefix = null)
    {
        $result = null;
        $options = array();
        if ($queueNamePrefix !== null) {
            $options['QueueNamePrefix'] = (string) $queueNamePrefix;
        }
        $response = $this->client->call('ListQueues', $options, '/');
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ListQueuesResult)) {
                throw new SqsException('Unexpected response! ' . $response->getRawContent());
            }
            $result = new QueueList();
            $result->setSqs($this->sqs);
            if (!empty($sxml->ListQueuesResult->QueueUrl)) {
                $em = $this->getEntityManager();
                foreach ($sxml->ListQueuesResult->QueueUrl as $queueUrl) {
                    $queueUrl = (string) $queueUrl;
                    $queueName = preg_replace('#^.+/([^/]+)/?$#', '\\1', $queueUrl);
                    if (!empty($queueName)) {
                        $queue = $this->sqs->queue->get($queueName);
                        if (!($queue instanceof QueueData)) {
                            $queue = new QueueData();
                            $queue->setSqs($this->sqs);
                            $queue->queueUrl = $queueUrl;
                            $queue->queueName = $queueName;
                            $em->attach($queue);
                        } else {
                            $queue->queueUrl = $queueUrl;
                        }
                        $result->append($queue);
                        unset($queue);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Gets queue
     *
     * Tries to get queue from storage.
     * If it doesn't exist it will retrieve queue from Amazon API.
     * Retrieved queue then can be used for obtaining uriPath to compose API requests.
     *
     * @param   string     $queueName
     * @return  QueueData  Returns queue data
     * @throws  ClientException
     * @throws  SqsException
     */
    private function retrieveQueueByName($queueName)
    {
        $queue = $this->sqs->queue->get($queueName);
        if (!($queue instanceof QueueData)) {
            //We need to request a queue url at first. Tries to request.
            //It might cause error if queue belongs to another AWS account, therefore
            //to use this method more safely you should call getQueueUrl yourself with the second parameter.
            $queue = $this->getQueueUrl($queueName);
        }
        return $queue;
    }

    /**
     * The GetQueueAttributes action returns one or all attributes of a queue.
     *
     * This method retrieved attributes for the queue as part of QueueData object.
     *
     * @param   string             $queueName          The queue name.
     * @param   ListDataType       $queueAttributeList optional The attribute list you want to get.
     * @return  QueueData  Returns QueueData
     * @throws  ClientException
     * @throws  SqsException
     */
    public function getQueueAttributes($queueName, ListDataType $queueAttributeList = null)
    {
        $queue = $this->retrieveQueueByName((string) $queueName);
        $options = array();
        if ($queueAttributeList === null || count($queueAttributeList) == 0) {
            $options['AttributeName'] = 'All';
        } else {
            $options = $queueAttributeList->getQueryArrayBare('Attribute');
        }
        $response = $this->client->call('GetQueueAttributes', $options, $queue->getUriPath());
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->GetQueueAttributesResult)) {
                throw new SqsException('Unexpected response! ' . $response->getRawContent());
            }
            $ptr =& $sxml->GetQueueAttributesResult;
            if (!empty($ptr->Attribute)) {
                foreach ($ptr->Attribute as $v) {
                    $attrname = (string) $v->Name;
                    $setfn = 'set' . $attrname;
                    switch ($attrname) {
                        case 'CreatedTimestamp':
                        case 'LastModifiedTimestamp':
                            $attrvalue = new \DateTime(null, new \DateTimeZone('UTC'));
                            $attrvalue->setTimestamp((string)$v->Value);
                            break;

                        case 'MaximumMessageSize':
                        case 'MessageRetentionPeriod':
                        case 'ApproximateNumberOfMessages':
                        case 'ApproximateNumberOfMessagesNotVisible':
                        case 'ApproximateNumberOfMessagesDelayed':
                        case 'VisibilityTimeout' :
                        case 'DelaySeconds':
                            $attrvalue = (int) $v->Value;
                            break;

                        default :
                            $attrvalue = (string) $v->Value;
                            break;
                    }
                    $queue->$setfn($attrvalue);
                }
            }
        }
        return $queue;
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
     * @param   string     $queueName    The queue name.
     * @param   string     $messageBody  The message to send.
     * @param   int        $delaySeconds optional The number of seconds to delay a specific message.
     *                                   Messages with a positive DelaySeconds value become available for
     *                                   processing after the delay time is finished.
     *                                   If you don't specify a value, the default value for the queue applies.
     * @return  MessageData              Returns MessageData object if success.
     * @throws  ClientException
     * @throws  SqsException
     */
    public function sendMessage($queueName, $messageBody, $delaySeconds = null)
    {
        $message = null;
        $options = array(
            'MessageBody' => (string) $messageBody,
        );
        $queue = $this->retrieveQueueByName((string)$queueName);
        if ($delaySeconds !== null) {
            $options['DelaySeconds'] = (int) $delaySeconds;
        }
        $response = $this->client->call('SendMessage', $options, $queue->getUriPath());
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->SendMessageResult)) {
                throw new SqsException('Unexpected response! ' . $response->getRawContent());
            }
            $message = new MessageData();
            $message->setSqs($this->sqs);
            $message->setQueueName($queue->queueName)->setQueueUrl($queue->queueUrl);
            $message->messageId = (string) $sxml->SendMessageResult->MessageId;
            $message->md5OfMessageBody = (string) $sxml->SendMessageResult->MD5OfMessageBody;
        }
        return $message;
    }

    /**
     * The DeleteQueue action deletes the queue specified by the queue URL, regardless of whether the
     * queue is empty. If the specified queue does not exist, SQS returns a successful response.

     * @param   string       $queueUrl  A queue url.
     * @return  bool         Returns TRUE if queue does successfully remove.
     * @throws  ClientException
     * @throws  SqsException
     */
    public function deleteQueue($queueUrl)
    {
        $result = false;
        $options = array();
        if (!preg_match('#^.+/([^/]+)/([^/]+)/?$#', $queueUrl, $match)) {
            throw new SqsException(sprintf('Queue url %s should contain awsAccountId as well as queueName.' . $queueUrl));
        }
        $queueName = $match[2];
        $queuePath = '/' . $match[1] . '/' . $queueName . '/';
        $response = $this->client->call('DeleteQueue', $options, $queuePath);
        if ($response->getError() === false) {
            $result = true;
            $queue = $this->sqs->queue->get($queueName);
            if ($queue instanceof QueueData) {
                $em = $this->getEntityManager();
                $em->detach($queue);
            }
        }
        return $result;
    }

    /**
     * Gets an EntityManager
     *
     * @return \Scalr\Service\Aws\EntityManager
     */
    public function getEntityManager()
    {
        return $this->sqs->getEntityManager();
    }
}