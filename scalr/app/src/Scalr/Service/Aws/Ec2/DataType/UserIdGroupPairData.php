<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * UserIdGroupPairData
 *
 * Describes a security group and AWS account ID pair.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 */
class UserIdGroupPairData extends AbstractEc2DataType
{

    /**
     * The ID of an AWS account. Cannot be used when specifying a CIDR
     * IP address range.
     * @var string
     */
    public $userId;

    /**
     * The ID of the security group in the specified AWS account. Cannot
     * be used when specifying a CIDR IP address range.
     * @var string
     */
    public $groupId;

    /**
     * The name of the security group in the specified AWS account. Cannot
     * be used when specifying a CIDR IP address range.
     * @var string
     */
    public $groupName;

    /**
     * Constructor
     *
     * @param   string     $userId    optional The ID of an AWS account. Cannot be used when specifying a CIDR IP address range
     * @param   string     $groupId   optional The ID of the security group in the specified AWS account. Cannot be used when specifying a CIDR IP address range
     * @param   string     $groupName optional The name of the security group in the specified AWS account. Cannot be used when specifying a CIDR IP address range.
     */
    public function __construct($userId = null, $groupId = null, $groupName = null)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->groupId = $groupId;
        $this->groupName = $groupName;
    }
}