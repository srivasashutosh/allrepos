<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DBSecurityGroupMembershipData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 */
class DBSecurityGroupMembershipData extends AbstractRdsDataType
{

    /**
     * The name of the DB Security Group.
     *
     * @var string
     */
    public $dBSecurityGroupName;

    /**
     * The status of the DB Security Group.
     *
     * @var string
     */
    public $status;

    /**
     * Constructor
     *
     * @param   string     $dBSecurityGroupName optional The name of the DB Security Group.
     * @param   string     $status              optional The status of the DB Security Group.
     */
    public function __construct($dBSecurityGroupName = null, $status = null)
    {
        parent::__construct();
        $this->dBSecurityGroupName = $dBSecurityGroupName;
        $this->status = $status;
    }
}