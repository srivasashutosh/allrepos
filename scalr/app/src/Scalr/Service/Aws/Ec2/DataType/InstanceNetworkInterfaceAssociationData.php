<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceNetworkInterfaceAssociationData
 *
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 */
class InstanceNetworkInterfaceAssociationData extends AbstractEc2DataType
{

    /**
     * The address of the Elastic IP address bound to the network interface.
     * @var string
     */
    public $publicIp;

    /**
     * The ID of the Elastic IP address owner
     * @var string
     */
    public $ipOwnerId;
}