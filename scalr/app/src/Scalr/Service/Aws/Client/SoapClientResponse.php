<?php
namespace Scalr\Service\Aws\Client;

use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\LoaderException;
use Scalr\Service\Aws\DataType\Loader\ErrorLoader;

/**
 * Soap Client Response
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.02.2013
 */
class SoapClientResponse implements ClientResponseInterface
{

    /**
     * Raw response
     *
     * @var string
     */
    private $response;

    /**
     * Response headers
     * @var array
     */
    private $responseHeaders;

    /**
     * Information about error if it's occured.
     *
     * @var ErrorData|boolean
     */
    private $errorData;

    /**
     * Http request
     * @var string
     */
    private $request;

    /**
     * Constructor
     *
     * @param   string $response
     */
    public function __construct($response, $responseHeaders, $request)
    {
        $this->response = $response;
        $this->responseHeaders = @http_parse_headers($responseHeaders);
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getHeaders()
     */
    public function getHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getHeader()
     */
    public function getHeader($headername)
    {
        return array_key_exists($headername, $this->responseHeaders) ?
               $this->responseHeaders[$headername] : null;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getRawContent()
     */
    public function getRawContent()
    {
        return $this->response;
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
                throw new SoapClientException($this->errorData);
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
        return $this->getHeader('Response Code');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getResponseStatus()
     */
    public function getResponseStatus()
    {
        return $this->getHeader('Response Status');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientResponseInterface::getRequest()
     */
    public function getRequest()
    {
        return $this->request;
    }
}