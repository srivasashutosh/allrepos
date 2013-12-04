<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * ImageFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    23.01.2013
 */
class ImageFilterNameType extends StringType
{

    /**
     * Filters the response based on a specific tag/value combination
     *
     * To instantiate the object with the tag:Anything we need to use following construction
     * InstanceFilterNameType::tag('Anything');
     */
    const TYPE_TAG_NAME = 'tag:Name';

    /**
     * The instance architecture.
     * i386 | x86_64
     */
    const TYPE_ARCHITECTURE = 'architecture';

    /**
     * Whether the Amazon EBS volume is deleted on instance termination.
     */
    const TYPE_BLOCK_DEVICE_MAPPING_DELETE_ON_TERMINATION = 'block-device-mapping.delete-on-termination';

    /**
     * The device name (for example, /dev/sdh) for the Amazon EBS volume.
     */
    const TYPE_BLOCK_DEVICE_MAPPING_DEVICE_NAME = 'block-device-mapping.device-name';

    /**
     * The ID of the snapshot used for the Amazon EBS volume.
     */
    const TYPE_BLOCK_DEVICE_MAPPING_SNAPSHOT_ID = 'block-device-mapping.snapshot-id';

    /**
     * The volume size of the Amazon EBS volume, in GiB.
     * Type: Integer
     */
    const TYPE_BLOCK_DEVICE_MAPPING_VOLUME_SIZE = 'block-device-mapping.volume-size';

    /**
     * The volume type of the Amazon EBS volume.
     * Valid values: standard | io1
     */
    const TYPE_BLOCK_DEVICE_MAPPING_VOLUME_TYPE = 'block-device-mapping.volume-type';

    /**
     * The description of the image (provided during image creation).
     */
    const TYPE_DESCRIPTION = 'description';

    /**
     * The ID of the image.
     */
    const TYPE_IMAGE_ID = 'image-id';

    /**
     * The image type.
     * Valid values: machine | kernel | ramdisk
     */
    const TYPE_IMAGE_TYPE = 'image-type';

    /**
     * Whether the image is public
     * Type: boolean
     */
    const TYPE_IS_PUBLIC = 'is-public';

    /**
     * The kernel ID
     */
    const TYPE_KERNEL_ID = 'kernel-id';

    /**
     * The location of the image manifest
     */
    const TYPE_MANIFEST_LOCATION = 'manifest-location';

    /**
     * The name of the AMI (provided during image creation).
     */
    const TYPE_NAME = 'name';

    /**
     * The AWS account alias (for example, amazon).
     */
    const TYPE_OWNER_ALIAS = 'owner-alias';

    /**
     * The AWS account ID of the image owner.
     */
    const TYPE_OWNER_ID = 'owner-id';

    /**
     * The platform. To only list Windows-based AMIs, use windows. Otherwise, leave blank.
     * Valid value: windows
     */
    const TYPE_PLATFORM = 'platform';

    /**
     * The product code
     */
    const TYPE_PRODUCT_CODE = 'product-code';

    /**
     * The type of product code.
     * Valid values: devpay | marketplace
     */
    const TYPE_PRODUCT_CODE_TYPE = 'product-code.type';

    /**
     * The RAM disk ID.
     */
    const TYPE_RAMDISK_ID = 'ramdisk-id';

    /**
     * The name of the root device volume (for example, /dev/sda1).
     */
    const TYPE_ROOT_DEVICE_NAME = 'root-device-name';

    /**
     * The type of the root device volume
     * Valid values: ebs | instance-store
     */
    const TYPE_ROOT_DEVICE_TYPE = 'root-device-type';

    /**
     * The state of the image.
     * Valid values: available | pending | failed
     */
    const TYPE_STATE = 'state';

    /**
     * The reason code for the state change.
     */
    const TYPE_STATE_REASON_CODE = 'state-reason-code';

    /**
     * A message that describes the state change.
     */
    const TYPE_STATE_REASON_MESSAGE = 'state-reason-message';

    /**
     * The key of a tag assigned to the resource.
     * This filter is independent of the tag-value filter.
     * For example, if you use both the filter "tag-key=Purpose" and the filter "tag-value=X",
     * you get any resources assigned both the tag key Purpose (regardless of what the tag's value is),
     * and the tag value X (regardless of what the tag's key is). If you want to list only resources where
     * Purpose is X, see the tag:key filter
     */
    const TYPE_TAG_KEY = 'tag-key';

    /**
     * The value of a tag assigned to the resource. This filter is independent of the tag-key filter.
     */
    const TYPE_TAG_VALUE = 'tag-value';

    /**
     * The virtualization type
     * Valid values: paravirtual | hvm
     */
    const TYPE_VIRTUALIZATION_TYPE = 'virtualization-type';

    /**
     * The hypervisor type.
     * Valid values: ovm | xen
     */
    const TYPE_HYPERVISOR = 'hypervisor';


    public static function getPrefix()
    {
        return 'TYPE_';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::validate()
     */
    protected function validate()
    {
        return preg_match('#^tag\:.+#', $this->value) ?: parent::validate();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::__callstatic()
     */
    public static function __callStatic($name, $args)
    {
        $class = __CLASS__;
        if ($name == 'tag') {
            if (!isset($args[0])) {
                throw new \InvalidArgumentException(sprintf(
                    'Tag name must be provided! Please use %s::tag("symbolic-name")', $class
                ));
            }
            return new $class('tag:' . $args[0]);
        }
        return parent::__callStatic($name, $args);
    }
}