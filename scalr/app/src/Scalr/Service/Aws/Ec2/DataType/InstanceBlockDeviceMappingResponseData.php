<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceBlockDeviceMappingResponseData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\EbsInstanceBlockDeviceMappingResponseData $ebs                Parameters used to automatically set up Amazon EBS volumes when the instance is launched.
 */
class InstanceBlockDeviceMappingResponseData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('ebs');

    /**
     * The device name exposed to the instance (for example, /dev/sdh, or xvdh).
     * @var string
     */
    public $deviceName;

    /**
     * The virtual device name
     * @var string
     */
    public $virtualName;
}