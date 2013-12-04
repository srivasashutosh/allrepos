<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * OptionGroupMembershipData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 */
class OptionGroupMembershipData extends AbstractRdsDataType
{

    /**
     * The name of the option group that the instance belongs to.
     *
     * @var string
     */
    public $optionGroupName;

    /**
     * The status of the DB Instance's option group membership
     * (e.g. in-sync, pending, pending-maintenance, applying).
     *
     * @var string
     */
    public $status;

    /**
     * Constructor
     *
     * @param   string     $optionGroupName optional The name of the option group that the instance belongs to.
     * @param   string     $status          optional The status of the DB Instance's option group membership
     */
    public function __construct($optionGroupName = null, $status = null)
    {
        parent::__construct();
        $this->optionGroupName = $optionGroupName;
        $this->status = $status;
    }
}