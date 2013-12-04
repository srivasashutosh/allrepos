<?php
namespace Scalr\Service\OpenStack\Client;

use Scalr\Service\OpenStack\Services\ServiceInterface;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\OpenStack\Type\AppFormat;

/**
 * ClientInterface
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
interface ClientInterface
{

    /**
     * Makes a REST request to OpenStack service
     *
     * @param   ServiceInterface|string $service  Service or endpoint url
     * @param   string           $path     optional Uri path
     * @param   array            $options  optional An array of the query parameters
     * @param   string           $verb     optional An HTTP Verb
     * @param   AppFormat        $accept   optional An application format. (json by default)
     * @param   bool             $auth     optional Whether it have to send X-Auth-Token header (authenticated request)
     * @return  ClientResponseInterface Returns response from server
     * @throws  ClientException
     */
    public function call($service, $path = '/', array $options = null, $verb = 'GET', AppFormat $accept = null, $auth = true);
}
