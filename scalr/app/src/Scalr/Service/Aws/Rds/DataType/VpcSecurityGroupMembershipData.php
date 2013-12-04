<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * VpcSecurityGroupMembershipData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 */
class VpcSecurityGroupMembershipData extends AbstractRdsDataType
{

    /**
     * The name of the VPC security group
     *
     * @var string
     */
    public $vpcSecurityGroupId;

    /**
     * The status of the VPC Security Group.
     *
     * @var string
     */
    public $status;

    /**
     * Constructor
     *
     * @param   string     $vpcSecurityGroupId optional The name of the VPC security group
     * @param   string     $status             optional The status of the VPC Security Group
     */
    public function __construct($vpcSecurityGroupId = null, $status = null)
    {
        parent::__construct();
        $this->vpcSecurityGroupId = $vpcSecurityGroupId;
        $this->status = $status;
    }
}