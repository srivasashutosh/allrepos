<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DBSecurityGroupData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    22.03.2013
 *
 * @property \Scalr\Service\Aws\Rds\DataType\EC2SecurityGroupList $eC2SecurityGroups
 *           A list of EC2SecurityGroupData objects
 *
 * @property \Scalr\Service\Aws\Rds\DataType\IPRangeList $iPRanges
 *           A list of IPRangeData objects
 */
class DBSecurityGroupData extends AbstractRdsDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'eC2SecurityGroups', 'iPRanges'
    );

    /**
     * Provides the description of the DB Security Group
     *
     * @var string
     */
    public $dBSecurityGroupDescription;

    /**
     * Specifies the name of the DB Security Group
     *
     * @var string
     */
    public $dBSecurityGroupName;

    /**
     * Provides the AWS ID of the owner of a specific DB Security Group
     *
     * @var string
     */
    public $ownerId;

    /**
     * Provides the VpcId of the DB Security Group.
     *
     * @var string
     */
    public $vpcId;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Rds.AbstractRdsDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->dBSecurityGroupName === null) {
            throw new RdsException(sprintf(
                'DBSecurityGroupName has not been initialized for "%s" yet', get_class($this)
            ));
        }
    }

    /**
     * DescribeDBSecurityGroups action
     *
     * Refreshes description of the object using request to Amazon.
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * If not, solution is to use $object = object->refresh() instead.
     *
     * @return  DBSecurityGroupList             Returns the list of the DBSecurityGroupData
     * @throws  ClientException
     * @throws  RdsException
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbSecurityGroup->describe($this->dBSecurityGroupName)->get(0);
    }

    /**
     * DeleteDBSecurityGroup action
     *
     * Deletes a DB Security Group.
     * Note! The specified DB Security Group must not be associated with any DB Instances.
     *
     * @return  bool       Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbSecurityGroup->delete($this->dBSecurityGroupName);
    }

    /**
     * AuthorizeDBSecurityGroupIngress action
     *
     * Enables ingress to a DBSecurityGroup using one of two forms of authorization.
     * First, EC2 or VPC Security Groups can be added to the DBSecurityGroup
     * if the application using the database is running on EC2 or VPC instances.
     * Second, IP ranges are available if the application accessing your database is running on
     * the Internet. Required parameters for this API are one of CIDR range,
     * EC2SecurityGroupId for VPC, or (EC2SecurityGroupOwnerId and either EC2SecurityGroupName or
     * EC2SecurityGroupId for non-VPC).
     *
     * Note! You cannot authorize ingress from an EC2 security group in one Region to an Amazon RDS DB
     * Instance in another.You cannot authorize ingress from a VPC security group in one VPC to an
     * Amazon RDS DB Instance in another.
     *
     * @param   DBSecurityGroupIngressRequestData $request
     * @return  DBSecurityGroupData     Returns DBSecurityGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function authorizeIngress(DBSecurityGroupIngressRequestData $request)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbSecurityGroup->authorizeIngress($request);
    }

    /**
     * RevokeDBSecurityGroupIngress action
     *
     * Revokes ingress from a DBSecurityGroup for previously
     * authorized IP ranges or EC2 or VPC Security Groups.
     * Required parameters for this API are one of CIDRIP,
     * EC2SecurityGroupId for VPC, or (EC2SecurityGroupOwnerId and
     * either EC2SecurityGroupName or EC2SecurityGroupId).
     *
     * @param   DBSecurityGroupIngressRequestData $request
     * @return  DBSecurityGroupData     Returns DBSecurityGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function revokeIngress(DBSecurityGroupIngressRequestData $request)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbSecurityGroup->revokeIngress($request);
    }

    /**
     * Gets new DBSecurityGroupIngressRequestData object for the current DB Security Group
     *
     * @return   DBSecurityGroupIngressRequestData
     */
    public function getIngressRequest()
    {
        $this->throwExceptionIfNotInitialized();
        return new DBSecurityGroupIngressRequestData($this->dBSecurityGroupName);
    }
}