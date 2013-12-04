<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * AssociateAddressRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    29.01.2013
 */
class AssociateAddressRequestData extends AbstractEc2DataType
{

    /**
     * The ID of the instance the address is associated with (if any).
     *
     * @var string
     */
    public $instanceId;

    /**
     * The public IP address
     *
     * @var string
     */
    public $publicIp;

    /**
     * The ID representing the allocation of the address for use with Amazon VPC.
     *
     * @var string
     */
    public $allocationId;

    /**
     * The ID of the network interface.
     *
     * @var string
     */
    public $networkInterfaceId;

    /**
     * Private IP Address
     *
     * @var string
     */
    public $privateIpAddress;

    /**
     * Allows an Elastic IP address that is already associated
     * with another network interface or instance to be
     * re-associated with the specified instance or interface. If
     * the Elastic IP address is associated, and this option is not
     * specified, the operation fails. Available for VPC Elastic IP
     * addresses only. (Default: false)
     *
     * @var bool
     */
    public $allowReassociation;

    /**
     * Constructor
     *
     * @param   string     $instanceId optional The instance to associate with the IP address.
     * @param   string     $publicIp   optional The Elastic IP address to assign to the instance.
     *                                          Condition: Required for EC2 Elastic IP addresses.
     */
    public function __construct($instanceId = null, $publicIp = null)
    {
        parent::__construct();
        $this->publicIp = $publicIp;
        $this->instanceId = $instanceId;
    }
}