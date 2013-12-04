<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * PlacementResponseData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 */
class PlacementResponseData extends AbstractEc2DataType
{

    /**
     * The Availability Zone of the instance.
     * @var string
     */
    public $availabilityZone;

    /**
     * The name of the placement group the instance is in (for cluster compute instances).
     * @var string
     */
    public $groupName;

    /**
     * The tenancy of the instance (if the instance is running within a VPC).
     * An instance with a tenancy of dedicated runs on single-tenant hardware.
     * @var string
     */
    public $tenancy;

    /**
     * Constructor
     *
     * @param   string     $availabilityZone optional The Availability Zone of the instance.
     * @param   string     $groupName        optional The name of the placement group the instance is in (for cluster compute instances).
     * @param   string     $tenancy          optional The tenancy of the instance (if the instance is running within a VPC).
     */
    public function __construct($availabilityZone = null, $groupName = null, $tenancy = null)
    {
        parent::__construct();
        $this->availabilityZone = $availabilityZone;
        $this->groupName = $groupName;
        $this->tenancy = $tenancy;
    }
}