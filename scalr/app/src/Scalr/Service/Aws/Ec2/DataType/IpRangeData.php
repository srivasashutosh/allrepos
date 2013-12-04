<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * IpRangeData
 *
 * Describes an IP range.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 */
class IpRangeData extends AbstractEc2DataType
{

    /**
     * The CIDR range. Cannot be used when specifying a source security group.
     * @var string
     */
    public $cidrIp;

    /**
     * Convenient constructor
     *
     * @param   string   $cidrIp optional CIDR IP
     */
    public function __construct($cidrIp = null)
    {
        parent::__construct();
        $this->cidrIp = $cidrIp;
    }
}