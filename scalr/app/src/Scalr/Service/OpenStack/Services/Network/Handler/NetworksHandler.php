<?php
namespace Scalr\Service\OpenStack\Services\Network\Handler;

use Scalr\Service\OpenStack\Services\Network\Type\ListNetworksFilter;
use Scalr\Service\OpenStack\Services\NetworkService;
use Scalr\Service\OpenStack\Services\ServiceHandlerInterface;
use Scalr\Service\OpenStack\Services\AbstractServiceHandler;

/**
 * NetworksHandler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.05.2013
 *
 * @method   array|object list()
 *           list(string $networkId = null, \Scalr\Service\OpenStack\Services\Network\Type\ListNetworksFilter|array $filter = null)
 *           List Networks action.
 *           Lists a summary of all networks defined in Quantum that are accessible
 *           to the tenant who submits the request.
 *
 * @method   object create()
 *           create(string $name = null, bool $adminStateUp = null, bool $shared = null, string $tenantId = null)
 *           Create Network action (POST /networks)
 *           Creates a new Quantum network.
 *
 * @method   object update()
 *           update(string $networkId, string $name = null, bool $adminStateUp = null)
 *           Update Network action (PUT /networks/network-id)
 *           Updates the specified network.
 *           Either name or admin_state_up must be provided for this action.
 *
 * @method   bool delete()
 *           delete(string $networkId)
 *           Delete Network action (DELETE /networks/network-id)
 *           This operation deletes a Quantum network and its associated subnets provided that no
 *           port is currently configured on the network.
 *           If ports are still configured on the network that you want to delete, a 409 Network In Use
 *           error is returned.
 */
class NetworksHandler extends AbstractServiceHandler implements ServiceHandlerInterface
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceHandlerInterface::getServiceMethodAliases()
     */
    public function getServiceMethodAliases()
    {
        return array(
            'list'   => 'listNetworks',
            'create' => 'createNetwork',
            'update' => 'updateNetwork',
            'delete' => 'deleteNetwork',
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.AbstractServiceHandler::getService()
     * @return  NetworkService
     */
    public function getService()
    {
        return parent::getService();
    }
}