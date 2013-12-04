<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * GroupData
 *
 * The GroupItemType data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 */
class GroupData extends AbstractEc2DataType
{

    /**
     * The ID of the security group.
     * In API versions before 2011-01-01, this field returned the name of the security group.
     * @var string
     */
    public $groupId;

    /**
     * The name of the security group.
     * @var string
     */
    public $groupName;

    /**
     * Constructor
     *
     * @param   string     $groupName optional The name of the security group.
     * @param   string     $groupId   optional The ID of the security group.
     */
    public function __construct($groupName = null, $groupId = null)
    {
        parent::__construct();
        $this->groupId = $groupId;
        $this->groupName = $groupName;
    }
}