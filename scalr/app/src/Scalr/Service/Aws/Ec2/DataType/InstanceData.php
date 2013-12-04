<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * AWS Ec2 InstanceData (RunningInstancesItemType)
 *
 * Describes a running instance.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceStateData                      $instanceState       The current state of the instance.
 * @property \Scalr\Service\Aws\Ec2\DataType\ProductCodeSetList                     $productCodes        The product codes list attached to this instance.
 * @property \Scalr\Service\Aws\Ec2\DataType\PlacementResponseData                  $placement           The location where the instance launched.
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceMonitoringStateData            $monitoring          The monitoring information for the instance.
 * @property \Scalr\Service\Aws\Ec2\DataType\GroupList                              $groupSet            A list of security groups.
 * @property \Scalr\Service\Aws\Ec2\DataType\StateReasonData                        $stateReason         The reason for the most recent state transition.
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceBlockDeviceMappingResponseList $blockDeviceMapping  Any block device mapping entries for the instance.
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList                     $tagSet              Any tags assigned to the resource.
 * @property \Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceSetList        $networkInterfaceSet The network interfaces for the instance.
 * @property \Scalr\Service\Aws\Ec2\DataType\IamInstanceProfileResponseData         $iamInstanceProfile  The IAM Instance Profile (IIP) associated with the instance.
 *
 * @method   string                      getReservationId()      getReservationId()          Gets an associated Reservation ID.
 * @method   InstanceData                setReservationId()      setReservationId($id)       Sets an associated Reservation ID.
 */
class InstanceData extends AbstractEc2DataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('reservationId');

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'instanceState', 'productCodes', 'placement', 'monitoring', 'groupSet',
        'stateReason', 'blockDeviceMapping', 'tagSet', 'networkInterfaceSet', 'iamInstanceProfile',
    );

    /**
     * The ID of the instance launched.
     * @var string
     */
    public $instanceId;

    /**
     * The ID of the AMI used to launch the instance.
     * @var string
     */
    public $imageId;

    /**
     * The private DNS name assigned to the instance. This DNS name
     * can only be used inside the Amazon EC2 network. This element
     * remains empty until the instance enters a running state.
     * @var string
     */
    public $privateDnsName;

    /**
     * The public DNS name assigned to the instance. This DNS name is
     * contactable from outside the Amazon EC2 network. This element
     * remains empty until the instance enters a running state.
     * @var string
     */
    public $dnsName;

    /**
     * The reason for the most recent state transition.
     * This might be an empty string.
     * @var string
     */
    public $reason;

    /**
     * The key pair name, if this instance was launched with an associated key pair.
     * @var string
     */
    public $keyName;

    /**
     * The AMI launch index, which can be used to find this instance within the launch group.
     * @var string
     */
    public $amiLaunchIndex;

    /**
     * The instance type (for example, m1.small).
     * @var string
     */
    public $instanceType;

    /**
     * The time the instance was launched.
     * @var DateTime
     */
    public $launchTime;

    /**
     * The kernel associated with this instance.
     * @var string
     */
    public $kernelId;

    /**
     * The RAM disk associated with this instance.
     * @var string
     */
    public $ramdiskId;

    /**
     * The platform of the instance (e.g., Windows).
     * @var string
     */
    public $platform;

    /**
     * The subnet ID in which the instance is running.
     * @var string
     */
    public $subnetId;

    /**
     * The VPC in which the instance is running.
     * @var string
     */
    public $vpcId;

    /**
     * The private IP address assigned to the instance.
     * @var string
     */
    public $privateIpAddress;

    /**
     * The IP address of the instance
     * @var string
     */
    public $ipAddress;

    /**
     * Specifes whether to enable a Network Address Translation (NAT)
     * instance in a VPC to perform NAT. This controls whether
     * source/destination checking is enabled on the instance. A value of
     * true means checking is enabled, and false means checking is
     * disabled. The value must be false for the instance to perform NAT
     *
     * @var bool
     */
    public $sourceDestCheck;

    /**
     * The architecture of the image.
     * i386 | x86_64
     * @var string
     */
    public $architecture;

    /**
     * The root device type used by the AMI. The AMI can use an Amazon
     * EBS or instance store root device.
     * ebs | instance-store
     * @var string
     */
    public $rootDeviceType;

    /**
     * The root device name (e.g., /dev/sda1).
     * @var string
     */
    public $rootDeviceName;

    /**
     * Whether this is a Spot Instance.
     * spot | blank (no value)
     * @var string
     */
    public $instanceLifecycle;

    /**
     * The ID of the Spot Instance request
     * @var string
     */
    public $spotInstanceRequestId;

    /**
     * The instance's virtualization type
     * @var string
     */
    public $virtualizationType;

    /**
     * The idempotency token you provided when you launched the instance.
     * @var string
     */
    public $clientToken;

    /**
     * The instance's hypervisor type.
     * ovm | xen
     * @var string
     */
    public $hypervisor;

    /**
     * Whether the instance is optimized for EBS I/O.
     * This optimization provides dedicated throughput to Amazon EBS and an optimized
     * configuration stack to provide optimal EBS I/O performance.
     * This optimization isn’t available with all instance types. Additional usage
     * charges apply when using an EBS Optimized instance.
     *
     * @var bool
     */
    public $ebsOptimized;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        if ($this->instanceId === null) {
            throw new Ec2Exception(sprintf(
                'instanceId property has not been initialized for the "%s" yet', get_class($this)
            ));
        }
    }

    /**
     * DescribeInstances action
     *
     * This action will refresh current InstanceData object with retrieved information from AWS.
     *
     * Describes one or more of your instances.
     * If you specify one or more instance IDs, Amazon EC2 returns information for those instances.
     * If you do not specify instance IDs, Amazon EC2 returns information for all relevant instances.
     * If you specify an invalid instance ID, an error is returned.
     * If you specify an instance that you do not own, it is not included in the returned results.
     * Recently terminated instances might appear in the returned results.
     * This interval is usually less than one hour.
     *
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  InstanceData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $ret = null;
        $this->throwExceptionIfNotInitialized();
        /* @var $reservation ReservationData */
        $reservation = $this->getEc2()->instance->describe($this->instanceId)->get(0);
        if (!$this->getEc2()->getEntityManagerEnabled()) {
            /* @var $instance InstanceData */
            foreach ($reservation->instancesSet as $instance) {
                if ($this->instanceId == $instance->instanceId) {
                    $ret = $instance;
                    break;
                }
            }
        } else {
            $ret = $this;
        }
        return $ret;
    }

    /**
     * DescribeInstanceStatus action
     *
     * @return  InstanceStatusData|null       Returns InstanceStatusData object or NULL
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeStatus()
    {
        $this->throwExceptionIfNotInitialized();
        $list = $this->getEc2()->instance->describeStatus($this->instanceId, true);
        return $list instanceof InstanceStatusList && !empty($list[0]) ? $list[0] : null;
    }

    /**
     * TerminateInstances
     *
     * Shuts down one or more instances. This operation is idempotent; if you terminate an instance more than
     * once, each call will succeed.
     * Terminated instances will remain visible after termination (approximately one hour).
     *
     * Note! By default, Amazon EC2 deletes all Amazon EBS volumes that were attached when the instance
     * launched. Amazon EBS volumes attached after instance launch continue running.
     * You can stop, start, and terminate EBS-backed instances.You can only terminate S3-backed instances.
     * What happens to an instance differs if you stop it or terminate it. For example, when you stop an instance,
     * the root device and any other devices attached to the instance persist. When you terminate an instance,
     * the root device and any other devices attached during the instance launch are automatically deleted.
     *
     * @return  InstanceStateChangeList Returns the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function terminate()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->instance->terminate($this->instanceId);
    }

    /**
     * RebootInstances action
     *
     * Requests a reboot of one or more instances. This operation is asynchronous; it only queues a request
     * to reboot the specified instance(s). The operation will succeed if the instances are valid and belong to
     * you. Requests to reboot terminated instances are ignored.
     *
     * Note! If a Linux/UNIX instance does not cleanly shut down within four minutes, Amazon EC2 will
     * perform a hard reboot.
     *
     * @return  bool         Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function reboot()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->instance->reboot($this->instanceId);
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
     * @param   string     $device      The device name as exposed to the instance
     * @return  AttachmentSetResponseData Returns AttachmentSetResponseData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attachVolume($volumeId, $device)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->volume->attach($volumeId, $this->instanceId, $device);
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
    public function detachVolume($volumeId, $device = null, $force = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->volume->detach($volumeId, $this->instanceId, $device, $force);
    }

    /**
     * CreateTags action
     *
     * Adds or overwrites one or more tags for the specified EC2 resource or resources. Each resource can
     * have a maximum of 10 tags. Each tag consists of a key and optional value. Tag keys must be unique per
     * resource.
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createTags($tagList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->tag->create($this->instanceId, $tagList);
    }

    /**
     * DeleteTags action
     *
     * Deletes a specific set of tags from a specific set of resources. This call is designed to follow a
     * DescribeTags call. You first determine what tags a resource has, and then you call DeleteTags with
     * the resource ID and the specific tags you want to delete.
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteTags($tagList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->tag->delete($this->instanceId, $tagList);
    }

    /**
     * GetConsoleOutput action
     *
     * Retrieves console output for the specified instance.
     * Instance console output is buffered and posted shortly after instance boot, reboot, and termination.
     * Amazon EC2 preserves the most recent 64 KB output which will be available for at least one hour after
     * the most recent post.
     *
     * @return  GetConsoleOutputResponseData  Returns object which represents console output.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function getConsoleOutput()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->instance->getConsoleOutput($this->instanceId);
    }

    /**
     * AttachNetworkInterface action
     *
     * Attaches a network interface to an instance.
     *
     * @param   string       $networkInterfaceId The ID of the network interface
     * @param   int          $deviceIndex        The index of the device for the network interface attachment.
     * @return  string  Returns Attachment ID on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attachNetworkInterface($networkInterfaceId, $deviceIndex)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->networkInterface->attach($networkInterfaceId, $this->instanceId, $deviceIndex);
    }

    /**
     * ModifyInstanceAttribute action
     *
     * Modifies the specified attribute of the specified instance.
     * You can specify only one attribute at a time.
     * To modify some attributes, the instance must be stopped.
     *
     * @param   InstanceAttributeType|string $attribute  The attribute name.
     * @param   mixed                        $value      The attribute value can be string, boolean,
     *                                                   array or object depends on attribute name.
     * @return  bool                         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \BadFunctionCallException
     */
    public function modifyAttribute($attribute, $value)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->instance->modifyAttribute($this->instanceId, $attribute, $value);
    }

    /**
     * StopInstances action
     *
     * Stops an Amazon EBS-backed instance. Each time you transition an instance from stopped to started,
     * we charge a full instance hour, even if transitions happen multiple times within a single hour.
     *
     * Important!
     * Although Spot Instances can use Amazon EBS-backed AMIs, they don't support Stop/Start. In
     * other words, you can't stop and start Spot Instances launched from an AMI with an Amazon EBS
     * root device.
     *
     * Instances that use Amazon EBS volumes as their root devices can be quickly stopped and started.When
     * an instance is stopped, the compute resources are released and you are not billed for hourly instance
     * usage. However, your root partition Amazon EBS volume remains, continues to persist your data, and
     * you are charged for Amazon EBS volume usage.You can restart your instance at any time.
     *
     * Note!
     * Before stopping an instance, make sure it is in a state from which it can be restarted. Stopping
     * an instance does not preserve data stored in RAM.
     * Performing this operation on an instance that uses an instance store as its root device returns
     * an error.
     *
     * You can stop, start, and terminate EBS-backed instances.You can only terminate S3-backed instances.
     * What happens to an instance differs if you stop it or terminate it. For example, when you stop an instance,
     * the root device and any other devices attached to the instance persist. When you terminate an instance,
     * the root device and any other devices attached during the instance launch are automatically deleted
     *
     * @param   bool         $force          optional
     *          Forces the instance to stop. The instance will not have an
     *          opportunity to flush file system caches or file system
     *          metadata. If you use this option, you must perform file
     *          system check and repair procedures. This option is not
     *          recommended for Windows instances.
     *
     * @return  InstanceStateChangeList  Return the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function stop($force = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->instance->stop($this->instanceId, $force);
    }

    /**
     * StartInstances action
     *
     * Starts an Amazon EBS-backed AMI that you've previously stopped.
     *
     * Instances that use Amazon EBS volumes as their root devices can be quickly stopped and started.
     * When an instance is stopped, the compute resources are released and you are not billed for hourly instance
     * usage. However, your root partition Amazon EBS volume remains, continues to persist your data, and
     * you are charged for Amazon EBS volume usage. You can restart your instance at any time. Each time
     * you transition an instance from stopped to started, we charge a full instance hour, even if transitions
     * happen multiple times within a single hour.
     *
     * Note! Before stopping an instance, make sure it is in a state from which it can be restarted.
     * Stopping an instance does not preserve data stored in RAM.
     * Performing this operation on an instance that uses an instance store as its root device returns
     * an error.
     *
     * @return  InstanceStateChangeList  Return the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function start()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->instance->start($this->instanceId);
    }

    /**
     * MonitorInstances action
     *
     * Enables monitoring for a running instance.
     *
     * @return  MonitorInstancesResponseSetList  Returns the MonitorInstancesResponseSetList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function monitor()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->instance->monitor($this->instanceId);
    }

    /**
     * UnmonitorInstances action
     *
     * Disables monitoring for a running instance.
     *
     * @return  MonitorInstancesResponseSetList  Returns the MonitorInstancesResponseSetList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function unmonitor()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->instance->unmonitor($this->instanceId);
    }
}