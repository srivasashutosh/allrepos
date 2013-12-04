<?php
namespace Scalr\Service\OpenStack\Client;

use Scalr\Service\OpenStack\Exception\OpenStackException;
use Scalr\Service\OpenStack\Services\ServiceInterface;
use Scalr\Service\OpenStack\Exception\RestClientException;
use Scalr\Service\OpenStack\OpenStackConfig;
use Scalr\Service\OpenStack\Type\AppFormat;
use \HttpRequest;
use \HttpMessage;
use \HttpException;

/**
 * OpenStack Rest Client
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class RestClient implements ClientInterface
{

    /**
     * OpenStack config
     * @var OpenStackConfig
     */
    protected $config;

    /**
     * Default useragent
     *
     * @var string
     */
    protected $useragent;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * Constructor
     *
     * @param   OpenStackConfig $config  A OpenStack config.
     */
    public function __construct(OpenStackConfig $config)
    {
        $this->config = $config;
        $this->useragent = sprintf('Scalr OpenStack Client (http://scalr.com) PHP/%s pecl_http/%s',
            phpversion(), phpversion('http')
        );
    }

    /**
     * Gets OpenStack config
     *
     * @return  OpenStackConfig A OpenStack config.
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Creates new HttpRequest object
     *
     * @return HttpRequest Returns new HttpRequest object
     */
    protected function createHttpRequest()
    {
        $req = new HttpRequest();
        $req->resetCookies();
        $req->setOptions(array(
            'redirect'  => 10,
            'useragent' => $this->useragent,
        ));
        return $req;
    }

    /**
     * Tries to send request on several attempts.
     *
     * @param    HttpRequest     $httpRequest
     * @param    int             $attempts     Attempts count.
     * @param    int             $interval     An sleep interval between an attempts in microseconds.
     * @throws   QueryClientException
     * @returns  HttpMessage    Returns HttpMessage if success.
     */
    protected function tryCall($httpRequest, $attempts = 5, $interval = 200)
    {
        try {
            $message = $httpRequest->send();
        } catch (HttpException $e) {
            if (--$attempts > 0) {
                usleep($interval);
                $message = $this->tryCall($httpRequest, $attempts, $interval * 2);
            } else {
                throw new RestClientException(sprintf('Cannot establish connection with OpenStack server on %s (%s).', $httpRequest->getUrl(), $e->getMessage()));
            }
        }
        return $message;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Client.ClientInterface::call()
     */
    public function call($service, $path = '/', array $options = null, $verb = 'GET',
                         AppFormat $accept = null, $auth = true)
    {
        if ($accept === null) {
            $accept = AppFormat::json();
        }
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        if ($options === null) {
            $options = array();
        }
        if (isset($options['content-type'])) {
            $ctype = (string) $options['content-type'];
            unset($options['content-type']);
        }
        $customOptions = array();
        foreach ($options as $key => $value) {
            if (substr($key, 0, 1) === '_') {
                $customOptions[substr($key, 1)] = $value;
                unset($options[$key]);
            }
        }

        $req = $this->createHttpRequest();
        $req->setMethod(constant('HTTP_METH_' . $verb));
        $ctype = 'json';
        $headers = array(
            'Accept'       => 'application/' . (string)$accept,
            'Content-Type' => 'application/' . (isset($ctype) ? $ctype : 'json') . '; charset=UTF-8',
        );
        if ($auth) {
            $token = $this->getConfig()->getAuthToken();
            if (!($token instanceof AuthToken) || $token->isExpired()) {
                $this->getConfig()->setAuthToken(null);
                //We need to sign on at first.
                $bAuthRequestSent = true;
                $authResult = $this->auth();
                if (($token = $this->getConfig()->getAuthToken()) === null) {
                    throw new RestClientException('Authentication to OpenStack server failed.');
                }
            }
            $headers['X-Auth-Token'] = $token->getId();
        }
        $endpoint = $service instanceof ServiceInterface ? $service->getEndpointUrl() : $service;
        if (substr($endpoint, -1) === '/') {
            //removes trailing slashes
            $endpoint = rtrim($endpoint, '/');
        }
        $req->addHeaders($headers);
        $req->setUrl($endpoint . $path);
        if ($verb == 'POST') {
            $req->setBody(json_encode($options));
        } elseif ($verb == 'PUT') {
            if (isset($customOptions['putData'])) {
                $req->setPutData($customOptions['putData']);
            } else if (isset($customOptions['putFile'])) {
                $req->setPutFile($customOptions['putFile']);
            }
        } else {
            $req->addQueryData($options);
        }

        $message = $this->tryCall($req);

        $response = new RestClientResponse($message, $accept);
        $response->setRawRequestMessage($req->getRawRequestMessage());
        if ($this->debug) {
            echo "\nURL: " . $req->getUrl() . "\n";
            echo $req->getRawRequestMessage() . "\n";
            echo $req->getRawResponseMessage() . "\n";
        }

        if ($response->getResponseCode() === 401 && !isset($bAuthRequestSent) &&
            $this->getConfig()->getAuthToken() !== null) {
            //When this token isn't expired and by some reason it causes unauthorized response
            //we should reset authurization token and force authentication request
            $this->getConfig()->setAuthToken(null);
            $response = call_user_func_array(array($this, __METHOD__), func_get_args());
        }

        return $response;
    }

    /**
     * Performs an authentication request
     *
     * @return  object Returns result
     */
    public function auth()
    {
        $cfg = $this->getConfig();
        $options = array(
            'auth' => $cfg->getAuthQueryString(),
        );
        $response = $this->call(
            $cfg->getIdentityEndpoint(),
            '/tokens', $options, 'POST', null, false
        );
        if ($response->hasError() === false) {
            $str = $response->getContent();
            $result = json_decode($str);
            $cfg->setAuthToken(AuthToken::loadJson($str));
            if (($cb = $cfg->getUpdateTokenCallback()) !== null && is_callable($cb)) {
                //It should save token in database
                $cb($cfg->getAuthToken());
            }
        }
        return $result;
    }

    /**
     * Sets debug mode
     *
     * @param   bool $bDebug If true it will output debug per request into stdout
     * @return  RestClient
     */
    public function setDebug($bDebug)
    {
        $this->debug = (bool)$bDebug;
        return $this;
    }

    /**
     * Resets debug mode
     *
     * @return  RestClient
     */
    public function resetDebug()
    {
        $this->debug = false;
        return $this;
    }
}
