<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceAttributeType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.04.2013
 */
class InstanceAttributeType extends StringType
{

    /**
     * The instance type
     */
    const TYPE_INSTANCE_TYPE = 'instanceType';

    /**
     * The ID of the kernel associated with the instance
     */
    const TYPE_KERNEL = 'kernel';

    /**
     * The ID of the RAM disk associated with the instance
     */
    const TYPE_RAMDISK = 'ramdisk';

    /**
     * MIME, Base64-encoded user data provided to the instance
     */
    const TYPE_USER_DATA = 'userData';

    /**
     * Whether the instance can be terminated using the Amazon EC2 API
     */
    const TYPE_DISABLE_API_TERMINATION = 'disableApiTermination';

    /**
     * Whether the instance stops or terminates when an instance
     * shutdown is initiated (default is stop)
     */
    const TYPE_INSTANCE_INITIATED_SHUTDOWN_BEHAVIOR = 'instanceInitiatedShutdownBehavior';

    /**
     * The name of the root device volume
     */
    const TYPE_ROOT_DEVICE_NAME = 'rootDeviceName';

    /**
     * The block device mapping
     */
    const TYPE_BLOCK_DEVICE_MAPPING = 'blockDeviceMapping';

    /**
     * This attribute exists to enable a Network Address Translation (NAT) instance in
     * a VPC to perform NAT. The attribute controls whether source/destination checking is enabled on the
     * instance. A value of true means checking is enabled. The value must be false for the instance to
     * perform NAT
     */
    const TYPE_SOURCE_DEST_CHECK = 'sourceDestCheck';

    /**
     * The security groups the instance belongs to.
     */
    const TYPE_GROUP_SET = 'groupSet';

    /**
     * The product codes associated with the instance.
     * Each product code contains a product code and a type
     */
    const TYPE_PRODUCT_CODES = 'productCodes';

    /**
     * Whether the instance is optimized for EBS I/O
     */
    const TYPE_EBS_OPTIMIZED = 'ebsOptimized';

    public static function getPrefix()
    {
        return 'TYPE_';
    }
}