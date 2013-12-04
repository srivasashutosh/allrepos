<?php
namespace Scalr\Service\Aws\Sqs\Handler;

use Scalr\Service\Aws\Sqs\DataType\QueueList;
use Scalr\Service\Aws\Sqs\DataType\QueueData;
use Scalr\Service\Aws\Sqs\DataType\QueueAttributeList;
use Scalr\Service\Aws\Sqs\DataType\QueueAttributeData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\SqsException;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Sqs\AbstractSqsHandler;
/**
 * QueueHandler for the Simple Queue Service
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 */
class QueueHandler extends AbstractSqsHandler
{

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
     * @param   string                                      $queueName           A Queue name
     * @param   array|QueueAttributeData|QueueAttributeList $queueAttributeList  optional QueueAttributeList
     * @return  QueueData                                   Returns QueueData
     * @throws  ClientException
     * @throws  SqsException
     */
    public function create($queueName, $queueAttributeList = null)
    {
        if ($queueAttributeList !== null && !($queueAttributeList instanceof QueueAttributeList)) {
            $queueAttributeList = new QueueAttributeList($queueAttributeList);
        }
        return $this->getSqs()->getApiHandler()->createQueue($queueName, $queueAttributeList);
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
    public function getUrl($queueName, $queueOwnerAwsAccountId = null)
    {
        return $this->getSqs()->getApiHandler()->getQueueUrl($queueName, $queueOwnerAwsAccountId);
    }

    /**
     * The GetQueueAttributes action returns one or all attributes of a queue.
     *
     * This method retrieved attributes for the queue as part of QueueData object.
     *
     * @param   string                    $queueName          The queue name.
     * @param   string|array|ListDataType $queueAttributeList optional The attribute list you want to get.
     * @return  QueueData                 Returns QueueData
     * @throws  ClientException
     * @throws  SqsException
     */
    public function getAttributes($queueName, $queueAttributeList = null)
    {
        if ($queueAttributeList !== null && !($queueAttributeList instanceof ListDataType)) {
            $queueAttributeList = new ListDataType($queueAttributeList);
        }
        return $this->getSqs()->getApiHandler()->getQueueAttributes($queueName, $queueAttributeList);
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
    public function getList($queueNamePrefix = null)
    {
        return $this->getSqs()->getApiHandler()->listQueues($queueNamePrefix);
    }

    /**
     * The DeleteQueue action deletes the queue specified by the queue URL, regardless of whether the
     * queue is empty. If the specified queue does not exist, SQS returns a successful response.

     * @param   string       $queueUrl  A queue url.
     * @return  bool         Returns TRUE if queue does successfully remove.
     * @throws  ClientException
     * @throws  SqsException
     */
    public function delete($queueUrl)
    {
        return $this->getSqs()->getApiHandler()->deleteQueue($queueUrl);
    }

    /**
     * Gets Queue from storage.
     *
     * It supposes that Queue has been previously created or retrieved.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param    string    $queueName   A Queue name
     * @return   QueueData Returns QueueData object if it has been created or described
     *                     or NULL if it does not exist.
     * @throws  ClientException
     */
    public function get($queueName)
    {
        return $this->getSqs()->getEntityManager()->getRepository('Sqs:Queue')->find($queueName);
    }
}