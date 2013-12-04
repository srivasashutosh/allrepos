<?php
namespace Scalr\Service\OpenStack\Services\Servers\Handler;

use Scalr\Service\OpenStack\Services\ServersService;
use Scalr\Service\OpenStack\Services\ServiceHandlerInterface;
use Scalr\Service\OpenStack\Services\AbstractServiceHandler;

/**
 * Servers KeypairsHandler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    17.12.2012
 *
 * @method   array  list()       list()                        Gets a lists of keypairs associated with the account.
 * @method   object get()        get($keypairName)             Gets a keypair by his name.
 * @method   object create()     create($name, $apiKey = null) Creates or imports keypair.
 * @method   bool   delete()     delete($name)                 Removes keypair by its name.
 */
class KeypairsHandler extends AbstractServiceHandler implements ServiceHandlerInterface
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceHandlerInterface::getServiceMethodAliases()
     */
    public function getServiceMethodAliases()
    {
        return array(
            'list'       => 'listKeypairs',
            'get'        => 'getKeypair',
            'create'     => 'createKeypair',
            'delete'     => 'deleteKeypair',
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