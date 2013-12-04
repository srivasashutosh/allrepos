<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * EC2SecurityGroupData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    22.03.2013
 */
class EC2SecurityGroupData extends AbstractRdsDataType
{

    const STATUS_AUTHORIZING = 'authorizing';

    const STATUS_AUTHORIZED = 'authorized';

    const STATUS_REVOKING = 'revoking';

    const STATUS_REVOKED = 'revoked';

    /**
     * Specifies the id of the EC2 Security Group.
     *
     * @var string
     */
    public $eC2SecurityGroupId;

    /**
     * Specifies the name of the EC2 Security Group
     *
     * @var string
     */
    public $eC2SecurityGroupName;

    /**
     * Specifies the AWS ID of the owner of the EC2
     * security group specified in the EC2SecurityGroupName field.
     *
     * @var string
     */
    public $eC2SecurityGroupOwnerId;

    /**
     * Provides the status of the EC2 security group.
     * Status can be "authorizing", "authorized", "revoking", and "revoked".
     *
     * @var string
     */
    public $status;
}