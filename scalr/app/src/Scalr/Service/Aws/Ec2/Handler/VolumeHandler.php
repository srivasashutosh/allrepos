<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\AttachmentSetResponseData;
use Scalr\Service\Aws\Ec2\DataType\CreateVolumeRequestData;
use Scalr\Service\Aws\Ec2\DataType\VolumeFilterData;
use Scalr\Service\Aws\Ec2\DataType\VolumeFilterList;
use Scalr\Service\Aws\Ec2\DataType\VolumeList;
use Scalr\Service\Aws\Ec2\DataType\VolumeData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * VolumeHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     21.01.2013
 */
class VolumeHandler extends AbstractEc2Handler
{

    /**
     * Gets VolumeData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string                    $volumeId  Unique Identifier.
     * @return  \Scalr\Service\Aws\Ec2\DataType\VolumeData|null    Returns VolumeData if it does exist in the cache or NULL otherwise.
     */
    public function get($volumeId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:Volume')->find($volumeId);
    }

    /**
     * DescribeVolumes action
     *
     * Describes one or more of your Amazon EBS volumes.
     *
     * @param   ListDataType|array|string               $volumeIdList optional The list of Volume ID
     * @param   VolumeFilterList|VolumeFilterData|array $filter       optional The filter list
     * @return  VolumeList       Returns the list of the volumes
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($volumeIdList = null, $filter = null)
    {
        if ($volumeIdList !== null && !($volumeIdList instanceof ListDataType)) {
            $volumeIdList = new ListDataType($volumeIdList);
        }
        if ($filter !== null && !($filter instanceof VolumeFilterList)) {
            $filter = new VolumeFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeVolumes($volumeIdList, $filter);
    }

    /**
     * CreateVolume action
     *
     * Creates an Amazon EBS volume that can be attached to any Amazon EC2 instance in the same Availability Zone.
     * Any AWS Marketplace product codes from the snapshot are propagated to the volume
     *
     * @param   CreateVolumeRequestData  $request Request that specifies parameters of the volume.
     * @return  VolumeData       Returns the VolumeData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create(CreateVolumeRequestData $request)
    {
        return $this->getEc2()->getApiHandler()->createVolume($request);
    }

    /**
     * DeleteVolume action
     *
     * Deletes an Amazon EBS volume. The volume must be in the available state (not attached to an instance)
     *
     * @param   string       $volumeId The ID of the volume.
     * @return  bool         Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($volumeId)
    {
        return $this->getEc2()->getApiHandler()->deleteVolume($volumeId);
    }

    /**
     * AttachVolume action
     *
     * Attaches an Amazon EBS volume to a running instance and exposes it to the instance with the specified
     * device name.
     *
     * For a list of supported device names, see Attaching the Volume to an Instance. Any device names that
     * aren't reserved for instance store volumes can be used for Amazon EBS volumes.
     *
     * Note! If a volume has an AWS Marketplace product code:
     *  -The volume can only be attached to the root device of a stopped instance.
     *
     *  -You must be subscribed to the AWS Marketplace code that is on the volume.
     *
     *  -The configuration (instance type, operating system) of the instance must support that specific
     *   AWS Marketplace code. For example, you cannot take a volume from a Windows instance
     *   and attach it to a Linux instance.
     *
     *  -AWS Marketplace product codes are copied from the volume to the instance.
     *
     * @param   string     $volumeId    The ID of the Amazon EBS volume. The volume and instance must be
     *                                  within the same Availability Zone
     * @param   string     $instanceId  The ID of the Instance. The instance must be running.
     * @param   string     $device      The device name as exposed to the instance
     * @return  AttachmentSetResponseData Returns AttachmentSetResponseData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attach($volumeId, $instanceId, $device)
    {
        return $this->getEc2()->getApiHandler()->attachVolume($volumeId, $instanceId, $device);
    }

    /**
     * DetachVolume action
     *
     * Detaches an Amazon EBS volume from an instance. Make sure to unmount any file systems on the
     * device within your operating system before detaching the volume. Failure to do so will result in volume
     * being stuck in "busy" state while detaching.
     *
     * Note! If an Amazon EBS volume is the root device of an instance, it cannot be detached while the
     * instance is in the "running" state. To detach the root volume, stop the instance first.
     * If the root volume is detached from an instance with an AWS Marketplace product code, then
     * the AWS Marketplace product codes from that volume are no longer associated with the instance.
     *
     * @param   string     $volumeId    The ID of the EBS volume.
     * @param   string     $instanceId  optional The ID of the Instance.
     * @param   string     $device      optional The device name.
     * @param   bool       $force       optional Forces detachment if the previous detachment attempt did
     *                                  not occur cleanly (logging into an instance, unmounting
     *                                  the volume, and detaching normally). This option can lead
     *                                  to data loss or a corrupted file system. Use this option only
     *                                  as a last resort to detach a volume from a failed instance.
     *                                  The instance won't have an opportunity to flush file system
     *                                  caches or file system metadata. If you use this option, you
     *                                  must perform file system check and repair procedures.
     * @return  AttachmentSetResponseData Returns AttachmentSetResponseData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detach($volumeId, $instanceId = null, $device = null, $force = null)
    {
        return $this->getEc2()->getApiHandler()->detachVolume($volumeId, $instanceId, $device, $force);
    }
}