<?php
namespace Scalr\Service\Aws;

use Scalr\Service\Aws\Client\ClientInterface;
use \SimpleXMLElement;

/**
 * AbstractApi
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     20.12.2012
 */
abstract class AbstractApi
{
    /**
     * Client
     * This property is explicitly used as reflection property
     * @var ClientInterface
     */
    protected $client;

    /**
     * Gets AWS Client
     *
     * @return  ClientInterface Retuns AWS Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets AWS Client
     *
     * @param   ClientInterface  $client  Client object
     * @return  AbstractApi
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Checks an existence of the SimpleXMLElement element.
     *
     * @param   \SimpleXMLElement $element Simple XML Element to check
     * @return  bool Returns true if element does exist.
     */
    public function exist($element)
    {
        return isset($element) && current($element) !== false;
    }

    /**
     * Escapes string to pass it over http request
     *
     * @param   string   $str
     * @return  string
     */
    protected static function escape($str)
    {
        return rawurlencode($str);
    }
}