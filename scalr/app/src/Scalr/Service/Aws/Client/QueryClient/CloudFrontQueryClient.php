<?php
namespace Scalr\Service\Aws\Client\QueryClient;

use Scalr\Service\Aws\Client\QueryClientResponse;

/**
 * Amazon CloudFront Query API client.
 *
 * HTTP Query-based requests are defined as any HTTP requests using the HTTP verb GET or POST
 * and a Query parameter named either Action or Operation.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 */

class CloudFrontQueryClient extends S3QueryClient
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client\QueryClient.S3QueryClient::call()
     */
    public function call ($action, $options, $path = '/')
    {
        $httpRequest = $this->createRequest();
        $httpMethod = $action ?: 'GET';
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        $path = '/' . $this->getApiVersion() . (!empty($path) ? $path : '/');

        //Wipes out extra options from headers and moves them to separate array.
        //It also collects an x-amz headers.
        $extraOptions = array();
        $amzHeaders = array();
        foreach ($options as $key => $val) {
            if (substr($key, 0, 1) === '_') {
                $extraOptions[substr($key, 1)] = $val;
                unset($options[$key]);
            } elseif (preg_match('/^x-amz-/i', $key)) {
                //Saves amz headers which are used to sign the request
                $amzHeaders[strtolower($key)] = $val;
            }
        }
        if (!isset($options['Date'])) {
            $options['Date'] = gmdate('r');
        }
        if (!isset($options['Host'])) {
            $options['Host'] = (isset($extraOptions['subdomain']) ? $extraOptions['subdomain'] . '.' : '') . $this->url;
        }

        if ($httpMethod === 'PUT' || $httpMethod === 'POST') {
            if (array_key_exists('putData', $extraOptions)) {
                if ($httpMethod === 'PUT') {
                    $httpRequest->setPutData($extraOptions['putData']);
                } else {
                    $httpRequest->setBody($extraOptions['putData']);
                }
            } elseif (array_key_exists('putFile', $extraOptions)) {
                if ($httpMethod === 'PUT') {
                    $httpRequest->setPutFile($extraOptions['putFile']);
                } else {
                    $httpRequest->setBody(file_get_contents($extraOptions['putFile']));
                }
            }
        }

        $options['Authorization'] = "AWS " . $this->awsAccessKeyId . ":"
              . base64_encode(hash_hmac('sha1', $options['Date'], $this->secretAccessKey, 1));

        $httpRequest->setUrl('https://' . $options['Host'] . $path);
        $httpRequest->setMethod(constant('HTTP_METH_' . $httpMethod));
        $httpRequest->setOptions(array(
            'redirect'  => 10,
            'useragent' => 'Scalr AWS Client (http://scalr.com)'
        ));
        $httpRequest->addHeaders($options);
        /* @var $message \HttpMessage */
        $message = $this->tryCall($httpRequest);
        $response = new QueryClientResponse($message);
        $response->setRequest($httpRequest);
        if ($this->getAws() && $this->getAws()->getDebug()) {
            echo "\n";
            echo $httpRequest->getRawRequestMessage() . "\n";
            echo $httpRequest->getRawResponseMessage() . "\n";
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client\QueryClient.S3QueryClient::getAllowedSubResources()
     */
    public static function getAllowedSubResources()
    {
        return array();
    }
}