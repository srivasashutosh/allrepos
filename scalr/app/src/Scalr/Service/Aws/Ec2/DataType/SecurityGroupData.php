<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * SecurityGroupData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\IpPermissionList   $ipPermissions         A list of inbound rules associated with the security group.
 * @property \Scalr\Service\Aws\Ec2\DataType\IpPermissionList   $ipPermissionsEgress   A list of outbound rules associated with the security group
 *                                                                                     (for VPC security groups).
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList $tagSet                Any tags assigned to the resource
 */
class SecurityGroupData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('ipPermissions', 'ipPermissionsEgress', 'tagSet');

    /**
     * The AWS account ID of the owner of the security group.
     * @var string
     */
    public $ownerId;

    /**
     * The ID of the security group.
     * @var string
     */
    public $groupId;

    /**
     * The name of the security group.
     * @var string
     */
    public $groupName;

    /**
     * A description of the security group.
     * @var string
     */
    public $groupDescription;

    /**
     * The ID of the VPC the security group is in (for VPC security groups).
     * @var string
     */
    public $vpcId;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->groupName === null || $this->groupId === null) {
            throw new Ec2Exception(sprintf("groupName and groupId have not been initialized for the %s yet.", get_class($this)));
        }
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
     * @return  bool       Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->securityGroup->delete($this->groupId);
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
     * @return  bool             Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function authorizeIngress($ipPermissions)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->securityGroup->authorizeIngress($ipPermissions, $this->groupId);
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
     * @return  bool Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function authorizeEgress($ipPermissions)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->securityGroup->authorizeEgress($ipPermissions, $this->groupId);
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
     * @return  bool Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function revokeIngress($ipPermissions)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->securityGroup->revokeIngress($ipPermissions, $this->groupId);
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
     * @return  bool   Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function revokeEgress($ipPermissions)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->securityGroup->revokeEgress($ipPermissions, $this->groupId);
    }

    /**
     * DescribeSecurityGroups action
     *
     * Describes current security group refreshing its properties.
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  SecurityGroupData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->securityGroup->describe(null, $this->groupId)->get(0);
    }

    /**
     * CreateTags action
     *
     * Adds or overwrites one or more tags for the specified EC2 resource or resources. Each resource can
     * have a maximum of 10 tags. Each tag consists of a key and optional value. Tag keys must be unique per
     * resource.
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createTags($tagList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->tag->create($this->groupId, $tagList);
    }

    /**
     * DeleteTags action
     *
     * Deletes a specific set of tags from a specific set of resources. This call is designed to follow a
     * DescribeTags call. You first determine what tags a resource has, and then you call DeleteTags with
     * the resource ID and the specific tags you want to delete.
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteTags($tagList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->tag->delete($this->groupId, $tagList);
    }
}