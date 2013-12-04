<?php
namespace Scalr\Service\OpenStack\Client;

use Scalr\Service\OpenStack\Type\AppFormat;
use Scalr\Service\OpenStack\Exception\RestClientException;

/**
 * OpenStack Rest Client Response
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class RestClientResponse implements ClientResponseInterface
{
    /**
     * @var \HttpMessage
     */
    private $message;

    /**
     * @var ErrorData|bool
     */
    private $errorData;

    /**
     * @var AppFormat
     */
    private $format;

    /**
     * Raw request message
     * @var string
     */
    private $rawRequestMessage;

    /**
     * Constructor
     *
     * @param   \HttpMessage $message  An HTTP message
     * @param   AppFormat    $format   An responce body application format
     */
    public function __construct(\HttpMessage $message, AppFormat $format)
    {
        $this->message = $message;
        $this->format = $format;
    }

    /**
     * Gets an HTTP Message
     *
     * @return \HttpMessage Returns an HttpMessage object
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Client.ClientResponseInterface::getContent()
     */
    public function getContent()
    {
        return $this->message->getBody();
    }


    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Client.ClientResponseInterface::getResponseCode()
     */
    public function getResponseCode()
    {
        return $this->message->getResponseCode();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Client.ClientResponseInterface::getHeader()
     */
    public function getHeader($headerName)
    {
        return $this->message->getHeader($headerName);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Client.ClientResponseInterface::getHeaders()
     */
    public function getHeaders()
    {
        return $this->message->getHeaders();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Client.ClientResponseInterface::hasError()
     */
    public function hasError()
    {
        if (!isset($this->errorData)) {
            $this->errorData = false;
            $code = $this->message->getResponseCode();
            if ($code < 200 || $code > 299) {
                $this->errorData = new ErrorData();
                if ($this->format == AppFormat::APP_JSON) {
                    $d = @json_decode($this->getContent());
                    if ($d === null) {
                        $this->errorData->code = $code;
                        $this->errorData->message = strip_tags($this->getContent());
                        $this->errorData->details = '';
                    } else {
                        list(, $v) = each($d);
                        $this->errorData->code = $v->code;
                        $this->errorData->message = $v->message;
                        //. (isset($this->rawRequestMessage) ? "\nError Code:" . $this->getResponseCode() . "\n" . $this->rawRequestMessage : '');
                        $this->errorData->details = isset($v->details) ? (string)$v->details : '';
                    }
                } else if ($this->format == AppFormat::APP_XML) {
                    $d = simplexml_load_string($this->getContent());
                    $this->errorData->code = $code;
                    $this->errorData->message = isset($d->message) ? (string)$d->message : '';
                    $this->errorData->details = isset($d->details) ? (string)$d->details : '';
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'Unexpected application format "%s" in class %s', (string)$this->format, get_class($this)
                    ));
                }
                throw new RestClientException($this->errorData);
            }
        }
        return $this->errorData;
    }

    /**
     * Gets raw request message
     *
     * @return  string  Returns raw request message
     */
    public function getRawRequestMessage()
    {
        return $this->rawRequestMessage;
    }

    /**
     * Sets raw request message
     *
     * @param   string   $rawRequestMessage  Raw request message
     * @return  RestClientResponse
     */
    public function setRawRequestMessage($rawRequestMessage)
    {
        $this->rawRequestMessage = $rawRequestMessage;
        return $this;
    }
}