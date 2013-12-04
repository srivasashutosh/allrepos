<?php
namespace Scalr\Service\Aws\Client;

use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\LoaderException;
use Scalr\Service\Aws\DataType\Loader\ErrorLoader;

/**
 * Query Client Response
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     23.09.2012
 */
class QueryClientResponse implements ClientResponseInterface
{

    /**
     * HttpMessage instance
     *
     * @var \HttpMessage
     */
    private $message;

    /**
     * Information about error if it's occured.
     *
     * @var ErrorData|boolean
     */
    private $errorData;

    /**
     * Http request
     *
     * @var \HttpRequest
     */
    private $request;

    /**
     * Constructor
     *
     * @param   \HttpMessage $message  HTTP Message object
     */
    public function __construct(\HttpMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Invokes HttpMessage object methods
     *
     * @param    string    $method
     * @param    array     $args
     * @return   mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->message, $method)) {
            return call_user_method_array($method, $this->message, $args);
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getHeaders()
     */
    public function getHeaders()
    {
        return $this->message->getHeaders();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getHeader()
     */
    public function getHeader($headername)
    {
        return $this->message->getHeader($headername);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getRawContent()
     */
    public function getRawContent()
    {
        return $this->message->getBody();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getError()
     */
    public function getError()
    {
        if (!isset($this->errorData)) {
            $this->errorData = false;
            $code = $this->getResponseCode();
            if ($code < 200 || $code > 299) {
                if ($code == 404) {
                    //Workaround for the Amazon S3 response with Delete Marker object
                    if ($this->getHeader('x-amz-delete-marker') !== null) {
                        return $this->errorData;
                    }
                }
                $loader = new ErrorLoader();
                $this->errorData = $loader->load($this->getRawContent());
                $this->errorData->request = $this->getRequest();
                throw new QueryClientException($this->errorData);
            }
        }
        return $this->errorData;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getResponseCode()
     */
    public function getResponseCode()
    {
        return $this->message->getResponseCode();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getResponseStatus()
     */
    public function getResponseStatus()
    {
        return $this->message->getResponseStatus();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getRequest()
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets raw request message
     *
     * @param   \HttpRequest   $request Request object
     * @return  RestClientResponse
     */
    public function setRequest(\HttpRequest $request)
    {
        $this->request = $request;
        return $this;
    }
}