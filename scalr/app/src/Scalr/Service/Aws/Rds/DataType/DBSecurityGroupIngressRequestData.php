<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DBSecurityGroupIngressRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.03.2013
 */
class DBSecurityGroupIngressRequestData extends AbstractRdsDataType
{

    /**
     * The IP range to authorize.
     *
     * @var string
     */
    public $cIDRIP;

    /**
     * Specifies the name of the DB Security Group
     *
     * @var string
     */
    public $dBSecurityGroupName;

    /**
     * Id of the EC2 Security Group to authorize.
     *
     * For VPC DB Security Groups, EC2SecurityGroupId must be provided.
     * Otherwise, EC2SecurityGroupOwnerId and either EC2SecurityGroupName or EC2SecurityGroupId must be provided.
     *
     * @var string
     */
    public $eC2SecurityGroupId;

    /**
     * Name of the EC2 Security Group to authorize.
     * For VPC DB Security Groups, EC2SecurityGroupId must be provided.
     * Otherwise, EC2SecurityGroupOwnerId and either EC2SecurityGroupName or EC2SecurityGroupId must be provided.
     *
     * @var string
     */
    public $eC2SecurityGroupName;

    /**
     * AWS Account Number of the owner of the EC2 Security Group specified in the
     * EC2SecurityGroupName parameter. The AWS Access Key ID is not an acceptable value. For VPC
     * DB Security Groups, EC2SecurityGroupId must be provided. Otherwise, EC2SecurityGroupOwnerId
     * and either EC2SecurityGroupName or EC2SecurityGroupId must be provided.
     *
     * @var string
     */
    public $eC2SecurityGroupOwnerId;

    /**
     * Construct
     *
     * @param   string     $groupName The name of the DB Security Group to revoke/authorize ingress from/to
     */
    public function __construct($groupName)
    {
        parent::__construct();
        $this->dBSecurityGroupName = $groupName;
    }
}