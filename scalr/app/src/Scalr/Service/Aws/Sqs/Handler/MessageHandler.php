<?php
namespace Scalr\Service\Aws\Sqs\Handler;

use Scalr\Service\Aws\Sqs\DataType\MessageData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\SqsException;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Sqs\AbstractSqsHandler;

/**
 * MessageHandler for the Simple Queue Service
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 */
class MessageHandler extends AbstractSqsHandler
{
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
    public function send($queueName, $messageBody, $delaySeconds = null)
    {
        return $this->getSqs()->getApiHandler()->sendMessage($queueName, $messageBody, $delaySeconds);
    }
}