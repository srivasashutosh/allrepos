<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * IpPermissionData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\UserIdGroupPairList     $groups          A list of security group and AWS account ID pairs.
 * @property \Scalr\Service\Aws\Ec2\DataType\IpRangeList             $ipRanges        A list of IP ranges.
 */
class IpPermissionData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('groups', 'ipRanges');

    /**
     * The protocol.
     *
     * When you call DescribeSecurityGroups, the protocol value
     * returned is the number. Exception: For TCP, UDP, and ICMP, the
     * value returned is the name (e.g., tcp, udp, or icmp).
     * Valid values for EC2 security groups: tcp | udp | icmp or
     * the corresponding protocol number (6 | 17 | 1).
     * Valid values for VPC groups: tcp | udp | icmp or any
     * protocol number
     *
     * @var string|number
     */
    public $ipProtocol;

    /**
     * The start of port range for the TCP and UDP protocols, or an ICMP
     * type number. A value of -1 indicates all ICMP types.
     *
     * @var int
     */
    public $fromPort;

    /**
     * The end of port range for the TCP and UDP protocols, or an ICMP
     * code. A value of -1 indicates all ICMP codes for the given ICMP type.
     *
     * @var int
     */
    public $toPort;

    /**
     * Convenient constructor
     *
     * @param   string|number                                 $ipProtocol optional The protocol
     * @param   int                                           $fromPort   optional From port
     * @param   int                                           $toPort     optional To port
     * @param   IpRangeList|IpRangeData|array                 $ipRanges   optional The list of IP Ranges
     * @param   UserIdGroupPairList|UserIdGroupPairData|array $groups     optional The list of the User ID groups
     */
    public function __construct($ipProtocol = null, $fromPort = null, $toPort = null, $ipRanges = null, $groups = null)
    {
        parent::__construct();
        $this->ipProtocol = $ipProtocol;
        $this->fromPort = $fromPort;
        $this->toPort = $toPort;
        $this->setIpRanges($ipRanges);
        $this->setGroups($groups);
    }

    /**
     * Sets the list of IP ranges.
     *
     * @param   UserIdGroupPairList|UserIdGroupPairData|array $groups A list of security group and AWS account ID pairs.
     * @return  IpPermissionData
     */
    public function setGroups($groups = null)
    {
        if ($groups !== null && !($groups instanceof UserIdGroupPairList)) {
            $groups = new UserIdGroupPairList($groups);
        }
        return $this->__call(__FUNCTION__, array($groups));
    }

    /**
     * Sets the list of IP ranges.
     *
     * @param   IpRangeList|IpRangeData|array $ipRanges A list of IP ranges.
     * @return  IpPermissionData
     */
    public function setIpRanges($ipRanges = null)
    {
        if ($ipRanges !== null && !($ipRanges instanceof IpRangeList)) {
            $ipRanges = new IpRangeList($ipRanges);
        }
        return $this->__call(__FUNCTION__, array($ipRanges));
    }
}