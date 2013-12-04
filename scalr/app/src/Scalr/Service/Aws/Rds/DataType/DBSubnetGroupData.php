<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DBSubnetGroupData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 *
 * @property \Scalr\Service\Aws\Rds\DataType\SubnetList $subnets
 *           A list of Subnet elements.
 */
class DBSubnetGroupData extends AbstractRdsDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'subnets',
    );

    /**
     * Provides the description of the DB Subnet Group.
     *
     * @var string
     */
    public $dBSubnetGroupDescription;

    /**
     * Specifies the name of the DB Subnet Group..
     *
     * @var string
     */
    public $dBSubnetGroupName;

    /**
     * Provides the status of the DB Subnet Group
     *
     * @var string
     */
    public $subnetGroupStatus;

    /**
     * Provides the VpcId of the DB Subnet Group
     *
     * @var string
     */
    public $vpcId;
}