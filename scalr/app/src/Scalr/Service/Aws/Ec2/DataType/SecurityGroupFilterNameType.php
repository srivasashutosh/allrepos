<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * SecurityGroupFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.12.2012
 */
class SecurityGroupFilterNameType extends StringType
{
    /**
     * The description of the security group.
     */
    const TYPE_DESCRIPTION = 'description';

    /**
     * The ID of the security group.
     */
    const TYPE_GROUP_ID = 'group-id';

    /**
     * The name of the security group.
     */
    const TYPE_GROUP_NAME = 'group-name';

    /**
     * The CIDR range that has been granted the permission.
     */
    const TYPE_IP_PERMISSION_CIDR = 'ip-permission.cidr';

    /**
     * The start of port range for the TCP and UDP protocols, or an ICMP type number.
     */
    const TYPE_IP_PERMISSION_FROM_PORT = 'ip-permission.from-port';

    /**
     * The name of security group that has been granted the permission.
     */
    const TYPE_IP_PERMISSION_GROUP_NAME = 'ip-permission.group-name';

    /**
     * The IP protocol for the permission.
     */
    const TYPE_IP_PERMISSION_PROTOCOL = 'ip-permission.protocol';

    /**
     * The end of port range for the TCP and UDP protocols, or an ICMP code.
     */
    const TYPE_IP_PERMISSION_TO_PORT = 'ip-permission.to-port';

    /**
     * The ID of an AWS account that has been granted the permission.
     */
    const TYPE_IP_PERMISSION_USER_ID = 'ip-permission.user-id';

    /**
     * The AWS account ID of the owner of the security group.
     */
    const TYPE_OWNER_ID = 'owner-id';

    /**
     * The key of a tag assigned to the security group
     */
    const TYPE_TAG_KEY = 'tag-key';

    /**
     * The value of a tag assigned to the security group.
     */
    const TYPE_TAG_VALUE = 'tag-value';

    /**
     * Only return the security groups that belong to the specified EC2-VPC ID.
     */
    const TYPE_VPC_ID = 'vpc-id';


    public static function getPrefix()
    {
        return 'TYPE_';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::validate()
     */
    protected function validate()
    {
        return preg_match('#^tag\:.+#', $this->value) ?: parent::validate();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::__callstatic()
     */
    public static function __callStatic($name, $args)
    {
        $class = __CLASS__;
        if ($name == 'tag') {
            if (!isset($args[0])) {
                throw new \InvalidArgumentException(sprintf(
                    'Tag name must be provided! Please use %s::tag("symbolic-name")', $class
                ));
            }
            return new $class('tag:' . $args[0]);
        }
        return parent::__callStatic($name, $args);
    }
}