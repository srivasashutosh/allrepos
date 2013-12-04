<?php

namespace Scalr\Service\Aws\Client;

/**
 * Client response interface
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     21.09.2012
 */
interface ClientResponseInterface
{
    /**
     * Gets raw response content.
     *
     * Gets raw content of the response that is obtained from Amazon web service.
     *
     * @return   string  Returns raw content of the response from AWS.
     */
    public function getRawContent();

    /**
     * Gets Error.
     *
     * @return  boolean         Returns FALSE if no error or throws an ClientException.
     * @throws  ClientException
     */
    public function getError();

    /**
     * Gets message headers.
     *
     * @return array Returns an associative array containing the messages HTTP headers.
     */
    public function getHeaders();

    /**
     * Gets a header value.
     *
     * @param   string        $headername A header name.
     * @return  string        Returns the header value on success or NULL if the header does not exist.
     */
    public function getHeader($headername);

    /**
     * Gets a response code
     *
     * @return  int Returns HTTP Response code.
     */
    public function getResponseCode();

    /**
     * Gets a response status
     *
     * @return  string Returns HTTP Response Status.
     */
    public function getResponseStatus();

    /**
     * Gets raw request message
     *
     * @return  \HttpRequest  Returns request object
     */
    public function getRequest();
}