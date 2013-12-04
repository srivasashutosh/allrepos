<?php
namespace Scalr\Service\OpenStack\Services\Volume\Handler;

use Scalr\Service\OpenStack\Services\VolumeService;
use Scalr\Service\OpenStack\Services\ServiceHandlerInterface;
use Scalr\Service\OpenStack\Services\AbstractServiceHandler;

/**
 * Volume Snapshots Handler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    18.12.2012
 * @method   array     list()    list($detailed = true)                      Gets a list of snapshots.
 * @method   object    create()  create($volumeId, $name = null, $description = null, $force = null) Creates snapshot of a volume.
 * @method   object    get()     get($snapshotId)                            Gets detailed information about single snapshot.
 * @method   bool      delete()  delete($snapshotId)                         Removes requested snapshot.
 */
class SnapshotsHandler extends AbstractServiceHandler implements ServiceHandlerInterface
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceHandlerInterface::getServiceMethodAliases()
     */
    public function getServiceMethodAliases()
    {
        return array(
            'list'   => 'listShanpshots',
            'create' => 'createSnapshot',
            'get'    => 'getSnapshot',
            'delete' => 'deleteSnapshot',
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.AbstractServiceHandler::getService()
     * @return  VolumeService
     */
    public function getService()
    {
        return parent::getService();
    }
}