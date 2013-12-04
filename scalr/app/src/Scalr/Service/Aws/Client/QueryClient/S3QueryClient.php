<?php
namespace Scalr\Service\Aws\Client\QueryClient;

use Scalr\Service\Aws;
use Scalr\Service\Aws\Client\QueryClientResponse;
use Scalr\Service\Aws\Client\QueryClient;

/**
 * Amazon S3 Query API client.
 *
 * HTTP Query-based requests are defined as any HTTP requests using the HTTP verb GET or POST
 * and a Query parameter named either Action or Operation.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     09.11.2012
 */
class S3QueryClient extends QueryClient
{

    /**
     * Gets the list of the allowed sub-resources
     *
     * @return   array Returns the list
     */
    public static function getAllowedSubResources ()
    {
        return array(
            'acl', 'lifecycle', 'location', 'logging', 'notification', 'partNumber',
            'policy', 'requestPayment', 'torrent', 'uploadId', 'uploads', 'versionId',
            'versioning', 'versions', 'website'
        );
    }

    /**
     * Calls Amazon web service method.
     *
     * It ensures execution of the certain AWS action by transporting the request
     * and receiving response.
     *
     * @param     string    $action           REST METHOD (GET|PUT|DELETE|HEAD etc).
     * @param     array     $options          An options array. It's used to send headers for the HTTP request.
     *                                        extra options: _subdomain, _putData, _putFile
     * @param     string    $path    optional A relative path.
     * @return    ClientResponseInterface
     * @throws    ClientException
     */
    public function call ($action, $options, $path = '/')
    {
        $httpRequest = $this->createRequest();
        $httpMethod = $action ?: 'GET';
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

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
                if (!isset($options['Content-Length'])) {
                    $options['Content-Length'] = strlen($extraOptions['putData']);
                }
                if (!isset($options['Content-MD5']) && !empty($options['Content-Length'])) {
                    $options['Content-MD5'] = Aws::getMd5Base64Digest($extraOptions['putData']);
                }
            } elseif (array_key_exists('putFile', $extraOptions)) {
                if ($httpMethod === 'PUT') {
                    $httpRequest->setPutFile($extraOptions['putFile']);
                } else {
                    $httpRequest->setBody(file_get_contents($extraOptions['putFile']));
                }
                if (!isset($options['Content-Length'])) {
                    $options['Content-Length'] = filesize($extraOptions['putFile']);
                }
                if (!isset($options['Content-MD5']) && !empty($options['Content-Length'])) {
                    $options['Content-MD5'] = Aws::getMd5Base64DigestFile($extraOptions['putFile']);
                }
            }
        }
        //This also includes a mock objects which look like "Mock_S3QueryClient_d65a1dc1".
        if (preg_match('#(?<=[_\\\\])S3QueryClient(?=_|$)#', get_class($this))) {
            //S3 Client has a special Authorization string
            $canonicalizedAmzHeaders = '';
            if (!empty($amzHeaders)) {
                ksort($amzHeaders);
                foreach ($amzHeaders as $k => $v) {
                    $canonicalizedAmzHeaders .= $k . ':' . trim(preg_replace('/#( *[\r\n]+ *)+#/', ' ', $v)) . "\n";
                }
            }

            //Note that in case of multiple sub-resources, sub-resources must be lexicographically sorted
            //by sub-resource name and separated by '&'. e.g. ?acl&versionId=value.
            $t = explode('?', $path);
            if (!empty($t[1])) {
                $canonPath = $t[0] . '?';
                parse_str($t[1], $subresources);
                ksort($subresources);
                $allowed = $this->getAllowedSubResources();
                foreach ($subresources as $k => $v) {
                    if (in_array($k, $allowed)) {
                        $canonPath .= $k . ($v !== '' ? '=' . $v : '' ) . '&';
                    }
                }
                $canonPath = substr($canonPath, 0, -1);
            }

            $canonicalizedResource = (isset($extraOptions['subdomain']) ? '/' . strtolower($extraOptions['subdomain']) : '')
              . (isset($canonPath) ? $canonPath : $path);

            $stringToSign =
                $httpMethod . "\n"
              . (!empty($options['Content-MD5']) ? (string)$options['Content-MD5'] : '') . "\n"
              . (!empty($options['Content-Type']) ? (string)$options['Content-Type'] : '') . "\n"
              . (isset($amzHeaders['x-amz-date']) ? '' : $options['Date'] . "\n")
              . $canonicalizedAmzHeaders . $canonicalizedResource
            ;

            $options['Authorization'] = "AWS " . $this->awsAccessKeyId . ":"
              . base64_encode(hash_hmac('sha1', $stringToSign, $this->secretAccessKey, 1));
        } else {
            $options['Authorization'] = "AWS " . $this->awsAccessKeyId . ":"
              . base64_encode(hash_hmac('sha1', $options['Date'], $this->secretAccessKey, 1));
        }

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
}