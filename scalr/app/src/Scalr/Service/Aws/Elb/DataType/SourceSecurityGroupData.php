<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * SourceSecurityGroupData
 *
 * This data type is used as a response element in the DescribeLoadBalancers action. For information
 * about Elastic Load Balancing security groups, go to Using Security Groups With Elastic Load Balancing
 * in the Elastic Load Balancing Developer Guide.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class SourceSecurityGroupData extends AbstractElbDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName'
    );

    /**
     * Name of the source security group. Use this value for the --source-group parameter
     * of the ec2-authorize command in the Amazon EC2 command line tool.
     *
     * @var string
     */
    public $groupName;

    /**
     * Owner of the source security group. Use this value for the --source-group-user
     * parameter of the ec2-authorize command in the Amazon EC2 command line tool.
     *
     * @var string
     */
    public $ownerAlias;
}