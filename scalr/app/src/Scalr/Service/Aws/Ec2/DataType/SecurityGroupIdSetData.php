<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * SecurityGroupIdSetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 */
class SecurityGroupIdSetData extends AbstractEc2DataType
{

    /**
     * The ID of the security group associated with the network interface.
     * @var string
     */
    public $groupId;

    /**
     * Constructor
     *
     * @param   string   $groupId optional The ID of the security group associated with the network interface.
     */
    public function __construct($groupId = null)
    {
        parent::__construct();
        $this->groupId = $groupId;
    }
}