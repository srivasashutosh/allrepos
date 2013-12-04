<?php
namespace Scalr\Service\OpenStack\Services\Network\Handler;

use Scalr\Service\OpenStack\Services\Network\Type\ListPortsFilter;
use Scalr\Service\OpenStack\Services\Network\Type\CreatePort;
use Scalr\Service\OpenStack\Services\NetworkService;
use Scalr\Service\OpenStack\Services\ServiceHandlerInterface;
use Scalr\Service\OpenStack\Services\AbstractServiceHandler;

/**
 * PortsHandler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.05.2013
 *
 * @method   array|object list()
 *           list(string $portId = null, \Scalr\Service\OpenStack\Services\Network\Type\ListPortsFilter|array $filter = null)
 *           ListPorts action (GET /ports[/port-id])
 *           Lists all ports to which the tenant has access.
 *
 * @method   object create()
 *           create(\Scalr\Service\OpenStack\Services\Network\Type\CreatePort|array $request)
 *           This operation creates a new Quantum port. The network where the port is created must
 *           be specified in the network_id attribute in the request body. You can also specify the
 *           following optional attributes.
 *
 * @method   object update()
 *           update(string $portId, array|object $options)
 *           Update port action (PUT /ports/port-id)
 *           You can use this operation to update information for a port, such as its symbolic name and
 *           associated IPs. When you update IPs for a port, the previously associated IPs are removed,
 *           returned to the respective subnets allocation pools, and replaced by the IPs specified in the
 *           body for the update request. Therefore, this operation replaces the fixed_ip attribute
 *           when it is specified in the request body. If the new IP addresses are not valid, for example,
 *           they are already in use, the operation fails and the existing IP addresses are not removed
 *           from the port.
 *
 * @method   bool delete()
 *           delete(string $portId)
 *           This operation removes a port from a Quantum network. If IP addresses are associated with
 *           the port, they are returned to the respective subnets allocation pools.
 */
class PortsHandler extends AbstractServiceHandler implements ServiceHandlerInterface
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceHandlerInterface::getServiceMethodAliases()
     */
    public function getServiceMethodAliases()
    {
        return array(
            'list'   => 'listPorts',
            'create' => 'createPort',
            'update' => 'updatePort',
            'delete' => 'deletePort',
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