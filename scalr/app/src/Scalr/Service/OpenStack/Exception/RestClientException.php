<?php
namespace Scalr\Service\OpenStack\Exception;

use Scalr\Service\OpenStack\Client\ErrorData;

/**
 * RestClientException
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class RestClientException extends OpenStackException
{
    /**
     * Error details
     * @var ErrorData
     */
    public $error;

    public function __construct($message, $code = null, $previous = null)
    {
        if ($message instanceof ErrorData) {
            $this->error = $message;
            parent::__construct('OpenStack error. ' . $message->message, $code, $previous);
        } else {
            parent::__construct($message, $code, $previous);
        }
    }
}