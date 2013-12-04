<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * NetworkInterfaceAssociationData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.04.2013
 */
class NetworkInterfaceAssociationData extends AbstractEc2DataType
{

    /**
     * The address of the Elastic IP address bound to the network interface
     *
     * @var string
     */
    public $publicIp;

    /**
     * The public DNS name.
     *
     * @var string
     */
    public $publicDnsName;

    /**
     * The ID of the Elastic IP address owner
     *
     * @var string
     */
    public $ipOwnerId;

    /**
     * The allocation ID.
     *
     * @var string
     */
    public $allocationId;

    /**
     * The association ID.
     *
     * @var string
     */
    public $associationId;
}