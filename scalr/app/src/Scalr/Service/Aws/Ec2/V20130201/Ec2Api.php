<?php
namespace Scalr\Service\Aws\Ec2\V20130201;

use Scalr\Service\Aws\Ec2\DataType\MonitorInstancesResponseSetList;
use Scalr\Service\Aws\Ec2\DataType\MonitorInstancesResponseSetData;
use Scalr\Service\Aws\Ec2\DataType\InstanceAttributeType;
use Scalr\Service\Aws\Ec2\DataType\PropagatingVgwData;
use Scalr\Service\Aws\Ec2\DataType\RouteTableAssociationData;
use Scalr\Service\Aws\Ec2\DataType\RouteTableAssociationList;
use Scalr\Service\Aws\Ec2\DataType\RouteData;
use Scalr\Service\Aws\Ec2\DataType\RouteList;
use Scalr\Service\Aws\Ec2\DataType\RouteTableData;
use Scalr\Service\Aws\Ec2\DataType\RouteTableList;
use Scalr\Service\Aws\Ec2\DataType\RouteTableFilterList;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayAttachmentData;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayAttachmentList;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayData;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayList;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayFilterList;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAttributeType;
use Scalr\Service\Aws\Ec2\DataType\CreateNetworkInterfaceRequestData;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfacePrivateIpAddressesSetData;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfacePrivateIpAddressesSetList;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAttachmentData;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAssociationData;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceList;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceData;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceFilterList;
use Scalr\Service\Aws\Ec2\DataType\VpcFilterList;
use Scalr\Service\Aws\Ec2\DataType\VpcList;
use Scalr\Service\Aws\Ec2\DataType\VpcData;
use Scalr\Service\Aws\Ec2\DataType\AccountAttributeValueData;
use Scalr\Service\Aws\Ec2\DataType\AccountAttributeSetList;
use Scalr\Service\Aws\Ec2\DataType\AccountAttributeSetData;
use Scalr\Service\Aws\Ec2\DataType\AccountAttributeValueList;
use Scalr\Service\Aws\Ec2\DataType\RegisterImageData;
use Scalr\Service\Aws\Ec2\DataType\KeyPairFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\KeyPairFilterList;
use Scalr\Service\Aws\Ec2\DataType\ImageFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupData;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupList;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupFilterList;
use Scalr\Service\Aws\Ec2\DataType\GetConsoleOutputResponseData;
use Scalr\Service\Aws\Ec2\DataType\SubnetList;
use Scalr\Service\Aws\Ec2\DataType\SubnetData;
use Scalr\Service\Aws\Ec2\DataType\SubnetFilterList;
use Scalr\Service\Aws\Ec2\DataType\SnapshotFilterData;
use Scalr\Service\Aws\Ec2\DataType\SnapshotData;
use Scalr\Service\Aws\Ec2\DataType\SnapshotList;
use Scalr\Service\Aws\Ec2\DataType\SnapshotFilterList;
use Scalr\Service\Aws\Ec2\DataType\AssociateAddressRequestData;
use Scalr\Service\Aws\Ec2\DataType\AddressData;
use Scalr\Service\Aws\Ec2\DataType\AddressList;
use Scalr\Service\Aws\Ec2\DataType\AddressFilterList;
use Scalr\Service\Aws\Ec2\DataType\KeyPairList;
use Scalr\Service\Aws\Ec2\DataType\KeyPairData;
use Scalr\Service\Aws\Ec2\DataType\CreateImageRequestData;
use Scalr\Service\Aws\Ec2\DataType\EbsBlockDeviceData;
use Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingData;
use Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingList;
use Scalr\Service\Aws\Ec2\DataType\ImageData;
use Scalr\Service\Aws\Ec2\DataType\ImageList;
use Scalr\Service\Aws\Ec2\DataType\ImageFilterList;
use Scalr\Service\Aws\Ec2\DataType\CreateVolumeRequestData;
use Scalr\Service\Aws\Ec2\DataType\AttachmentSetResponseData;
use Scalr\Service\Aws\Ec2\DataType\AttachmentSetResponseList;
use Scalr\Service\Aws\Ec2\DataType\VolumeData;
use Scalr\Service\Aws\Ec2\DataType\VolumeList;
use Scalr\Service\Aws\Ec2\DataType\VolumeFilterList;
use Scalr\Service\Aws\Ec2\DataType\InstanceStateChangeData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStateChangeList;
use Scalr\Service\Aws\Ec2\DataType\RunInstancesRequestData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusDetailsSetData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusDetailsSetList;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusTypeData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusEventTypeData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusList;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusFilterList;
use Scalr\Service\Aws\Ec2\DataType\RecurringChargesSetData;
use Scalr\Service\Aws\Ec2\DataType\RecurringChargesSetList;
use Scalr\Service\Aws\Ec2\DataType\ReservedInstanceData;
use Scalr\Service\Aws\Ec2\DataType\ReservedInstanceList;
use Scalr\Service\Aws\Ec2\DataType\OfferingType;
use Scalr\Service\Aws\Ec2\DataType\ReservedInstanceFilterList;
use Scalr\Service\Aws\Ec2\DataType\IamInstanceProfileResponseData;
use Scalr\Service\Aws\Ec2\DataType\InstancePrivateIpAddressesSetData;
use Scalr\Service\Aws\Ec2\DataType\InstancePrivateIpAddressesSetList;
use Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceSetList;
use Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceAssociationData;
use Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceAttachmentData;
use Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceSetData;
use Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList;
use Scalr\Service\Aws\Ec2\DataType\ResourceTagSetData;
use Scalr\Service\Aws\Ec2\DataType\EbsInstanceBlockDeviceMappingResponseData;
use Scalr\Service\Aws\Ec2\DataType\InstanceBlockDeviceMappingResponseData;
use Scalr\Service\Aws\Ec2\DataType\InstanceBlockDeviceMappingResponseList;
use Scalr\Service\Aws\Ec2\DataType\StateReasonData;
use Scalr\Service\Aws\Ec2\DataType\InstanceMonitoringStateData;
use Scalr\Service\Aws\Ec2\DataType\PlacementResponseData;
use Scalr\Service\Aws\Ec2\DataType\ProductCodeSetData;
use Scalr\Service\Aws\Ec2\DataType\ProductCodeSetList;
use Scalr\Service\Aws\Ec2\DataType\InstanceStateData;
use Scalr\Service\Aws\Ec2\DataType\GroupData;
use Scalr\Service\Aws\Ec2\DataType\GroupList;
use Scalr\Service\Aws\Ec2\DataType\ReservationData;
use Scalr\Service\Aws\Ec2\DataType\InstanceData;
use Scalr\Service\Aws\Ec2\DataType\ReservationList;
use Scalr\Service\Aws\Ec2\DataType\InstanceList;
use Scalr\Service\Aws\Ec2\DataType\InstanceFilterList;
use Scalr\Service\Aws\Ec2\DataType\IpRangeData;
use Scalr\Service\Aws\Ec2\DataType\UserIdGroupPairData;
use Scalr\Service\Aws\Ec2\DataType\IpRangeList;
use Scalr\Service\Aws\Ec2\DataType\UserIdGroupPairList;
use Scalr\Service\Aws\Ec2\DataType\IpPermissionData;
use Scalr\Service\Aws\Ec2\DataType\IpPermissionList;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupData;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupList;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupFilterList;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneMessageData;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneMessageList;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneList;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneFilterList;
use Scalr\Service\Aws\AbstractApi;
use Scalr\Service\Aws\Client\ClientResponseInterface;
use Scalr\Service\Aws;
use Scalr\Service\Aws\Client\QueryClientException;
use Scalr\Service\Aws\Ec2;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\EntityManager;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Client\QueryClientResponse;
use Scalr\Service\Aws\Client\ClientInterface;
use \DateTimeZone;
use \DateTime;

/**
 * Ec2 Api messaging.
 *
 * Implements Ec2 Low-Level API Actions.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     12.03.2013
 */
class Ec2Api extends AbstractApi
{

    /**
     * @var Ec2
     */
    protected $ec2;

    /**
     * @var string
     */
    protected $versiondate;

    /**
     * Constructor
     *
     * @param   Ec2                 $ec2          Ec2 instance
     * @param   ClientInterface     $client       Client Interface
     */
    public function __construct(Ec2 $ec2, ClientInterface $client)
    {
        $this->ec2 = $ec2;
        $this->client = $client;
        $this->versiondate = preg_replace('#^.+V(\d{4})(\d{2})(\d{2})$#', '\\1-\\2-\\3', __NAMESPACE__);
    }

    /**
     * Gets an entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->ec2->getEntityManager();
    }

    /**
     * Loads RouteList from simple xml object
     *
     * @param   string            $name The name of the ListDataType extended object without suffix "List"
     * @param   \SimpleXMLElement $sxml The simplexmlelement object
     * @return  Ec2ListDataType   Returns loaded object
     */
    protected function _loadListByName($name, \SimpleXMLElement $sxml)
    {
        $class = 'Scalr\\Service\\Aws\\Ec2\\DataType\\' . $name . 'List';
        $list = new $class;
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = $this->{'_load' . $name . 'Data'}($v);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * DescribeAvailabilityZones action
     *
     * Describes one or more of the Availability Zones that are currently available to the account. The results
     * include zones only for the region you're currently using.
     *
     * Note! Availability Zones are not the same across accounts.The Availability Zone us-east-1a for account
     * A is not necessarily the same as us-east-1a for account B. Zone assignments are mapped
     * independently for each account.
     *
     * @param   Ec2ListDataType            $zoneName optional Zone Name List to filter.
     * @param   AvailabilityZoneFilterList $filter   optional Filter to apply.
     * @return  AvailabilityZoneList       Returns the list of Availability Zones
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeAvailabilityZones(ListDataType $zoneName = null, AvailabilityZoneFilterList $filter = null)
    {
        $result = null;
        $isSoap = $this->client->getType() == Aws::CLIENT_SOAP;
        $options = array();
        if ($zoneName !== null) {
            if ($isSoap) {
                foreach ($zoneName as $v) {
                    $options['availabilityZoneSet']['item'][] = array(
                        'zoneName' => $v
                    );
                }
            } else {
                $options = $zoneName->getQueryArrayBare('ZoneName');
            }
        }
        if ($filter !== null) {
            if ($isSoap) {
                foreach ($filter as $v) {
                    $vals = array();
                    foreach ($v->value as $vs) {
                        $vals[] = array(
                            'value' => $vs,
                        );
                    }
                    $t = array(
                        'name'     => $v->name,
                        'valueSet' => array(
                            'item' => $vals,
                        ),
                    );
                    $options['filterSet']['item'][] = $t;
                }
            } else {
                $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
            }
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            unset($response);
            if (!empty($sxml->availabilityZoneInfo->item)) {
                $result = new AvailabilityZoneList();
                $result
                    ->setEc2($this->ec2)
                    ->setRequestId((string)$sxml->requestId)
                ;
                foreach ($sxml->availabilityZoneInfo->item as $v) {
                    $zname = (string) $v->zoneName;
                    $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->availabilityZone->get($zname) : null;
                    if ($item === null) {
                        $item = new AvailabilityZoneData();
                        $item->setEc2($this->ec2);
                        $bAttach = true;
                    } else {
                        $bAttach = false;
                    }
                    $messageList = new AvailabilityZoneMessageList();
                    $messageList->setEc2($this->ec2);
                    if (!empty($v->messageSet->item)) {
                        foreach ($v->messageSet->item as $m) {
                            $message = new AvailabilityZoneMessageData();
                            $message->setEc2($this->ec2);
                            $message->message = (string) $m->message;
                            $messageList->append($message);
                            unset($message);
                        }
                    }
                    $item->zoneName = $zname;
                    $item->zoneState = (string)$v->zoneState;
                    $item->regionName = (string)$v->regionName;
                    $item->setMessageSet($messageList);
                    $result->append($item);
                    if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                        $this->getEntityManager()->attach($item);
                    }
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * DescribeSecurityGroups action
     *
     * Describes one or more of your security groups.
     * This includes both EC2 security groups and VPC security groups
     *
     * @param   ListDataType            $groupName optional One or more security group names.
     * @param   ListDataType            $groupId   optional One or more security group IDs.
     * @param   SecurityGroupFilterList $filter    optional The name/value pairs list for the filter.
     * @return  \Scalr\Service\Aws\Ec2\DataType\SecurityGroupList Returns SecurityGroupList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeSecurityGroups(ListDataType $groupName = null, ListDataType $groupId = null,
                                           SecurityGroupFilterList $filter = null)
    {
        $result = null;
        if ($groupName !== null) {
            $options = $groupName->getQueryArrayBare('GroupName');
        } else {
            $options = array();
        }
        if ($groupId !== null) {
            $options = array_merge($options, $groupId->getQueryArrayBare('GroupId'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new SecurityGroupList();
            $result
                ->setEc2($this->ec2)
                ->setRequestId((string)$sxml->requestId)
            ;
            if (!empty($sxml->securityGroupInfo->item)) {
                foreach ($sxml->securityGroupInfo->item as $v) {
                    $sgid = (string)$v->groupId;
                    $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->securityGroup->get($sgid) : null;
                    if ($item === null) {
                        $item = new SecurityGroupData();
                        $item->setEc2($this->ec2);
                        $bAttach = true;
                    } else {
                        $item->resetObject();
                        $bAttach = false;
                    }
                    $item->ownerId = (string)$v->ownerId;
                    $item->groupId = (string)$v->groupId;
                    $item->groupName = $this->exist($v->groupName) ? (string)$v->groupName : null;
                    $item->groupDescription = $this->exist($v->groupDescription) ? (string)$v->groupDescription : null;
                    $item->vpcId = $this->exist($v->vpcId) ? (string)$v->vpcId : null;
                    $item
                        ->setIpPermissions($this->_loadIpPermissionList($v->ipPermissions))
                        ->setIpPermissionsEgress($this->_loadIpPermissionList($v->ipPermissionsEgress))
                        ->setTagSet($this->_loadResourceTagSetList($v->tagSet))
                    ;
                    $result->append($item);
                    if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                        $this->getEntityManager()->attach($item);
                    }
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads IpPermissionList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  IpPermissionList  Returns IpPermissionList
     */
    protected function _loadIpPermissionList(\SimpleXMLElement $sxml)
    {
        $list = new IpPermissionList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new IpPermissionData();
                $item->setEc2($this->ec2);
                $item->ipProtocol = $this->exist($v->ipProtocol) ? (string) $v->ipProtocol : null;
                $item->fromPort = $this->exist($v->fromPort) ? (int) $v->fromPort : null;
                $item->toPort   = $this->exist($v->toPort) ? (int) $v->toPort : null;
                $item->setGroups($this->_loadUserIdGroupPairList($v->groups));
                $item->setIpRanges($this->_loadIpRangeList($v->ipRanges));
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads UserIdGroupPairList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  UserIdGroupPairList  Returns UserIdGroupPairList
     */
    protected function _loadUserIdGroupPairList(\SimpleXMLElement $sxml)
    {
        $list = new UserIdGroupPairList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new UserIdGroupPairData();
                $item->setEc2($this->ec2);
                $item->groupId = $this->exist($v->groupId) ? (string) $v->groupId : null;
                $item->userId = $this->exist($v->userId) ? (string) $v->userId : null;
                $item->groupName = $this->exist($v->groupName) ? (string) $v->groupName : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads IpRangeList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  IpRangeList  Returns IpRangeList
     */
    protected function _loadIpRangeList(\SimpleXMLElement $sxml)
    {
        $list = new IpRangeList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new IpRangeData($this->exist($v->cidrIp) ? (string) $v->cidrIp : null);
                $item->setEc2($this->ec2);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
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
    public function deleteSecurityGroup($groupId = null, $groupName = null)
    {
        $result = false;
        $options = array();
        if ($groupName === null && $groupId === null || $groupName !== null && $groupId !== null) {
            throw new \InvalidArgumentException(sprintf(
                'Either groupName or groupId is required for the %s. '
              . 'Also you cannot specify both in the same call.', __METHOD__
            ));
        }
        if ($groupId !== null) {
            $options['GroupId'] = (string) $groupId;
        } else if ($groupName !== null) {
            $options['GroupName'] = (string) $groupName;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $result = true;
            $entity = null;
            if (!empty($options['GroupId'])) {
                $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->securityGroup->get($options['GroupId']) : null;
            } else if (!empty($options['GroupName'])) {
                //Undesirable workaround.
                //You can have an EC2 security group with the same name as a VPC security group
                //(each group has a unique security group ID separate from the name).
                /*
                $entity = $this
                    ->getEntityManager()->getRepository('Ec2:SecurityGroup')
                    ->findOneBy(array('groupName' => $options['GroupName']))
                ;
                */
            }
            if (isset($entity)) {
                $this->getEntityManager()->detach($entity);
            }
        }
        return $result;
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
     * @return  string       Returns security group ID on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createSecurityGroup($groupName, $groupDescription, $vpcId = null)
    {
        $result = null;
        $options = array(
            'GroupName'        => (string) $groupName,
            'GroupDescription' => (string) $groupDescription,
        );
        if ($vpcId !== null) {
            $options['VpcId'] = (string) $vpcId;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not create security group %s. It returned "%s"',
                    $groupName, $sxml->return
                ));
            }
            $result = (string) $sxml->groupId;
        }
        return $result;
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
     * @param   IpPermissionList $ipPermissions Ip permission list object
     * @param   string           $groupId       optional The ID of the EC2 or VPC security group to modify.
     *                                                   The group must belong to your account.
     * @param   string           $groupName     optional The name of the EC2 security group to modify.
     *                                                   It can be used instead of group ID for EC2 security groups.
     * @return  bool             Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function authorizeSecurityGroupIngress(IpPermissionList $ipPermissions, $groupId = null, $groupName = null)
    {
        $result = false;
        $options = $ipPermissions->getQueryArrayBare('IpPermissions');
        if ($groupName === null && $groupId === null || $groupName !== null && $groupId !== null) {
            throw new \InvalidArgumentException(sprintf(
                'Either groupName or groupId is required for the %s. '
              . 'Also you cannot specify both in the same call.', __METHOD__
            ));
        }
        if ($groupId !== null) {
            $options['GroupId'] = (string) $groupId;
        } else if ($groupName !== null) {
            $options['GroupName'] = (string) $groupName;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not add ingress rules to a security group "%s". It returned "%s"',
                    ($options['GroupId'] ?: $options['GroupName']), $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
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
     * @param   IpPermissionList $ipPermissions Ip permission list object
     * @param   string           $groupId       optional The ID of the EC2 or VPC security group to modify.
     *                                                   The group must belong to your account.
     * @param   string           $groupName     optional The name of the EC2 security group to modify.
     *                                                   It can be used instead of group ID for EC2 security groups.
     * @return  bool             Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function revokeSecurityGroupIngress(IpPermissionList $ipPermissions, $groupId = null, $groupName = null)
    {
        $result = false;
        $options = $ipPermissions->getQueryArrayBare('IpPermissions');
        if ($groupName === null && $groupId === null || $groupName !== null && $groupId !== null) {
            throw new \InvalidArgumentException(sprintf(
                'Either groupName or groupId is required for the %s. '
              . 'Also you cannot specify both in the same call.', __METHOD__
            ));
        }
        if ($groupId !== null) {
            $options['GroupId'] = (string) $groupId;
        } else if ($groupName !== null) {
            $options['GroupName'] = (string) $groupName;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not revoke ingress rules to a security group "%s". It returned "%s"',
                    ($options['GroupId'] ?: $options['GroupName']), $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
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
     * @param   IpPermissionList $ipPermissions Ip permission list object
     * @param   string           $groupId       optional The ID of the security group to modify.
     * @return  bool             Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function authorizeSecurityGroupEgress(IpPermissionList $ipPermissions, $groupId)
    {
        $result = false;
        $options = $ipPermissions->getQueryArrayBare('IpPermissions');
        $options['GroupId'] = (string) $groupId;

        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s GroupId:"%s". It returned "%s"',
                    $action, $options['GroupId'], $sxml->return
                ));
            }
            $result = true;
        }

        return $result;
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
     * @param   IpPermissionList $ipPermissions Ip permission list object
     * @param   string           $groupId       optional The ID of the security group to modify.
     * @return  bool             Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function revokeSecurityGroupEgress(IpPermissionList $ipPermissions, $groupId)
    {
        $result = false;
        $options = $ipPermissions->getQueryArrayBare('IpPermissions');
        $options['GroupId'] = (string) $groupId;

        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s GroupId:"%s". It returned "%s"',
                    $action, $options['GroupId'], $sxml->return
                ));
            }
            $result = true;
        }

        return $result;
    }

    /**
     * DescribeInstances action
     *
     * Describes one or more of your instances.
     * If you specify one or more instance IDs, Amazon EC2 returns information for those instances.
     * If you do not specify instance IDs, Amazon EC2 returns information for all relevant instances.
     * If you specify an invalid instance ID, an error is returned.
     * If you specify an instance that you do not own, it is not included in the returned results.
     * Recently terminated instances might appear in the returned results.
     * This interval is usually less than one hour.
     *
     * @param   ListDataType       $instanceIdList optional One or more instance IDs
     * @param   InstanceFilterList $filter         optional A Filter list
     * @return  ReservationList    Returns List of the reservations on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeInstances(ListDataType $instanceIdList = null, InstanceFilterList $filter = null)
    {
        $result = null;
        $options = array();
        if ($instanceIdList !== null) {
            $options = array_merge($options, $this->getInstanceIdListQuery($instanceIdList));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new ReservationList();
            $result->setEc2($this->ec2);
            $result->setRequestId((string)$sxml->requestId);
            if (!empty($sxml->reservationSet->item)) {
                foreach ($sxml->reservationSet->item as $r) {
                    $item = new ReservationData();
                    $item->setEc2($this->ec2);
                    $item->reservationId = (string)$r->reservationId;
                    $item->ownerId = $this->exist($r->ownerId) ? (string) $r->ownerId : null;
                    $item->requesterId = $this->exist($r->requesterId) ? (string) $r->requesterId : null;
                    $item
                        ->setGroupSet($this->_loadGroupList($r->groupSet))
                        ->setInstancesSet($this->_loadInstanceList($r->instancesSet))
                    ;
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads GroupList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  GroupList         Returns GroupList
     */
    protected function _loadGroupList(\SimpleXMLElement $sxml)
    {
        $list = new GroupList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new GroupData();
                $item->setEc2($this->ec2);
                $item->groupId = $this->exist($v->groupId) ? (string) $v->groupId : null;
                $item->groupName = $this->exist($v->groupName) ? (string) $v->groupName : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads InstanceList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceList      Returns InstanceList
     */
    protected function _loadInstanceList(\SimpleXMLElement $sxml)
    {
        $list = new InstanceList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $instanceId = (string)$v->instanceId;
                $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->instance->get($instanceId) : null;
                if ($item === null) {
                    $item = new InstanceData();
                    $item->setEc2($this->ec2);
                    $bAttach = true;
                } else {
                    $item->resetObject();
                    $bAttach = false;
                }
                $item->instanceId = $instanceId;
                $item->imageId = $this->exist($v->imageId) ? (string) $v->imageId : null;
                $item->privateDnsName = (string) $v->privateDnsName;
                $item->dnsName = $this->exist($v->dnsName) ? (string) $v->dnsName : null;
                $item->reason = $this->exist($v->reason) ? (string) $v->reason : null;
                $item->keyName = $this->exist($v->keyName) ? (string) $v->keyName : null;
                $item->amiLaunchIndex = $this->exist($v->amiLaunchIndex) ? (string) $v->amiLaunchIndex : null;
                $item->instanceType = (string) $v->instanceType;
                $item->launchTime = new DateTime((string)$v->launchTime, new DateTimeZone('UTC'));
                $item->kernelId = $this->exist($v->kernelId) ? (string) $v->kernelId : null;
                $item->ramdiskId = $this->exist($v->ramdiskId) ? (string) $v->ramdiskId : null;
                $item->platform = $this->exist($v->platform) ? (string) $v->platform : null;
                $item->subnetId = $this->exist($v->subnetId) ? (string) $v->subnetId : null;
                $item->vpcId = $this->exist($v->vpcId) ? (string) $v->vpcId : null;
                $item->privateIpAddress = $this->exist($v->privateIpAddress) ? (string) $v->privateIpAddress : null;
                $item->ipAddress = $this->exist($v->ipAddress) ? (string) $v->ipAddress : null;
                $item->sourceDestCheck = $this->exist($v->sourceDestCheck) ? ((string)$v->sourceDestCheck == 'true') : null;
                $item->architecture = $this->exist($v->architecture) ? (string) $v->architecture : null;
                $item->rootDeviceType = $this->exist($v->rootDeviceType) ? (string) $v->rootDeviceType : null;
                $item->rootDeviceName = $this->exist($v->rootDeviceName) ? (string) $v->rootDeviceName : null;
                $item->instanceLifecycle = $this->exist($v->instanceLifecycle) ? (string) $v->instanceLifecycle : null;
                $item->spotInstanceRequestId = $this->exist($v->spotInstanceRequestId) ? (string) $v->spotInstanceRequestId : null;
                $item->virtualizationType = $this->exist($v->virtualizationType) ? (string) $v->virtualizationType : null;
                $item->clientToken = $this->exist($v->clientToken) ? (string) $v->clientToken : null;
                $item->hypervisor = $this->exist($v->hypervisor) ? (string) $v->hypervisor : null;
                $item->ebsOptimized = $this->exist($v->ebsOptimized) ? ((string)$v->ebsOptimized == 'true') : null;
                $item
                    ->setInstanceState($this->_loadInstanceStateData($v->instanceState))
                    ->setProductCodes($this->_loadProductCodeSetList($v->productCodes))
                    ->setPlacement($this->_loadPlacementResponseData($v->placement))
                    ->setMonitoring($this->_loadInstanceMonitoringStateData($v->monitoring))
                    ->setGroupSet($this->_loadGroupList($v->groupSet))
                    ->setStateReason($this->_loadStateReasonData($v->stateReason))
                    ->setBlockDeviceMapping($this->_loadInstanceBlockDeviceMappingResponseList($v->blockDeviceMapping))
                    ->setTagSet($this->_loadResourceTagSetList($v->tagSet))
                    ->setNetworkInterfaceSet($this->_loadInstanceNetworkInterfaceSetList($v->networkInterfaceSet))
                    ->setIamInstanceProfile($this->_loadIamInstanceProfileResponseData($v->iamInstanceProfile))
                ;
                $list->append($item);
                if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                    $this->getEntityManager()->attach($item);
                }
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads InstanceStateData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceStateData Returns InstanceStateData
     */
    protected function _loadInstanceStateData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new InstanceStateData(
                ($this->exist($sxml->code) ? (int)$sxml->code : null),
                ($this->exist($sxml->name) ? (string)$sxml->name : null)
            );
            $item->setEc2($this->ec2);
        }
        return $item;
    }

    /**
     * Loads ProductCodeSetList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  ProductCodeSetList Returns ProductCodeSetList
     */
    protected function _loadProductCodeSetList(\SimpleXMLElement $sxml)
    {
        $list = new ProductCodeSetList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new ProductCodeSetData();
                $item->setEc2($this->ec2);
                $item->productCode = $this->exist($v->productCode) ? (string) $v->productCode : null;
                $item->type = $this->exist($v->type) ? (string) $v->type : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads PlacementResponseData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  PlacementResponseData Returns PlacementResponseData
     */
    protected function _loadPlacementResponseData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new PlacementResponseData();
            $item->setEc2($this->ec2);
            $item->availabilityZone = $this->exist($sxml->availabilityZone) ? (string)$sxml->availabilityZone : null;
            $item->groupName = $this->exist($sxml->groupName) ? (string)$sxml->groupName : null;
            $item->tenancy = $this->exist($sxml->tenancy) ? (string)$sxml->tenancy : null;
        }
        return $item;
    }

    /**
     * Loads InstanceMonitoringStateData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceMonitoringStateData Returns InstanceMonitoringStateData
     */
    protected function _loadInstanceMonitoringStateData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new InstanceMonitoringStateData();
            $item->setEc2($this->ec2);
            $item->state = $this->exist($sxml->state) ? (string)$sxml->state : null;
        }
        return $item;
    }

    /**
     * Loads StateReasonData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  StateReasonData Returns StateReasonData
     */
    protected function _loadStateReasonData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new StateReasonData();
            $item->setEc2($this->ec2);
            $item->code = $this->exist($sxml->code) ? (string)$sxml->code : null;
            $item->message = $this->exist($sxml->message) ? (string)$sxml->message : null;
        }
        return $item;
    }


    /**
     * Loads InstanceBlockDeviceMappingResponseList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceBlockDeviceMappingResponseList Returns InstanceBlockDeviceMappingResponseList
     */
    protected function _loadInstanceBlockDeviceMappingResponseList(\SimpleXMLElement $sxml)
    {
        $list = new InstanceBlockDeviceMappingResponseList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new InstanceBlockDeviceMappingResponseData();
                $item->setEc2($this->ec2);
                $item->deviceName = $this->exist($v->deviceName) ? (string) $v->deviceName : null;
                $item->virtualName = $this->exist($v->virtualName) ? (string) $v->virtualName : null;
                if ($this->exist($v->noDevice)) {
                    $item->noDevice = '';
                }
                $item->setEbs($this->_loadEbsInstanceBlockDeviceMappingResponseData($v->ebs));
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads EbsInstanceBlockDeviceMappingResponseData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  EbsInstanceBlockDeviceMappingResponseData Returns EbsInstanceBlockDeviceMappingResponseData
     */
    protected function _loadEbsInstanceBlockDeviceMappingResponseData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new EbsInstanceBlockDeviceMappingResponseData();
            $item->setEc2($this->ec2);
            $item->volumeId = $this->exist($sxml->volumeId) ? (string)$sxml->volumeId : null;
            $item->status = $this->exist($sxml->status) ? (string)$sxml->status : null;
            $item->attachTime = $this->exist($sxml->attachTime) ? new DateTime((string)$sxml->attachTime, new DateTimeZone('UTC')) : null;
            $item->deleteOnTermination = $this->exist($sxml->deleteOnTermination) ? ((string)$sxml->deleteOnTermination == 'true') : null;
        }
        return $item;
    }

    /**
     * Loads ResourceTagSetList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  ResourceTagSetList Returns ResourceTagSetList
     */
    protected function _loadResourceTagSetList(\SimpleXMLElement $sxml)
    {
        $list = new ResourceTagSetList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new ResourceTagSetData();
                $item->setEc2($this->ec2);
                $item->key = $this->exist($v->key) ? (string) $v->key : null;
                $item->value = $this->exist($v->value) ? (string) $v->value : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads InstanceNetworkInterfaceSetList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceNetworkInterfaceSetList Returns InstanceNetworkInterfaceSetList
     */
    protected function _loadInstanceNetworkInterfaceSetList(\SimpleXMLElement $sxml)
    {
        $list = new InstanceNetworkInterfaceSetList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = $this->_loadInstanceNetworkInterfaceSetData($v);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads InstanceNetworkInterfaceSetData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceNetworkInterfaceSetData Returns InstanceNetworkInterfaceSetData
     */
    protected function _loadInstanceNetworkInterfaceSetData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new InstanceNetworkInterfaceSetData();
            $item->setEc2($this->ec2);
            $item->networkInterfaceId = $this->exist($sxml->networkInterfaceId) ? (string)$sxml->networkInterfaceId : null;
            $item->subnetId = $this->exist($sxml->subnetId) ? (string)$sxml->subnetId : null;
            $item->vpcId = $this->exist($sxml->vpcId) ? (string)$sxml->vpcId : null;
            $item->description = $this->exist($sxml->description) ? (string)$sxml->description : null;
            $item->ownerId = $this->exist($sxml->ownerId) ? (string)$sxml->ownerId : null;
            $item->status = $this->exist($sxml->status) ? (string)$sxml->status : null;
            $item->privateIpAddress = $this->exist($sxml->privateIpAddress) ? (string)$sxml->privateIpAddress : null;
            $item->privateDnsName = $this->exist($sxml->privateDnsName) ? (string)$sxml->privateDnsName : null;
            $item->sourceDestCheck = $this->exist($sxml->sourceDestCheck) ? ((string)$sxml->sourceDestCheck == 'true') : null;
            $item->setGroupSet($this->_loadGroupList($sxml->groupSet));
            $item->setAttachment($this->_loadInstanceNetworkInterfaceAttachmentData($sxml->attachment));
            $item->setAssociation($this->_loadInstanceNetworkInterfaceAssociationData($sxml->association));
            $item->setPrivateIpAddressesSet($this->_loadInstancePrivateIpAddressesSetList($sxml->privateIpAddressesSet));
        }
        return $item;
    }



    /**
     * Loads InstanceNetworkInterfaceAttachmentData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceNetworkInterfaceAttachmentData Returns InstanceNetworkInterfaceAttachmentData
     */
    protected function _loadInstanceNetworkInterfaceAttachmentData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new InstanceNetworkInterfaceAttachmentData();
            $item->setEc2($this->ec2);
            $item->attachmentId = $this->exist($sxml->attachmentId) ? (string)$sxml->attachmentId : null;
            $item->deviceIndex = $this->exist($sxml->deviceIndex) ? (int)$sxml->deviceIndex : null;
            $item->status = $this->exist($sxml->status) ? (string)$sxml->status : null;
            $item->attachTime = $this->exist($sxml->attachTime) ? new DateTime((string)$sxml->attachTime, new DateTimeZone('UTC')) : null;
            $item->deleteOnTermination = $this->exist($sxml->deleteOnTermination) ? ((string)$sxml->deleteOnTermination == 'true') : null;
        }
        return $item;
    }

    /**
     * Loads InstanceNetworkInterfaceAssociationData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceNetworkInterfaceAssociationData Returns InstanceNetworkInterfaceAssociationData
     */
    protected function _loadInstanceNetworkInterfaceAssociationData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new InstanceNetworkInterfaceAssociationData();
            $item->setEc2($this->ec2);
            $item->ipOwnerId = $this->exist($sxml->ipOwnerId) ? (string)$sxml->ipOwnerId : null;
            $item->publicIp = $this->exist($sxml->publicIp) ? (string)$sxml->publicIp : null;
        }
        return $item;
    }

    /**
     * Loads InstancePrivateIpAddressesSetList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstancePrivateIpAddressesSetList Returns InstancePrivateIpAddressesSetList
     */
    protected function _loadInstancePrivateIpAddressesSetList(\SimpleXMLElement $sxml)
    {
        $list = new InstancePrivateIpAddressesSetList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new InstancePrivateIpAddressesSetData();
                $item->setEc2($this->ec2);
                $item->primary = $this->exist($v->primary) ? ((string)$v->primary == 'true') : null;
                $item->privateIpAddress = $this->exist($v->privateIpAddress) ? (string) $v->privateIpAddress : null;
                $item->setAssociation($this->_loadInstanceNetworkInterfaceAssociationData($v->association));
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads IamInstanceProfileResponseData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  IamInstanceProfileResponseData Returns IamInstanceProfileResponseData
     */
    protected function _loadIamInstanceProfileResponseData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new IamInstanceProfileResponseData();
            $item->setEc2($this->ec2);
            $item->arn = $this->exist($sxml->arn) ? (string)$sxml->arn : null;
            $item->iamInstanceProfileId = $this->exist($sxml->iamInstanceProfileId) ? (string)$sxml->iamInstanceProfileId : null;
        }
        return $item;
    }

    /**
     * DescribeReservedInstances action
     *
     * Describes one or more of the Reserved Instances that you purchased.
     * Starting with the 2011-11-01 API version, AWS expanded its offering of Amazon EC2 Reserved Instances
     * to address a range of projected instance use. There are three types of Reserved Instances based on
     * customer utilization levels: Heavy Utilization, Medium Utilization, and Light Utilization.You determine the
     * type of the Reserved Instances offerings by including the optional offeringType parameter.The Medium
     * Utilization offering type is equivalent to the Reserved Instance offering available before API version
     * 2011-11-01. If you are using tools that predate the 2011-11-01 API version, you only have access to the
     * Medium Utilization Reserved Instance offering type.
     *
     * @param   ListDataType               $reservedInstanceIdList optional One or more instance IDs
     * @param   ReservedInstanceFilterList $filter                 optional A Filter list
     * @return  ReservedInstanceList       Returns reserved list of the reserved instances
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeReservedInstances(ListDataType $reservedInstanceIdList = null,
                                              ReservedInstanceFilterList $filter = null, OfferingType $offefingType = null)
    {
        $result = null;
        $options = array();
        if ($reservedInstanceIdList !== null) {
            $options = array_merge($options, $this->getInstanceIdListQuery($reservedInstanceIdList, 'ReservedInstancesId'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        if ($offefingType !== null) {
            $options['OfferingType'] = (string) $offefingType;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new ReservedInstanceList();
            $result->setEc2($this->ec2);
            $result->setRequestId((string) $sxml->requestId);
            if (!empty($sxml->reservedInstancesSet->item)) {
                foreach ($sxml->reservedInstancesSet->item as $v) {
                    $reservedInstancesId = (string)$v->reservedInstancesId;
                    $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->reservedInstance->get($reservedInstancesId) : null;
                    if ($item === null) {
                        $item = new ReservedInstanceData();
                        $item->setEc2($this->ec2);
                        $bAttach = true;
                    } else {
                        $item->resetObject();
                        $bAttach = false;
                    }
                    $item->reservedInstancesId = $reservedInstancesId;
                    $item->instanceType = $this->exist($v->instanceType) ? (string)$v->instanceType : null;
                    $item->availabilityZone = $this->exist($v->availabilityZone) ? (string)$v->availabilityZone : null;
                    $item->duration = $this->exist($v->duration) ? $v->duration - 0 : null;
                    $item->fixedPrice = $this->exist($v->fixedPrice) ? floatval($v->fixedPrice) : null;
                    $item->usagePrice = $this->exist($v->usagePrice) ? floatval($v->usagePrice) : null;
                    $item->instanceCount = $this->exist($v->instanceCount) ? intval($v->instanceCount) : null;
                    $item->state = $this->exist($v->state) ? (string)$v->state : null;
                    $item->productDescription = $this->exist($v->productDescription) ? (string)$v->productDescription : null;
                    $item->start = $this->exist($v->start) ? new \DateTime((string)$v->start, new \DateTimeZone('UTC')) : null;
                    $item->instanceTenancy = $this->exist($v->instanceTenancy) ? (string)$v->instanceTenancy : null;
                    $item->currencyCode = $this->exist($v->currencyCode) ? (string)$v->currencyCode : null;
                    $item->offeringType = $this->exist($v->offeringType) ? (string)$v->offeringType : null;
                    $item
                        ->setTagSet($this->_loadResourceTagSetList($v->tagSet))
                        ->setRecurringCharges($this->_loadRecurringChargesSetList($v->recurringCharges))
                    ;
                    $result->append($item);
                    if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                        $this->getEntityManager()->attach($item);
                    }
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads RecurringChargesSetList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  RecurringChargesSetList Returns RecurringChargesSetList
     */
    protected function _loadRecurringChargesSetList(\SimpleXMLElement $sxml)
    {
        $list = new RecurringChargesSetList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new RecurringChargesSetData();
                $item->setEc2($this->ec2);
                $item->frequency = $this->exist($v->frequency) ? (string)$v->frequency : null;
                $item->amount = $this->exist($v->amount) ? floatval($v->amount) : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * DescribeInstanceStatus action
     *
     * @param   ListDataType             $instanceIdList      optional The list of the instance IDs
     * @param   bool                     $includeAllInstances optional When true, returns the health status for all instances
     *                                                        (for example, running, stopped, pending, shutting down).When
     *                                                        false, returns only the health status for running instances.
     * @param   InstanceStatusFilterList $filter              optional A Filter
     * @param   string                   $nextToken           The next paginated set of results to return
     * @param   int                      $maxResults          The maximum number of paginated instance items per response.
     * @return  InstanceStatusList       Returns the list of the InstanceStatusData objects
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeInstanceStatus(ListDataType $instanceIdList = null, $includeAllInstances = null,
                                           InstanceStatusFilterList $filter = null, $nextToken = null, $maxResults = null)
    {
        $result = null;
        $options = array();
        if ($instanceIdList !== null) {
            $options = array_merge($options, $this->getInstanceIdListQuery($instanceIdList));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        if ($includeAllInstances !== null) {
            $options['IncludeAllInstances'] = $includeAllInstances ? 'true' : 'false';
        }
        if ($nextToken !== null) {
            $options['NextToken'] = (string) $nextToken;
        }
        if ($maxResults !== null) {
            $options['MaxResults'] = (int) $maxResults;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new InstanceStatusList();
            $result->setEc2($this->ec2);
            $result->setRequestId((string) $sxml->requestId);
            $result->setNextToken($this->exist($sxml->nextToken) ? (string) $sxml->nextToken : null);
            if (!empty($sxml->instanceStatusSet->item)) {
                foreach ($sxml->instanceStatusSet->item as $v) {
                    $item = new InstanceStatusData();
                    $item->setEc2($this->ec2);
                    $item->instanceId = (string) $v->instanceId;
                    $item->availabilityZone = (string) $v->availabilityZone;
                    $item
                        ->setEventsSet($this->_loadInstanceStatusEventTypeData($v->eventsSet))
                        ->setInstanceState($this->_loadInstanceStateData($v->instanceState))
                        ->setSystemStatus($this->_loadInstanceStatusTypeData($v->systemStatus))
                        ->setInstanceStatus($this->_loadInstanceStatusTypeData($v->instanceStatus))
                    ;
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads InstanceStatusEventTypeData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceStatusEventTypeData Returns InstanceStatusEventTypeData
     */
    protected function _loadInstanceStatusEventTypeData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new InstanceStatusEventTypeData();
            $item->setEc2($this->ec2);
            $item->code = $this->exist($sxml->code) ? (string)$sxml->code : null;
            $item->description = $this->exist($sxml->description) ? (string)$sxml->description : null;
            $item->notAfter = $this->exist($sxml->notAfter) ? new DateTime((string)$sxml->notAfter, new DateTimeZone('UTC')) : null;
            $item->notBefore = $this->exist($sxml->notBefore) ? new DateTime((string)$sxml->notBefore, new DateTimeZone('UTC')) : null;
        }
        return $item;
    }

    /**
     * Loads InstanceStatusTypeData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceStatusTypeData Returns InstanceStatusTypeData
     */
    protected function _loadInstanceStatusTypeData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new InstanceStatusTypeData();
            $item->setEc2($this->ec2);
            $item->setDetails($this->_loadInstanceStatusDetailsSetList($sxml->details));
            $item->status = $this->exist($sxml->status) ? (string)$sxml->status : null;
        }
        return $item;
    }

    /**
     * Loads InstanceStatusDetailsSetList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceStatusDetailsSetList Returns InstanceStatusDetailsSetList
     */
    protected function _loadInstanceStatusDetailsSetList(\SimpleXMLElement $sxml)
    {
        $list = new InstanceStatusDetailsSetList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new InstanceStatusDetailsSetData();
                $item->setEc2($this->ec2);
                $item->name = (string) $v->name;
                $item->status = (string) $v->status;
                $item->impairedSince = $this->exist($v->impairedSince) ?
                    new DateTime((string)$v->impairedSince, new DateTimeZone('UTC')) : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * RunInstances action
     *
     * Launches the specified number of instances of an AMI for which you have permissions.
     * If Amazon EC2 cannot launch the minimum number of instances you request, no instances will be
     * launched. If there is insufficient capacity to launch the maximum number of instances you request, Amazon
     * EC2 launches the minimum number specified and allocates the remaining available instances using round robin.
     *
     * Note! Every instance is launched in a security group (created using the CreateSecurityGroup
     * operation). If you don't specify a security group in the RunInstances request, the "default"
     * security group is used.
     *
     * @param   RunInstancesRequestData $request Request data
     * @return  ReservationData         Returns the ReservationData object
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function runInstances(RunInstancesRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArrayBare();
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new ReservationData();
            $result->setEc2($this->ec2);
            $result->reservationId = (string)$sxml->reservationId;
            $result->ownerId = (string)$sxml->ownerId;
            $result->requesterId = $this->exist($sxml->requesterId) ? (string)$sxml->requesterId : null;
            $result->setGroupSet($this->_loadGroupList($sxml->groupSet));
            $result->setInstancesSet($this->_loadInstanceList($sxml->instancesSet));
        }
        return $result;
    }

    /**
     * Type cast the list of the InstanceId into accepted state.
     *
     * It transforms 'Instances.member.N.InstanceId' structure to acceptable 'InstancesId.N' structure
     *
     * @param   ListDataType $instanceIdList  The list of the Instance ID
     * @param   string       $name            optional URL parameter name
     * @return  array Returns array of the query parameters for the request
     */
    public function getInstanceIdListQuery(ListDataType $instanceIdList, $name = 'InstanceId')
    {
        if ($instanceIdList instanceof \Scalr\Service\Aws\Ec2\DataType\InstanceData ||
            $instanceIdList instanceof \Scalr\Service\Aws\Elb\DataType\InstanceData) {
            $instanceIdList = new ListDataType(array_unique(array_values($instanceIdList->getQueryArray('Instances'))));
        }
        $options = $instanceIdList->getQueryArrayBare($name);
        return $options;
    }

    /**
     * TerminateInstances
     *
     * Shuts down one or more instances. This operation is idempotent; if you terminate an instance more than
     * once, each call will succeed.
     * Terminated instances will remain visible after termination (approximately one hour).
     *
     * Note! By default, Amazon EC2 deletes all Amazon EBS volumes that were attached when the instance
     * launched. Amazon EBS volumes attached after instance launch continue running.
     * You can stop, start, and terminate EBS-backed instances.You can only terminate S3-backed instances.
     * What happens to an instance differs if you stop it or terminate it. For example, when you stop an instance,
     * the root device and any other devices attached to the instance persist. When you terminate an instance,
     * the root device and any other devices attached during the instance launch are automatically deleted.
     *
     * @param   ListDataType     $instanceIdList
     * @return  InstanceStateChangeList Returns the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function terminateInstances(ListDataType $instanceIdList)
    {
        $result = null;
        $options = array();
        if ($instanceIdList !== null) {
            $options = array_merge($options, $this->getInstanceIdListQuery($instanceIdList));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadListByName('InstanceStateChange', $sxml->instancesSet);
        }
        return $result;
    }

    /**
     * Loads InstanceStateChangeData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InstanceStateChangeData Returns InstanceStateChangeData
     */
    protected function _loadInstanceStateChangeData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new InstanceStateChangeData();
            $item->setEc2($this->ec2);
            $item->instanceId = (string) $v->instanceId;
            $item->setCurrentState($this->_loadInstanceStateData($v->currentState));
            $item->setPreviousState($this->_loadInstanceStateData($v->previousState));
        }
        return $item;
    }

    /**
     * RebootInstances action
     *
     * Requests a reboot of one or more instances. This operation is asynchronous; it only queues a request
     * to reboot the specified instance(s). The operation will succeed if the instances are valid and belong to
     * you. Requests to reboot terminated instances are ignored.
     *
     * Note! If a Linux/UNIX instance does not cleanly shut down within four minutes, Amazon EC2 will
     * perform a hard reboot.
     *
     * @param   ListDataType $instanceIdList The list of the Instance IDs
     * @return  bool         Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function rebootInstances(ListDataType $instanceIdList)
    {
        $result = false;
        $options = array();
        if ($instanceIdList !== null) {
            $options = array_merge($options, $this->getInstanceIdListQuery($instanceIdList));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not reboot instances. It returned "%s"', $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DescribeVolumes action
     *
     * Describes one or more of your Amazon EBS volumes.
     *
     * @param   ListDataType     $volumeIdList optional The list of Volume ID
     * @param   VolumeFilterList $filter       optional The filter list
     * @return  VolumeList       Returns the list of the volumes
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeVolumes(ListDataType $volumeIdList = null, VolumeFilterList $filter = null)
    {
        $result = null;
        $options = array();
        if ($volumeIdList !== null) {
            $options = array_merge($options, $volumeIdList->getQueryArrayBare('VolumeId'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new VolumeList();
            $result->setEc2($this->ec2);
            $result->setRequestId((string)$sxml->requestId);
            if (!empty($sxml->volumeSet->item)) {
                foreach ($sxml->volumeSet->item as $v) {
                    $result->append($this->_loadVolumeData($v));
                }
            }
        }
        return $result;
    }

    /**
     * Loads AttachmentSetResponseList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  AttachmentSetResponseList Returns AttachmentSetResponseList
     */
    protected function _loadAttachmentSetResponseList(\SimpleXMLElement $sxml)
    {
        $list = new AttachmentSetResponseList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new AttachmentSetResponseData();
                $item->setEc2($this->ec2);
                $item->volumeId = $this->exist($v->volumeId) ? (string) $v->volumeId : null;
                $item->status = $this->exist($v->status) ? (string) $v->status : null;
                $item->instanceId = $this->exist($v->instanceId) ? (string) $v->instanceId : null;
                $item->attachTime = $this->exist($v->attachTime) ? new DateTime((string)$v->attachTime, new DateTimeZone('UTC')) : null;
                $item->deleteOnTermination = $this->exist($v->deleteOnTermination) ? ((string)$v->deleteOnTermination == 'true') : null;
                $item->device = $this->exist($v->device) ? (string) $v->device : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads VolumeData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  VolumeData Returns VolumeData
     */
    protected function _loadVolumeData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $volumeId = (string)$v->volumeId;
            $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->volume->get($volumeId) : null;
            if ($item === null) {
                $item = new VolumeData();
                $item->setEc2($this->ec2);
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }
            $item->volumeId = $volumeId;
            $item->volumeType = $this->exist($v->volumeType) ? (string) $v->volumeType : null;
            $item->iops = $this->exist($v->iops) ? (int) $v->iops : null;
            $item->availabilityZone = $this->exist($v->availabilityZone) ? (string) $v->availabilityZone : null;
            $item->createTime = $this->exist($v->createTime) ? new DateTime((string)$v->createTime, new DateTimeZone('UTC')) : null;
            $item->size = $this->exist($v->size) ? (string) $v->size : null;
            $item->snapshotId = $this->exist($v->snapshotId) ? (string)$v->snapshotId : null;
            $item->status = $this->exist($v->status) ? (string) $v->status : null;
            $item
                ->setTagSet($this->_loadResourceTagSetList($v->tagSet))
                ->setAttachmentSet($this->_loadAttachmentSetResponseList($v->attachmentSet))
            ;
            if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * CreateVolume action
     *
     * Creates an Amazon EBS volume that can be attached to any Amazon EC2 instance in the same Availability Zone.
     * Any AWS Marketplace product codes from the snapshot are propagated to the volume
     *
     * @param   CreateVolumeRequestData  $request Request that specifies parameters of the volume.
     * @return  VolumeData       Returns the VolumeData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createVolume(CreateVolumeRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArrayBare();
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadVolumeData($sxml);
        }
        return $result;
    }

    /**
     * DeleteVolume action
     *
     * Deletes an Amazon EBS volume. The volume must be in the available state (not attached to an instance)
     *
     * @param   string       $volumeId The ID of the volume.
     * @return  bool         Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteVolume($volumeId)
    {
        $result = false;
        $options = array(
            'VolumeId' => (string) $volumeId,
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not delete the volume with ID "%s". It returned "%s"',
                    $options['VolumeId'], $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->volume->get($options['VolumeId']) : null;
            if ($entity !== null) {
                $this->getEntityManager()->detach($entity);
            }
        }
        return $result;
    }

    /**
     * AttachVolume action
     *
     * Attaches an Amazon EBS volume to a running instance and exposes it to the instance with the specified
     * device name.
     *
     * For a list of supported device names, see Attaching the Volume to an Instance. Any device names that
     * aren't reserved for instance store volumes can be used for Amazon EBS volumes.
     *
     * Note! If a volume has an AWS Marketplace product code:
     *  -The volume can only be attached to the root device of a stopped instance.
     *
     *  -You must be subscribed to the AWS Marketplace code that is on the volume.
     *
     *  -The configuration (instance type, operating system) of the instance must support that specific
     *   AWS Marketplace code. For example, you cannot take a volume from a Windows instance
     *   and attach it to a Linux instance.
     *
     *  -AWS Marketplace product codes are copied from the volume to the instance.
     *
     * @param   string     $volumeId    The ID of the Amazon EBS volume. The volume and instance must be
     *                                  within the same Availability Zone
     * @param   string     $instanceId  The ID of the Instance. The instance must be running.
     * @param   string     $device      The device name as exposed to the instance
     * @return  AttachmentSetResponseData Returns AttachmentSetResponseData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attachVolume($volumeId, $instanceId, $device)
    {
        $result = null;
        $options = array(
            'VolumeId'   => (string) $volumeId,
            'InstanceId' => (string) $instanceId,
            'Device'     => (string) $device,
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new AttachmentSetResponseData();
            $result->setEc2($this->ec2);
            $result->attachTime = $this->exist($sxml->attachTime) ? new DateTime((string)$sxml->attachTime, new DateTimeZone('UTC')) : null;
            $result->volumeId = (string) $sxml->volumeId;
            $result->deleteOnTermination = true;
            $result->device = (string) $sxml->device;
            $result->instanceId = (string) $sxml->instanceId;
            $result->status = (string) $sxml->status;

            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->volume->get($options['VolumeId']) : null;
            if ($entity !== null) {
                foreach ($entity->attachmentSet as $index => $v) {
                    if ($v->volumeId == $result->volumeId &&
                        $v->instanceId == $result->instanceId &&
                        $v->device == $result->device) {
                        $result->deleteOnTermination = $v->deleteOnTermination;
                        unset($entity->attachmentSet[$index]);
                        $entity->attachmentSet->rewind();
                        break;
                    }
                }
            }
            if ($entity !== null) {
                $entity->attachmentSet->append($result);
            }
        }
        return $result;
    }

    /**
     * DetachVolume action
     *
     * Detaches an Amazon EBS volume from an instance. Make sure to unmount any file systems on the
     * device within your operating system before detaching the volume. Failure to do so will result in volume
     * being stuck in "busy" state while detaching.
     *
     * Note! If an Amazon EBS volume is the root device of an instance, it cannot be detached while the
     * instance is in the "running" state. To detach the root volume, stop the instance first.
     * If the root volume is detached from an instance with an AWS Marketplace product code, then
     * the AWS Marketplace product codes from that volume are no longer associated with the instance.
     *
     * @param   string     $volumeId    The ID of the EBS volume.
     * @param   string     $instanceId  optional The ID of the Instance.
     * @param   string     $device      optional The device name.
     * @param   bool       $force       optional Forces detachment if the previous detachment attempt did
     *                                  not occur cleanly (logging into an instance, unmounting
     *                                  the volume, and detaching normally). This option can lead
     *                                  to data loss or a corrupted file system. Use this option only
     *                                  as a last resort to detach a volume from a failed instance.
     *                                  The instance won't have an opportunity to flush file system
     *                                  caches or file system metadata. If you use this option, you
     *                                  must perform file system check and repair procedures.
     * @return  AttachmentSetResponseData Returns AttachmentSetResponseData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detachVolume($volumeId, $instanceId = null, $device = null, $force = null)
    {
        $result = null;
        $options = array(
            'VolumeId'   => (string) $volumeId,
        );
        if ($instanceId !== null) {
            $options['InstanceId'] = (string) $instanceId;
        }
        if ($device !== null) {
            $options['Device'] = (string) $device;
        }
        if ($force !== null) {
            $options['Force'] = $force ? 'true' : 'false';
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());

            $result = new AttachmentSetResponseData();
            $result->setEc2($this->ec2);
            $result->attachTime = $this->exist($sxml->attachTime) ? new DateTime((string)$sxml->attachTime, new DateTimeZone('UTC')) : null;
            $result->volumeId = (string) $sxml->volumeId;
            $result->deleteOnTermination = true;
            $result->device = (string) $sxml->device;
            $result->instanceId = (string) $sxml->instanceId;
            $result->status = (string) $sxml->status;

            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->volume->get($options['VolumeId']) : null;
            if ($entity !== null) {
                foreach ($entity->attachmentSet as $index => $v) {
                    if ($v->volumeId == $result->volumeId &&
                        $v->instanceId == $result->instanceId &&
                        $v->device == $result->device) {
                        $result->deleteOnTermination = $v->deleteOnTermination;
                        unset($entity->attachmentSet[$index]);
                        $entity->attachmentSet->rewind();
                        break;
                    }
                }
            }
            if ($entity !== null) {
                $entity->attachmentSet->append($result);
            }
        }
        return $result;
    }

    /**
     * CreateTags action
     *
     * Adds or overwrites one or more tags for the specified EC2 resource or resources. Each resource can
     * have a maximum of 10 tags. Each tag consists of a key and optional value. Tag keys must be unique per
     * resource.
     *
     * @param   ListDataType       $resourceIdList The ID of a resource to tag. For example, ami-1a2b3c4d.
     *                                             You can specify multiple resources to assign the tags to.
     * @param   ResourceTagSetList $tagList        The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createTags(ListDataType $resourceIdList, ResourceTagSetList $tagList)
    {
        $result = false;
        $options = array_merge($resourceIdList->getQueryArrayBare('ResourceId'), $tagList->getQueryArrayBare('Tag'));
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not create the Tags. It returned "%s"', $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DeleteTags action
     *
     * Deletes a specific set of tags from a specific set of resources. This call is designed to follow a
     * DescribeTags call. You first determine what tags a resource has, and then you call DeleteTags with
     * the resource ID and the specific tags you want to delete.
     *
     * @param   ListDataType       $resourceIdList The ID of a resource to tag. For example, ami-1a2b3c4d.
     *                                             You can specify multiple resources to assign the tags to.
     * @param   ResourceTagSetList $tagList        The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteTags(ListDataType $resourceIdList, ResourceTagSetList $tagList)
    {
        $result = false;
        $options = array_merge(
            $resourceIdList->getQueryArrayBare('ResourceId'),
            //Tag.n.Value might be unset so we need to substract them from request.
            array_filter($tagList->getQueryArrayBare('Tag'), function ($val) {
                return $val !== null;
            })
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not delete the Tags. It returned "%s"', $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DescribeImages action
     *
     * Describes the images (AMIs, AKIs, and ARIs) available to you. Images available to you include public
     * images, private images that you own, and private images owned by other AWS accounts but for which
     * you have explicit launch permissions.
     *
     * @param   ListDataType    $imageIdList      optional One or more AMI IDs.
     * @param   ListDataType    $ownerList        optional The AMIs owned by the specified owner. Multiple owner
     *                                            values can be specified. The IDs amazon, aws-marketplace,
     *                                            and self can be used to include AMIs owned by Amazon, AWS Marketplace,
     *                                            or AMIs owned by you, respectively.
     *                                            Valid values: amazon | aws-marketplace | self | AWS account ID | all
     * @param   ImageFilterList $filter           optional Filter list
     * @param   ListDataType    $executableByList optional The AMIs for which the specified user ID has explicit
     *                                            launch permissions. The user ID can be an AWS account
     *                                            ID, self to return AMIs for which the sender of the request
     *                                            has explicit launch permissions, or all to return AMIs with
     *                                            public launch permissions.
     * @return  ImageList       Returns the list of Images on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeImages(ListDataType $imageIdList = null, ListDataType $ownerList = null,
                                   ImageFilterList $filter = null, ListDataType $executableByList = null)
    {
        $result = null;
        $options = array();
        if ($imageIdList !== null) {
            $options = array_merge($options, $imageIdList->getQueryArrayBare('ImageId'));
        }
        if ($executableByList !== null) {
            $options = array_merge($options, $executableByList->getQueryArrayBare('ExecutableBy'));
        }
        if ($ownerList !== null) {
            $options = array_merge($options, $ownerList->getQueryArrayBare('Owner'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new ImageList();
            $result->setEc2($this->ec2);
            $result->setRequestId((string)$sxml->requestId);
            if (!empty($sxml->imagesSet->item)) {
                foreach ($sxml->imagesSet->item as $v) {
                    $imageId = (string) $v->imageId;
                    $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->image->get($imageId) : null;
                    if ($item === null) {
                        $item = new ImageData();
                        $item->setEc2($this->ec2);
                        $bAttach = true;
                    } else {
                        $item->resetObject();
                        $bAttach = false;
                    }
                    $item->imageId = $imageId;
                    $item->architecture = $this->exist($v->architecture) ? (string) $v->architecture : null;
                    $item->description = $this->exist($v->description) ? (string) $v->description : null;
                    $item->hypervisor = $this->exist($v->hypervisor) ? (string) $v->hypervisor : null;
                    $item->imageLocation = $this->exist($v->imageLocation) ? (string) $v->imageLocation : null;
                    $item->imageOwnerAlias = $this->exist($v->imageOwnerAlias) ? (string) $v->imageOwnerAlias : null;
                    $item->imageOwnerId = $this->exist($v->imageOwnerId) ? (string) $v->imageOwnerId : null;
                    $item->imageState = $this->exist($v->imageState) ? (string) $v->imageState : null;
                    $item->imageType = $this->exist($v->imageType) ? (string) $v->imageType : null;
                    $item->isPublic = $this->exist($v->isPublic) ? ((string) $v->isPublic == 'true') : null;
                    $item->kernelId = $this->exist($v->kernelId) ? (string) $v->kernelId : null;
                    $item->name = $this->exist($v->name) ? (string)$v->name : null;
                    $item->platform = $this->exist($v->platform) ? (string) $v->platform : null;
                    $item->ramdiskId = $this->exist($v->ramdiskId) ? (string) $v->ramdiskId : null;
                    $item->rootDeviceName = $this->exist($v->rootDeviceName) ? (string) $v->rootDeviceName : null;
                    $item->rootDeviceType = $this->exist($v->rootDeviceType) ? (string) $v->rootDeviceType : null;
                    $item->virtualizationType = $this->exist($v->virtualizationType) ? (string) $v->virtualizationType : null;
                    $item
                        ->setStateReason($this->_loadStateReasonData($v->stateReason))
                        ->setProductCodes($this->_loadProductCodeSetList($v->productCodes))
                        ->setTagSet($this->_loadResourceTagSetList($v->tagSet))
                        ->setBlockDeviceMapping($this->_loadBlockDeviceMappingList($v->blockDeviceMapping))
                    ;
                    $result->append($item);
                    if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                        $this->getEntityManager()->attach($item);
                    }
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads BlockDeviceMappingList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  BlockDeviceMappingList Returns BlockDeviceMappingList
     */
    protected function _loadBlockDeviceMappingList(\SimpleXMLElement $sxml)
    {
        $list = new BlockDeviceMappingList();
        $list->setEc2($this->ec2);
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = new BlockDeviceMappingData();
                $item->setEc2($this->ec2);
                $item->deviceName = $this->exist($v->deviceName) ? (string) $v->deviceName : null;
                $item->virtualName = $this->exist($v->virtualName) ? (string) $v->virtualName : null;
                $item->noDevice = $this->exist($v->noDevice) ? (string) $v->noDevice : null;
                $item->setEbs($this->_loadEbsBlockDeviceData($v->ebs));
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads EbsBlockDeviceData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  EbsBlockDeviceData Returns EbsBlockDeviceData
     */
    protected function _loadEbsBlockDeviceData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new EbsBlockDeviceData();
            $item->setEc2($this->ec2);
            $item->snapshotId = $this->exist($sxml->snapshotId) ? (string)$sxml->snapshotId : null;
            $item->volumeSize = $this->exist($sxml->volumeSize) ? (int)$sxml->volumeSize : null;
            $item->volumeType = $this->exist($sxml->volumeType) ? (string)$sxml->volumeType : null;
            $item->iops = $this->exist($sxml->iops) ? (int)$sxml->iops : null;
            $item->deleteOnTermination = $this->exist($sxml->deleteOnTermination) ? ((string)$sxml->deleteOnTermination == 'true') : null;
        }
        return $item;
    }

    /**
     * CreateImage action
     *
     * Creates an Amazon EBS-backed AMI from an Amazon EBS-backed instance that is either running or
     * stopped.
     * Note! If you customized your instance with instance store volumes or EBS volumes in addition to the
     * root device volume, the new AMI contains block device mapping information for those volumes.
     * When you launch an instance from this new AMI, the instance automatically launches with those
     * additional volumes.
     *
     * @param   CreateImageRequestData     $request   Request object
     * @return  string Returns ID of the created image on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createImage(CreateImageRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArrayBare();
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = (string) $sxml->imageId;
        }
        return $result;
    }

    /**
     * RegisterImage action
     *
     * Registers a new AMI with Amazon EC2. When you're creating an AMI, this is the final step you must
     * complete before you can launch an instance from the AMI
     *
     * @param   RegisterImageData $request Register Image request
     * @return  string            Returns the ID of the newly registered AMI
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function registerImage(RegisterImageData $request)
    {
        $result = null;
        $options = $request->getQueryArrayBare();
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = (string) $sxml->imageId;
        }
        return $result;
    }

    /**
     * DeregisterImage action
     *
     * Deregisters the specified AMI. Once deregistered, the AMI cannot be used to launch new instances.
     * Note! This command does not delete the AMI.
     *
     * @param   string      $imageId The ID of the AMI
     * @return  bool        Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deregisterImage($imageId)
    {
        $result = false;
        $options['ImageId'] = (string)$imageId;
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not deregister image "%s". It returned "%s"',
                    $options['ImageId'], $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->image->get($imageId) : null;
            if ($entity !== null) {
                $this->getEntityManager()->detach($entity);
            }
        }
        return $result;
    }

    /**
     * CreateKeyPair action
     *
     * Creates a new 2048-bit RSA key pair with the specified name. The public key is stored by Amazon EC2
     * and the private key is returned to you. The private key is returned as an unencrypted PEM encoded
     * PKCS#8 private key. If a key with the specified name already exists, Amazon EC2 returns an error.
     *
     * Tip! The key pair returned to you works only in the region you're using when you create the key pair.
     * To create a key pair that works in all regions, use ImportKeyPair
     *
     * @param   string       $keyName A unique name for the key pair.
     * @return  KeyPairData  Returns KeyPairData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createKeyPair($keyName)
    {
        $result = null;
        $options['KeyName'] = (string)$keyName;
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new KeyPairData();
            $result->setEc2($this->ec2);
            $result->keyName = (string) $sxml->keyName;
            $result->keyFingerprint = (string) $sxml->keyFingerprint;
            $result->keyMaterial = (string) $sxml->keyMaterial;
        }
        return $result;
    }

    /**
     * DeleteKeyPair action
     *
     * Deletes the specified key pair, by removing the public key from Amazon EC2.You must own the key pair
     *
     * @param   string       $keyName A unique key name for the key pair.
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteKeyPair($keyName)
    {
        $result = false;
        $options['KeyName'] = (string)$keyName;
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not delete key pair "%s". It returned "%s"',
                    $options['KeyName'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DescribeKeyPairs action
     *
     * Describes one or more of your key pairs.
     *
     * @param   ListDataType    $keyNameList  The list of the names
     * @param   array           $filter       Array of the key => value properties.
     * @return  KeyPairList     Returns KeyPairList on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeKeyPairs(ListDataType $keyNameList = null, array $filter = null)
    {
        //Filter parameter is excluded here and can be added in the future version of method.
        $result = null;
        $options = array();
        if ($keyNameList !== null) {
            $options = array_merge($options, $keyNameList->getQueryArrayBare('KeyName'));
        }
        if (!empty($filter)) {
            //Here we use list from another data set just to avoid duplicates.
            $arr = array();
            foreach ($filter as $k => $v) {
                array_push($arr, array(
                    'name'  => new KeyPairFilterNameType((string)$k),
                    'value' => (string) $v,
                ));
            }
            $flt = new KeyPairFilterList($arr);
            $options = array_merge($options, $flt->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new KeyPairList();
            $result->setEc2($this->ec2);
            $result->setRequestId($sxml->requestId);
            if (isset($sxml->keySet->item)) {
                foreach ($sxml->keySet->item as $v) {
                    $item = new KeyPairData();
                    $item->setEc2($this->ec2);
                    $item->keyName = (string) $v->keyName;
                    $item->keyFingerprint = (string) $v->keyFingerprint;
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * DescribeAddresses action
     *
     * Describes one or more of the Elastic IP addresses allocated to your account.
     * This action applies to both EC2 and VPC Elastic IP addresses.
     *
     * @param   ListDataType      $publicIpList     optional One or more EC2 Elastic IP addresses
     * @param   ListDataType      $allocationIdList optional One or more allocation IDs corresponding to the address
     *                                                       or addresses to describe (VPC addresses only).
     * @param   AddressFilterList $filter           optional The filter list.
     * @return  AddressList       Returns AddressList on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeAddresses(ListDataType $publicIpList = null, ListDataType $allocationIdList = null,
                                      AddressFilterList $filter = null)
    {
        $result = null;
        $options = array();
        if ($publicIpList !== null) {
            $options = array_merge($options, $publicIpList->getQueryArrayBare('PublicIp'));
        }
        if ($allocationIdList !== null) {
            $options = array_merge($options, $allocationIdList->getQueryArrayBare('AllocationId'));
        }
        if (!empty($filter)) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new AddressList();
            $result->setEc2($this->ec2);
            $result->setRequestId($sxml->requestId);
            if (isset($sxml->addressesSet->item)) {
                foreach ($sxml->addressesSet->item as $v) {
                    $item = new AddressData();
                    $item->setEc2($this->ec2);
                    $item->allocationId = $this->exist($v->allocationId) ? (string) $v->allocationId : null;
                    $item->associationId = $this->exist($v->associationId) ? (string) $v->associationId : null;
                    $item->domain = $this->exist($v->domain) ? (string) $v->domain : null;
                    $item->instanceId = $this->exist($v->instanceId) ? (string) $v->instanceId : null;
                    $item->publicIp = $this->exist($v->publicIp) ? (string) $v->publicIp : null;
                    $item->privateIpAddress = $this->exist($v->privateIpAddress) ? (string) $v->privateIpAddress : null;
                    $item->networkInterfaceId = $this->exist($v->networkInterfaceId) ? (string) $v->networkInterfaceId : null;
                    $item->networkInterfaceOwnerId = $this->exist($v->networkInterfaceOwnerId) ? (string) $v->networkInterfaceOwnerId : null;
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * AssociateAddress action
     *
     * Associates an Elastic IP address with an instance.
     * This action applies to both EC2 and VPC Elastic IP addresses.
     *
     * EC2: If the IP address is currently assigned to another instance,
     * the IP address is assigned to the new instance.
     *
     * VPC: If the IP address is currently assigned to another instance, Amazon EC2 returns an error unless
     * you specify the AllowReassociation parameter
     *
     * This is an idempotent operation. If you enter it more than once, Amazon EC2 does not return an error
     *
     * @param   AssociateAddressRequestData $request
     * @return  bool|string       Returns associationId (for VPC Elastic IP addresses) or boolean TRUE (for others) on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function associateAddress(AssociateAddressRequestData $request)
    {
        $result = false;
        $options = $request->getQueryArrayBare();
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not associate elastic IP address. It returned "%s"',
                    $sxml->return
                ));
            }
            $result = $this->exist($sxml->associationId) ? (string) associationId : true;
        }
        return $result;
    }

    /**
     * DisassociateAddress action
     *
     * Disassociates an Elastic IP address from the instance it's assigned to.
     * This action applies to both EC2 Elastic IP addresses and VPC Elastic IP addresses.
     * This is an idempotent action. If you enter it more than once, Amazon EC2 does not return an error
     *
     * @param   string       $publicIp      optional The EC2 Elastic IP address.
     *                                      Condition: Required for EC2 Elastic IP addresses
     * @param   string       $associationId optional The association ID corresponding to the VPC Elastic IP Conditional
     *                                      address. Condition: Required for VPC Elastic IP addresses
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function disassociateAddress($publicIp = null, $associationId = null)
    {
        if ($publicIp === null && $associationId === null) {
            throw new \InvalidArgumentException('Either publicIp or associationId must be provided.');
        }
        $result = false;
        $options = array();
        if ($publicIp !== null) {
            $options['PublicIp'] = (string) $publicIp;
        }
        if ($associationId !== null) {
            $options['AssociationId'] = (string) $associationId;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not disassociate elastic IP address. It returned "%s"',
                    $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * AllocateAddress action
     *
     * Acquires an Elastic IP address for use with your instances.
     *
     * @param   string    $domain  optional Set to vpc to allocate the address for use with instances in a VPC.
     *                             Valid values: vpc. Condition: Required when allocating an address for use
     *                             with instances in a VPC
     * @return  AddressData        Returns AddressData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function allocateAddress($domain = null)
    {
        $result = null;
        $options = array();
        if ($domain !== null) {
            $options['Domain'] = (string) $domain;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new AddressData();
            $result->setEc2($this->ec2);
            $result->domain = (string) $sxml->domain;
            $result->publicIp = $this->exist($sxml->publicIp) ? (string) $sxml->publicIp : null;
            $result->allocationId = $this->exist($sxml->allocationId) ? (string) $sxml->allocationId : null;
        }
        return $result;
    }

    /**
     * ReleaseAddress action
     *
     * Releases an Elastic IP address allocated to your account.
     * Important! After releasing an Elastic IP address, it is released to the IP address pool and might be unavailable
     * to your account. Make sure to update your DNS records and any servers or devices that communicate with the address.
     *
     * @param   string       $publicIp     optional The EC2 Elastic IP address
     * @param   string       $allocationId optional The allocation ID that AWS provided when you allocated
     *                                              the address for use with Amazon VPC.
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function releaseAddress($publicIp = null, $allocationId = null)
    {
        if ($publicIp === null && $allocationId === null) {
            throw new \InvalidArgumentException('Either publicIp or allocationId must be provided.');
        }
        $result = false;
        $options = array();
        if ($publicIp !== null) {
            $options['PublicIp'] = (string) $publicIp;
        }
        if ($allocationId !== null) {
            $options['AllocationId'] = (string) $allocationId;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not release elastic IP address "%s". It returned "%s"',
                    (isset($options['AllocationId']) ? $options['AllocationId'] : $options['PublicIp']),
                    $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DescribeSnapshots action
     *
     * Describes one or more of the Amazon EBS snapshots available to you. Snapshots available to you include
     * public snapshots available for any AWS account to launch, private snapshots you own, and private
     * snapshots owned by another AWS account but for which you've been given explicit create volume
     * permissions.
     *
     * @param   ListDataType       $snapshotIdList   optional One or more snapshot IDs. By default it describes snapshots
     *                                                        for which you have launch permissions.
     * @param   ListDataType       $ownerList        optional Returns the snapshots owned by the specified owner.
     *                                                        Multiple owners can be specified.
     *                                                        Valid values: self | amazon | AWS Account ID
     * @param   SnapshotFilterList $filter           optional The list of filters.
     * @param   ListDataType       $restorableByList optional One or more AWS accounts IDs that can create volumes
     *                                                        from the snapshot.
     * @return  SnapshotList       Returns the list of snapshots on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeSnapshots(ListDataType $snapshotIdList = null, ListDataType $ownerList = null,
                                      SnapshotFilterList $filter = null, ListDataType $restorableByList = null)
    {
        $result = null;
        $options = array();
        if ($snapshotIdList !== null) {
            $options = array_merge($options, $snapshotIdList->getQueryArrayBare('SnapshotId'));
        }
        if ($ownerList !== null) {
            $options = array_merge($options, $ownerList->getQueryArrayBare('Owner'));
        }
        if ($restorableByList !== null) {
            $options = array_merge($options, $restorableByList->getQueryArrayBare('RestorableBy'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new SnapshotList();
            $result->setEc2($this->ec2);
            $result->setRequestId((string) $sxml->requestId);
            if (!empty($sxml->snapshotSet->item)) {
                foreach ($sxml->snapshotSet->item as $v) {
                    $item = $this->_loadSnapshotData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads SnapshotData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  SnapshotData Returns SnapshotData
     */
    protected function _loadSnapshotData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $snapshotId = (string)$v->snapshotId;
            $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->snapshot->get($snapshotId) : null;
            if ($item === null) {
                $item = new SnapshotData();
                $item->setEc2($this->ec2);
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }
            $item->snapshotId = $snapshotId;
            $item->volumeId = (string) $v->volumeId;
            $item->volumeSize = (string) $v->volumeSize;
            $item->ownerId = (string) $v->ownerId;
            $item->progress = (string) $v->progress;
            $item->status = (string) $v->status;
            $item->startTime = new DateTime((string)$v->startTime, new DateTimeZone('UTC'));
            $item->description = $this->exist($v->description) ? (string) $v->description : null;
            $item->ownerAlias = $this->exist($v->ownerAlias) ? (string) $v->ownerAlias : null;
            $item->setTagSet($this->_loadResourceTagSetList($v->tagSet));

            if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * CreateSnapshot action
     *
     * Creates a snapshot of an Amazon EBS volume and stores it in Amazon S3.You can use snapshots for
     * backups, to make copies of instance store volumes, and to save data before shutting down an instance.
     *
     * @param   string     $volumeId    The ID of the Amazon EBS volume.
     * @param   string     $description optional A description of the Amazon EBS snapshot. (Up to 255 characters)
     * @return  SnapshotData            Returns the SnapshotData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createSnapshot($volumeId, $description = null)
    {
        $result = null;
        $options = array(
            'VolumeId' => (string) $volumeId,
        );
        if ($description !== null) {
            $options['Description'] = (string) $description;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadSnapshotData($sxml);
        }
        return $result;
    }

    /**
     * DeleteSnapshot action
     *
     * Deletes a snapshot of an Amazon EBS volume.
     * Note! If you make periodic snapshots of a volume, the snapshots are incremental so that only the
     * blocks on the device that have changed since your last snapshot are incrementally saved in the
     * new snapshot. Even though snapshots are saved incrementally, the snapshot deletion process
     * is designed so that you need to retain only the most recent snapshot in order to restore the
     * volume.
     *
     * @param   string       $snapshotId The ID of the Amazon EBS snapshot.
     * @return  bool         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteSnapshot($snapshotId)
    {
        $result = false;
        $options = array(
            'SnapshotId' => (string) $snapshotId,
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not delete snapshot "%s". It returned "%s"',
                    $options['SnapshotId'], $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->snapshot->get($options['SnapshotId']) : null;
            if ($entity !== null) {
                $this->getEntityManager()->detach($entity);
            }
        }
        return $result;
    }

    /**
     * CopySnapshot action
     *
     * Copies a point-in-time snapshot of an Amazon Elastic Block Store (Amazon EBS) volume and stores it
     * in Amazon Simple Storage Service (Amazon S3).You can copy the snapshot within the same region or
     * from one region to another.You can use the snapshot to create new Amazon EBS volumes or Amazon
     * Machine Images (AMIs)
     *
     * @param   string     $srcRegion     The ID of the AWS region that contains the snapshot to be copied.
     * @param   string     $srcSnapshotId The ID of the Amazon EBS snapshot to copy.
     * @param   string     $description   optional A description of the new Amazon EBS snapshot.
     * @param   string     $destRegion    optional The ID of the destination region.
     * @return  string     Returns ID of the created snapshot on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function copySnapshot($srcRegion, $srcSnapshotId, $description = null, $destRegion = null)
    {
        $result = null;
        $options = array(
            'SourceRegion'     => (string) $srcRegion,
            'SourceSnapshotId' => (string) $srcSnapshotId,
        );
        if ($description !== null) {
            $options['Description'] = (string) $description;
        }
        if ($destRegion !== null) {
            //It overrides region to copy
            $options['_host'] = $this->ec2->getUrl($destRegion);
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = (string) $sxml->snapshotId;
        }
        return $result;
    }

    /**
     * DescribeSubnets action
     *
     * Describes one or more of your subnets.
     *
     * @param   ListDataType     $subnetIdList optional A subnet ID list.
     * @param   SubnetFilterList $filter       optional The list of the filters.
     * @return  SubnetList       Returns the list of found subnets on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeSubnets(ListDataType $subnetIdList = null, SubnetFilterList $filter = null)
    {
        $result = null;
        $options = array();
        if ($subnetIdList !== null) {
            $options = array_merge($options, $subnetIdList->getQueryArrayBare('SubnetId'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new SubnetList();
            $result->setEc2($this->ec2);
            $result->setRequestId((string) $sxml->requestId);
            if (isset($sxml->subnetSet->item)) {
                foreach ($sxml->subnetSet->item as $v) {
                    $item = $this->_loadSubnetData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads SubnetData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  SubnetData Returns SubnetData
     */
    protected function _loadSubnetData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $subnetId = (string)$v->subnetId;
            $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->subnet->get($subnetId) : null;
            if ($item === null) {
                $item = new SubnetData();
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }
            $item->setEc2($this->ec2);
            $item->availabilityZone = $this->exist($v->availabilityZone) ? (string) $v->availabilityZone : null;
            $item->availableIpAddressCount = $this->exist($v->availableIpAddressCount) ? (int) $v->availableIpAddressCount : null;
            $item->cidrBlock = $this->exist($v->cidrBlock) ? (string) $v->cidrBlock : null;
            $item->state = $this->exist($v->state) ? (string) $v->state : null;
            $item->subnetId = $subnetId;
            $item->vpcId = $this->exist($v->vpcId) ? (string) $v->vpcId : null;
            $item->setTagSet($this->_loadResourceTagSetList($v->tagSet));

            if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * CreateSubnet action
     *
     * Creates a subnet in an existing VPC. You can create up to 20 subnets in a VPC. If you add more than
     * one subnet to a VPC, they're set up in a star topology with a logical router in the middle.
     *
     * @param   string     $vpcId            The ID of the VPC.
     * @param   string     $cidrBlock        The CIDR block for the subnet to cover (for example, 10.0.0.0/24).
     * @param   string     $availabilityZone optional The Availability Zone for the subnet. By default AWS
     *                                       selects a zone for you (recommended)
     * @return  SubnetData       Returns the SubnetData on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createSubnet($vpcId, $cidrBlock, $availabilityZone = null)
    {
        $result = null;
        $options = array(
            'VpcId'     => (string) $vpcId,
            'CidrBlock' => (string) $cidrBlock,
        );
        if ($availabilityZone !== null) {
            $options['AvailabilityZone'] = (string) $availabilityZone;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadSubnetData($sxml->subnet);
        }
        return $result;
    }

    /**
     * DeleteSubnet action
     *
     * Deletes a subnet from a VPC. You must terminate all running instances in the subnet before deleting it,
     * otherwise Amazon VPC returns an error
     *
     * @param   string       $subnetId   The ID of the subnet.
     * @return  bool         Returns TRUE on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteSubnet($subnetId)
    {
        $result = null;
        $options = array(
            'SubnetId'     => (string) $subnetId,
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not delete subnet "%s". It returned "%s"',
                    $options['SubnetId'], $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->subnet->get($options['SubnetId']) : null;
            if ($entity !== null) {
                $this->getEntityManager()->detach($entity);
            }
        }
        return $result;
    }

    /**
     * GetConsoleOutput action
     *
     * Retrieves console output for the specified instance.
     * Instance console output is buffered and posted shortly after instance boot, reboot, and termination.
     * Amazon EC2 preserves the most recent 64 KB output which will be available for at least one hour after
     * the most recent post.
     *
     * @param   string      $instanceId       The ID of the EC2 instance.
     * @return  GetConsoleOutputResponseData  Returns object which represents console output.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function getConsoleOutput($instanceId)
    {
        $result = null;
        $options = array(
            'InstanceId' => (string) $instanceId,
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new GetConsoleOutputResponseData();
            $result->setEc2($this->ec2);
            $result->output = (string)$sxml->output;
            $result->timestamp = new DateTime((string)$sxml->timestamp, new DateTimeZone('UTC'));
            $result->instanceId = (string)$sxml->instanceId;
            $result->setRequestId((string)$sxml->requestId);
        }
        return $result;
    }

    /**
     * DescribePlacementGroups action
     *
     * Describes one or more of your placement groups.
     *
     * @param   ListDataType             $groupNameList optional One or more placement group names.
     * @param   PlacementGroupFilterList $filter        optional The list of the filters.
     * @return  PlacementGroupList       Returns the list of the PlacementGroupData objects on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describePlacementGroups(ListDataType $groupNameList = null, PlacementGroupFilterList $filter = null)
    {
        $result = null;
        $options = array();
        if ($groupNameList !== null) {
            $options = array_merge($options, $groupNameList->getQueryArrayBare('GroupName'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new PlacementGroupList();
            $result->setEc2($this->ec2);
            $result->setRequestId((string) $sxml->requestId);
            if (isset($sxml->placementGroupSet->item)) {
                foreach ($sxml->placementGroupSet->item as $v) {
                    $item = $this->_loadPlacementGroupData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads PlacementGroupData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  PlacementGroupData Returns PlacementGroupData
     */
    protected function _loadPlacementGroupData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $groupName = (string)$v->groupName;
            $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->placementGroup->get($groupName) : null;
            if ($item === null) {
                $item = new PlacementGroupData();
                $item->setEc2($this->ec2);
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }
            $item->groupName = $groupName;
            $item->state = (string)$v->state;
            $item->strategy = (string)$v->strategy;
            if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * CreatePlacementGroup action
     *
     * Creates a placement group that you launch cluster instances into.You must give the group a name unique
     * within the scope of your account.
     *
     * @param   string       $groupName The name of the placement group.
     * @param   string       $strategy  optional The placement group strategy.
     * @return  bool         Returns True on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createPlacementGroup($groupName, $strategy = 'cluster')
    {
        $result = false;
        $options = array(
            'GroupName' => (string) $groupName,
            'Strategy'  => (string) $strategy,
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not create placement group "%s". It returned "%s"',
                    $options['GroupName'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DeletePlacementGroup action
     *
     * Deletes a placement group from your account.You must terminate all instances in the placement group
     * before deleting it.
     *
     * @param   string       $groupName   The name of the placement group.
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deletePlacementGroup($groupName)
    {
        $result = false;
        $options = array(
            'GroupName' => (string) $groupName,
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not delete placement group "%s". It returned "%s"',
                    $options['GroupName'], $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->placementGroup->get($options['GroupName']) : null;
            if ($entity !== null) {
                $this->getEntityManager()->detach($entity);
            }
        }
        return $result;
    }

    /**
     * CopyImage action
     *
     * Initiates the copy of an AMI from the specified source region to the region in which the API call is executed
     *
     * @param   string     $srcRegion     The ID of the AWS region that contains the AMI to be copied.
     * @param   string     $srcImageId    The ID of the Amazon EC2 AMI to copy.
     * @param   string     $name          optional The name of the new Amazon EC2 AMI.
     * @param   string     $description   optional A description of the new EC2 AMI in the destination region.
     * @param   string     $clientToken   optional Unique, case-sensitive identifier you provide to ensure
     *                                             idempotency of the request
     * @param   string     $destRegion    optional The ID of the destination region.
     * @return  string     Returns ID of the created image on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function copyImage($srcRegion, $srcImageId, $name = null, $description = null,
                              $clientToken = null, $destRegion = null)
    {
        $result = null;
        $options = array(
            'SourceRegion'     => (string) $srcRegion,
            'SourceImageId'    => (string) $srcImageId,
        );
        if ($description !== null) {
            $options['Description'] = (string) $description;
        }
        if ($name !== null) {
            $options['Name'] = (string) $name;
        }
        if ($name !== null) {
            $options['ClientToken'] = (string) $clientToken;
        }
        if ($destRegion !== null) {
            //It overrides region to copy
            $options['_host'] = $this->ec2->getUrl($destRegion);
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = (string) $sxml->imageId;
        }
        return $result;
    }

    /**
     * DescribeAccountAttributes
     *
     * Describes the specified attribute of your AWS account.
     *
     * @param   ListDataType $attributeName List of the The following table lists the supported account attributes
     * @return  AccountAttributeSetList     Returns list of the names and values of the requested attributes
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeAccountAttributes(ListDataType $attributeName)
    {
        $result = null;
        $options = $attributeName->getQueryArrayBare('AttributeName');
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = new AccountAttributeSetList();
            $result->setEc2($this->ec2);
            if (isset($sxml->accountAttributeSet->item)) {
                foreach ($sxml->accountAttributeSet->item as $v) {
                    $item = new AccountAttributeSetData();
                    $item->setEc2($this->ec2);
                    $item->attributeName = (string)$v->attributeName;
                    $item->attributeValueSet = new AccountAttributeValueList();
                    $item->attributeValueSet->setEc2($this->ec2);
                    foreach ($v->attributeValueSet->item as $r) {
                        $val = new AccountAttributeValueData();
                        $val->setEc2($this->ec2);
                        $val->attributeValue = (string)$r->attributeValue;
                        $item->attributeValueSet->append($val);
                        unset($val);
                    }
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * DescribeVpcs action
     *
     * Describes one or more of your VPCs.
     *
     * @param   ListDataType  $vpcIdList optional The list of the VpcID
     * @param   VpcFilterList $filter    optional The filter list
     * @return  VpcList       Returns the list of the Vpcs
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeVpcs(ListDataType $vpcIdList = null, VpcFilterList $filter = null)
    {
        $result = null;
        $options = array();
        $action = ucfirst(__FUNCTION__);
        if ($vpcIdList !== null) {
            $options = array_merge($options, $vpcIdList->getQueryArrayBare('VpcId'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = $this->_loadListByName('Vpc', $sxml->vpcSet);
            $result->setRequestId((string)$sxml->requestId);
        }
        return $result;
    }

    /**
     * Loads VpcData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  VpcData Returns VpcData
     */
    protected function _loadVpcData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $vpcId = (string) $v->vpcId;
            $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->vpc->get($vpcId) : null;
            if ($item === null) {
                $item = new VpcData();
                $item->setEc2($this->ec2);
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }
            $item->vpcId = $vpcId;
            $item->cidrBlock = $this->exist($v->cidrBlock) ? (string) $v->cidrBlock : null;
            $item->dhcpOptionsId = $this->exist($v->dhcpOptionsId) ? (string) $v->dhcpOptionsId : null;
            $item->instanceTenancy = $this->exist($v->instanceTenancy) ? (string) $v->instanceTenancy : null;
            $item->isDefault = $this->exist($v->isDefault) ? (((string)$v->isDefault) == 'true') : null;
            $item->state = $this->exist($v->state) ? (string) $v->state : null;
            $item->setTagSet($this->_loadResourceTagSetList($v->tagSet));

            if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * DescribeNetworkInterfaces action
     *
     * Describes one or more of your network interfaces.
     *
     * @param   ListDataType               $networkInterfaceIdList optional One or more network interface IDs
     * @param   NetworkInterfaceFilterList $filter                 optional Filter list
     * @return  NetworkInterfaceList       Returns the list of the Network Interfaces
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeNetworkInterfaces(ListDataType $networkInterfaceIdList = null,
                                              NetworkInterfaceFilterList $filter = null)
    {
        $result = null;
        $options = array();
        $action = ucfirst(__FUNCTION__);
        if ($networkInterfaceIdList !== null) {
            $options = array_merge($options, $networkInterfaceIdList->getQueryArrayBare('NetworkInterfaceId'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = $this->_loadListByName('NetworkInterface', $sxml->networkInterfaceSet);
            $result->requestId = (string)$sxml->requestId;
        }
        return $result;
    }

    /**
     * Loads NetworkInterfaceData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  NetworkInterfaceData Returns NetworkInterfaceData
     */
    protected function _loadNetworkInterfaceData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $newtorkInterfaceId = (string)$v->networkInterfaceId;
            $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->networkInterface->get($newtorkInterfaceId) : null;
            if ($item === null) {
                $item = new NetworkInterfaceData();
                $item->setEc2($this->ec2);
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }
            $item->networkInterfaceId = $newtorkInterfaceId;
            $item->availabilityZone = $this->exist($v->availabilityZone) ? (string)$v->availabilityZone : null;
            $item->description = $this->exist($v->description) ? (string)$v->description : null;
            $item->macAddress = (string)$v->macAddress;
            $item->ownerId = (string)$v->ownerId;
            $item->privateDnsName = $this->exist($v->privateDnsName) ? (string)$v->privateDnsName : null;
            $item->privateIpAddress = (string)$v->privateIpAddress;
            $item->requesterId = $this->exist($v->requesterId) ? (string)$v->requesterId : null;
            $item->requesterManaged = $this->exist($v->requesterManaged) ? (string)$v->requesterManaged : null;
            $item->sourceDestCheck = (((string)$v->sourceDestCheck) == 'true');
            $item->status = (string)$v->status;
            $item->subnetId = $this->exist($v->subnetId) ? (string)$v->subnetId : null;
            $item->vpcId = $this->exist($v->vpcId) ? (string)$v->vpcId : null;
            $item
                ->setAssociation($this->_loadNetworkInterfaceAssociationData($v->association))
                ->setAttachment($this->_loadNetworkInterfaceAttachmentData($v->attachment))
                ->setGroupSet($this->_loadGroupList($v->groupSet))
                ->setTagSet($this->_loadResourceTagSetList($v->tagSet))
                ->setPrivateIpAddressesSet($this->_loadListByName('NetworkInterfacePrivateIpAddressesSet', $v->privateIpAddressesSet))
            ;
            if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * Loads NetworkInterfaceAssociationData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  NetworkInterfaceAssociationData Returns NetworkInterfaceAssociationData
     */
    protected function _loadNetworkInterfaceAssociationData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new NetworkInterfaceAssociationData();
            $item->setEc2($this->ec2);
            $item->allocationId = $this->exist($v->allocationId) ? (string)$v->allocationId : null;
            $item->associationId = $this->exist($v->associationId) ? (string)$v->associationId : null;
            $item->ipOwnerId = $this->exist($v->ipOwnerId) ? (string)$v->ipOwnerId : null;
            $item->publicDnsName = $this->exist($v->publicDnsName) ? (string)$v->publicDnsName : null;
            $item->publicIp = (string)$v->publicIp;
        }
        return $item;
    }

    /**
     * Loads NetworkInterfaceAttachmentData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  NetworkInterfaceAttachmentData Returns NetworkInterfaceAttachmentData
     */
    protected function _loadNetworkInterfaceAttachmentData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new NetworkInterfaceAttachmentData();
            $item->setEc2($this->ec2);
            $item->attachmentId = (string)$v->attachmentId;
            $item->attachTime = new DateTime((string)$v->attachTime, new DateTimeZone('UTC'));
            $item->deleteOnTermination = (((string)$v->deleteOnTermination) == 'true');
            $item->deviceIndex = (int)$v->deviceIndex;
            $item->instanceId = $this->exist($v->instanceId) ? (string)$v->instanceId : null;
            $item->instanceOwnerId = $this->exist($v->instanceOwnerId) ? (string)$v->instanceOwnerId : null;
            $item->status = (string)$v->status;
        }
        return $item;
    }

    /**
     * Loads NetworkInterfacePrivateIpAddressesSetData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  NetworkInterfacePrivateIpAddressesSetData Returns NetworkInterfacePrivateIpAddressesSetData
     */
    protected function _loadNetworkInterfacePrivateIpAddressesSetData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new NetworkInterfacePrivateIpAddressesSetData();
            $item->setEc2($this->ec2);
            $item->primary = ((string)$v->primary == 'true');
            $item->privateDnsName = $this->exist($v->privateDnsName) ? (string)$v->privateDnsName : null;
            $item->privateIpAddress = (string)$v->privateIpAddress;
            $item->setAssociation($this->_loadNetworkInterfaceAssociationData($v->association));
        }
        return $item;
    }

    /**
     * CreateNetworkInterface action
     *
     * Creates a network interface in the specified subnet
     *
     * @param   CreateNetworkInterfaceRequestData $request   Create Request
     * @return  NetworkInterfaceData Returns created Network Interface
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createNetworkInterface(CreateNetworkInterfaceRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArrayBare();
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadNetworkInterfaceData($sxml->networkInterface);
        }
        return $result;
    }

    /**
     * CreateVpc action
     *
     * Creates a VPC with the specified CIDR block.
     * The smallest VPC you can create uses a /28 netmask (16 IP addresses),
     * and the largest uses a /16 netmask (65,536 IP addresses).
     *
     * By default, each instance you launch in the VPC has the default DHCP options, which includes only a
     * default DNS server that we provide (AmazonProvidedDNS)
     *
     * @param   string     $cidrBlock       The CIDR block you want the VPC to cover (for example, 10.0.0.0/16).
     * @param   string     $instanceTenancy optional The supported tenancy options for instances
     *                                      launched into the VPC. A value of default means
     *                                      that instances can be launched with any tenancy;
     *                                      a value of dedicated means all instances are
     *                                      launched as dedicated tenancy instances
     *                                      regardless of the tenancy assigned to the instance
     *                                      at launch. Setting the instance tenancy to
     *                                      dedicated runs your instance on single-tenant
     *                                      hardware.
     * @return  VpcData Returns VpcData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createVpc($cidrBlock, $instanceTenancy = null)
    {
        $result = null;
        $options = array(
            'CidrBlock' => (string) $cidrBlock,
        );
        if ($instanceTenancy != null) {
            $options['InstanceTenancy'] = (string) $instanceTenancy;
        }
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadVpcData($sxml->vpc);
        }
        return $result;
    }

    /**
     * DeleteVpc action
     *
     * Deletes a VPC.You must detach or delete all gateways or
     * other objects that are dependent on the VPC
     * first. For example, you must terminate all running instances,
     * delete all security groups (except the default),
     * delete all the route tables (except the default), and so on.
     *
     * @param   string      $vpcId The ID of the VPC
     * @return  bool Returns true on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteVpc($vpcId)
    {
        $result = null;
        $options = array(
            'VpcId' => (string) $vpcId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s "%s". It returned "%s"',
                    $action, $options['VpcId'], $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->vpc->get($options['VpcId']) : null;
            if ($entity !== null) {
                $this->getEntityManager()->detach($entity);
            }
        }
        return $result;
    }

    /**
     * DeleteNetworkInterface action
     *
     * Deletes the specified network interface.
     *
     * @param   string     $networkInterfaceId The ID of the network interface
     * @return  bool Returns true on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteNetworkInterface($networkInterfaceId)
    {
        $result = false;
        $options = array(
            'NetworkInterfaceId' => (string) $networkInterfaceId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s "%s". It returned "%s"',
                    $action, $options['NetworkInterfaceId'], $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ? $this->ec2->networkInterface->get($options['NetworkInterfaceId']) : null;
            if ($entity !== null) {
                $this->getEntityManager()->detach($entity);
            }
        }
        return $result;
    }

    /**
     * AttachNetworkInterface action
     *
     * Attaches a network interface to an instance.
     *
     * @param   string       $networkInterfaceId The ID of the network interface
     * @param   string       $instanceId         The ID of the instance to attach to the network interface
     * @param   int          $deviceIndex        The index of the device for the network interface attachment.
     * @return  string  Returns Attachment ID on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attachNetworkInterface($networkInterfaceId, $instanceId, $deviceIndex)
    {
        $result = null;
        $options = array(
            'NetworkInterfaceId' => (string) $networkInterfaceId,
            'InstanceId'         => (string) $instanceId,
            'DeviceIndex'        => (string) $deviceIndex,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = (string) $sxml->attachmentId;
        }
        return $result;
    }

    /**
     * DetachNetworkInterface
     *
     * Detaches a network interface from an instance
     *
     * @param   string     $attachmentId The ID of the Attachment
     * @param   bool       $force        optional Set to TRUE to force a detachment
     * @return  bool  Returns TRUE on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detachNetworkInterface($attachmentId, $force = null)
    {
        $result = null;
        $options = array(
            'AttachmentId' => (string) $attachmentId,
        );
        if ($force !== null) {
            $options['Force'] = ($force ? 'true' : 'false');
        }
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s "%s". It returned "%s"',
                    $action, $options['AttachmentId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DescribeNetworkInterfaceAttribute action
     *
     * Describes a network interface attribute.You can specify only one attribute at a time.
     *
     * @param   string                        $networkInterfaceId The ID of the network interface.
     * @param   NetworkInterfaceAttributeType $attribute          The attribute.
     * @return  mixed  Returns attribute value. It can be string, boolean, NetworkInterfaceAttachmentData or
     *                 GroupList depends on attribute value you provided.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeNetworkInterfaceAttribute($networkInterfaceId, NetworkInterfaceAttributeType $attribute)
    {
        $result = null;
        $options = array(
            'NetworkInterfaceId' => (string) $networkInterfaceId,
            'Attribute'          => (string) $attribute,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $ptr = $sxml->{$options['Attribute']};
            $entity = $this->ec2->getEntityManagerEnabled() ?
                $this->ec2->networkInterface->get($options['NetworkInterfaceId']) : null;
            switch ($options['Attribute']) {
                case NetworkInterfaceAttributeType::ATTR_DESCRIPTION :
                    $result = (string)$ptr->value;
                    break;
                case NetworkInterfaceAttributeType::ATTR_SOURCE_DEST_CHECK :
                    $result = ((string)$ptr->value == 'true');
                    break;
                case NetworkInterfaceAttributeType::ATTR_ATTACHMENT :
                    $result = $this->_loadNetworkInterfaceAttachmentData($ptr);
                    break;
                case NetworkInterfaceAttributeType::ATTR_GROUP_SET :
                    $result = $this->_loadGroupList($ptr);
                    break;
                default :
                    throw new \InvalidArgumentException(sprintf(
                        'Unexpected attribute "%s" in %s call',
                        $options['Attribute'], $action
                    ));
            }
            if ($entity !== null) {
                $entity->{$options['Attribute']} = $result;
            }
        }
        return $result;
    }

    /**
     * ModifyNetworkInterfaceAttribute action
     *
     * Modifies a network interface attribute.You can specify only one attribute at a time.
     *
     * @param   string                        $networkInterfaceId The ID of the network interface.
     * @param   NetworkInterfaceAttributeType $attr               The attribute name.
     * @param   mixed                         $value              The attribute value.
     * @return  bool  Returns TRUE on success
     * @throws  \BadFunctionCallException
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function modifyNetworkInterfaceAttribute($networkInterfaceId, NetworkInterfaceAttributeType $attr, $value)
    {
        $result = false;
        $options = array(
            'NetworkInterfaceId' => (string) $networkInterfaceId,
        );
        $action = ucfirst(__FUNCTION__);
        $attr = (string) $attr;
        if ($attr == NetworkInterfaceAttributeType::ATTR_DESCRIPTION) {
            $options['Description.Value'] = (string) $value;
        } elseif ($attr == NetworkInterfaceAttributeType::ATTR_GROUP_SET) {
            if ($value instanceof GroupList) {
                $s = array();
                foreach ($value as $v) {
                    $s[] = $v->groupId;
                }
                $value = new ListDataType($s);
                unset($s);
            } elseif (!($value instanceof ListDataType)) {
                $value = new ListDataType($value);
            }
            $options = array_merge($options, $value->getQueryArrayBare('SecurityGroupId'));
        } elseif ($attr == NetworkInterfaceAttributeType::ATTR_SOURCE_DEST_CHECK) {
            $options['SourceDestCheck.Value'] = (($value === 'true' || $value === 'false') ? $value :
                ($value ? true : false));
        } elseif ($attr == NetworkInterfaceAttributeType::ATTR_ATTACHMENT) {
            if ($value instanceof NetworkInterfaceAttachmentData) {
                if ($value->attachmentId !== null) {
                    $options['Attachment.AttachmentId'] = $value->attachmentId;
                }
                if ($value->deleteOnTermination !== null) {
                    $options['Attachment.DeleteOnTermination'] = $value->deleteOnTermination ? 'true' : 'false';
                }
            } elseif (is_array($value) && (array_key_exists('attachmentId', $value) ||
                      array_key_exists('deleteOnTermination', $value))) {
                if ($value['attachmentId'] !== null) {
                    $options['Attachment.AttachmentId'] = $value['attachmentId'];
                }
                if ($value['deleteOnTermination'] !== null) {
                    $options['Attachment.DeleteOnTermination'] =
                        (($value['deleteOnTermination'] === 'true' || $value['deleteOnTermination'] === 'false') ?
                            $value['deleteOnTermination'] : ($value['deleteOnTermination'] ? true : false));
                }
            } else {
                throw new \BadFunctionCallException(sprintf(
                    'Invalid value for the "attachment" attribute in %s call', $action
                ));
            }
        }
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s NetworkInterfaceId:"%s" Attribute:"%s". It returned "%s"',
                    $action, $options['NetworkInterfaceId'], $attr, $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ?
                $this->ec2->networkInterface->get($options['NetworkInterfaceId']) : null;
            //It does not update attribute value stored in the entity manager.
        }
        return $result;
    }

    /**
     * ResetNetworkInterfaceAttribute action
     *
     * Resets a network interface attribute.You can specify only one attribute at a time
     *
     * @param   string                        $networkInterfaceId The ID of the network interface.
     * @param   NetworkInterfaceAttributeType $attr               The attribute name.
     * @return  bool  Returns TRUE on success
     * @throws  \BadFunctionCallException
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function resetNetworkInterfaceAttribute($networkInterfaceId, NetworkInterfaceAttributeType $attr)
    {
        $result = false;
        $options = array(
            'NetworkInterfaceId' => (string) $networkInterfaceId,
            'Attribute'          => (string) $attr,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s NetworkInterfaceId:"%s" Attribute:"%s". It returned "%s"',
                    $action, $options['NetworkInterfaceId'], $attr, $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DescribeInternetGateways
     *
     * Describes one or more of your Internet gateways.
     *
     * @param   ListDataType              $internetGatewayIdList optional The list of Internet gateway identifiers.
     * @param   InternetGatewayFilterList $filter                optional The filter list.
     * @return  InternetGatewayList Returns InternetGatewayList on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeInternetGateways(ListDataType $internetGatewayIdList = null,
                                             InternetGatewayFilterList $filter = null)
    {
        $result = null;
        $options = array();
        $action = ucfirst(__FUNCTION__);
        if ($internetGatewayIdList !== null) {
            $options = array_merge($options, $internetGatewayIdList->getQueryArrayBare('InternetGatewayId'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = $this->_loadListByName('InternetGateway', $sxml->internetGatewaySet);
            $result->setRequestId((string)$sxml->requestId);
        }
        return $result;
    }

    /**
     * Loads InternetGatewayData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InternetGatewayData Returns InternetGatewayData
     */
    protected function _loadInternetGatewayData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $internetGatewayId = (string) $v->internetGatewayId;
            $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->internetGateway->get($internetGatewayId) : null;
            if ($item === null) {
                $item = new InternetGatewayData($internetGatewayId);
                $item->setEc2($this->ec2);
                $bAttach = true;
            } else {
                $item->resetObject();
                $item->internetGatewayId = $internetGatewayId;
                $bAttach = false;
            }
            $item->setAttachmentSet($this->_loadInternetGatewayAttachmentList($v->attachmentSet, $internetGatewayId));
            $item->setTagSet($this->_loadResourceTagSetList($v->tagSet));

            if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * Loads InternetGatewayAttachmentList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @param   string            $internetGatewayId The ID of the Internet Gateway
     * @return  InternetGatewayAttachmentList Returns InternetGatewayAttachmentList
     */
    protected function _loadInternetGatewayAttachmentList(\SimpleXMLElement $sxml, $internetGatewayId = null)
    {
        $list = new InternetGatewayAttachmentList();
        $list->setEc2($this->ec2);
        if ($internetGatewayId !== null) {
            $list->setInternetGatewayId($internetGatewayId);
            $list->_setExternalKeysUpdated(true);
        }
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = $this->_loadInternetGatewayAttachmentData($v);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads InternetGatewayAttachmentData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  InternetGatewayAttachmentData Returns InternetGatewayAttachmentData
     */
    protected function _loadInternetGatewayAttachmentData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new InternetGatewayAttachmentData((string)$v->vpcId, (string)$v->state);
            $item->setEc2($this->ec2);
        }
        return $item;
    }

    /**
     * CreateInternetGateway action
     *
     * Creates a new Internet gateway for use with a VPC
     *
     * @return  InternetGatewayData Returns InternetGatewayData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createInternetGateway()
    {
        $result = null;
        $options = array();
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadInternetGatewayData($sxml->internetGateway);
        }
        return $result;
    }

    /**
     * DeleteInternetGateway action
     *
     * Deletes an Internet gateway from your AWS account.
     * The gateway must not be attached to a VPC
     *
     * @param   string     $internetGatewayId The ID of the Internet Gateway
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteInternetGateway($internetGatewayId)
    {
        $result = false;
        $options = array(
            'InternetGatewayId' => (string) $internetGatewayId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s InternetGatewayId:"%s". It returned "%s"',
                    $action, $options['InternetGatewayId'], $sxml->return
                ));
            }
            $result = true;
            $entity = $this->ec2->getEntityManagerEnabled() ?
                $this->ec2->internetGateway->get($options['InternetGatewayId']) : null;
            if ($entity !== null) {
                $this->ec2->getEntityManager()->detach($entity);
            }
        }
        return $result;
    }

    /**
     * AttachInternetGateway action
     *
     * Attaches an Internet gateway to a VPC, enabling connectivity between the Internet and the VPC
     *
     * @param   string     $internetGatewayId The ID of the internet gateway
     * @param   string     $vpcId             The ID of the VPC
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attachInternetGateway($internetGatewayId, $vpcId)
    {
        $result = false;
        $options = array(
            'InternetGatewayId' => (string) $internetGatewayId,
            'VpcId'             => (string) $vpcId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s InternetGatewayId:"%s", VpcId:"%s". It returned "%s"',
                    $action, $options['InternetGatewayId'], $options['VpcId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DetachInternetGateway action
     *
     * Detaches an Internet gateway from a VPC, disabling connectivity between the Internet and the VPC
     *
     * @param   string     $internetGatewayId The ID of the internet gateway
     * @param   string     $vpcId             The ID of the VPC
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detachInternetGateway($internetGatewayId, $vpcId)
    {
        $result = false;
        $options = array(
            'InternetGatewayId' => (string) $internetGatewayId,
            'VpcId'             => (string) $vpcId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s InternetGatewayId:"%s", VpcId:"%s". It returned "%s"',
                    $action, $options['InternetGatewayId'], $options['VpcId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DescribeRouteTables action
     *
     * Describes one or more of your route tables.
     *
     * @param   ListDataType         $routeTableIdList optional The list of Route Table IDs
     * @param   RouteTableFilterList $filter           optional The filter list
     * @return  RouteTableList       Returns the list of the RouteTableData objects on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeRouteTables(ListDataType $routeTableIdList = null, RouteTableFilterList $filter = null)
    {
        $result = null;
        $options = array();
        $action = ucfirst(__FUNCTION__);
        if ($routeTableIdList !== null) {
            $options = array_merge($options, $routeTableIdList->getQueryArrayBare('RouteTableId'));
        }
        if ($filter !== null) {
            $options = array_merge($options, $filter->getQueryArrayBare('Filter'));
        }
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = $this->_loadListByName('RouteTable', $sxml->routeTableSet);
            $result->setRequestId((string)$sxml->requestId);
        }
        return $result;
    }

    /**
     * Loads RouteTableData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  RouteTableData Returns RouteTableData
     */
    protected function _loadRouteTableData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $routeTableId = (string) $v->routeTableId;
            $item = $this->ec2->getEntityManagerEnabled() ? $this->ec2->routeTable->get($routeTableId) : null;
            if ($item === null) {
                $item = new RouteTableData($routeTableId);
                $item->setEc2($this->ec2);
                $bAttach = true;
            } else {
                $item->resetObject();
                $item->routeTableId = $routeTableId;
                $bAttach = false;
            }
            $item->vpcId = (string) $v->vpcId;
            $item->setRouteSet($this->_loadRouteList($v->routeSet, $routeTableId));
            $item->setTagSet($this->_loadResourceTagSetList($v->tagSet));
            $item->setAssociationSet($this->_loadListByName('RouteTableAssociation', $v->associationSet));
            $item->setPropagatingVgwSet($this->_loadListByName('PropagatingVgw', $v->propagatingVgwSet));

            if ($bAttach && $this->ec2->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * Loads RouteList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @param   string            $routeTableId optional The ID of the Route table
     * @return  RouteList Returns RouteList
     */
    protected function _loadRouteList(\SimpleXMLElement $sxml, $routeTableId = null)
    {
        $list = new RouteList();
        $list->setEc2($this->ec2);
        if ($routeTableId !== null) {
            $list->setRouteTableId($routeTableId);
            $list->_setExternalKeysUpdated(true);
        }
        if (!empty($sxml->item)) {
            foreach ($sxml->item as $v) {
                $item = $this->_loadRouteData($v);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads RouteData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  RouteData Returns RouteData
     */
    protected function _loadRouteData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new RouteData();
            $item->setEc2($this->ec2);
            $item->destinationCidrBlock = (string) $v->destinationCidrBlock;
            $item->state = (string) $v->state;
            $item->origin = (string) $v->origin;
            $item->gatewayId = $this->exist($v->gatewayId) ? (string) $v->gatewayId : null;
            $item->instanceId = $this->exist($v->instanceId) ? (string) $v->instanceId : null;
            $item->instanceOwnerId = $this->exist($v->instanceOwnerId) ? (string) $v->instanceOwnerId : null;
            $item->networkInterfaceId = $this->exist($v->networkInterfaceId) ? (string) $v->networkInterfaceId : null;
        }
        return $item;
    }

    /**
     * Loads RouteTableAssociationData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  RouteTableAssociationData Returns RouteTableAssociationData
     */
    protected function _loadRouteTableAssociationData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new RouteTableAssociationData();
            $item->setEc2($this->ec2);
            $item->routeTableAssociationId = (string) $v->routeTableAssociationId;
            $item->routeTableId = (string) $v->routeTableId;
            $item->main = $this->exist($v->main) ? (((string)$v->main) == 'true') : null;
            $item->subnetId = $this->exist($v->subnetId) ? (string)$v->subnetId : null;
        }
        return $item;
    }

    /**
     * Loads PropagatingVgwData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  PropagatingVgwData Returns PropagatingVgwData
     */
    protected function _loadPropagatingVgwData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new PropagatingVgwData((string) $v->gatewayId);
            $item->setEc2($this->ec2);
        }
        return $item;
    }

    /**
     * CreateRouteTable action
     *
     * Creates a route table within a VPC. After you create a new route table,
     * you can add routes and associate the table with a subnet
     *
     * @param   string     $vpcId The ID of the VPC
     * @return  RouteTableData Returns the RouteTableData object on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createRouteTable($vpcId)
    {
        $result = null;
        $options = array(
            'VpcId' => (string) $vpcId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadRouteTableData($sxml->routeTable);

        }
        return $result;
    }

    /**
     * DeleteRouteTable action
     *
     * Deletes a route table from a VPC. The route table must not be associated
     * with a subnet. You can't delete the main route table
     *
     * @param   string       $routeTableId The ID of the Route Table
     * @return  bool         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteRouteTable($routeTableId)
    {
        $result = false;
        $options = array(
            'RouteTableId' => (string) $routeTableId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s RouteTableId:"%s". It returned "%s"',
                    $action, $options['RouteTableId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * AssociateRouteTable action
     *
     * Associates a subnet with a route table. The subnet and route table must be in the same VPC.
     * This association causes traffic originating from the subnet to be routed
     * according to the routes in the route table. The action returns an association ID,
     * which you need if you want to disassociate the route table
     * from the subnet later. A route table can be associated with multiple subnets.
     *
     * @param   string     $routeTableId The ID of the Route Table.
     * @param   string     $subnetId     The ID of the subnet.
     * @return  string     Returns the ID of the association on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function associateRouteTable($routeTableId, $subnetId)
    {
        $result = null;
        $options = array(
            'RouteTableId' => (string) $routeTableId,
            'SubnetId'     => (string) $subnetId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = (string) $sxml->associationId;
        }
        return $result;
    }

    /**
     * DisassociateRouteTable action
     *
     * Disassociates a subnet from a route table.
     * After you perform this action, the subnet no longer uses the routes in the route table.
     * Instead, it uses the routes in the VPC's main route table.
     *
     * @param   string     $associationId The association ID representing the current association between
     *                                    the route table and subnet.
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function disassociateRouteTable($associationId)
    {
        $result = false;
        $options = array(
            'AssociationId' => (string) $associationId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s AssociationId:"%s". It returned "%s"',
                    $action, $options['AssociationId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * ReplaceRouteTableAssociation action
     *
     * Changes the route table associated with a given subnet in a VPC.
     * After you execute this action, the subnet uses the routes
     * in the new route table it's associated with.
     *
     * You can also use this action to change which table is the main route table in the VPC.
     * You just specify the main route table's association ID and the route table that you
     * want to be the new main route table.
     *
     * @param   string     $routeTableId  The ID of the new route table to associate with the subnet.
     * @param   string     $associationId The ID representing the current association
     *                                    between the original route table and the subnet.
     * @return  string     Returns the ID of the new association on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function replaceRouteTableAssociation($routeTableId, $associationId)
    {
        $result = false;
        $options = array(
            'AssociationId' => (string) $associationId,
            'RouteTableId'  => (string) $routeTableId,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = (string) $sxml->newAssociationId;
        }
        return $result;
    }

    /**
     * CreateRoute action
     *
     * Creates a route in a route table within a VPC.
     * The route's target can be either a gateway attached to the
     * VPC or a NAT instance in the VPC.
     * When determining how to route traffic, we use the route with the most specific match.
     * For example, let's say the traffic is destined for 192.0.2.3, and the route table
     * includes the following two routes:
     * 192.0.2.0/24 (goes to some target A)
     * 192.0.2.0/28 (goes to some target B)
     *
     * Both routes apply to the traffic destined for 192.0.2.3.
     * However, the second route in the list covers a
     * smaller number of IP addresses and is therefore more specific,
     * so we use that route to determine where to target the traffic.
     *
     * Condition:You must provide only one of the following:
     * a GatewayId, InstanceId, or NetworkInterfaceId.
     *
     * @param   string     $routeTableId         The ID of the route table where the route will be added.
     * @param   string     $destinationCidrBlock The CIDR address block used for the destination match.
     * @param   string     $gatewayId            optional The ID of a gataway attached to your VPC.
     * @param   string     $instanceId           optional The ID of a NAT instance in your VPC.
     * @param   string     $networkInterfaceId   optional Allows the routing of network interface IDs.
     *                                           Exactly one interface must be attached when
     *                                           specifying an instance ID or it fails.
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createRoute($routeTableId, $destinationCidrBlock, $gatewayId = null, $instanceId = null,
                                $networkInterfaceId = null)
    {
        $result = false;
        $options = array(
            'DestinationCidrBlock' => (string) $destinationCidrBlock,
            'RouteTableId'         => (string) $routeTableId,
        );
        $f = 0;
        if ($gatewayId !== null) {
            $options['GatewayId'] = (string) $gatewayId;
            $f++;
        }
        if ($instanceId !== null) {
            $options['InstanceId'] = (string) $instanceId;
            $f++;
        }
        if ($networkInterfaceId !== null) {
            $options['NetworkInterfaceId'] = (string) $networkInterfaceId;
            $f++;
        }
        if ($f > 1) {
            throw new \InvalidArgumentException(sprintf(
                'You must provide only one of the following: a GatewayId, InstanceId, or NetworkInterfaceId.'
            ));
        }
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s RouteTableId:"%s". It returned "%s"',
                    $action, $options['RouteTableId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DeleteRoute action
     *
     * Deletes a route table from a VPC. The route table must not be associated
     * with a subnet. You can't delete the main route table
     *
     * @param   string       $routeTableId         The ID of the Route Table.
     * @param   string       $destinationCidrBlock The CIDR range for the route to delete.
     * @return  bool         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteRoute($routeTableId, $destinationCidrBlock)
    {
        $result = false;
        $options = array(
            'RouteTableId'         => (string) $routeTableId,
            'DestinationCidrBlock' => (string) $destinationCidrBlock,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s RouteTableId:"%s". It returned "%s"',
                    $action, $options['RouteTableId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * ReplaceRoute action
     *
     * Replaces an existing route within a route table in a VPC
     *
     * Condition:You must provide only one of the following:
     * a GatewayId, InstanceId, or NetworkInterfaceId.
     *
     * @param   string     $routeTableId         The ID of the route table where the route will be added.
     * @param   string     $destinationCidrBlock The CIDR address block used for the destination match.
     * @param   string     $gatewayId            optional The ID of a gataway attached to your VPC.
     * @param   string     $instanceId           optional The ID of a NAT instance in your VPC.
     * @param   string     $networkInterfaceId   optional Allows the routing of network interface IDs.
     *                                           Exactly one interface must be attached when
     *                                           specifying an instance ID or it fails.
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function replaceRoute($routeTableId, $destinationCidrBlock, $gatewayId = null, $instanceId = null,
                                 $networkInterfaceId = null)
    {
        $result = false;
        $options = array(
            'DestinationCidrBlock' => (string) $destinationCidrBlock,
            'RouteTableId'         => (string) $routeTableId,
        );
        $f = 0;
        if ($gatewayId !== null) {
            $options['GatewayId'] = (string) $gatewayId;
            $f++;
        }
        if ($instanceId !== null) {
            $options['InstanceId'] = (string) $instanceId;
            $f++;
        }
        if ($networkInterfaceId !== null) {
            $options['NetworkInterfaceId'] = (string) $networkInterfaceId;
            $f++;
        }
        if ($f > 1) {
            throw new \InvalidArgumentException(sprintf(
                'You must provide only one of the following: a GatewayId, InstanceId, or NetworkInterfaceId.'
            ));
        }
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s RouteTableId:"%s". It returned "%s"',
                    $action, $options['RouteTableId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * ModifyInstanceAttribute action
     *
     * Modifies the specified attribute of the specified instance.
     * You can specify only one attribute at a time.
     * To modify some attributes, the instance must be stopped.
     *
     * @param   string                $instanceId The ID of the Instance.
     * @param   InstanceAttributeType $attribute  The attribute name.
     * @param   mixed                 $value      The attribute value can be string, boolean,
     *                                            array or object depends on attribute name.
     * @return  bool                  Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \BadFunctionCallException
     */
    public function modifyInstanceAttribute($instanceId, InstanceAttributeType $attribute, $value)
    {
        $result = false;
        $options = array(
            'InstanceId' => (string) $instanceId,
        );
        $attribute = (string) $attribute;
        if (in_array($attribute, array(
                InstanceAttributeType::TYPE_INSTANCE_TYPE,
                InstanceAttributeType::TYPE_KERNEL,
                InstanceAttributeType::TYPE_RAMDISK,
                InstanceAttributeType::TYPE_USER_DATA,
                InstanceAttributeType::TYPE_INSTANCE_INITIATED_SHUTDOWN_BEHAVIOR,
            ))) {
            $options[ucfirst($attribute) . '.Value'] = (string) $value;
        } else if (in_array($attribute, array(
                InstanceAttributeType::TYPE_SOURCE_DEST_CHECK,
                InstanceAttributeType::TYPE_DISABLE_API_TERMINATION,
            ))) {
            $options[ucfirst($attribute) . '.Value'] = is_string($value) && ($value === 'true' || $value === 'false') ?
               $value : ($value ? 'true' : 'false');
        } else if ($attribute == InstanceAttributeType::TYPE_BLOCK_DEVICE_MAPPING) {
            if (!($value instanceof InstanceBlockDeviceMappingResponseList)) {
                $value = new InstanceBlockDeviceMappingResponseList($value);
            }
            $options = array_merge($options, $value->getQueryArrayBare('BlockDeviceMapping'));
        } else if ($attribute == InstanceAttributeType::TYPE_GROUP_SET) {
            if (!($value instanceof ListDataType)) {
                $value = new ListDataType($value);
            }
            $options = array_merge($options, $value->getQueryArrayBare('GroupId'));
        } else if ($attribute == InstanceAttributeType::TYPE_EBS_OPTIMIZED) {
            $options['EbsOptimized'] = is_string($value) && ($value === 'true' || $value === 'false') ?
               $value : ($value ? 'true' : 'false');
        } else {
            throw new \BadFunctionCallException(sprintf('Unexpected attribute "%s" in the %s', $attribute, __FUNCTION__));
        }
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if ((string)$sxml->return != 'true') {
                throw new Ec2Exception(sprintf(
                    'Amazon Ec2 could not %s InstanceId:"%s". It returned "%s"',
                    $action, $options['InstanceId'], $sxml->return
                ));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * StopInstances action
     *
     * Stops an Amazon EBS-backed instance. Each time you transition an instance from stopped to started,
     * we charge a full instance hour, even if transitions happen multiple times within a single hour.
     *
     * Important!
     * Although Spot Instances can use Amazon EBS-backed AMIs, they don't support Stop/Start. In
     * other words, you can't stop and start Spot Instances launched from an AMI with an Amazon EBS
     * root device.
     *
     * Instances that use Amazon EBS volumes as their root devices can be quickly stopped and started.When
     * an instance is stopped, the compute resources are released and you are not billed for hourly instance
     * usage. However, your root partition Amazon EBS volume remains, continues to persist your data, and
     * you are charged for Amazon EBS volume usage.You can restart your instance at any time.
     *
     * Note!
     * Before stopping an instance, make sure it is in a state from which it can be restarted. Stopping
     * an instance does not preserve data stored in RAM.
     * Performing this operation on an instance that uses an instance store as its root device returns
     * an error.
     *
     * You can stop, start, and terminate EBS-backed instances.You can only terminate S3-backed instances.
     * What happens to an instance differs if you stop it or terminate it. For example, when you stop an instance,
     * the root device and any other devices attached to the instance persist. When you terminate an instance,
     * the root device and any other devices attached during the instance launch are automatically deleted
     *
     * @param   ListDataType $instanceIdList One or more instance IDs.
     *
     * @param   bool         $force          optional
     *          Forces the instance to stop. The instance will not have an
     *          opportunity to flush file system caches or file system
     *          metadata. If you use this option, you must perform file
     *          system check and repair procedures. This option is not
     *          recommended for Windows instances.
     *
     * @return  InstanceStateChangeList  Return the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function stopInstances(ListDataType $instanceIdList, $force = null)
    {
        $result = false;
        $options = $instanceIdList->getQueryArrayBare('InstanceId');
        if ($force !== null) {
            $options['Force'] = is_string($force) && ($force == 'true' || $force == 'false') ? $force :
                ($force ? 'true' : 'false');
        }
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadListByName('InstanceStateChange', $sxml->instancesSet);
        }
        return $result;
    }

    /**
     * StartInstances action
     *
     * Starts an Amazon EBS-backed AMI that you've previously stopped.
     *
     * Instances that use Amazon EBS volumes as their root devices can be quickly stopped and started.
     * When an instance is stopped, the compute resources are released and you are not billed for hourly instance
     * usage. However, your root partition Amazon EBS volume remains, continues to persist your data, and
     * you are charged for Amazon EBS volume usage. You can restart your instance at any time. Each time
     * you transition an instance from stopped to started, we charge a full instance hour, even if transitions
     * happen multiple times within a single hour.
     *
     * Note! Before stopping an instance, make sure it is in a state from which it can be restarted.
     * Stopping an instance does not preserve data stored in RAM.
     * Performing this operation on an instance that uses an instance store as its root device returns
     * an error.
     *
     * @param   ListDataType $instanceIdList One or more instance IDs.
     *
     * @return  InstanceStateChangeList  Return the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function startInstances(ListDataType $instanceIdList)
    {
        $result = false;
        $options = $instanceIdList->getQueryArrayBare('InstanceId');
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadListByName('InstanceStateChange', $sxml->instancesSet);
        }
        return $result;
    }

    /**
     * MonitorInstances action
     *
     * Enables monitoring for a running instance.
     *
     * @param   ListDataType $instanceIdList One or more instance IDs
     * @return  MonitorInstancesResponseSetList  Returns the MonitorInstancesResponseSetList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function monitorInstances(ListDataType $instanceIdList)
    {
        $result = null;
        $options = $instanceIdList->getQueryArrayBare('InstanceId');
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = $this->_loadListByName('MonitorInstancesResponseSet', $sxml->instancesSet);
        }
        return $result;
    }

    /**
     * UnmonitorInstances action
     *
     * Disables monitoring for a running instance
     *
     * @param   ListDataType $instanceIdList One or more instance IDs
     * @return  MonitorInstancesResponseSetList  Returns the MonitorInstancesResponseSetList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function unmonitorInstances(ListDataType $instanceIdList)
    {
        $result = null;
        $options = $instanceIdList->getQueryArrayBare('InstanceId');
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $response = null;
            $result = $this->_loadListByName('MonitorInstancesResponseSet', $sxml->instancesSet);
        }
        return $result;
    }

    /**
     * Loads MonitorInstancesResponseSetData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  MonitorInstancesResponseSetData Returns MonitorInstancesResponseSetData
     */
    protected function _loadMonitorInstancesResponseSetData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $item = new MonitorInstancesResponseSetData((string)$v->instanceId);
            $item->setEc2($this->ec2);
            $item->setMonitoring($this->_loadInstanceMonitoringStateData($v->monitoring));
        }
        return $item;
    }
}
