<?php
namespace Scalr\Service\Aws\Rds\Handler;

use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupIngressRequestData;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupList;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsHandler;

/**
 * Amazon RDS DbSecurityGroupHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     22.03.2013
 */
class DbSecurityGroupHandler extends AbstractRdsHandler
{

    /**
     * Gets DBSecurityGroupData object from the EntityManager.
     *
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string                  $dBSecurityGroupName.
     * @return  DBSecurityGroupData|null Returns DBSecurityGroupData if it does exist in the cache or NULL otherwise.
     */
    public function get($dBSecurityGroupName)
    {
        return $this->getRds()->getEntityManager()->getRepository('Rds:DBSecurityGroup')->find($dBSecurityGroupName);
    }

    /**
     * DescribeDBSecurityGroups action
     *
     * Returns a list of DBSecurityGroup descriptions.
     * If a DBSecurityGroupName is specified, the list will contain
     * only the descriptions of the specified DBSecurityGroup.
     *
     * @param   string     $dBSecurityGroupName optional The name of the DB Security Group to return details for.
     * @param   string     $marker              optional Pagination token, provided by a previous request.
     * @param   string     $maxRecords          optional The maximum number of records to include in the response.
     * @return  DBSecurityGroupList             Returns the list of the DBSecurityGroupData
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describe($dBSecurityGroupName = null, $marker = null, $maxRecords = null)
    {
        return $this->getRds()->getApiHandler()->describeDBSecurityGroups($dBSecurityGroupName, $marker, $maxRecords);
    }

    /**
     * DeleteDBSecurityGroup action
     *
     * Deletes a DB Security Group.
     * Note! The specified DB Security Group must not be associated with any DB Instances.
     *
     * @param   string     $dBSecurityGroupName The Name of the DB security group to delete.
     * @return  bool       Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function delete($dBSecurityGroupName)
    {
        return $this->getRds()->getApiHandler()->deleteDBSecurityGroup($dBSecurityGroupName);
    }

    /**
     * CreateDBSecurityGroup
     *
     * Creates a new DB Security Group. DB Security Groups control access to a DB Instance
     *
     * @param   string     $name        The name for the DB Security Group. This value is stored as a lowercase string
     * @param   string     $description The description for the DB Security Group
     * @return  DBSecurityGroupData     Returns DBSecurityGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function create($name, $description)
    {
        return $this->getRds()->getApiHandler()->createDBSecurityGroup($name, $description);
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
        return $this->getRds()->getApiHandler()->authorizeDBSecurityGroupIngress($request);
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
        return $this->getRds()->getApiHandler()->revokeDBSecurityGroupIngress($request);
    }
}