<?php
namespace Scalr\Service\OpenStack\Services\Volume\V1;

use Scalr\Service\OpenStack\Services\Volume\Type\VolumeType;
use Scalr\Service\OpenStack\Exception\RestClientException;
use Scalr\Service\OpenStack\Client\RestClientResponse;
use Scalr\Service\OpenStack\Client\ClientInterface;
use Scalr\Service\OpenStack\Services\VolumeService;

/**
 * Volume API
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    18.12.2012
 */
class VolumeApi
{

    /**
     * @var VolumeService
     */
    protected $service;

    /**
     * Constructor
     *
     * @param   VolumeService $service A Volume service interface
     */
    public function __construct(VolumeService $service)
    {
        $this->service = $service;
    }

    /**
     * Gets HTTP Client
     *
     * @return  ClientInterface Returns HTTP Client
     */
    public function getClient()
    {
        return $this->service->getOpenStack()->getClient();
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
        $result = null;
        $options = array();
        $response = $this->getClient()->call(
            $this->service, '/volumes' . ($detailed ? '/detail' : ''), $options, 'GET'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->volumes;
        }
        return $result;
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
        $result = null;
        $volume = array(
            'size' => (int) $size,
        );
        if ($name !== null) {
            $volume['display_name'] = (string) $name;
        }
        if ($desc !== null) {
            $volume['display_description'] = (string) $desc;
        }
        if ($snapshotId !== null) {
            $volume['snapshot_id'] = (string) $snapshotId;
        }
        if ($type !== null) {
            $volume['volume_type'] = (string) $type;
        }
        if ($metadata !== null) {
            $volume['metadata'] = $metadata;
        }
        if ($availabilityZone !== null) {
            $volume['availability_zone'] = $availabilityZone;
        }
        $options = array(
            "volume" => $volume,
        );
        $response = $this->getClient()->call(
            $this->service, '/volumes', $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->volume;
        }
        return $result;
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
        $result = false;
        $response = $this->getClient()->call(
            $this->service, sprintf('/volumes/%s', $volumeId), null, 'DELETE'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
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
        $result = null;
        $response = $this->getClient()->call(
            $this->service, sprintf('/volumes/%s', $volumeId)
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->volume;
        }
        return $result;
    }

    /**
     * List Volume Types action
     *
     * @return  array  Returns the list of volume types.
     * @throws  RestClientException
     */
    public function listVolumeTypes()
    {
        $result = null;
        $response = $this->getClient()->call(
            $this->service, '/types'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->volume_types;
        }
        return $result;
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
        $result = null;
        $response = $this->getClient()->call(
            $this->service, sprintf('/types/%s', $typeId)
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->volume_type;
        }
        return $result;
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
        $result = null;
        $response = $this->getClient()->call(
            $this->service, '/snapshots' . ($detailed ? '/detail' : '')
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->snapshots;
        }
        return $result;
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
        $result = null;
        $snapshot = array(
            'volume_id' => (string) $volumeId,
        );
        if ($name !== null) {
            $snapshot['display_name'] = (string) $name;
        }
        if ($description !== null) {
            $snapshot['display_description'] = (string) $description;
        }
        if ($force !== null) {
            $snapshot['force'] = $force ? 'true' : 'false';
        }
        $options = array(
            'snapshot' => $snapshot,
        );
        $response = $this->getClient()->call(
            $this->service, '/snapshots', $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->snapshot;
        }
        return $result;
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
        $result = null;
        $response = $this->getClient()->call(
            $this->service, sprintf('/snapshots/%s', $snapshotId)
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->snapshot;
        }
        return $result;
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
        $result = false;
        $response = $this->getClient()->call(
            $this->service, sprintf('/snapshots/%s', $snapshotId), null, 'DELETE'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }
}