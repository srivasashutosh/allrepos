<?php
namespace Scalr\Service\OpenStack\Services\Network\Type;

use Scalr\Service\OpenStack\Type\AbstractInitType;

/**
 * FixedIp
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    09.05.2013
 */
class FixedIp extends AbstractInitType
{
    /**
     * IP Address
     *
     * @var string
     */
    public $ip_address;

    /**
     * Subnet ID
     *
     * @var string
     */
    public $subnet_id;

    /**
     * Constructor
     * @param   string     $ipAddress An IP Address
     * @param   string     $subnetId  The ID of the subnet
     */
    public function __construct($ipAddress = null, $subnetId = null)
    {
        $this->ip_address = $ipAddress;
        $this->subnet_id = $subnetId;
    }

    /**
     * Initializes a new FixedIp
     *
     * @param   string     $ipAddress An IP Address
     * @param   string     $subnetId  The ID of the subnet
     * @return  FixedIp
     */
    public static function init($ipAddress = null, $subnetId = null)
    {
        return call_user_func_array('parent::init', func_get_args());
    }
}