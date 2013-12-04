<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * CreateImageRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingList $blockDeviceMapping Any block device mapping entries.
 * @method   \Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingList getBlockDeviceMapping() getBlockDeviceMapping() Gets block device mapping entries.
 */
class CreateImageRequestData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('blockDeviceMapping');

    /**
     * The ID of the AMI (Amazon Machine Image)
     *
     * @var string
     */
    public $imageId;

    /**
     * The name of the AMI that was provided during image creation
     * Constraints: 3-128 alphanumeric characters, parenthesis
     * (()), commas (,), slashes (/), dashes (-), or underscores(_)
     *
     * @var string
     */
    public $name;

    /**
     * The description of the AMI that was provided during image creation
     * Constraints: Up to 255 characters
     *
     * @var string
     */
    public $description;

    /**
     * The ID of the instance
     *
     * @var string
     */
    public $instanceId;

    /**
     * By default this parameter is set to false, which means
     * Amazon EC2 attempts to cleanly shut down the instance
     * before image creation and reboots the instance afterwards.
     * When the parameter is set to true, Amazon EC2 does
     * not shut down the instance before creating the image.
     * When this option is used, file system integrity on the
     * created image cannot be guaranteed
     *
     * @var bool
     */
    public $noReboot;

    /**
     * Convenient constructor
     *
     * @param   string                                              $instanceId  The ID of the instance.
     * @param   string                                              $name        The image name.
     * @param   BlockDeviceMappingList|BlockDeviceMappingData|array $blockDeviceMapping optional Block device mapping list
     */
    public function __construct($instanceId, $name, $blockDeviceMapping = null)
    {
        parent::__construct();
        $this->instanceId = $instanceId;
        $this->name = $name;
        $this->setBlockDeviceMapping($blockDeviceMapping);
    }

    /**
     * Sets block device mapping list
     *
     * @param   BlockDeviceMappingList|BlockDeviceMappingData|array $blockDeviceMapping Block Device Mapping List
     * @return  CreateImageRequestData
     */
    public function setBlockDeviceMapping($blockDeviceMapping)
    {
        if ($blockDeviceMapping !== null && !($blockDeviceMapping instanceof BlockDeviceMappingList)) {
            $blockDeviceMapping = new BlockDeviceMappingList($blockDeviceMapping);
        }
        return $this->__call(__FUNCTION__, array($blockDeviceMapping));
    }
}