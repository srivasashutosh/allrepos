<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * RegisterImageData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.02.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingList $blockDeviceMapping Any block device mapping entries.
 */
class RegisterImageData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('blockDeviceMapping');

    /**
     * The Location of the AMI
     *
     * @var string
     */
    public $imageLocation;

    /**
     * The name of the AMI that was provided during image creation
     *
     * @var string
     */
    public $name;

    /**
     * The description of the AMI that was provided during image creation
     *
     * @var string
     */
    public $description;

    /**
     * The architecture of the image.
     * i386 | x86_64
     *
     * @var string
     */
    public $architecture;

    /**
     * The kernel associated with the image, if any. Only applicable for machine images.
     *
     * @var string
     */
    public $kernelId;

    /**
     * The RAM disk associated with the image, if any. Only applicable for machine images.
     *
     * @var string
     */
    public $ramdiskId;

    /**
     * The device name of the root device (e.g., /dev/sda1, or xvda).
     * @var string
     */
    public $rootDeviceName;

    /**
     * Convenient constructor
     *
     * @param   string                                              $name        The image name.
     * @param   BlockDeviceMappingList|BlockDeviceMappingData|array $blockDeviceMapping optional Block device mapping list
     */
    public function __construct($name, $blockDeviceMapping = null)
    {
        parent::__construct();
        $this->name = $name;
        $this->architecture = 'i386';
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