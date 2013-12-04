<?php

use Scalr\Service\Aws\Ec2\DataType\SnapshotFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\AddressFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\SnapshotData;
use Scalr\Service\Aws\Ec2\DataType\SubnetFilterNameType;

use Scalr\Farm\Role\FarmRoleService;
use Scalr\Service\Aws\Ec2\DataType\RouteTableFilterNameType;


class Scalr_UI_Controller_Platforms_Ec2 extends Scalr_UI_Controller
{
    public function xListElbAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $response = $aws->elb->describeLoadBalancers();
        $data = array();
        /* @var $elb \Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData */
        foreach ($response as $elb) {
            $info = array(
                'name' => $elb->loadBalancerName,
                'hostname' => $elb->dnsName
            );

            $farmRoleService = FarmRoleService::findFarmRoleService($this->environment->id, $elb->loadBalancerName);
            if ($farmRoleService) {
                $info['used'] = true;
                $info['farmRoleId'] = $farmRoleService->getFarmRole()->ID;
                $info['farmId'] = $farmRoleService->getFarmRole()->FarmID;
                $info['roleName'] = $farmRoleService->getFarmRole()->GetRoleObject()->name;
                $info['farmName'] = $farmRoleService->getFarmRole()->GetFarmObject()->Name;
            }

            //OLD notation
            try {
                $farmRoleId = $this->db->GetOne("SELECT farm_roleid FROM farm_role_settings WHERE name='lb.name' AND value=?", array(
                    $elb->loadBalancerName
                ));
                if ($farmRoleId) {
                    $dbFarmRole = DBFarmRole::LoadByID($farmRoleId);
                    $info['used'] = true;
                    $info['farmRoleId'] = $dbFarmRole->ID;
                    $info['farmId'] = $dbFarmRole->FarmID;
                    $info['roleName'] = $dbFarmRole->GetRoleObject()->name;
                    $info['farmName'] = $dbFarmRole->GetFarmObject()->Name;
                }
            } catch (Exception $e) {}

            $data[] = $info;
        }

        $this->response->data(array('data' => $data));
    }

    public function getAvailZones($cloudLocation)
    {
        $aws = $this->getEnvironment()->aws($cloudLocation);
        // Get Avail zones
        $response = $aws->ec2->availabilityZone->describe();
        $data = array();
        /* @var $zone \Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneData */
        foreach ($response as $zone) {
            $data[] = array(
                'id'    => (string)$zone->zoneName,
                'name'  => (string)$zone->zoneName,
                'state' => (string)$zone->zoneState,
            );
        }
        /*
        if ($this->getParam('roleId')) {
            $dbRole = DBRole::loadById($this->getParam('roleId'));
            $locations = $dbRole->getCloudLocations($this->getParam('platform'));
            $data['locations'] = $locations;
        }
        */

        return $data;
    }

    public function xGetAvailZonesAction()
    {
        $this->response->data(array('data' => $this->getAvailZones($this->getParam('cloudLocation'))));

    }

    public function xGetFarmRoleElasicIpsAction()
    {
        $vpcId = $this->environment->getPlatformConfigValue(Modules_Platforms_Ec2::DEFAULT_VPC_ID.".{$this->getParam('cloudLocation')}");

        $retval = $this->getFarmRoleElasticIps($this->getParam('cloudLocation'), $this->getParam('farmRoleId'), $vpcId);

        $this->response->data(array('data' => $retval));
    }

    public function xGetSnapshotsAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $response = $aws->ec2->snapshot->describe(null, null, array(array(
            'name'  => SnapshotFilterNameType::ownerId(),
            'value' => $this->getEnvironment()->getPlatformConfigValue(Modules_Platforms_Ec2::ACCOUNT_ID),
        ), array(
            'name'  => SnapshotFilterNameType::status(),
            'value' => SnapshotData::STATUS_COMPLETED,
        )));

        $data = array();
        /* @var $pv \Scalr\Service\Aws\Ec2\DataType\SnapshotData */
        foreach ($response as $pv) {
            if ($pv->status == SnapshotData::STATUS_COMPLETED) {
                $data[] = array(
                    // old format
                    'snapid'        => $pv->snapshotId,
                    'createdat'     => Scalr_Util_DateTime::convertTz($pv->startTime),
                    'size'          => $pv->volumeSize,
                    // new format
                    'snapshotId'    => $pv->snapshotId,
                    'createdDate'   => Scalr_Util_DateTime::convertTz($pv->startTime),
                    'size'          => $pv->volumeSize,
                    'volumeId'      => $pv->volumeId,
                    'description'   => (string)$pv->description,
                );
            }
        }

        $this->response->data(array('data' => $data));
    }

    public function xGetSubnetsListAction()
    {
        $this->response->data(array(
            'data' => $this->getSubnetsList($this->getParam('cloudLocation'), $this->getParam('vpcId')),
        ));
    }

    public function xGetRoutingTableListAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $tables = $aws->ec2->routeTable->describe(null, array(array(
            'name'  => RouteTableFilterNameType::vpcId(),
            'value' => $this->getParam('vpcId')
        )));
        $rows = array();
        /* @var $tableData Scalr\Service\Aws\Ec2\DataType\RouteTableData */
        foreach ($tables as $tableData) {
            $rows[] = array(
                'id'   => $tableData->routeTableId,
                'name' => $tableData->routeTableId
            );
        }

        $this->response->data(array(
            'tables' => $rows,
        ));
    }

    public function xGetVpcListAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $vpcList = $aws->ec2->vpc->describe();
        $rows = array();
        /* @var $vpcData Scalr\Service\Aws\Ec2\DataType\VpcData */
        foreach ($vpcList as $vpcData) {
            $rows[] = array(
                'id'   => $vpcData->vpcId,
                'name' => "{$vpcData->vpcId} ({$vpcData->cidrBlock}, Tenancy: {$vpcData->instanceTenancy})",
            );
        }

        $this->response->data(array(
            'vpc' => $rows,
        ));
    }

    public function xGetPlatformDataAction(){
        $cloudLocation = $this->getParam('cloudLocation');
        $farmRoleId = $this->getParam('farmRoleId');

        $vpcId = $this->environment->getPlatformConfigValue(Modules_Platforms_Ec2::DEFAULT_VPC_ID.".{$cloudLocation}");
        if ($this->getParam('vpcId') != '')
            $vpcId = $this->getParam('vpcId');

        $retval = array();
        $retval['eips'] = $this->getFarmRoleElasticIps($cloudLocation, $farmRoleId, $vpcId);
        $retval['subnets'] = $this->getSubnetsList($cloudLocation, $vpcId);
        $this->response->data(array('data' => $retval));

    }

    public function getFarmRoleElasticIps($cloudLocation, $farmRoleId, $vpcId = null) {
        $aws = $this->getEnvironment()->aws($cloudLocation);
        $map = array();

        if ($farmRoleId) {
            $dbFarmRole = DBFarmRole::LoadByID($farmRoleId);
            $this->user->getPermissions()->validate($dbFarmRole);

            $maxInstances = $dbFarmRole->GetSetting(DBFarmRole::SETTING_SCALING_MAX_INSTANCES);
            for ($i = 1; $i <= $maxInstances; $i++) {
                $map[] = array('serverIndex' => $i);
            }

            $servers = $dbFarmRole->GetServersByFilter();
            for ($i = 0; $i < count($servers); $i++) {
                if ($servers[$i]->status != SERVER_STATUS::TERMINATED && $servers[$i]->status != SERVER_STATUS::TROUBLESHOOTING && $servers[$i]->index) {
                    $map[$servers[$i]->index - 1]['serverIndex'] = $servers[$i]->index;
                    $map[$servers[$i]->index - 1]['serverId'] = $servers[$i]->serverId;
                    $map[$servers[$i]->index - 1]['remoteIp'] = $servers[$i]->remoteIp;
                    $map[$servers[$i]->index - 1]['instanceId'] = $servers[$i]->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID);
                }
            }

            $ips = $this->db->GetAll('SELECT ipaddress, instance_index FROM elastic_ips WHERE farm_roleid = ?', array($dbFarmRole->ID));
            for ($i = 0; $i < count($ips); $i++) {
                $map[$ips[$i]['instance_index'] - 1]['elasticIp'] = $ips[$i]['ipaddress'];
            }
        }

        $domain = $vpcId ? 'vpc' : 'standard';

        $response = $aws->ec2->address->describe(null, null, array(array(
            'name'  => AddressFilterNameType::domain(),
            'value' => $domain
        )));

        $ips = array();
        /* @var $ip \Scalr\Service\Aws\Ec2\DataType\AddressData */
        foreach ($response as $ip) {
            $itm = array(
                'ipAddress'  => $ip->publicIp,
                'instanceId' => $ip->instanceId,
            );

            $info = $this->db->GetRow("
                SELECT * FROM elastic_ips WHERE ipaddress = ?
            ", array($itm['ipAddress']));

            if ($info) {
                try {
                    if ($info['server_id'] && $itm['instanceId']) {
                        $dbServer = DBServer::LoadByID($info['server_id']);
                        if ($dbServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID) != $itm['instanceId']) {
                            for ($i = 0; $i < count($map); $i++) {
                                if ($map[$i]['elasticIp'] == $itm['ipAddress'])
                                    $map[$i]['warningInstanceIdDoesntMatch'] = true;
                            }
                        }
                    }

                    $farmRole = DBFarmRole::LoadByID($info['farm_roleid']);
                    $this->user->getPermissions()->validate($farmRole);

                    $itm['roleName'] = $farmRole->GetRoleObject()->name;
                    $itm['farmName'] = $farmRole->GetFarmObject()->Name;
                    $itm['serverIndex'] = $info['instance_index'];
                } catch (Exception $e) {}
            }

            //TODO: Mark Router EIP ad USED

            $ips[] = $itm;
        }

        return array('map' => $map, 'ips' => $ips);
    }

    public function getSubnetsList($cloudLocation, $vpcId)
    {
        $aws = $this->getEnvironment()->aws($cloudLocation);

        $subnets = $aws->ec2->subnet->describe(null, array(array(
            'name'  => SubnetFilterNameType::vpcId(),
            'value' => $vpcId,
        )));

        $rows = array();
        foreach($subnets as $subnet) {
            /* @var $subnet \Scalr\Service\Aws\Ec2\DataType\SubnetData */
            $item = array(
                'id'          => $subnet->subnetId,
                'description' => "{$subnet->subnetId}",
                'sidr'        => $subnet->cidrBlock,
                'availability_zone' => $subnet->availabilityZone,
                'ips_left' => $subnet->availableIpAddressCount
            );

            foreach ($subnet->tagSet as $tag) {
                if ($tag->key == 'scalr-sn-type')
                    $item['internet'] = $tag->value;
            }
            $rows[] = $item;
        }

        return $rows;
    }

}
