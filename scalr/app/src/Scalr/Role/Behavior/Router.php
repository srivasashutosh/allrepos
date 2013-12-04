<?php

use Scalr\Service\Aws\Ec2\DataType\CreateNetworkInterfaceRequestData;
use Scalr\Service\Aws\Ec2\DataType\AssociateAddressRequestData;
use Scalr\Service\Aws\Ec2\DataType\IpPermissionData;
use Scalr\Service\Aws\Ec2\DataType\IpRangeList;
use Scalr\Service\Aws\Ec2\DataType\IpRangeData;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAttributeType;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\SubnetFilterNameType;

class Scalr_Role_Behavior_Router extends Scalr_Role_Behavior implements Scalr_Role_iBehavior
{
    const ROLE_VPC_NID 	            = 'router.vpc.networkInterfaceId';
    const ROLE_VPC_IP 	            = 'router.vpc.ip';
    const ROLE_VPC_AID 	            = 'router.vpc.ipAllocationId';
    const ROLE_VPC_ROUTER_CONFIGURED = 'router.vpc.configured';
    const ROLE_VPC_NAT_ENABLED      = 'router.vpc.nat_enabled';

    const INTERNET_ACCESS_FULL = 'full';
    const INTERNET_ACCESS_OUTBOUND = 'outbound-only';

    public function __construct($behaviorName)
    {
        parent::__construct($behaviorName);
    }

    public function getSecurityRules()
    {
        return array();
    }

    public function createSubnet($type) {

    }

    public function onFarmSave(DBFarm $dbFarm, DBFarmRole $dbFarmRole)
    {
        $vpcId = $dbFarm->GetSetting(DBFarm::SETTING_EC2_VPC_ID);
        if (!$vpcId) {
            //REMOVE VPC RELATED SETTINGS
            return;
        }

        if ($dbFarmRole->GetSetting(self::ROLE_VPC_ROUTER_CONFIGURED) == 1) {
            // ALL OBJECTS ALREADY CONFIGURED
            return true;
        }


        $aws = $dbFarm->GetEnvironmentObject()->aws($dbFarmRole->CloudLocation);

        $filter = array(array(
            'name'  => SubnetFilterNameType::vpcId(),
            'value' => $vpcId,
        ), array(
            'name'  => 'tag-key',
            'value' => 'scalr-sn-type'
        ), array(
            'name'  => 'tag-value',
            'value' => self::INTERNET_ACCESS_FULL
        ));

        // Try to find scalr FULL subnet
        $subnets = $aws->ec2->subnet->describe(null, $filter);
        if ($subnets->count() > 0)
            $subnetId = $subnets->get(0)->subnetId;

        if (!$subnetId) {
            $platform = PlatformFactory::NewPlatform(SERVER_PLATFORMS::EC2);
            $subnet = $platform->AllocateNewSubnet($aws->ec2, $vpcId, null);

            $subnetId = $subnet->subnetId;

            //ADD TAGS
            try {
                $subnet->createTags(array(
                    array('key' => "scalr-id", 'value' => SCALR_ID),
                    array('key' => "scalr-sn-type", 'value' => self::INTERNET_ACCESS_FULL),
                    array('key' => "Name", 'value' => 'Scalr System Subnet')
                ));
            } catch (Exception $e) {}

            $routingTableId = $platform->getRoutingTable(self::INTERNET_ACCESS_FULL, $aws, null, $vpcId);

            //Associate Routing table with subnet
            $aws->ec2->routeTable->associate($routingTableId, $subnetId);
        }

        $niId = $dbFarmRole->GetSetting(self::ROLE_VPC_NID);
        if (!$niId) {
            //Create Network interface
            $createNetworkInterfaceRequestData = new CreateNetworkInterfaceRequestData($subnetId);

            // Check and create security group
            $filter = array(
                array(
                    'name' => SecurityGroupFilterNameType::groupName(),
                    'value' => array('SCALR-VPC')
                ),
                array(
                    'name'  => SecurityGroupFilterNameType::vpcId(),
                    'value' => $vpcId,
                )
            );
            try {
                $list = $aws->ec2->securityGroup->describe(null, null, $filter);
                if ($list->count() > 0 && $list->get(0)->groupName == 'SCALR-VPC')
                    $sgId = $list->get(0)->groupId;

            } catch (Exception $e) {
                throw new Exception("Cannot get list of security groups (1): {$e->getMessage()}");
            }

            if (!$sgId) {
                $sgId = $aws->ec2->securityGroup->create('SCALR-VPC', 'System SG for Scalr VPC integration', $vpcId);

                $ipRangeList = new IpRangeList();
                $ipRangeList->append(new IpRangeData('0.0.0.0/0'));

                $ipRangeListLocal = new IpRangeList();
                $ipRangeListLocal->append(new IpRangeData('10.0.0.0/8'));

                $aws->ec2->securityGroup->authorizeIngress(array(
                    new IpPermissionData('tcp', 8008, 8013, $ipRangeList),
                    new IpPermissionData('tcp', 80, 80, $ipRangeList),
                    new IpPermissionData('tcp', 443, 443, $ipRangeList),
                    new IpPermissionData('tcp', 0, 65535, $ipRangeListLocal),
                    new IpPermissionData('udp', 0, 65535, $ipRangeListLocal)
                ), $sgId);
            }

            $createNetworkInterfaceRequestData->setSecurityGroupId(array(
                'groupId' => $sgId
            ));

            $networkInterface = $aws->ec2->networkInterface->create($createNetworkInterfaceRequestData);

            // Disable sourceDeskCheck
            $networkInterface->modifyAttribute(NetworkInterfaceAttributeType::sourceDestCheck(), 0);

            $niId = $networkInterface->networkInterfaceId;
            $dbFarmRole->SetSetting(self::ROLE_VPC_NID, $niId);

            try {
                $networkInterface->createTags(array(
                    array('key' => "scalr-id", 'value' => SCALR_ID),
                    array('key' => "Name", 'value' => 'Scalr System ENI')
                ));
            } catch (Exception $e) {}
        }

        // If there is no public IP allocate it and associate with NI
        $publicIp = $dbFarmRole->GetSetting(self::ROLE_VPC_IP);
        if ($niId && !$publicIp) {
            $address = $aws->ec2->address->allocate('vpc');
            $publicIp = $address->publicIp;

            $dbFarmRole->SetSetting(self::ROLE_VPC_IP, $publicIp);
            $dbFarmRole->SetSetting(self::ROLE_VPC_AID, $address->allocationId);

            $associateAddressRequestData = new AssociateAddressRequestData();
            $associateAddressRequestData->networkInterfaceId = $niId;
            $associateAddressRequestData->allocationId = $address->allocationId;

            //Associate PublicIP with NetworkInterface
            $aws->ec2->address->associate($associateAddressRequestData);
        }

        $dbFarmRole->SetSetting(self::ROLE_VPC_ROUTER_CONFIGURED, 1);
    }

    public function extendMessage(Scalr_Messaging_Msg $message, DBServer $dbServer)
    {
        $message = parent::extendMessage($message, $dbServer);

        switch (get_class($message)) {
            case "Scalr_Messaging_Msg_HostInitResponse":
                $message->router = new stdClass();

                // Set scalr address
                $message->router->scalrAddr =
                    \Scalr::config('scalr.endpoint.scheme') . "://" .
                    \Scalr::config('scalr.endpoint.host');

                // Set scalr IPs whitelist
                $message->router->whitelist = \Scalr::config('scalr.aws.ip_pool');

                // Set CIDR
                $message->router->cidr = '10.0.0.0/8';
        }

        return $message;
    }
}