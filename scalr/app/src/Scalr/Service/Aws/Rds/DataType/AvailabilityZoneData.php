<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * AvailabilityZoneData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 */
class AvailabilityZoneData extends AbstractRdsDataType
{

    /**
     * The name of the availability zone.
     *
     * @var string
     */
    public $name;

    /**
     * True indicates the availability zone
     * is capable of provisioned IOPs
     *
     * @var bool
     */
    public $provisionedIopsCapable;

    /**
     * Constructor
     *
     * @param   string     $name                   optional The name of the availability zone.
     * @param   bool       $provisionedIopsCapable optional True indicates the availability zone
     *                                             is capable of provisioned IOPs
     */
    public function __construct($name = null, $provisionedIopsCapable = null)
    {
        parent::__construct();
        $this->name = $name;
        $this->provisionedIopsCapable = $provisionedIopsCapable;
    }
}