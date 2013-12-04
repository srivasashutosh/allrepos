<?php
namespace Scalr\Service\OpenStack\Services\Servers\Handler;

use Scalr\Service\OpenStack\Services\ServersService;
use Scalr\Service\OpenStack\Services\ServiceHandlerInterface;
use Scalr\Service\OpenStack\Services\AbstractServiceHandler;

/**
 * Servers FloatingIpsHandler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    14.12.2012
 *
 * @method   array    list()         list()                       Gets the list of floating ips.
 * @method   object   get()          get($floatingIpAddressId)    Gets floating Ip details.
 * @method   object   create()       create($pool = null)         Allocates a new floating IP address to a tenant or account.
 * @method   bool     delete()       delete($floatingIpAddressId) Deallocates the floating IP address associated with floating_IP_address_ID.
 */
class FloatingIpsHandler extends AbstractServiceHandler implements ServiceHandlerInterface
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceHandlerInterface::getServiceMethodAliases()
     */
    public function getServiceMethodAliases()
    {
        return array(
            'list'   => 'listFloatingIps',
            'get'    => 'getFloatingIp',
            'create' => 'createFloatingIp',
            'delete' => 'deleteFloatingIp',
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.AbstractServiceHandler::getService()
     * @return  ServersService
     */
    public function getService()
    {
        return parent::getService();
    }
}