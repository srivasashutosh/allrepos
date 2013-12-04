<?php
namespace Scalr\Service\OpenStack\Services;

use Scalr\Service\OpenStack\OpenStack;
use Scalr\Service\OpenStack\Services\Volume\Handler\SnapshotsHandler;
use Scalr\Service\OpenStack\Exception\OpenStackException;
use Scalr\Service\OpenStack\Client\RestClientResponse;
use Scalr\Service\OpenStack\Services\Volume\V1\VolumeApi;
/**
 * OpenStack Volumes (Rackspace Cloud Block Storage)
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    18.12.2012
 *
 * @property \Scalr\Service\OpenStack\Services\Volume\Handler\SnapshotsHandler $snapshots
 *           Gets a Snapshots service interface handler.
 *
 * @method   \Scalr\Service\OpenStack\Services\Volume\V1\VolumeApi getApiHandler()
 *           getApiHandler()
 *           Gets an Volume API handler for the specific version
 */
class VolumeService extends AbstractService implements ServiceInterface
{

    const VERSION_V1 = 'V1';

    const VERSION_DEFAULT = self::VERSION_V1;

    /**
     * Miscellaneous cache
     * @var array
     */
    private $cache;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceInterface::getType()
     */
    public static function getType()
    {
        return OpenStack::SERVICE_VOLUME;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceInterface::getVersion()
     */
    public function getVersion()
    {
        return self::VERSION_DEFAULT;
    }

    /**
     * List Volumes action
     *
     * View a list of Volume entities.
     *
     * @param   bool  $detailed  optional Detailed info
     * @return  array Returns the list of volumes
     * @throws  RestClientException
     */
    public function listVolumes($detailed = true)
    {
        return $this->getApiHandler()->listVolumes();
    }

    /**
     * Create Volume action.
     *
     * @param   int        $size             The size (in GB) of the volume.
     * @param   string     $name             optional The display name of the volume.
     * @param   string     $desc             optional A description of the volume.
     * @param   string     $snapshotId       optional A snapshot ID from which to create a volume.
     * @param   VolumeType|string $type      optional The type of volume to create. (SATA by default)
     * @param   array      $metadata         optional Metadata key-value pairs.
     * @param   string     $availabilityZone optional An availability zone.
     * @return  object     Returns created volume
     * @throws  RestClientException
     */
    public function createVolume($size, $name = null, $desc = null, $snapshotId = null, $type = null, array $metadata = null,
                                 $availabilityZone = null)
    {
        return $this->getApiHandler()->createVolume($size, $name, $desc, $snapshotId, $type, $metadata, $availabilityZone);
    }

    /**
     * Delete Volume action
     *
     * @param   string    $volumeId A volume ID.
     * @return  bool      Returns true on success
     * @throws  RestClientException
     */
    public function deleteVolume($volumeId)
    {
        return $this->getApiHandler()->deleteVolume($volumeId);
    }

    /**
     * Show Volume action
     *
     * @param   string    $volumeId A volume ID.
     * @return  object    Returns information about specified single volume
     * @throws  RestClientException
     */
    public function getVolume($volumeId)
    {
        return $this->getApiHandler()->getVolume($volumeId);
    }

    /**
     * List Volume Types action
     *
     * @return  array  Returns the list of volume types.
     * @throws  RestClientException
     */
    public function listVolumeTypes()
    {
        return $this->getApiHandler()->listVolumeTypes();
    }

    /**
     * Describe Volume Type action
     *
     * @param   int    $typeId  A Volume Type Id.
     * @return  object Returns information about volume type.
     * @throws  RestClientException
     */
    public function getVolumeType($typeId)
    {
        return $this->getApiHandler()->getVolumeType($typeId);
    }

    /**
     * List Snapshots action
     *
     * @param   bool      $detailed  optional  Detailed list by default.
     * @return  array     Returns the list of snapshots.
     * @throws  RestClientException
     */
    public function listShanpshots($detailed = true)
    {
        return $this->getApiHandler()->listShanpshots($detailed);
    }

    /**
     * Create Snapshot action.
     *
     * @param   string     $volumeId    A volume ID.
     * @param   string     $name        optional The voulume display name.
     * @param   string     $description optional The volume display description.
     * @param   bool       $force       optional Indicate whether to snapshot, even if the volume is attached. (false by default)
     * @return  object     Returns snapshot object
     * @throws  RestClientException
     */
    public function createSnapshot($volumeId, $name = null, $description = null, $force = null)
    {
        return $this->getApiHandler()->createSnapshot($volumeId, $name, $description, $force);
    }

    /**
     * Show Snapshot action.
     *
     * View all information about a single Snapshot.
     *
     * @param   string     $snapshotId  A snapshot ID.
     * @return  object     Returns information about snapshot.
     * @throws  RestClientException
     */
    public function getSnapshot($snapshotId)
    {
        return $this->getApiHandler()->getSnapshot($snapshotId);
    }

    /**
     * Delete Snapshot action.
     *
     * @param   string     $snapshotId  A snapshot ID.
     * @return  bool       Returns true on success
     * @throws  RestClientException
     */
    public function deleteSnapshot($snapshotId)
    {
        return $this->getApiHandler()->deleteSnapshot($snapshotId);
    }
}