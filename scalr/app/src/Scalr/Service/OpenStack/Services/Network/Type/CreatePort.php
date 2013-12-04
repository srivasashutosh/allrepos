<?php
namespace Scalr\Service\OpenStack\Services\Network\Type;

use Scalr\Service\OpenStack\Type\BooleanType;

use Scalr\Service\OpenStack\Type\AbstractInitType;

/**
 * CreatePort
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    09.05.2013
 */
class CreatePort extends AbstractInitType
{

    /**
     * The ID of the network
     *
     * @var string
     */
    public $network_id;

    /**
     * Admin state
     *
     * @var bool
     */
    public $admin_state_up;

    /**
     * Device ID
     *
     * @var string
     */
    public $device_id;

    /**
     * Port name
     *
     * @var string
     */
    public $name;

    /**
     * Device owner
     *
     * @var string
     */
    public $device_owner;

    /**
     * The list of FixedIp objects
     *
     * @var array
     */
    public $fixed_ips;

    /**
     * ID
     *
     * @var string
     */
    public $id;

    /**
     * MAC Address
     *
     * @var string
     */
    public $mac_address;

    /**
     * ID of the tenant
     *
     * @var string
     */
    public $tenant_id;

    /**
     * Constructor
     *
     * @param   string     $networkId The ID of the network
     */
    public function __construct($networkId = null)
    {
        $this->network_id = $networkId;
    }

    /**
     * Initializes a new CreatePort
     *
     * @param   string     $networkId The ID of the network
     * @return  CreatePort
     */
    public static function init($networkId = null)
    {
        return call_user_func_array('parent::init', func_get_args());
    }
}