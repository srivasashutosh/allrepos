<?php
namespace Scalr\Service\OpenStack\Services\Servers\Handler;

use Scalr\Service\OpenStack\Services\Servers\Type\ListImagesFilter;
use Scalr\Service\OpenStack\Services\ServersService;
use Scalr\Service\OpenStack\Services\ServiceHandlerInterface;
use Scalr\Service\OpenStack\Services\AbstractServiceHandler;

/**
 * Servers ImagesHandler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    17.12.2012
 *
 * @method   array  list()       list($detailed = true, ListImagesFilter $filter = null) Gets a lists of images associated with the account.
 * @method   object get()        get($imageId)                                           Gets an image detailed info object.
 * @method   bool   delete()     delete($imageId)                                        Removes specified image.
 * @method   string create()     create($serverId, $name, array $metadata = null)        Creates a new image for a specific server. Returns the ID to the newly created image
 */
class ImagesHandler extends AbstractServiceHandler implements ServiceHandlerInterface
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceHandlerInterface::getServiceMethodAliases()
     */
    public function getServiceMethodAliases()
    {
        return array(
            'list'       => 'listImages',
            'get'        => 'getImage',
            'delete'     => 'deleteImage',
            'create'     => 'createImage'
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