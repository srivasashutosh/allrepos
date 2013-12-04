<?php
namespace Scalr\Tests\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\DataType\PlacementResponseData;
use Scalr\Service\Aws\Ec2\DataType\PrivateIpAddressesSetRequestList;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupIdSetData;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupIdSetList;
use Scalr\Service\Aws\Ec2\DataType\InstanceNetworkInterfaceSetRequestData;
use Scalr\Service\Aws\Ec2\DataType\MonitoringInstanceData;
use Scalr\Service\Aws\Ec2\DataType\IamInstanceProfileRequestData;
use Scalr\Service\Aws\Ec2\DataType\EbsBlockDeviceData;
use Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingData;
use Scalr\Service\Aws\Ec2\DataType\RunInstancesRequestData;
use Scalr\Service\Aws\Ec2\DataType\InstanceFilterNameType;
use Scalr\Tests\Service\AwsTestCase;

/**
 * RunInstancesRequestDataTest
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     17.01.2013
 */
class RunInstancesRequestDataTest extends AwsTestCase
{

    /**
     * @test
     */
    public function testConstruction()
    {
        $r = new RunInstancesRequestData('image-id', 1, 3);
        $r->clientToken = 'client-token';
        $r->disableApiTermination = true;
        $r->instanceInitiatedShutdownBehavior = 'stop';
        $r->setInstanceType('t1.micro');
        $r->kernelId = 'kernel-id';
        $r->keyName = 'key-name';
        $r->privateIpAddress = 'private-ip-address-ex';
        $r->ramdiskId = 'ramdisk-id';
        $r->subnetId = 'subnet-id';
        $r->userData = 'user-data';

        $ebs = new EbsBlockDeviceData();
        $ebs->deleteOnTermination = false;
        $ebs->iops = 12;
        $ebs->snapshotId = 'snapshot-id';
        $ebs->volumeSize = 10;
        $ebs->volumeType = 'standard';

        $bdm = new BlockDeviceMappingData();
        $bdm->deviceName = 'device-name-1';
        $bdm->ebs = $ebs;
        $bdm->noDevice = '';
        $bdm->setVirtualName('virtual-name');

        $r->setBlockDeviceMapping($bdm);

        $r->setIamInstanceProfile(new IamInstanceProfileRequestData('arn', 'name'));

        $r->setMonitoring(new MonitoringInstanceData(true));

        $ni = new InstanceNetworkInterfaceSetRequestData();
        $ni->deleteOnTermination = true;
        $ni->description = 'description-1';
        $ni->deviceIndex = 1;
        $ni->networkInterfaceId = 'network-interface-id';
        $ni->privateIpAddress = 'private-ip-address';
        $ni->privateIpAddresses = array(
            array(
                'primary'          => false,
                'privateIpAddress' => '10.10.0.1',
            ),
            array(
                'primary'          => true,
                'privateIpAddress' => '10.10.0.2',
            ),
        );
        $ni->securityGroupId = array(
            array('groupId' => 'security-group-id-1'),
            array('groupId' => 'security-group-id-2'),
        );
        $ni->secondaryPrivateIpAddressCount = 2;

        $ni2 = clone $ni;
        $ni2->description = 'description-2';
        $ni2->deviceIndex = 2;

        $r->setNetworkInterface(array($ni, $ni2));

        $r->setSecurityGroup(array('security-group-name-1', 'security-group-name-2'));

        $r->setSecurityGroupId(array('security-group-id-1', 'security-group-id-2'));

        $r->setPlacement(new PlacementResponseData('availability-zone', 'group-name', 'tenancy'));

        $this->assertEquals('image-id', $r->imageId);
        $this->assertEquals($r->imageId, $r->getImageId());
        $this->assertEquals(1, $r->minCount);
        $this->assertEquals($r->minCount, $r->getMinCount());
        $this->assertEquals(3, $r->maxCount);
        $this->assertEquals($r->maxCount, $r->getMaxCount());

        $fxtr = array(
            'ImageId' => 'image-id',
            'MinCount' => '1',
            'MaxCount' => '3',
            'KeyName' => 'key-name',
            'UserData' => 'user-data',
            'InstanceType' => 't1.micro',
            'KernelId' => 'kernel-id',
            'RamdiskId' => 'ramdisk-id',
            'SubnetId' => 'subnet-id',
            'DisableApiTermination' => 'true',
            'InstanceInitiatedShutdownBehavior' => 'stop',
            'PrivateIpAddress' => 'private-ip-address-ex',
            'ClientToken' => 'client-token',
            'SecurityGroupId.1' => 'security-group-id-1',
            'SecurityGroupId.2' => 'security-group-id-2',
            'SecurityGroup.1' => 'security-group-name-1',
            'SecurityGroup.2' => 'security-group-name-2',
            'Placement.AvailabilityZone' => 'availability-zone',
            'Placement.GroupName' => 'group-name',
            'Placement.Tenancy' => 'tenancy',
            'BlockDeviceMapping.1.DeviceName' => 'device-name-1',
            'BlockDeviceMapping.1.VirtualName' => 'virtual-name',
            'BlockDeviceMapping.1.NoDevice' => '',
            'BlockDeviceMapping.1.Ebs.SnapshotId' => 'snapshot-id',
            'BlockDeviceMapping.1.Ebs.VolumeSize' => '10',
            'BlockDeviceMapping.1.Ebs.DeleteOnTermination' => 'false',
            'BlockDeviceMapping.1.Ebs.VolumeType' => 'standard',
            'BlockDeviceMapping.1.Ebs.Iops' => '12',
            'Monitoring.Enabled' => 'true',
            'NetworkInterface.1.NetworkInterfaceId' => 'network-interface-id',
            'NetworkInterface.1.DeviceIndex' => '1',
            'NetworkInterface.1.Description' => 'description-1',
            'NetworkInterface.1.PrivateIpAddress' => 'private-ip-address',
            'NetworkInterface.1.SecurityGroupId.1' => 'security-group-id-1',
            'NetworkInterface.1.SecurityGroupId.2' => 'security-group-id-2',
            'NetworkInterface.1.DeleteOnTermination' => 'true',
            'NetworkInterface.1.PrivateIpAddresses.1.Primary' => 'false',
            'NetworkInterface.1.PrivateIpAddresses.1.PrivateIpAddress' => '10.10.0.1',
            'NetworkInterface.1.PrivateIpAddresses.2.Primary' => 'true',
            'NetworkInterface.1.PrivateIpAddresses.2.PrivateIpAddress' => '10.10.0.2',
            'NetworkInterface.1.SecondaryPrivateIpAddressCount' => '2',
            'NetworkInterface.2.NetworkInterfaceId' => 'network-interface-id',
            'NetworkInterface.2.DeviceIndex' => '2',
            'NetworkInterface.2.Description' => 'description-2',
            'NetworkInterface.2.PrivateIpAddress' => 'private-ip-address',
            'NetworkInterface.2.SecurityGroupId.1' => 'security-group-id-1',
            'NetworkInterface.2.SecurityGroupId.2' => 'security-group-id-2',
            'NetworkInterface.2.DeleteOnTermination' => 'true',
            'NetworkInterface.2.PrivateIpAddresses.1.Primary' => 'false',
            'NetworkInterface.2.PrivateIpAddresses.1.PrivateIpAddress' => '10.10.0.1',
            'NetworkInterface.2.PrivateIpAddresses.2.Primary' => 'true',
            'NetworkInterface.2.PrivateIpAddresses.2.PrivateIpAddress' => '10.10.0.2',
            'NetworkInterface.2.SecondaryPrivateIpAddressCount' => '2',
            'IamInstanceProfile.Arn' => 'arn',
            'IamInstanceProfile.Name' => 'name'
        );
        $this->assertEquals($fxtr, $r->getQueryArrayBare());
    }
}