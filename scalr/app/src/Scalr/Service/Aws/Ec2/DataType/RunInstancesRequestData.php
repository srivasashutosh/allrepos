<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * RunInstancesRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    16.01.2013
 *
 * @method   \Scalr\Service\Aws\DataType\ListDataType getSecurityGroupId()
 *           getSecurityGroupId()
 *           Gets a security group IDs.
 *
 * @method   \Scalr\Service\Aws\DataType\ListDataType getSecurityGroup()
 *           getSecurityGroup()
 *           Gets a list of security group names.
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\PlacementResponseData getPlacement()
 *           getPlacement()
 *           Gets a Placement object.
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingList getBlockDeviceMapping()
 *           getBlockDeviceMapping()
 *           Gets a block device mapping list.
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\MonitoringInstanceData getMonitoring()
 *           getMonitoring()
 *           Gets an monitoring instance data.
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceSetRequestList getNetworkInterface()
 *           getNetworkInterface()
 *           Gets an network interfaces list
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\IamInstanceProfileRequestData getIamInstanceProfile()
 *           getIamInstanceProfile()
 *           Gets an IamInstanceProfileRequestData
 */
class RunInstancesRequestData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'securityGroupId', 'securityGroup', 'placement', 'blockDeviceMapping' , 'monitoring',
        'networkInterface', 'iamInstanceProfile'
    );

    /**
     * The ID of the AMI. (required)
     *
     * @var string
     */
    public $imageId;

    /**
     * The minimum number of instances to launch. (required)
     * If the value is more than Amazon EC2 can launch, no instances are
     * launched at all.
     *
     * @var int
     */
    public $minCount;

    /**
     * The maximum number of instances to launch. (required)
     * If the value is more than Amazon EC2 can launch, the largest possible
     * number above MinCount will be launched instead.
     *
     * @var int
     */
    public $maxCount;

    /**
     * The name of the key pair to use.
     *
     * @var string
     */
    public $keyName;

    /**
     * The Base64-encoded MIME user data to be made available to the instance(s) in this reservation.
     *
     * @var string
     */
    public $userData;

    /**
     * The instance type.
     *
     * Valid values: t1.micro | m1.small | m1.medium | m1.large | m1.xlarge | c1.medium | c1.xlarge |
     * m2.xlarge | m2.2xlarge | m2.4xlarge | m3.xlarge | m3.2xlarge | hi1.4xlarge | cc1.4xlarge |
     * cg1.4xlarge | cc2.8xlarge
     *
     * @var string
     */
    public $instanceType;

    /**
     * The ID of the kernel with which to launch the instance.
     *
     * @var string
     */
    public $kernelId;

    /**
     * The ID of the RAM disk. Some kernels require additional
     * drivers at launch.
     *
     * @var string
     */
    public $ramdiskId;

    /**
     * If you're using Amazon Virtual Private Cloud, this specifies
     * the ID of the subnet you want to launch the instance into.
     *
     * @var string
     */
    public $subnetId;

    /**
     * Whether you can terminate the instance using the EC2 API.
     * A value of true means you can't terminate the instance using the API (i.e., the instance is "locked");
     * a value of false means you can. If you set this to true, and you later want to terminate the instance, you must first
     * change the disableApiTermination attribute's value to false using ModifyInstanceAttribute.
     *
     * @var bool
     */
    public $disableApiTermination;

    /**
     * Whether the instance stops or terminates on instance-initiated shutdown.
     * Valid values: stop | terminate
     *
     * @var string
     */
    public $instanceInitiatedShutdownBehavior;

    /**
     * If you're using Amazon Virtual Private Cloud, you can optionally use this parameter
     * to assign the instance a specific available IP address from the subnet (e.g., 10.0.0.25) as the primary IP address.
     * Only one private IP address can be designated as primary.
     * Therefore, you cannot specify this parameter if you are
     * also specifying PrivateIpAddresses.n.Primary with a value of true with the
     * PrivateIpAddresses.n.PrivateIpAddress option.
     * Default: Amazon VPC selects an IP address from the subnet for the instance
     *
     * @var string
     */
    public $privateIpAddress;

    /**
     * Unique, case-sensitive identifier you provide to ensure idempotency of the request
     * Constraints: Maximum 64 ASCII characters
     *
     * @var string
     */
    public $clientToken;

    /**
     * Whether the instance is optimized for EBS I/O. This optimization provides dedicated
     * throughput to Amazon EBS and an optimized configuration stack to provide
     * optimal EBS I/O performance. This optimization isn't
     * available with all instance types. Additional usage charges
     * apply when using an EBS Optimized instance.
     *
     * @var bool
     */
    public $ebsOptimized;

    /**
     * Constructor
     *
     * @param   string     $imageId  The Id of the AMI.
     * @param   int        $minCount The minimum number of instances to launch.
     * @param   int        $maxCount The maximum number of instances to launch.
     */
    public function __construct($imageId, $minCount, $maxCount)
    {
        parent::__construct();
        $this->imageId = (string) $imageId;
        $this->minCount = (int) $minCount;
        $this->maxCount = (int) $maxCount;
    }

    /**
     * Sets securityGroupId list
     *
     * Condition: Required for VPC security groups; optional for EC2 security groups
     *
     * @param   ListDataType|array|string $securityGroupIdList
     * @return  RunInstancesRequestData
     */
    public function setSecurityGroupId($securityGroupIdList = null)
    {
        if ($securityGroupIdList !== null && !($securityGroupIdList instanceof ListDataType)) {
            $securityGroupIdList = new ListDataType($securityGroupIdList);
        }
        return $this->__call(__FUNCTION__, array($securityGroupIdList));
    }

    /**
     * Appends the ID of the Security Group to the request
     *
     * @param   string       $securityGroupId The ID of the security group.
     * @return  RunInstancesRequestData
     */
    public function appendSecurityGroupId($securityGroupId)
    {
        if ($this->getSecurityGroupId() === null) {
            $this->setSecurityGroupId(new ListDataType());
        }
        $this->getSecurityGroupId()->append((string)$securityGroupId);

        return $this;
    }

    /**
     * Sets securityGroup list
     *
     * Condition: Valid only for EC2 security groups; for EC2 groups either a group ID or a group name is accepted
     *
     * @param   ListDataType|array|string $securityGroupList
     * @return  RunInstancesRequestData
     */
    public function setSecurityGroup($securityGroupList = null)
    {
        if ($securityGroupList !== null && !($securityGroupList instanceof ListDataType)) {
            $securityGroupList = new ListDataType($securityGroupList);
        }
        return $this->__call(__FUNCTION__, array($securityGroupList));
    }

    /**
     * Appends the Name of the security group to the request
     *
     * @param   string       $securityGroup The Name of the security group.
     * @return  RunInstancesRequestData
     */
    public function appendSecurityGroup($securityGroup)
    {
        if ($this->getSecurityGroup() === null) {
            $this->setSecurityGroup(new ListDataType());
        }
        $this->getSecurityGroup()->append((string)$securityGroup);

        return $this;
    }

    /**
     * Sets placement object
     *
     * @param   PlacementResponseData $placement
     * @return  RunInstancesRequestData
     */
    public function setPlacement(PlacementResponseData $placement = null)
    {
        return $this->__call(__FUNCTION__, array($placement));
    }

    /**
     * Sets BlockDeviceMapping
     *
     * @param   BlockDeviceMappingList|BlockDeviceMappingData|array $blockDeviceMapping
     * @return  RunInstancesRequestData
     */
    public function setBlockDeviceMapping($blockDeviceMapping = null)
    {
        if ($blockDeviceMapping !== null && !($blockDeviceMapping instanceof BlockDeviceMappingList)) {
            $blockDeviceMapping = new BlockDeviceMappingList($blockDeviceMapping);
        }
        return $this->__call(__FUNCTION__, array($blockDeviceMapping));
    }

    /**
     * Appends block device mapping data to the request
     *
     * @param  BlockDeviceMappingData|array $blockDevice The block device data
     * @return RunInstancesRequestData
     */
    public function appendBlockDeviceMapping($blockDevice)
    {
        if ($blockDevice !== null) {
            if ($this->getBlockDeviceMapping() === null) {
                return $this->setBlockDeviceMapping($blockDevice);
            } else {
                $this->getBlockDeviceMapping()->append($blockDevice);
            }
        }
        return $this;
    }

    /**
     * Sets Monitoring
     *
     * @param   MonitoringInstanceData|boolean $monitoring
     *          MonitoringInstanceData or boolean value which means
     *          whether monitoring is enabled for the instance
     *
     * @return  RunInstancesRequestData
     */
    public function setMonitoring($monitoring = null)
    {
        if ($monitoring !== null && !($monitoring instanceof MonitoringInstanceData)) {
            $monitoring = new MonitoringInstanceData((bool)$monitoring);
        }
        return $this->__call(__FUNCTION__, array($monitoring));
    }

    /**
     * Sets IamInstanceProfile
     *
     * @param   IamInstanceProfileRequestData $iamInstanceProfile
     * @return  RunInstancesRequestData
     */
    public function setIamInstanceProfile(IamInstanceProfileRequestData $iamInstanceProfile = null)
    {
        return $this->__call(__FUNCTION__, array($iamInstanceProfile));
    }

    /**
     * Sets NetworkInterface
     *
     * @param   InstanceNetworkInterfaceSetRequestList|InstanceNetworkInterfaceSetRequestData|array $networkInterface
     * @return  RunInstancesRequestData
     */
    public function setNetworkInterface($networkInterface = null)
    {
        if ($networkInterface !== null && !($networkInterface instanceof InstanceNetworkInterfaceSetRequestList)) {
            $networkInterface = new InstanceNetworkInterfaceSetRequestList($networkInterface);
        }
        return $this->__call(__FUNCTION__, array($networkInterface));
    }
}