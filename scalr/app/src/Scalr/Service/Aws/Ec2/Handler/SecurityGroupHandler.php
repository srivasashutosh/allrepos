<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\IpPermissionData;
use Scalr\Service\Aws\Ec2\DataType\IpPermissionList;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupFilterData;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupList;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupFilterList;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * SecurityGroupHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.12.2012
 */
class SecurityGroupHandler extends AbstractEc2Handler
{

    /**
     * Gets SecurityGroupData object from the EntityManager.
     *
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string                    $groupId Identifier.
     * @return  \Scalr\Service\Aws\Ec2\DataType\SecurityGroupData|null    Returns SecurityGroupData if it does exist in the cache or NULL otherwise.
     */
    public function get($groupId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:SecurityGroup')->find($groupId);
    }

    /**
     * DescribeSecurityGroups action
     *
     * Describes one or more of your security groups.
     * This includes both EC2 security groups and VPC security groups
     *
     * @param   ListDataType|array|string                             $groupName optional One or more security group names.
     * @param   ListDataType|array|string                             $groupId   optional One or more security group IDs.
     * @param   SecurityGroupFilterList|SecurityGroupFilterData|array $filter    optional The name/value pairs list for the filter.
     * @return  \Scalr\Service\Aws\Ec2\DataType\SecurityGroupList Returns SecurityGroupList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($groupName = null, $groupId = null, $filter = null)
    {
        if ($groupName !== null && !($groupName instanceof ListDataType)) {
            $groupName = new ListDataType($groupName);
        }
        if ($groupId !== null && !($groupId instanceof ListDataType)) {
            $groupId = new ListDataType($groupId);
        }
        if ($filter !== null && !($filter instanceof SecurityGroupFilterList)) {
            $filter = new SecurityGroupFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeSecurityGroups($groupName, $groupId, $filter);
    }

    /**
     * DeleteSecurityGroup action
     *
     * Deletes a security group. This action applies to both EC2 security groups and VPC security groups.
     * For information about VPC security groups and how they differ from EC2 security groups, see Security Groups
     * in the Amazon Virtual Private Cloud User Guide.
     *
     * Note! If you attempt to delete a security group that contains instances, or attempt to delete a security
     * group that is referenced by another security group, an error is returned. For example, if security
     * group B has a rule that allows access from security group A, security group A cannot be deleted
     * until the rule is removed.
     *
     * The fault returned is InvalidGroup.InUse for EC2 security groups, or DependencyViolation
     * for VPC security groups.
     *
     * @param   string     $groupId        optional The ID of the security group to remove.
     * @param   string     $groupName      optional The name of security group to remove.
     * @return  bool       Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($groupId = null, $groupName = null)
    {
        return $this->getEc2()->getApiHandler()->deleteSecurityGroup($groupId, $groupName);
    }

    /**
     * CreateSecurityGroup action
     *
     * Creates a new security group.You can create either an EC2 security group (which works only with EC2),
     * or a VPC security group (which works only with Amazon Virtual Private Cloud). The two types of groups
     * have different capabilities
     *
     * When you create a security group, you give it a friendly name of your choice.You can have an EC2
     * security group with the same name as a VPC security group (each group has a unique security group ID
     * separate from the name). Two standard groups can't have the same name, and two VPC groups can't
     * have the same name.
     * If you don't specify a security group when you launch an instance, the instance is launched into the default
     * security group. This group (and only this group) includes a default rule that gives the instances in the
     * group unrestricted network access to each other. You have a default EC2 security group for instances
     * you launch with EC2 (i.e., outside a VPC), and a default VPC security group for instances you launch in
     * your VPC.
     *
     * @param   string       $groupName        The name of the security group.
     * @param   string       $groupDescription A description of the security group. This is information only.
     * @param   string       $vpcId            optional The ID of the VPC. (Required for VPC security groups)
     * @return  string       Returns ID of the created security group on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($groupName, $groupDescription, $vpcId = null)
    {
        return $this->getEc2()->getApiHandler()->createSecurityGroup($groupName, $groupDescription, $vpcId);
    }

    /**
     * AuthorizeSecurityGroupIngress action
     *
     * Adds one or more ingress rules to a security group. This action applies to both EC2 security groups and
     * VPC security groups.
     *
     * For EC2 security groups, this action gives one or more CIDR IP address ranges permission to access a
     * security group in your account, or gives one or more security groups (called the source groups) permission
     * to access a security group in your account. A source group can be in your own AWS account, or another.
     *
     * For VPC security groups, this action gives one or more CIDR IP address ranges permission to access a
     * security group in your VPC, or gives one or more other security groups (called the source groups)
     * permission to access a security group in your VPC. The groups must all be in the same VPC.
     *
     * Each rule consists of the protocol (e.g., TCP), plus either a CIDR range or a source group. For the TCP
     * and UDP protocols, you must also specify the destination port or port range. For the ICMP protocol, you
     * must also specify the ICMP type and code.You can use -1 for the type or code to mean all types or all
     * codes.
     *
     * Rule changes are propagated to instances within the security group as quickly as possible. However, a
     * small delay might occur.
     *
     * @param   IpPermissionList|IpPermissionData|array $ipPermissions Ip permission list
     * @param   string                                  $groupId       optional The ID of the EC2 or VPC security group to modify.
     *                                                                 The group must belong to your account.
     * @param   string                                  $groupName     optional The name of the EC2 security group to modify.
     *                                                                 It can be used instead of group ID for EC2 security groups.
     * @return  bool             Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function authorizeIngress($ipPermissions, $groupId = null, $groupName = null)
    {
        if (!($ipPermissions instanceof IpPermissionList)) {
            $ipPermissions = new IpPermissionList($ipPermissions);
        }
        return $this->getEc2()->getApiHandler()->authorizeSecurityGroupIngress($ipPermissions, $groupId, $groupName);
    }

    /**
     * RevokeSecurityGroupIngress action
     *
     * This action applies to both EC2 security groups and VPC security groups.
     * This action removes one or more ingress rules from a security group. The values that you specify in the
     * revoke request (e.g., ports, etc.) must match the existing rule's values for the rule to be removed.
     *
     * Each rule consists of the protocol and the CIDR range or source security group. For the TCP and UDP
     * protocols, you must also specify the destination port or range of ports. For the ICMP protocol, you must
     * also specify the ICMP type and code.
     *
     * Rule changes are propagated to instances within the security group as quickly as possible. However,
     * depending on the number of instances, a small delay might occur
     *
     * @param   IpPermissionList|IpPermissionData|array $ipPermissions Ip permission list object
     * @param   string                                  $groupId       optional The ID of the EC2 or VPC security group to modify.
     *                                                                 The group must belong to your account.
     * @param   string                                  $groupName     optional The name of the EC2 security group to modify.
     *                                                                 It can be used instead of group ID for EC2 security groups.
     * @return  bool Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function revokeIngress($ipPermissions, $groupId = null, $groupName = null)
    {
        if (!($ipPermissions instanceof IpPermissionList)) {
            $ipPermissions = new IpPermissionList($ipPermissions);
        }
        return $this->getEc2()->getApiHandler()->revokeSecurityGroupIngress($ipPermissions, $groupId, $groupName);
    }

    /**
     * AuthorizeSecurityGroupEgress action
     *
     * Adds one or more egress rules to a security group for use with a VPC.
     * Specifically, this action permits instances to send traffic to one or more
     * destination CIDR IP address ranges, or to one or more destination security groups for the same VPC.
     *
     * Important!
     * You can have up to 50 rules per group (covering both ingress and egress rules).
     *
     * A security group is for use with instances either in the EC2-Classic platform or in a specific VPC.
     * This action doesn't apply to security groups for EC2-Classic.
     *
     * Each rule consists of the protocol (for example, TCP), plus either a CIDR range or a source group.
     * For the TCP and UDP protocols, you must also specify the destination port or port range.
     * For the ICMP protocol, you must also specify the ICMP type and code.
     * You can use -1 for the type or code to mean all types or all codes.
     *
     * Rule changes are propagated to affected instances as quickly as possible.
     * However, a small delay might occur.
     *
     * @param   IpPermissionList|IpPermissionData|array $ipPermissions
     *          Ip permission list object
     *
     * @param   string $groupId optional
     *          The ID of the security group to modify.
     *
     * @return  bool Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function authorizeEgress($ipPermissions, $groupId)
    {
        if (!($ipPermissions instanceof IpPermissionList)) {
            $ipPermissions = new IpPermissionList($ipPermissions);
        }
        return $this->getEc2()->getApiHandler()->authorizeSecurityGroupEgress($ipPermissions, $groupId);
    }

    /**
     * RevokeSecurityGroupEgress action
     *
     * Removes one or more egress rules from a security group for EC2-VPC.
     * The values that you specify in the revoke request (for example, ports)
     * must match the existing rule's values for the rule to be revoked.
     *
     * Each rule consists of the protocol and the CIDR range or destination security group.
     * For the TCP and UDP protocols, you must also specify the destination port or range of ports.
     * For the ICMP protocol, you must also specify the ICMP type and code.
     *
     * Rule changes are propagated to instances within the security group as quickly as possible.
     * However, a small delay might occur.
     *
     * @param   IpPermissionList|IpPermissionData|array $ipPermissions
     *          Ip permission list
     *
     * @param   string $groupId optional
     *          The ID of the security group to modify.
     *
     * @return  bool   Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function revokeEgress($ipPermissions, $groupId)
    {
        if (!($ipPermissions instanceof IpPermissionList)) {
            $ipPermissions = new IpPermissionList($ipPermissions);
        }
        return $this->getEc2()->getApiHandler()->revokeSecurityGroupEgress($ipPermissions, $groupId);
    }
}