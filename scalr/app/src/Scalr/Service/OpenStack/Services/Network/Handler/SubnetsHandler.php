<?php
namespace Scalr\Service\OpenStack\Services\Network\Handler;

use Scalr\Service\OpenStack\Services\Network\Type\CreateSubnet;
use Scalr\Service\OpenStack\Services\Network\Type\ListSubnetsFilter;
use Scalr\Service\OpenStack\Services\NetworkService;
use Scalr\Service\OpenStack\Services\ServiceHandlerInterface;
use Scalr\Service\OpenStack\Services\AbstractServiceHandler;
/**
 * SubnetsHandler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.05.2013
 *
 * @method   array|object list()
 *           list(string $subnetId = null, \Scalr\Service\OpenStack\Services\Network\Type\ListSubnetsFilter|array $filter = null)
 *           ListSubnets action (GET /subnets[/subnet-id])
 *           Lists all subnets that are accessible to the tenant who submits the request.
 *
 * @method   object create()
 *           create(\Scalr\Service\OpenStack\Services\Network\Type\CreateSubnet|array $request)
 *           This operation creates a new subnet on the specified network
 *           Usage:
 *           $rs->newtork->subnets->create(CreateSubnet::init($networkId, $cidr));
 *           or
 *           $rs->newtork->subnets->create(array('network_id' => $networkId, ...));
 *
 * @method   object update()
 *           update(string $subnetId, array|object $options)
 *           Updates the specified subnet
 *
 * @method   bool delete()
 *           deleteSubnet(string $subnetId)
 *           Removes the specified subnet
 */
class SubnetsHandler extends AbstractServiceHandler implements ServiceHandlerInterface
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceHandlerInterface::getServiceMethodAliases()
     */
    public function getServiceMethodAliases()
    {
        return array(
            'list'   => 'listSubnets',
            'create' => 'createSubnet',
            'update' => 'updateSubnet',
            'delete' => 'deleteSubnet',
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