<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\Ec2\DataType\EbsBlockDeviceData;
use Scalr\Service\Aws\Ec2\DataType\InstanceMonitoringStateData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStateChangeData;
use Scalr\Service\Aws\Ec2\DataType\InstanceAttributeType;
use Scalr\Service\Aws\Ec2\DataType\RouteData;
use Scalr\Service\Aws\Ec2\DataType\RouteTableAssociationData;
use Scalr\Service\Aws\Ec2\DataType\RouteTableFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\RouteTableData;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayData;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayAttachmentData;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAttributeType;
use Scalr\Service\Aws\Ec2\DataType\VpcData;
use Scalr\Service\Aws\Ec2\DataType\VpcFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\SubnetFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceData;
use Scalr\Service\Aws\Ec2\DataType\ImageFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupData;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupFilterData;
use Scalr\Service\Aws\Ec2\DataType\GetConsoleOutputResponseData;
use Scalr\Service\Aws\Ec2\DataType\SubnetData;
use Scalr\Service\Aws\Ec2\DataType\SnapshotFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\SnapshotFilterData;
use Scalr\Service\Aws\Ec2\DataType\SnapshotData;
use Scalr\Service\Aws\Ec2\DataType\AssociateAddressRequestData;
use Scalr\Service\Aws\Ec2\DataType\AddressFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\AddressData;
use Scalr\Service\Aws\Ec2\DataType\CreateImageRequestData;
use Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingData;
use Scalr\Service\Aws\Ec2\DataType\ImageData;
use Scalr\Service\Aws\Ec2\DataType\InstanceFilterData;
use Scalr\Service\Aws\Ec2\DataType\InstanceFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\ResourceTagSetData;
use Scalr\Service\Aws\Ec2\DataType\PlacementResponseData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStateData;
use Scalr\Service\Aws\Ec2\DataType\CreateVolumeRequestData;
use Scalr\Service\Aws\Ec2\DataType\AttachmentSetResponseData;
use Scalr\Service\Aws\Ec2\DataType\VolumeData;
use Scalr\Service\Aws\Ec2\DataType\RunInstancesRequestData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusEventTypeData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusDetailsSetData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusData;
use Scalr\Service\Aws\Ec2\DataType\OfferingType;
use Scalr\Service\Aws\Ec2\DataType\ReservedInstanceData;
use Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceSetData;
use Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceSetList;
use Scalr\Service\Aws\Ec2\DataType\InstanceBlockDeviceMappingResponseData;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use Scalr\Service\Aws\Ec2\DataType\InstanceData;
use Scalr\Service\Aws\Ec2\DataType\ReservationData;
use Scalr\Service\Aws\Ec2\DataType\IpRangeData;
use Scalr\Service\Aws\Ec2\DataType\IpRangeList;
use Scalr\Service\Aws\Ec2\DataType\IpPermissionData;
use Scalr\Service\Aws\Ec2\DataType\IpPermissionList;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupData;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupFilterData;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneList;
use Scalr\Service\Aws\Client\ClientResponseInterface;
use Scalr\Service\Aws;
use Scalr\Service\Aws\Ec2;
use Scalr\Tests\Service\AwsTestCase;
use \SplFileInfo;

/**
 * Amazon Ec2 Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     27.12.2012
 */
class Ec2Test extends AwsTestCase
{

    const CLASS_EC2 = 'Scalr\\Service\\Aws\\Ec2';

    const TAG_NAME_KEY = 'Name';

    const NAME_NETWORK_INTERFACE = 'eni';

    const NAME_SUBNET = 'subnet';

    const NAME_TAG_VALUE = 'tag → value';

    const NAME_SNAPSHOT = 'sn';

    const NAME_SECURITY_GROUP_VPC = 'sg-vpc';

    const INSTANCE_TYPE = 'm1.small';

    const INSTANCE_IMAGE_ID = 'ami-82fa58eb';


    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixturesDirectory()
     */
    public function getFixturesDirectory()
    {
        return parent::getFixturesDirectory() . '/Ec2';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixtureFilePath()
     */
    public function getFixtureFilePath($filename)
    {
        return $this->getFixturesDirectory() . '/' . Ec2::API_VERSION_CURRENT . '/' . $filename;
    }

    /**
     * Gets Ec2 Mock
     *
     * @param    callback $callback
     * @return   Ec2      Returns Ec2 Mock class
     */
    public function getEc2Mock($callback = null)
    {
        return $this->getServiceInterfaceMock('Ec2');
    }

    public function testUnmonitorInstances()
    {
        $ec2 = $this->getEc2Mock();
        $list = $ec2->instance->unmonitor(array('i-43a4412a', 'i-23a3397d'));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\MonitorInstancesResponseSetList'), $list);
        $this->assertSame($ec2, $list->getEc2());
        $this->assertEquals(2, count($list));

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\MonitorInstancesResponseSetData'), $list->get(0));
        $this->assertSame($ec2, $list->get(0)->getEc2());
        $this->assertEquals('i-43a4412a', $list->get(0)->instanceId);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceMonitoringStateData'), $list->get(0)->monitoring);
        $this->assertSame($ec2, $list->get(0)->monitoring->getEc2());
        $this->assertEquals(InstanceMonitoringStateData::STATE_DISABLED, $list->get(0)->monitoring->state);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\MonitorInstancesResponseSetData'), $list->get(1));
        $this->assertSame($ec2, $list[1]->getEc2());
        $this->assertEquals('i-23a3397d', $list[1]->instanceId);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceMonitoringStateData'), $list[1]->monitoring);
        $this->assertSame($ec2, $list[1]->monitoring->getEc2());
        $this->assertEquals(InstanceMonitoringStateData::STATE_DISABLED, $list[1]->monitoring->state);

        $ec2->getEntityManager()->detachAll();
    }

    /**
     * @test
     */
    public function testDescribeSecurityGroups()
    {
        $ec2 = $this->getEc2Mock();
        $list = $ec2->securityGroup->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SecurityGroupList'), $list);
        $sg1 = $ec2->securityGroup->get('sg-5ff8a023');
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SecurityGroupData'), $sg1);
        $this->assertSame($sg1, $list[1]);
        $this->assertEquals('111122223333', $sg1->ownerId);
        $this->assertEquals('sg-5ff8a023', $sg1->groupId);
        $this->assertEquals('RangedPortsBySource', $sg1->getGroupName());
        $this->assertEquals('Group A', $sg1->groupDescription);
        $this->assertEquals('tcp', $sg1->ipPermissions[0]->ipProtocol);
        $this->assertEquals(6000, $sg1->ipPermissions[0]->fromPort);
        $this->assertEquals(7000, $sg1->ipPermissions[0]->getToPort());
        $this->assertEquals('111122223333', $sg1->ipPermissions[0]->groups[0]->userId);
        $this->assertEquals('sg-99gh4012', $sg1->ipPermissions[0]->groups[0]->getGroupId());
        $this->assertEquals('Group B', $sg1->ipPermissions[0]->groups[0]->getGroupName());
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\IpRangeList'), $sg1->ipPermissions[0]->ipRanges);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\IpPermissionList'), $sg1->ipPermissionsEgress);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ResourceTagSetList'), $sg1->tagSet);
        unset($sg1);
        unset($list);
    }

    /**
     * @test
     */
    public function testDescribeAvailabilityZones()
    {
        $ec2 = $this->getEc2Mock();
        $list = $ec2->availabilityZone->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AvailabilityZoneList'), $list);
        $this->assertEquals(4, count($list));
        $az = $ec2->availabilityZone->get('us-east-1a');
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AvailabilityZoneData'), $az);
        $this->assertInstanceOf(get_class($ec2), $az->getEc2());
        $this->assertEquals('us-east-1a', $az->zoneName);
        $this->assertEquals('us-east-1a', $az->getZoneName());
        $this->assertEquals('available', $az->getZoneState());
        $this->assertEquals('available', $az->zoneState);
        $this->assertEquals('us-east-1', $az->regionName);
        $this->assertEquals('us-east-1', $az->getRegionName());
        unset($az);
        unset($list);
    }

    /**
     * @test
     */
    public function testDescribeInstances()
    {
        $ec2 = $this->getEc2Mock();
        $resList = $ec2->instance->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservationList'), $resList);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $resList->getEc2());
        $this->assertEquals(2, count($resList));
        /* @var $r1 ReservationData */
        $r1 = $resList[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservationData'), $r1);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $r1->getEc2());
        $this->assertEquals('r-0ece705a', $r1->reservationId);
        $this->assertEquals($r1->reservationId, $r1->getReservationId());
        $this->assertNull($r1->requesterId);
        $this->assertEquals('053230519467', $r1->ownerId);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\GroupList'), $r1->groupSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $r1->groupSet->getEc2());
        $this->assertEquals(0, count($r1->groupSet));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceList'), $r1->instancesSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $r1->instancesSet->getEc2());
        $this->assertEquals(1, count($r1->instancesSet));
        /* @var $i1 InstanceData */
        $i1 = $r1->instancesSet[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceData'), $i1);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->getEc2());
        $this->assertEquals('i-7a00642e', $i1->instanceId);
        $this->assertSame($i1, $ec2->instance->get($i1->instanceId));
        $this->assertEquals('ami-1cd4924e', $i1->imageId);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStateData'), $i1->instanceState);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->instanceState->getEc2());
        $this->assertEquals(16, $i1->instanceState->code);
        $this->assertEquals('running', $i1->instanceState->name);
        $this->assertEquals('', $i1->privateDnsName);
        $this->assertNull($i1->dnsName);
        $this->assertNull($i1->reason);
        $this->assertEquals('VPCKey', $i1->keyName);
        $this->assertEquals('0', $i1->amiLaunchIndex);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ProductCodeSetList'), $i1->productCodes);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->productCodes->getEc2());
        $this->assertEquals('c1.medium', $i1->instanceType);
        $this->assertEquals('2012-06-28T17:41:48+00:00', $i1->launchTime->format("c"));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\PlacementResponseData'), $i1->placement);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->placement->getEc2());
        $this->assertEquals('ap-southeast-1b', $i1->placement->availabilityZone);
        $this->assertEquals('default', $i1->placement->tenancy);
        $this->assertEquals(null, $i1->placement->groupName);
        $this->assertEquals('windows', $i1->platform);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceMonitoringStateData'), $i1->monitoring);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->monitoring->getEc2());
        $this->assertEquals('disabled', $i1->monitoring->state);
        $this->assertEquals('vpc-cc3c87a5', $i1->vpcId);
        $this->assertEquals('10.0.0.12', $i1->privateIpAddress);
        $this->assertEquals('46.51.219.63', $i1->ipAddress);
        $this->assertTrue($i1->sourceDestCheck);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\GroupList'), $i1->groupSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->groupSet->getEc2());
        $this->assertEquals(1, count($i1->groupSet));
        $this->assertEquals('sg-374b565b', $i1->groupSet[0]->groupId);
        $this->assertEquals('quick-start-3', $i1->groupSet[0]->groupName);
        $this->assertEquals('x86_64', $i1->architecture);
        $this->assertEquals('ebs', $i1->rootDeviceType);
        $this->assertEquals('/dev/sda1', $i1->rootDeviceName);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceBlockDeviceMappingResponseList'), $i1->blockDeviceMapping);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->blockDeviceMapping->getEc2());
        $this->assertEquals(1, count($i1->blockDeviceMapping));
        /* @var $bdm InstanceBlockDeviceMappingResponseData */
        $bdm = $i1->blockDeviceMapping[0];
        $this->assertEquals('/dev/sda1', $bdm->deviceName);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\EbsInstanceBlockDeviceMappingResponseData'), $bdm->ebs);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $bdm->ebs->getEc2());
        $this->assertEquals('vol-9e151bfc', $bdm->ebs->volumeId);
        $this->assertEquals('attached', $bdm->ebs->status);
        $this->assertEquals('2012-06-28T17:42:05+00:00', $bdm->ebs->attachTime->format("c"));
        $this->assertEquals(true, $bdm->ebs->deleteOnTermination);
        $this->assertEquals('hvm', $i1->virtualizationType);
        $this->assertEquals('JNlxa1340905307390', $i1->clientToken);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ResourceTagSetList'), $i1->tagSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->tagSet->getEc2());
        $this->assertEquals(1, count($i1->tagSet));
        $this->assertEquals('Name', $i1->tagSet[0]->key);
        $this->assertEquals('SingleENI', $i1->tagSet[0]->value);
        $this->assertEquals('xen', $i1->hypervisor);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceNetworkInterfaceSetList'), $i1->networkInterfaceSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $i1->networkInterfaceSet->getEc2());
        $this->assertEquals(1, count($i1->networkInterfaceSet));
        /* @var $ni InstanceNetworkInterfaceSetData */
        $ni = $i1->networkInterfaceSet[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceNetworkInterfaceSetData'), $ni);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ni->getEc2());
        $this->assertEquals('eni-d83388b1', $ni->networkInterfaceId);
        $this->assertEquals('subnet-c53c87ac', $ni->subnetId);
        $this->assertEquals('vpc-cc3c87a5', $ni->vpcId);
        $this->assertEquals('Primary network interface', $ni->description);
        $this->assertEquals('053230519467', $ni->ownerId);
        $this->assertEquals('in-use', $ni->status);
        $this->assertEquals('10.0.0.12', $ni->privateIpAddress);
        $this->assertEquals(true, $ni->sourceDestCheck);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\GroupList'), $ni->groupSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ni->groupSet->getEc2());
        $this->assertEquals(1, count($ni->groupSet));
        $this->assertEquals('sg-374b565b', $ni->groupSet[0]->groupId);
        $this->assertEquals('quick-start-3', $ni->groupSet[0]->groupName);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceNetworkInterfaceAttachmentData'), $ni->attachment);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ni->attachment->getEc2());
        $this->assertEquals('eni-attach-31b87358', $ni->attachment->attachmentId);
        $this->assertEquals(0, $ni->attachment->deviceIndex);
        $this->assertEquals('attached', $ni->attachment->status);
        $this->assertEquals('2012-06-28T17:41:48+00:00', $ni->attachment->attachTime->format('c'));
        $this->assertEquals(true, $ni->attachment->deleteOnTermination);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceNetworkInterfaceAssociationData'), $ni->association);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ni->association->getEc2());
        $this->assertEquals('46.51.219.63', $ni->association->publicIp);
        $this->assertEquals('053230519467', $ni->association->ipOwnerId);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstancePrivateIpAddressesSetList'), $ni->privateIpAddressesSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ni->privateIpAddressesSet->getEc2());
        $this->assertEquals(2, count($ni->privateIpAddressesSet));

        $this->assertEquals('10.0.0.12', $ni->privateIpAddressesSet[0]->privateIpAddress);
        $this->assertEquals(true, $ni->privateIpAddressesSet[0]->primary);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceNetworkInterfaceAssociationData'), $ni->privateIpAddressesSet[0]->association);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ni->privateIpAddressesSet[0]->association->getEc2());
        $this->assertEquals('46.51.219.63', $ni->privateIpAddressesSet[0]->association->publicIp);
        $this->assertEquals('053230519467', $ni->privateIpAddressesSet[0]->association->ipOwnerId);

        $this->assertEquals('10.0.0.14', $ni->privateIpAddressesSet[1]->privateIpAddress);
        $this->assertEquals(false, $ni->privateIpAddressesSet[1]->primary);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceNetworkInterfaceAssociationData'), $ni->privateIpAddressesSet[1]->association);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ni->privateIpAddressesSet[1]->association->getEc2());
        $this->assertEquals('46.51.221.177', $ni->privateIpAddressesSet[1]->association->publicIp);
        $this->assertEquals('053230519467', $ni->privateIpAddressesSet[1]->association->ipOwnerId);
        $ec2->getEntityManager()->detachAll();
        unset($ni);
        unset($bdm);
        unset($i1);
        unset($r1);
        unset($resList);
    }

    /**
     * @test
     */
    public function testDescribeReservedInstances()
    {
        $ec2 = $this->getEc2Mock();
        $resList = $ec2->reservedInstance->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservedInstanceList'), $resList);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $resList->getEc2());
        $this->assertEquals('50082c4b-85a8-42ba-b615-78befc576fb3', $resList->getRequestId());
        $this->assertEquals(1, count($resList));
        /* @var $ri ReservedInstanceData */
        $ri = $resList[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservedInstanceData'), $ri);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ri->getEc2());
        $this->assertEquals('b847fa93-815e-4e61-b21e-34fea354e420', $ri->reservedInstancesId);
        $this->assertEquals('m1.small', $ri->instanceType);
        $this->assertEquals('us-east-1c', $ri->availabilityZone);
        $this->assertEquals('2011-09-05T08:13:53+00:00', $ri->start->format('c'));
        $this->assertEquals(94608000, $ri->duration);
        $this->assertEquals(350.0, $ri->fixedPrice);
        $this->assertEquals(0.03, $ri->usagePrice);
        $this->assertEquals(4, $ri->instanceCount);
        $this->assertEquals('Linux/UNIX', $ri->productDescription);
        $this->assertEquals('active', $ri->state);
        $this->assertEquals('default', $ri->instanceTenancy);
        $this->assertEquals('USD', $ri->currencyCode);
        $this->assertEquals(OfferingType::TYPE_MEDIUM_UTILIZATION, $ri->offeringType);

        //Item from the entity storage must be the same object
        $this->assertSame($ri, $ec2->reservedInstance->get($ri->reservedInstancesId));

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RecurringChargesSetList'), $ri->recurringCharges);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ri->recurringCharges->getEc2());
        $this->assertEquals(1, count($ri->recurringCharges));
        $this->assertEquals('Hourly', $ri->recurringCharges[0]->frequency);
        $this->assertEquals(0.03, $ri->recurringCharges[0]->amount);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ResourceTagSetList'), $ri->tagSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ri->tagSet->getEc2());
        $this->assertEquals(1, count($ri->tagSet));
        $this->assertEquals('Name', $ri->tagSet[0]->key);
        $this->assertEquals('Single', $ri->tagSet[0]->value);

        unset($ri);
        $ec2->getEntityManager()->detachAll();
    }

    /**
     * @test
     */
    public function testRebootInstances()
    {
        $ec2 = $this->getEc2Mock();
        $b = $ec2->instance->reboot('i-1a2b3c4d');
        $this->assertTrue($b);
    }

    /**
     * @test
     */
    public function testDescribeInstanceStatus()
    {
        $ec2 = $this->getEc2Mock();
        $resList = $ec2->instance->describeStatus();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusList'), $resList);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $resList->getEc2());
        $this->assertEquals('3be1508e-c444-4fef-89cc-0b1223c4f02f', $resList->getRequestId());
        $this->assertEquals(null, $resList->getNextToken());
        $this->assertEquals(4, count($resList));
        /* @var $rs InstanceStatusData */
        $rs = $resList[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusData'), $rs);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rs->getEc2());
        $this->assertEquals('i-283f9f47', $rs->instanceId);
        $this->assertEquals('us-east-1d', $rs->availabilityZone);

        $fxtr = array (
            'InstanceId' => 'i-283f9f47',
            'AvailabilityZone' => 'us-east-1d',
            'EventsSet.Code' => 'instance-retirement',
            'EventsSet.Description' => 'The instance is running on degraded hardware',
            'EventsSet.NotBefore' => '2011-12-05T13:00:00+00:00',
            'EventsSet.NotAfter' => '2011-12-06T13:00:00+00:00',
            'InstanceState.Code' => '16',
            'InstanceState.Name' => 'running',
            'SystemStatus.Status' => 'impaired',
            'SystemStatus.Details.1.Name' => 'reachability',
            'SystemStatus.Details.1.Status' => 'failed',
            'SystemStatus.Details.1.ImpairedSince' => '2012-03-27T16:10:46+00:00',
            'InstanceStatus.Status' => 'impaired',
            'InstanceStatus.Details.1.Name' => 'reachability',
            'InstanceStatus.Details.1.Status' => 'failed',
            'InstanceStatus.Details.1.ImpairedSince' => '2012-03-27T16:10:47+00:00',
        );
        //Verifies getQueryArray method for Data objects
        $this->assertEquals($fxtr, $rs->getQueryArrayBare());

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStateData'), $rs->instanceState);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rs->instanceState->getEc2());
        $this->assertEquals(16, $rs->instanceState->code);
        $this->assertEquals('running', $rs->instanceState->name);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusTypeData'), $rs->systemStatus);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rs->systemStatus->getEc2());
        $this->assertEquals('impaired', $rs->systemStatus->status);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusDetailsSetList'), $rs->systemStatus->details);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rs->systemStatus->details->getEc2());
        $this->assertEquals(1, count($rs->systemStatus->details));
        /* @var $ist InstanceStatusDetailsSetData */
        $ist = $rs->systemStatus->details[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusDetailsSetData'), $ist);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ist->getEc2());
        $this->assertEquals('2012-03-27T16:10:46+00:00', $ist->impairedSince->format('c'));
        $this->assertEquals('reachability', $ist->name);
        $this->assertEquals('failed', $ist->status);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusTypeData'), $rs->instanceStatus);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rs->instanceStatus->getEc2());
        $this->assertEquals('impaired', $rs->instanceStatus->status);
        unset($ist);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusDetailsSetList'), $rs->instanceStatus->details);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rs->instanceStatus->details->getEc2());
        $this->assertEquals(1, count($rs->instanceStatus->details));
        /* @var $ist InstanceStatusDetailsSetData */
        $ist = $rs->instanceStatus->details[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusDetailsSetData'), $ist);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ist->getEc2());
        $this->assertEquals('2012-03-27T16:10:47+00:00', $ist->impairedSince->format('c'));
        $this->assertEquals('reachability', $ist->name);
        $this->assertEquals('failed', $ist->status);
        unset($ist);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStatusEventTypeData'), $rs->eventsSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rs->eventsSet->getEc2());
        $this->assertEquals('instance-retirement', $rs->eventsSet->code);
        $this->assertEquals('2011-12-05T13:00:00+00:00', $rs->eventsSet->notBefore->format('c'));
        $this->assertEquals('2011-12-06T13:00:00+00:00', $rs->eventsSet->notAfter->format('c'));
        $this->assertContains('The instance is running on degraded hardware', $rs->eventsSet->description);
    }

    /**
     * @test
     */
    public function testDescribeVolumes()
    {
        $ec2 = $this->getEc2Mock();
        $resList = $ec2->volume->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\VolumeList'), $resList);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $resList->getEc2());
        $this->assertEquals(1, count($resList));

        /* @var $vd VolumeData */
        $vd = $resList[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\VolumeData'), $vd);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $vd->getEc2());
        $this->assertEquals('vol-4282672b', $vd->volumeId);
        $this->assertEquals(80, $vd->size);
        $this->assertNull($vd->snapshotId);
        $this->assertEquals('us-east-1a', $vd->availabilityZone);
        $this->assertEquals('in-use', $vd->status);
        $this->assertEquals('2008-05-07T11:51:50+00:00', $vd->createTime->format('c'));
        $this->assertEquals('standard', $vd->volumeType);

        /* @var $at AttachmentSetResponseData */
        $at = $vd->attachmentSet[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AttachmentSetResponseData'), $at);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $at->getEc2());
        $this->assertEquals('vol-4282672b', $at->volumeId);
        $this->assertEquals('i-6058a509', $at->instanceId);
        $this->assertEquals('/dev/sdh', $at->device);
        $this->assertEquals('attached', $at->status);
        $this->assertEquals('2008-05-07T12:51:50+00:00', $at->attachTime->format('c'));
        $this->assertFalse($at->deleteOnTermination);
    }

    /**
     * @test
     */
    public function testVolume()
    {
        $ec2 = $this->getEc2Mock();
        $request = new CreateVolumeRequestData('us-east-1a');
        $request->setSize('80')->setVolumeType('standard');
        $vd = $ec2->volume->create($request);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\VolumeData'), $vd);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $vd->getEc2());
        $this->assertEquals('vol-4d826724', $vd->volumeId);
        $this->assertEquals('80', $vd->size);
        $this->assertEquals(null, $vd->snapshotId);
        $this->assertEquals('creating', $vd->status);
        $this->assertEquals('2008-05-07T11:51:50+00:00', $vd->createTime->format('c'));
        $this->assertEquals('standard', $vd->volumeType);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AttachmentSetResponseList'), $vd->attachmentSet);
        $this->assertEquals(0, count($vd->attachmentSet));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ResourceTagSetList'), $vd->tagSet);
        $this->assertEquals(0, count($vd->tagSet));

        $this->assertSame($vd, $ec2->volume->get('vol-4d826724'));

        $at = $vd->attach('i-6058a509', '/dev/sdh');
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AttachmentSetResponseData'), $at);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $at->getEc2());
        $this->assertEquals('vol-4d826724', $at->volumeId);
        $this->assertEquals('i-6058a509', $at->instanceId);
        $this->assertEquals('/dev/sdh', $at->device);
        $this->assertEquals('attaching', $at->status);
        $this->assertEquals('2008-05-07T11:51:50+00:00', $at->attachTime->format('c'));
        $this->assertTrue($at->deleteOnTermination);

        $this->assertSame($at, $vd->attachmentSet[0]);

        $at2 = $vd->detach();
        $this->assertEquals(1, count($vd->attachmentSet));
        $this->assertSame($at2, $vd->attachmentSet[0]);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AttachmentSetResponseData'), $at2);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $at2->getEc2());
        $this->assertEquals('vol-4d826724', $at2->volumeId);
        $this->assertEquals('i-6058a509', $at2->instanceId);
        $this->assertEquals('/dev/sdh', $at2->device);
        //Status is detaching
        $this->assertEquals('detaching', $at2->status);
        $this->assertEquals('2008-05-08T11:51:50+00:00', $at2->attachTime->format('c'));
        $this->assertTrue($at2->deleteOnTermination);

        $res = $vd->delete();
        $this->assertTrue($res);
        $this->assertNull($ec2->volume->get('vol-4d826724'));
    }

    /**
     * @test
     */
    public function testTags()
    {
        $ec2 = $this->getEc2Mock();
        $ret = $ec2->tag->create('hypothetic-resource-id', new ResourceTagSetData('Name','phpunit'));
        $this->assertTrue($ret);
        $ret = $ec2->tag->delete('hypothetic-resource-id', array(array('key' => 'Name', 'value' => null)));
        $this->assertTrue($ret);
    }

    /**
     * @test
     */
    public function testDescribeImages()
    {
        $ec2 = $this->getEc2Mock();
        $list = $ec2->image->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ImageList'), $list);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $list->getEc2());
        $this->assertEquals('59dbff89-35bd-4eac-99ed-be587EXAMPLE', $list->getRequestId());
        $this->assertEquals(1, $list->count());
        /* @var $ami ImageData */
        $ami = $list->get(0);
        $this->assertEquals('ami-be3adfd7', $ami->imageId);
        $this->assertSame($ami, $ec2->image->get($ami->imageId));
        $this->assertEquals('amazon/getting-started', $ami->imageLocation);
        $this->assertEquals('available', $ami->imageState);
        $this->assertEquals('206029621532', $ami->imageOwnerId);
        $this->assertEquals(true, $ami->isPublic);
        $this->assertEquals('i386', $ami->architecture);
        $this->assertEquals('machine', $ami->imageType);
        $this->assertEquals('aki-d3376696', $ami->kernelId);
        $this->assertEquals('ari-e73766a2', $ami->ramdiskId);
        $this->assertEquals('amazon', $ami->imageOwnerAlias);
        $this->assertEquals('getting-started', $ami->name);
        $this->assertEquals('Fedora 8 v1.11 i386 lvm-rootVG-rootFS ext3 ec2pnp enabled', $ami->description);
        $this->assertEquals('ebs', $ami->rootDeviceType);
        $this->assertEquals('/dev/sda', $ami->rootDeviceName);
        $this->assertEquals('paravirtual', $ami->virtualizationType);
        $this->assertEquals('xen', $ami->hypervisor);
        $this->assertEquals(null, $ami->platform);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ProductCodeSetList'), $ami->productCodes);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ami->productCodes->getEc2());
        $this->assertEquals(0, $ami->productCodes->count());

        $this->assertNull($ami->stateReason);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ResourceTagSetList'), $ami->tagSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ami->tagSet->getEc2());
        $this->assertEquals(0, $ami->tagSet->count());

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\BlockDeviceMappingList'), $ami->blockDeviceMapping);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ami->blockDeviceMapping->getEc2());
        $this->assertEquals(1, $ami->blockDeviceMapping->count());
        /* @var $bd BlockDeviceMappingData */
        $bd = $ami->blockDeviceMapping[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\BlockDeviceMappingData'), $bd);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $bd->getEc2());
        $this->assertEquals('/dev/sda1', $bd->deviceName);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\EbsBlockDeviceData'), $bd->ebs);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $bd->ebs->getEc2());
        $this->assertEquals('snap-32885f5a', $bd->ebs->snapshotId);
        $this->assertEquals(15, $bd->ebs->volumeSize);
        $this->assertEquals(false, $bd->ebs->deleteOnTermination);
        $this->assertEquals('standard', $bd->ebs->volumeType);
    }

    /**
     * @test
     */
    public function testDescribeAddresses()
    {
        $ec2 = $this->getEc2Mock();
        $list = $ec2->address->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AddressList'), $list);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $list->getEc2());
        $this->assertEquals(1, count($list));
        $this->assertEquals('f7de5e98-491a-4c19-a92d-908d6EXAMPLE', $list->getRequestId());

        /* @var $ad AddressData */
        $ad = $list->get(0);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AddressData'), $ad);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ad->getEc2());
        $this->assertEquals('46.51.223.41', $ad->publicIp);
        $this->assertEquals('eipalloc-08229861', $ad->allocationId);
        $this->assertEquals('vpc', $ad->domain);
        $this->assertEquals('i-64600030', $ad->instanceId);
        $this->assertEquals('eipassoc-f0229899', $ad->associationId);
        $this->assertEquals('eni-ef229886', $ad->networkInterfaceId);
        $this->assertEquals('053230519467', $ad->networkInterfaceOwnerId);
        $this->assertEquals('10.0.0.228', $ad->privateIpAddress);
    }

    /**
     * @test
     */
    public function testDescribeSnapshots()
    {
        $ec2 = $this->getEc2Mock();
        $list = $ec2->snapshot->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SnapshotList'), $list);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $list->getEc2());
        $this->assertEquals(1, count($list));
        $this->assertEquals('59dbff89-35bd-4eac-99ed-be587EXAMPLE', $list->getRequestId());

        /* @var $sn SnapshotData */
        $sn = $list->get(0);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SnapshotData'), $sn);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $sn->getEc2());
        $this->assertEquals('snap-78a54011', $sn->snapshotId);
        $this->assertSame($sn, $ec2->snapshot->get($sn->snapshotId));
        $this->assertEquals('vol-4d826724', $sn->volumeId);
        $this->assertEquals('pending', $sn->status);
        $this->assertEquals('2008-05-07T12:51:50+00:00', $sn->startTime->format('c'));
        $this->assertEquals('80%', $sn->progress);
        $this->assertEquals('111122223333', $sn->ownerId);
        $this->assertEquals('10', $sn->volumeSize);
        $this->assertEquals('Daily Backup', $sn->description);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ResourceTagSetList'), $sn->tagSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $sn->tagSet->getEc2());
        $this->assertEquals(0, count($sn->tagSet));
    }

    /**
     * @test
     */
    public function testDescribeSubnets()
    {
        $ec2 = $this->getEc2Mock();
        $list = $ec2->subnet->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SubnetList'), $list);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $list->getEc2());
        $this->assertEquals(1, count($list));
        $this->assertEquals('7a62c49f-347e-4fc4-9331-6e8eEXAMPLE', $list->getRequestId());

        /* @var $s SubnetData */
        $s = $list[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SubnetData'), $s);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $s->getEc2());
        $this->assertEquals('subnet-9d4a7b6c', $s->subnetId);
        $this->assertEquals(SubnetData::STATE_AVAILABLE, $s->state);
        $this->assertEquals('vpc-1a2b3c4d', $s->vpcId);
        $this->assertEquals('10.0.1.0/24', $s->cidrBlock);
        $this->assertEquals(250, $s->availableIpAddressCount);
        $this->assertEquals('us-east-1a', $s->availabilityZone);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ResourceTagSetList'), $s->tagSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $s->tagSet->getEc2());
        $this->assertEquals(0, count($s->tagSet));
    }

    /**
     * @test
     */
    public function testGetConsoleOutput()
    {
        $ec2 = $this->getEc2Mock();
        $output = $ec2->instance->getConsoleOutput('i-28a64341');
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\GetConsoleOutputResponseData'), $output);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $output->getEc2());
        $this->assertEquals('59dbff89-35bd-4eac-99ed-be587EXAMPLE', $output->getRequestId());
        $this->assertEquals('i-28a64341', $output->instanceId);
        $this->assertEquals('2010-10-14T01:12:41+00:00', $output->timestamp->format('c'));
        $this->assertContains('YXZlIGFuZCByZXN0b3JlLi4uIGRvbmUuCg==', $output->output);
    }

    /**
     * @test
     */
    public function testDescribeNetworkInterfaces()
    {
        $ec2 = $this->getEc2Mock();
        $eniList = $ec2->networkInterface->describe();

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\NetworkInterfaceList'), $eniList);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $eniList->getEc2());
        $this->assertEquals(2, count($eniList));
        $this->assertEquals('fc45294c-006b-457b-bab9-012f5b3b0e40', $eniList->requestId);

        /* @var $eni NetworkInterfaceData */
        $eni = $eniList->get(0);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\NetworkInterfaceData'), $eni);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $eni->getEc2());
        $this->assertEquals('eni-0f62d866', $eni->networkInterfaceId);
        $this->assertEquals('', $eni->description);
        $this->assertEquals('subnet-c53c87ac', $eni->subnetId);
        $this->assertEquals('053230519467', $eni->ownerId);
        $this->assertEquals(null, $eni->privateDnsName);
        $this->assertEquals('10.0.0.146', $eni->privateIpAddress);
        $this->assertEquals(true, $eni->sourceDestCheck);
        $this->assertEquals('in-use', $eni->status);
        $this->assertEquals('vpc-cc3c87a5', $eni->vpcId);

        $this->assertEquals(null, $eni->association);
//         $this->assertInstanceOf($this->getEc2ClassName('DataType\\NetworkInterfaceAssociationData'), $eni->association);
//         $this->assertInstanceOf($this->getAwsClassName('Ec2'), $eni->association->getEc2());

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\NetworkInterfaceAttachmentData'), $eni->attachment);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $eni->attachment->getEc2());
        $this->assertEquals('eni-attach-6537fc0c', $eni->attachment->attachmentId);
        $this->assertEquals('i-22197876', $eni->attachment->instanceId);
        $this->assertEquals('053230519467', $eni->attachment->instanceOwnerId);
        $this->assertEquals(0, $eni->attachment->deviceIndex);
        $this->assertEquals('attached', $eni->attachment->status);
        $this->assertEquals('2012-07-01T21:45:27+00:00', $eni->attachment->attachTime->format('c'));
        $this->assertEquals(true, $eni->attachment->deleteOnTermination);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\GroupList'), $eni->groupSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $eni->groupSet->getEc2());
        $this->assertEquals(1, count($eni->groupSet));
        $this->assertEquals('sg-3f4b5653', $eni->groupSet[0]->groupId);
        $this->assertEquals('default', $eni->groupSet[0]->groupName);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\NetworkInterfacePrivateIpAddressesSetList'), $eni->privateIpAddressesSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $eni->privateIpAddressesSet->getEc2());
        $this->assertEquals(3, count($eni->privateIpAddressesSet));
        $this->assertEquals('10.0.0.148', $eni->privateIpAddressesSet[1]->privateIpAddress);
        $this->assertEquals(false, $eni->privateIpAddressesSet[1]->primary);
        $this->assertEquals(true, $eni->privateIpAddressesSet[0]->primary);

        unset($eniList);
        $ec2->getEntityManager()->detachAll();
    }

    /**
     * @test
     */
    public function testDescribeRouteTables()
    {
        $ec2 = $this->getEc2Mock();
        $resList = $ec2->routeTable->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RouteTableList'), $resList);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $resList->getEc2());
        $this->assertEquals('6f570b0b-9c18-4b07-bdec-73740dcf861a', $resList->getRequestId());
        $this->assertEquals(2, count($resList));

        /* @var $rt RouteTableData */
        $rt = $resList[0];
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RouteTableData'), $rt);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rt->getEc2());
        $this->assertEquals('rtb-13ad487a', $rt->routeTableId);
        $this->assertEquals('vpc-11ad4878', $rt->vpcId);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RouteTableAssociationList'), $rt->associationSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rt->associationSet->getEc2());
        $this->assertEquals(1, count($rt->associationSet));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RouteTableAssociationData'), $rt->associationSet[0]);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rt->associationSet[0]->getEc2());
        $this->assertEquals('rtbassoc-12ad487b', $rt->associationSet[0]->routeTableAssociationId);
        $this->assertEquals('rtb-13ad487a', $rt->associationSet[0]->routeTableId);
        $this->assertEquals(true, $rt->associationSet[0]->main);
        $this->assertEquals(null, $rt->associationSet[0]->subnetId);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\PropagatingVgwList'), $rt->propagatingVgwSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rt->propagatingVgwSet->getEc2());
        $this->assertEquals(0, count($rt->propagatingVgwSet));

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RouteList'), $rt->routeSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rt->routeSet->getEc2());
        $this->assertEquals(1, count($rt->routeSet));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RouteData'), $rt->routeSet[0]);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rt->routeSet[0]->getEc2());
        $this->assertEquals('10.0.0.0/22', $rt->routeSet[0]->destinationCidrBlock);
        $this->assertEquals('local', $rt->routeSet[0]->gatewayId);
        $this->assertEquals(null, $rt->routeSet[0]->instanceId);
        $this->assertEquals('CreateRouteTable', $rt->routeSet[0]->origin);
        $this->assertNull($rt->routeSet[0]->instanceOwnerId);
        $this->assertNull($rt->routeSet[0]->networkInterfaceId);
        $this->assertEquals('active', $rt->routeSet[0]->state);

        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ResourceTagSetList'), $rt->tagSet);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $rt->tagSet->getEc2());
        $this->assertEquals(0, count($rt->tagSet));

        $ec2->getEntityManager()->detachAll();
    }

    /**
     * @test
     */
    public function testFunctionalErrorMessageShouldContainAction()
    {
        $this->skipIfEc2PlatformDisabled();
        $aws = $this->getContainer()->aws(AwsTestCase::REGION);

        try {
            $group = $aws->ec2->securityGroup->create('5 &% illegal group name ^{', '');
            $this->assertTrue(false, 'ClientException must be thrown here.');
        } catch (ClientException $e) {
            $this->assertContains('Request CreateSecurityGroup failed.', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function testFunctionalEc2()
    {
        $this->skipIfEc2PlatformDisabled();

        $aws = $this->getContainer()->aws(AwsTestCase::REGION);
        $aws->ec2->enableEntityManager();
        //We should use different ec2 instance for another region
        $aws2 = $this->getContainer()->aws(Aws::REGION_US_WEST_2);

        $nameTag = new ResourceTagSetData(self::TAG_NAME_KEY, self::getTestName(self::NAME_TAG_VALUE));

        $subnetList = $aws->ec2->subnet->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SubnetList'), $subnetList);
        unset($subnetList);

        $imageList = $aws->ec2->image->describe(null, 'self');
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ImageList'), $imageList);
        unset($imageList);

        $volumeList = $aws->ec2->volume->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\VolumeList'), $volumeList);
        unset($volumeList);

        $snapshotList = $aws->ec2->snapshot->describe(
            null, 'self',
            new SnapshotFilterData(
                SnapshotFilterNameType::tag(self::TAG_NAME_KEY),
                self::getTestName(self::NAME_TAG_VALUE)
            )
        );
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SnapshotList'), $snapshotList);
        /* @var $sn SnapshotList */
        foreach ($snapshotList as $sn) {
            $sn->delete();
        }
        unset($snapshotList);

        $reservationsList = $aws->ec2->instance->describe(
            null,
            new InstanceFilterData(
                InstanceFilterNameType::tag(self::TAG_NAME_KEY),
                self::getTestName(self::NAME_TAG_VALUE)
            )
        );
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservationList'), $reservationsList);
        foreach ($reservationsList as $r) {
            /* @var $i InstanceData */
            foreach ($r->instancesSet as $i) {
                $ds = $i->describeStatus();
                if ($ds->instanceState->name == InstanceStateData::NAME_RUNNING) {
                    //Whether the elastic ip is still associated with the instance
                    $adlist = $aws->ec2->address->describe(null, null, array(
                        array(
                            'name'  => AddressFilterNameType::instanceId(),
                            'value' => $i->instanceId,
                        )
                    ));
                    foreach ($adlist as $v) {
                        //Removes associated elastic IP address
                        $aws->ec2->address->disassociate($v->publicIp);
                        $aws->ec2->address->release($v->publicIp);
                    }
                    unset($adlist);
                } else if ($ds->instanceState->name == InstanceStateData::NAME_TERMINATED ||
                           $ds->instanceState->name == InstanceStateData::NAME_SHUTTING_DOWN) {
                    continue;
                }
                $i->terminate();
                $i = $i->refresh();
                for ($t = time(); time() - $t < 100 && isset($i->instanceState) &&
                     !in_array($i->instanceState->name, array(InstanceStateData::NAME_TERMINATED)); sleep(5)) {
                    $i = $i->refresh();
                }
            }
        }

        $placementGroups = $aws->ec2->placementGroup->describe(
            null,
            new PlacementGroupFilterData(
                PlacementGroupFilterNameType::groupName(),
                self::getTestName('placement-group')
            )
        );
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\PlacementGroupList'), $placementGroups);
        /* @var $pg PlacementGroupData */
        foreach ($placementGroups as $pg) {
            $pg->delete();
            unset($pg);
        }
        unset($placementGroups);

        $reservedInstancesList = $aws->ec2->reservedInstance->describe();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservedInstanceList'), $reservedInstancesList);

        $availabilityZoneList = $aws->ec2->availabilityZone->describe('us-east-1a', array(
            array(
                'name'  => AvailabilityZoneFilterNameType::state(),
                'value' => array('available'),
            )
        ));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AvailabilityZoneList'), $availabilityZoneList);
        $this->assertEquals(1, count($availabilityZoneList));
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $availabilityZoneList->getEc2());
        $this->assertNotEmpty($availabilityZoneList->getRequestId());
        $this->assertEquals('us-east-1a', $availabilityZoneList[0]->getZoneName());
        $this->assertEquals('available', $availabilityZoneList[0]->getZoneState());
        $this->assertEquals('us-east-1', $availabilityZoneList[0]->getRegionName());
        $ml = $availabilityZoneList[0]->getMessageSet();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AvailabilityZoneMessageList'), $ml);
        $this->assertInstanceOf($this->getAwsClassName('Ec2'), $ml->getEc2());
        unset($ml);

        $al = $aws->ec2->address->describe(null, null, array(array(
            'name'  => AddressFilterNameType::domain(),
            'value' => 'standard',
        )));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AddressList'), $al);
        unset($al);

        //Describes keypair
        $keyname = self::getTestName('keyname');
        $kplist = $aws->ec2->keyPair->describe(null, array('key-name' => $keyname));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\KeyPairList'), $kplist);
        if (count($kplist) > 0) {
            foreach ($kplist as $kp) {
                $kp->delete();
            }
        }
        unset($kplist);

        //Creates keypair
        $kp = $aws->ec2->keyPair->create($keyname);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\KeyPairData'), $kp);
        $this->assertEquals($keyname, $kp->keyName);
        $this->assertNotNull($kp->keyFingerprint);
        $this->assertNotNull($kp->keyMaterial);

        //We should be assured that group which is used for the test does not exists
        $list = $aws->ec2->securityGroup->describe(
            null, null,
            new SecurityGroupFilterData(SecurityGroupFilterNameType::groupName(), self::getTestName('security-group'))
        );
        if (count($list) > 0) {
            foreach ($list as $v) {
                $v->delete();
            }
        }
        unset($list);

        //Creates security group
        $securityGroupId = $aws->ec2->securityGroup->create(self::getTestName('security-group'), self::getTestName('security-group') . ' description');
        $this->assertNotEmpty($securityGroupId);
        sleep(2);
        /* @var $sg SecurityGroupData */
        $sg = $aws->ec2->securityGroup->describe(null, $securityGroupId)->get(0);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SecurityGroupData'), $sg);
        $this->assertNotEmpty($sg->groupId);
        $this->assertEquals(self::getTestName('security-group'), $sg->groupName);
        $this->assertContains(self::getTestName('security-group'), $sg->groupDescription);

        $ret = $sg->createTags($nameTag);
        $this->assertTrue($ret);

        //Verifies that security group entity which is stored in manager is the same object
        $sgMirror = $aws->ec2->securityGroup->get($sg->groupId);
        $this->assertSame($sg, $sgMirror);
        unset($sgMirror);

        //Athorizes Security Group Ingress
        $ipperm = new IpPermissionData('tcp', 80, 80, new IpRangeList(array(new IpRangeData('192.0.2.0/24'), new IpRangeData('192.51.100.0/24'))));
        $ret = $sg->authorizeIngress($ipperm);
        $this->assertTrue($ret);

        $ipperm2 = new IpPermissionData('tcp', 8080, 8080, new IpRangeList(array(new IpRangeData('192.66.12.0/24'))));
        $ret = $sg->authorizeIngress($ipperm2);
        $this->assertTrue($ret);
        $ret = $sg->revokeIngress($ipperm2);
        $this->assertTrue($ret);

        //Describes itself
        $sg->refresh();
        $this->assertContains(self::TAG_NAME_KEY, $sg->tagSet->getQueryArrayBare('Tag'));

        //Creates placement group
        $ret = $aws->ec2->placementGroup->create(self::getTestName('placement-group'));
        $this->assertTrue($ret);
        //Sometimes it takes a moment
        sleep(3);
        $pg = $aws->ec2->placementGroup->describe(self::getTestName('placement-group'))->get(0);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\PlacementGroupData'), $pg);
        $this->assertEquals(self::getTestName('placement-group'), $pg->groupName);
        $this->assertSame($pg, $aws->ec2->placementGroup->get($pg->groupName));

        //RunInstance test
        $request = new RunInstancesRequestData(self::INSTANCE_IMAGE_ID, 1, 1);
        $request->instanceType = self::INSTANCE_TYPE;
        //Placement groups may not be used with instances of type 'm1.small'.
        $request->setPlacement(new PlacementResponseData(AwsTestCase::AVAILABILITY_ZONE_A));
        $request->setMonitoring(true);
        $request->ebsOptimized = false;
        $request->userData = base64_encode("test=26;");
        $request->appendSecurityGroupId($securityGroupId);
        $request->appendBlockDeviceMapping(new BlockDeviceMappingData(
            "/dev/sdb", 'ephemeral0', null,
            new EbsBlockDeviceData(1, null, null, null, true)
        ));
        $request->keyName = $keyname;

        $rd = $aws->ec2->instance->run($request);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservationData'), $rd);
        /* @var $ind InstanceData */
        $ind = $rd->instancesSet[0];
        unset($request);

        //Monitors instance
        $ret = $ind->monitor();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\MonitorInstancesResponseSetList'), $ret);
        $this->assertContains($ret->get(0)->monitoring->state, array(
            InstanceMonitoringStateData::STATE_PENDING,
            InstanceMonitoringStateData::STATE_ENABLED,
        ));
        unset($ret);

        //Instance state must be in the running state
        for ($t = time(), $s = 5; time() - $t < 300 && $ind->instanceState->name !== InstanceStateData::NAME_RUNNING; $s += 5) {
            sleep($s);
            $ind = $ind->refresh();
        }
        $this->assertEquals(InstanceStateData::NAME_RUNNING, $ind->instanceState->name);

        //Creates the tag for the instance
        $res = $ind->createTags(array($nameTag, array('key' => 'Extratag', 'value' => 'extravalue')));
        $this->assertTrue($res);
        //Verifies that tag has been successfully set.
        $ind->refresh();
        $this->assertContains(self::TAG_NAME_KEY, $ind->tagSet->getQueryArrayBare());
        $this->assertContains('Extratag', $ind->tagSet->getQueryArrayBare());
        //Removes an extratag
        $ind->deleteTags(array(array('key' => 'Extratag', 'value' => null)));

        $this->assertEquals(self::INSTANCE_TYPE, $ind->instanceType);
        $this->assertEquals(self::INSTANCE_IMAGE_ID, $ind->imageId);
        $this->assertEquals(false, $ind->ebsOptimized);
        $this->assertContains($ind->monitoring->state, array('enabled', 'pending'));
        $this->assertEquals(AwsTestCase::AVAILABILITY_ZONE_A, $ind->placement->availabilityZone);
        $this->assertEquals($keyname, $ind->keyName);

        $this->assertContains($securityGroupId, array_map(function($arr){
            return $arr->groupId;
        }, iterator_to_array($ind->groupSet, false)));

        $this->assertContains(array("/dev/sdb", ''), array_map(function($arr){
            return array($arr->deviceName, $arr->virtualName);
        }, iterator_to_array($ind->blockDeviceMapping, false)));

        //Creates AMI
//         $cr = new CreateImageRequestData($ind->instanceId, sprintf(self::getTestName('i%s'), $ind->instanceId));
//         $cr->description = 'It is supposed to be removed immediately after creation.';
//         $imageId = $aws->ec2->image->create($cr);
//         sleep(3);
//         $ami = $aws->ec2->image->describe($imageId);
//         $this->assertInstanceOf($this->getEc2ClassName('DataType\\ImageData'), $ami);
//         $this->assertNotNull($ami->imageId);
//         //Waits while snapshot is created.
//         for ($t = time(), $s = 5; time() - $t < 300 && $ami->imageState === ImageData::STATE_PENDING; $s += 5) {
//             sleep($s);
//             $ami = $ami->refresh();
//         }
//         $this->assertTrue(in_array($ami->imageState, array(ImageData::STATE_AVAILABLE, SnapshotData::STATUS_ERROR)));
//         unset($cr);
//         $ami->createTags($nameTag);
//         //Copy
//         $copyImageId = $ami->copy(substr(AwsTestCase::AVAILABILITY_ZONE_A, 0, -1), null, 'phpunit copied AMI', null, Aws::REGION_US_WEST_2);
//         $this->assertNotNull($copyImageId);
//         /* @var $ami2 ImageData */
//         $ami2 = $aws2->ec2->image->describe($copyImageId)->get(0);
//         $this->assertNotNull($ami2);
//         $ami2->deregister();
//         //Deregisters an AMI
//         $ami->deregister();

        //Creates Elastic IP Address
        $address = $aws->ec2->address->allocate();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AddressData'), $address);
        $this->assertNotNull($address->publicIp);

        $ret = $aws->ec2->address->associate(new AssociateAddressRequestData($ind->instanceId, $address->publicIp));
        $this->assertTrue($ret);
        //DescribeAddress should return allocated addres
        $adlist = $aws->ec2->address->describe($address->publicIp, null, array(
            array(
                'name'  => AddressFilterNameType::instanceId(),
                'value' => $ind->instanceId,
            )
        ));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AddressList'), $adlist);
        $this->assertEquals(1, count($adlist));
        $this->assertEquals($address->publicIp, $adlist->get(0)->publicIp);
        //It must be associated with the instance
        $this->assertEquals($ind->instanceId, $adlist->get(0)->instanceId);
        $this->assertEquals($address->domain, $adlist->get(0)->domain);

        //Dissassociates address
        $ret = $aws->ec2->address->disassociate($address->publicIp);
        $this->assertTrue($ret);

        //Releases address
        $ret = $aws->ec2->address->release($address->publicIp);
        $this->assertTrue($ret);

        unset($adlist);
        unset($address);

        //Creates the volume
        $cvRequest = new CreateVolumeRequestData(AwsTestCase::AVAILABILITY_ZONE_A);
        $cvRequest->setSize(2)->setVolumeType(CreateVolumeRequestData::VOLUME_TYPE_STANDARD);
        $vd = $aws->ec2->volume->create($cvRequest);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\VolumeData'), $vd);
        $volumeid = $vd->volumeId;
        unset($cvRequest);

        $res = $vd->createTags($nameTag);
        $this->assertTrue($res);

        //Volume must be in the Available status
        for ($t = time(), $s = 2; time() - $t < 300 && $vd->status !== VolumeData::STATUS_AVAILABLE; $s += 5) {
            sleep($s);
            $vd = $vd->refresh();
        }
        $this->assertEquals(VolumeData::STATUS_AVAILABLE, $vd->status);

        //Attaching the volume
        $at = $vd->attach($ind->instanceId, '/dev/sdh');
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AttachmentSetResponseData'), $at);

        //Creates snapshot
        $sn = $vd->createSnapshot(self::getTestName(self::NAME_SNAPSHOT));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SnapshotData'), $sn);
        $this->assertNotEmpty($sn->snapshotId);
        $sn->createTags($nameTag);
        //Waits while snapshot is created.
        for ($t = time(), $s = 2; time() - $t < 300 && $sn->status === SnapshotData::STATUS_PENDING; $s += 5) {
            sleep($s);
            $sn->refresh();
        }
        $this->assertTrue(in_array($sn->status, array(SnapshotData::STATUS_COMPLETED, SnapshotData::STATUS_ERROR)));

        //Copies snapshot to different region
        //We should provide the same region where snapshot was created.
        $copySnapshotId = $sn->copy(substr(AwsTestCase::AVAILABILITY_ZONE_A, 0, -1), 'phpunit copied snapshot', Aws::REGION_US_WEST_2);
        $this->assertNotNull($copySnapshotId);

        $aws2->ec2->tag->create($copySnapshotId, $nameTag);
        $csn = $aws2->ec2->snapshot->describe($copySnapshotId)->get(0);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SnapshotData'), $csn);
        //Waits while snapshot is created.
        for ($t = time(), $s = 2; time() - $t < 600 && $csn->status === SnapshotData::STATUS_PENDING; $s += 5) {
            sleep($s);
            $csn = $csn->refresh();
        }
        $this->assertTrue(in_array($csn->status, array(SnapshotData::STATUS_COMPLETED, SnapshotData::STATUS_ERROR)));

        //Removes snapshot
        $ret = $sn->delete();
        $this->assertTrue($ret);

        //Removes copied snapshot
        $ret = $csn->delete();
        $this->assertTrue($ret);

        //Detaching the volume
        $at = $vd->detach();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AttachmentSetResponseData'), $at);

        $maxTimeout = 200;
        $interval = 2;
        while ($maxTimeout > 0) {
            if (count($vd->attachmentSet) == 0 || $vd->attachmentSet[0]->status == AttachmentSetResponseData::STATUS_DETACHED) {
                break;
            }
            sleep($interval);
            $maxTimeout -= $interval;
            $interval *= 2;
            $vd->refresh();
        }
        if (isset($vd->attachmentSet[0]->status) &&
            $vd->attachmentSet[0]->status !== AttachmentSetResponseData::STATUS_DETACHED) {
            $this->assertTrue(false, sprintf(
                'The volume %s has not been detached from the instance %s yet.',
                $volumeid, $ind->instanceId
            ));
        }

        $vd->refresh();
        $this->assertContains(self::TAG_NAME_KEY, $vd->tagSet->getQueryArrayBare('Tag'));

        //Deletes the volume.
        $ret = $vd->delete();
        $this->assertTrue($ret);
        $this->assertNull($aws->ec2->volume->get($volumeid));
        unset($volumeid);
        unset($vd);
        unset($at);

        $ind->refresh();
        //Verifies that extratag has been successfully removed
        $this->assertNotContains('Extratag', $ind->tagSet->getQueryArrayBare());

        $consoleOutput = $ind->getConsoleOutput();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\GetConsoleOutputResponseData'), $consoleOutput);
        unset($consoleOutput);

        //Reboots the instance
        $ret = $ind->reboot();
        $this->assertTrue($ret);

        //Stoping the instance
        $scList = $ind->stop(true);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStateChangeList'), $scList);
        $this->assertEquals(1, count($scList));
        unset($scList);
        for ($t = time(); time() - $t < 300 && $ind->instanceState->name !== InstanceStateData::NAME_STOPPED; sleep(5)) {
            $ind = $ind->refresh();
        }
        $this->assertEquals(InstanceStateData::NAME_STOPPED, $ind->instanceState->name);

        //Modifies instance attribute
        //Instance is required to be stopped.
        $ret = $ind->modifyAttribute(InstanceAttributeType::userData(), base64_encode('user data'));
        $this->assertTrue($ret);

        //Starts the instance
        $scList = $ind->start();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStateChangeList'), $scList);
        unset($scList);
        $ind = $ind->refresh();
        for ($t = time(), $s = 5; time() - $t < 200 && $ind->instanceState->name !== InstanceStateData::NAME_RUNNING; $s += 5) {
            sleep($s);
            $ind = $ind->refresh();
        }
        $this->assertEquals(InstanceStateData::NAME_RUNNING, $ind->instanceState->name);

        //Unmonitors instance
        $ret = $ind->unmonitor();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\MonitorInstancesResponseSetList'), $ret);
        $this->assertContains($ret->get(0)->monitoring->state, array(
            InstanceMonitoringStateData::STATE_DISABLING,
            InstanceMonitoringStateData::STATE_DISABLED,
        ));
        unset($ret);

        //Terminates the instance
        $st = $ind->terminate();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceStateChangeList'), $st);
        $this->assertEquals(1, count($st));
        $this->assertEquals($rd->instancesSet[0]->instanceId, $st[0]->getInstanceId());

        for ($t = time(); time() - $t < 200 && $ind && $ind->instanceState->name != InstanceStateData::NAME_TERMINATED; sleep(5)) {
            $ind = $ind->refresh();
        }
        $this->assertTrue(!$ind || $ind->instanceState->name == InstanceStateData::NAME_TERMINATED);
        if (isset($ind)) {
            unset($ind);
        }

        //Removes keypair
        $ret = $kp->delete();
        $this->assertTrue($ret);
        unset($kp);

        //Removes security group
        $sg->delete();
        //Verifies that security group is detached from the storage
        $sgMirror = $aws->ec2->securityGroup->get($sg->groupId);
        $this->assertNull($sgMirror);
        unset($sg);

        //Deletes placement group.
        $ret = $pg->delete();
        $this->assertTrue($ret);
        $this->assertNull($aws->ec2->placementGroup->get(self::getTestName('placement-group')));

        //Releases all memory
        $aws->getEntityManager()->detachAll();
    }

    /**
     * @test
     * @depends testFunctionalEc2
     */
    public function testFunctionalVpc()
    {
        $this->skipIfEc2PlatformDisabled();

        $aws = $this->getContainer()->aws(AwsTestCase::REGION);
        $aws->ec2->enableEntityManager();

        $nameTag = new ResourceTagSetData(self::TAG_NAME_KEY, self::getTestName(self::NAME_TAG_VALUE));

        $ret = $aws->ec2->describeAccountAttributes(array('supported-platforms', 'default-vpc'));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\AccountAttributeSetList'), $ret);
        unset($ret);

        //Removes previously created route tables if they exist.
        $rtList = $aws->ec2->routeTable->describe(null, array(array(
            'name'  => RouteTableFilterNameType::tagName(),
            'value' => self::getTestName(self::NAME_TAG_VALUE),
        )));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RouteTableList'), $rtList);
        foreach ($rtList as $rt) {
            /* @var $rt RouteTableData */
            foreach ($rt->routeSet as $route) {
                /* @var $route RouteData */
                try {
                    $route->delete();
                } catch (ClientException $e) {
                }
            }
            foreach ($rt->associationSet as $rtassoc) {
                try {
                    $rtassoc->disassociate();
                } catch (ClientException $e) {
                }
            }
            $rt->delete();
        }
        unset($rtList);

        //Removes previously created Network Interfaces if they have not been removed during past test executions.
        $eniList = $aws->ec2->networkInterface->describe(null, array(array(
            'name'  => NetworkInterfaceFilterNameType::tag(self::TAG_NAME_KEY),
            'value' => self::getTestName(self::NAME_TAG_VALUE),
        )));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\NetworkInterfaceList'), $eniList);
        foreach ($eniList as $v) {
            $v->delete();
        }
        unset($eniList);

        $subnetList = $aws->ec2->subnet->describe(null, array(array(
            'name'  => SubnetFilterNameType::tag(self::TAG_NAME_KEY),
            'value' => self::getTestName(self::NAME_TAG_VALUE),
        )));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SubnetList'), $subnetList);
        foreach ($subnetList as $subnet) {
            /* @var $subnet SubnetData */
            $subnet->delete();
        }
        unset($subnetList);

        //Removes previously created Internet Gateways which has not been removed during previous test run.
        $igwList = $aws->ec2->internetGateway->describe(null, array(array(
            'name'  => InternetGatewayFilterNameType::tag(self::TAG_NAME_KEY),
            'value' => self::getTestName(self::NAME_TAG_VALUE),
        )));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InternetGatewayList'), $igwList);
        foreach ($igwList as $igw) {
            /* @var $igw InternetGatewayData */
            if (count($igw->attachmentSet)) {
                //Detaches previously attachet VPC
                $igw->attachmentSet->get(0)->detach();
                for ($t = time(); time() - $t < 100 && !empty($igw->attachmentSet[0]) &&
                     $igw->attachmentSet[0]->state == InternetGatewayAttachmentData::STATE_DETACHING;
                     sleep(3)) {
                    $igw = $igw->refresh();
                }
            }
            //Deletes previously created internet gateways
            $igw->delete();
        }
        unset($igwList);

        //We should be assured that group which is used for the test does not exists
        $list = $aws->ec2->securityGroup->describe(
            null, null,
            new SecurityGroupFilterData(SecurityGroupFilterNameType::groupName(), self::getTestName(self::NAME_SECURITY_GROUP_VPC))
        );
        if (count($list) > 0) {
            foreach ($list as $v) {
                $v->delete();
            }
        }
        unset($list);

        //Describes VPC
        $vpcList = $aws->ec2->vpc->describe(null, array(array(
            'name'  => VpcFilterNameType::tag(self::TAG_NAME_KEY),
            'value' => self::getTestName(self::NAME_TAG_VALUE),
        )));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\VpcList'), $vpcList);
        //We should remove VPC which has not been removed by some reason.
        foreach ($vpcList as $vpc) {
            $vpc->delete();
            unset($vpc);
        }
        unset($vpcList);

        //Creates VPC
        $vpc = $aws->ec2->vpc->create('10.0.0.0/16');
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\VpcData'), $vpc);
        for ($t = time(); (time() - $t) < 600 && $vpc->state !== VpcData::STATE_AVAILABLE; ) {
            sleep(5);
            $vpc = $vpc->refresh();
        }
        $this->assertTrue($vpc->state == VpcData::STATE_AVAILABLE);
        $ret = $vpc->createTags($nameTag);
        $this->assertTrue($ret);

        //Creates an VPC Security group
        $securityGroupId = $aws->ec2->securityGroup->create(
            self::getTestName(self::NAME_SECURITY_GROUP_VPC), self::getTestName(self::NAME_SECURITY_GROUP_VPC) . ' description', $vpc->vpcId);
        $this->assertNotEmpty($securityGroupId);
        sleep(2);

        $sg = $aws->ec2->securityGroup->describe(null, $securityGroupId)->get(0);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SecurityGroupData'), $sg);

        //Authorizes security group Egress
        //Example, how to construct the list with arrays
        $ipperm3array = array(array(
            'ipProtocol' => 'tcp',
            'fromPort'   => 80,
            'toPort'     => 80,
            'ipRanges'   => array(
                array(
                    'cidrIp' => '192.0.2.0/24'
                ),
                array(
                    'cidrIp' => '198.51.100.0/24'
                )
            )
        ));
        $ipperm3 = new IpPermissionList($ipperm3array);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\IpPermissionData'), $ipperm3->get(0));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\IpRangeList'), $ipperm3->get(0)->ipRanges);
        $this->assertEquals(2, $ipperm3->get(0)->ipRanges->count());
        $this->assertEquals('192.0.2.0/24', $ipperm3->get(0)->ipRanges->get(0)->cidrIp);
        $this->assertEquals('198.51.100.0/24', $ipperm3->get(0)->ipRanges->get(1)->cidrIp);

        //The same can be produced in the another way
        $ipperm4 = new IpPermissionList(new IpPermissionData('tcp', 80, 80, array(
            new IpRangeData('192.0.2.0/24'),
            new IpRangeData('198.51.100.0/24'),
        )));
        //Checks the equality
        $this->assertEquals($ipperm3->toArray(), $ipperm4->toArray());

        //Authorizes IP Permission Egress
        $ret = $sg->authorizeEgress($ipperm3);
        $this->assertTrue($ret);
        sleep(1);

        //Checks if specified IP Permission is successfully set
        $sg->refresh();
        $this->assertContains('192.0.2.0/24', $sg->ipPermissionsEgress->getQueryArrayBare());

        //Revokes IP Permission Egress
        //You may pass an array directly to the method
        $ret = $sg->revokeEgress($ipperm3array);
        $this->assertTrue($ret);
        sleep(1);

        $sg->refresh();
        //Checks if IP Permission is successfully revoked.
        $this->assertNotContains('192.0.2.0/24', $sg->ipPermissionsEgress->getQueryArrayBare());
        $this->assertNotContains('198.51.100.0/24', $sg->ipPermissionsEgress->getQueryArrayBare());

        //Creates subneet for the networkInterface
        $subnet = $aws->ec2->subnet->create($vpc->vpcId, '10.0.0.0/16');
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\SubnetData'), $subnet);
        for ($t = time(); (time() - $t) < 600 && $subnet->state !== SubnetData::STATE_AVAILABLE; ) {
            sleep(5);
            $subnet = $subnet->refresh();
        }
        $this->assertTrue($subnet->state == SubnetData::STATE_AVAILABLE);
        $ret = $subnet->createTags($nameTag);
        $this->assertTrue($ret);

        //Creates network interface
        $eni = $aws->ec2->networkInterface->create($subnet->subnetId);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\NetworkInterfaceData'), $eni);
        sleep(4);
        $ret = $eni->createTags($nameTag);
        $this->assertTrue($ret);

        //DescribeAttribute test
        foreach (NetworkInterfaceAttributeType::getAllowedValues() as $attr) {
            $expected = $eni->{$attr};
            $v = $eni->describeAttribute($attr);
            $this->assertEquals($expected, $v);
            if (is_object($v)) {
                //It's true only if entityManager is enabled
                $this->assertSame($eni->{$attr}, $v);
            }
        }

        //ModifyAttribute test
        $ret = $eni->modifyAttribute(NetworkInterfaceAttributeType::sourceDestCheck(), true);
        $this->assertTrue($ret);

        //ResetAttrubute test
        $ret = $eni->resetAttribute(NetworkInterfaceAttributeType::sourceDestCheck());
        $this->assertTrue($ret);

        //Creates Internet Gateway
        $igw = $aws->ec2->internetGateway->create();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InternetGatewayData'), $igw);
        $this->assertNotEmpty($igw->internetGatewayId);
        sleep(4);
        $igw->createTags($nameTag);

        //Attaches Internet Gateway to VPC
        $ret = $igw->attach($vpc->vpcId);
        $this->assertTrue($ret);
        $t = time();
        do {
            sleep(3);
            $igw = $igw->refresh();
            //Verifies that external index for attachmentSet is set properly.
            $this->assertEquals($igw->internetGatewayId, $igw->attachmentSet[0]->getInternetGatewayId());
        } while (time() - $t < 100 && $igw->attachmentSet[0]->state != InternetGatewayAttachmentData::STATE_ATTACHED);
        $this->assertTrue($igw->attachmentSet[0]->state == InternetGatewayAttachmentData::STATE_AVAILABLE);

        //Detaches Internet Gateway from VPC
        $ret = $igw->detach($vpc->vpcId);
        $this->assertTrue($ret);
        for ($t = time(); time() - $t < 100 && count($igw->attachmentSet) &&
             $igw->attachmentSet[0]->state == InternetGatewayAttachmentData::STATE_DETACHING;
             sleep(3)) {
            $igw = $igw->refresh();
        }
        $this->assertTrue($igw->attachmentSet[0]->state !== InternetGatewayAttachmentData::STATE_DETACHING);

        //Creates RouteTable
        $rt = $vpc->createRouteTable();
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\RouteTableData'), $rt);
        $this->assertNotEmpty($rt->routeTableId);
        $this->assertEquals($vpc->vpcId, $rt->vpcId);
        sleep(5);
        $ret = $rt->createTags($nameTag);
        $this->assertTrue($ret);

        //Associates route table with the subnet
        $associationId = $rt->associate($subnet->subnetId);
        $this->assertNotEmpty($associationId);

        $rt = $rt->refresh();

        $this->assertTrue(count($rt->associationSet) > 0);
        $c = array();
        foreach ($rt->associationSet as $rtassoc) {
            /* @var $rtassoc RouteTableAssociationData */
            $c[] = $rtassoc->routeTableAssociationId;
        }
        $this->assertContains($associationId, $c);

        //Adds Route to Route Table
        $destinationCidrBlock = '0.0.0.0/0';
        $ret = $rt->createRoute($destinationCidrBlock, null, null, $eni->networkInterfaceId);
        $this->assertTrue($ret);

        $rt = $rt->refresh();

        $this->assertTrue(count($rt->routeSet) > 0);
        $c = array();
        foreach ($rt->routeSet as $route) {
            /* @var $route RouteData */
            $c[$route->destinationCidrBlock] = $route;
            unset($route);
        }
        $this->assertArrayHasKey($destinationCidrBlock, $c);
        $route = $c[$destinationCidrBlock];

        //Deletes Route
        $ret = $route->delete();
        $this->assertTrue($ret);
        unset($route);

        $rt = $rt->refresh();

        //Disassociates route table with the subnet
        foreach ($rt->associationSet as $rtassoc) {
            if ($rtassoc->routeTableAssociationId == $associationId) {
                $ret = $rtassoc->disassociate();
                $this->assertTrue($ret);
            }
        }

        //Removes Route Table
        $ret = $rt->delete();
        $this->assertTrue($ret);

        //Removes Internet Gateway
        $ret = $igw->delete();
        $this->assertTrue($ret);

        //Removes Network Interface
        $ret = $eni->delete();
        $this->assertTrue($ret);

        //Removes Subnet
        $ret = $subnet->delete();
        $this->assertTrue($ret);

        //Removes securigy group
        $ret = $sg->delete();
        $this->assertTrue($ret);

        //Removes VPC
        $ret = $vpc->delete();
        $this->assertTrue($ret);

        $aws->ec2->getEntityManager()->detachAll();
    }
}
