<?php
namespace Scalr\Service\OpenStack\Services\Network\Type;

use Scalr\Service\OpenStack\Type\BooleanType;

use Scalr\Service\OpenStack\Type\AbstractInitType;

/**
 * CreateSubnet
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    09.05.2013
 */
class CreateSubnet extends AbstractInitType
{

    /**
     * The ID of the network
     *
     * @var string
     */
    public $network_id;

    /**
     * The version of the IP Protocol
     *
     * @var string
     */
    public $ip_version;

    /**
     * CIDR
     *
     * @var string
     */
    public $cidr;

    /**
     * Allocation pools
     *
     * @var array
     */
    public $allocation_pools;

    /**
     * @var boolean
     */
    public $enable_dhcp;

    /**
     * The Gateway IP
     *
     * @var string
     */
    public $gateway_ip;

    /**
     * The Host routes
     * @var array
     */
    public $host_routes;

    /**
     * The DNS nameservers
     *
     * @var array
     */
    public $dns_nameservers;

    /**
     * ID
     *
     * @var string
     */
    public $id;

    /**
     * The name
     *
     * @var string
     */
    public $name;

    /**
     * The ID ot the tenant
     *
     * @var string
     */
    public $tenant_id;

    /**
     * Constructor
     * @param   string     $networkId The ID of the network
     * @param   string     $cidr      The CIDR
     * @param   int        $ipVersion The version of the IP protocol
     */
    public function __construct($networkId = null, $cidr = null, $ipVersion = 4)
    {
        $this->ip_version = $ipVersion;
        $this->network_id = $networkId;
        $this->cidr = $cidr;
    }

    /**
     * Initializes a new CreateSubnet
     *
     * @param   string     $networkId The ID of the network
     * @param   string     $cidr      The CIDR
     * @param   int        $ipVersion The version of the IP protocol
     * @return  CreateSubnet
     */
    public static function init($networkId = null, $cidr = null, $ipVersion = 4)
    {
        return call_user_func_array('parent::init', func_get_args());
    }
}