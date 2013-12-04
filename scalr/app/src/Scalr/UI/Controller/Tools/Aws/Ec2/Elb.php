<?php

use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\ListenerList;
use Scalr\Service\Aws\Elb\DataType\HealthCheckData;
use Scalr\Farm\Role\FarmRoleService;

class Scalr_UI_Controller_Tools_Aws_Ec2_Elb extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'elbName';

    public static function getPermissionDefinitions()
    {
        return array();
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function xDeleteAction()
    {
        $roleid = $this->db->GetOne("SELECT farm_roleid FROM farm_role_settings WHERE name=? AND value=?",
        array(
            DBFarmRole::SETTING_BALANCING_NAME,
            $this->getParam('elbName')
        ));

        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;
        $elb->loadBalancer->delete($this->getParam('elbName'));

        if ($roleid) {
            $DBFarmRole = DBFarmRole::LoadByID($roleid);
            $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_USE_ELB, 0);
            $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_HOSTNAME, "");
            $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_NAME, "");
        }
        $this->response->success("Selected Elastic Load Balancers successfully removed");
    }

    public function viewAction()
    {
        $this->response->page('ui/tools/aws/ec2/elb/view.js', array(
            'locations'	=> self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false)
        ));
    }

    public function createAction()
    {
        $this->response->page('ui/tools/aws/ec2/elb/create.js', array(
            'zones' => self::loadController('Ec2', 'Scalr_UI_Controller_Platforms')->getAvailZones($this->getParam('cloudLocation'))
        ));
    }

    public function xCreateAction()
    {
        $this->request->defineParams(array(
            'listeners' => array('type' => 'json'),
            'healthcheck' => array('type' => 'json')
        ));

        $healthCheck = $this->getParam('healthcheck');

        $elb = $this->environment->aws($this->getParam('cloudLocation'))->elb;

        //prepare listeners
        $listenersList = new ListenerList();
        $li = 0;
        foreach ($this->getParam('listeners') as $listener) {
            $listener_chunks = explode("#", $listener);
            $listenersList->append(new ListenerData(
                trim($listener_chunks[1]), trim($listener_chunks[2]),
                trim($listener_chunks[0]), null,
                trim($listener_chunks[3])
            ));
        }

        $availZones = array();
        foreach ($this->getParam('zones') as $zone => $enabled) {
            if ($enabled == 'on') {
                array_push($availZones, $zone);
            }
        }

        $elb_name = sprintf("scalr-%s-%s", Scalr_Util_CryptoTool::sault(10), rand(100,999));

        $healthCheckType = new HealthCheckData();
        $healthCheckType->target = $healthCheck['target'];
        $healthCheckType->healthyThreshold = $healthCheck['healthyThreshold'];
        $healthCheckType->interval = $healthCheck['interval'];
        $healthCheckType->timeout = $healthCheck['timeout'];
        $healthCheckType->unhealthyThreshold = $healthCheck['unhealthyThreshold'];

        //Creates a new ELB
        $dnsName = $elb->loadBalancer->create($elb_name, $listenersList, $availZones);

        try {
            $elb->loadBalancer->configureHealthCheck($elb_name, $healthCheckType);
        } catch (Exception $e) {
            $elb->loadBalancer->delete($elb_name);
            throw $e;
        }

        // return all as in xListElb
        $this->response->data(array(
            'elb' => array(
                'name'     => $elb_name,
                'hostname' => $dnsName,
            )
        ));
    }

    public function xDeleteListenersAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;
        $elb->loadBalancer->deleteListeners($this->getParam('elbName'), $this->getParam('lbPort'));
        $this->response->success('Listener successfully removed from load balancer');
    }

    public function xCreateListenersAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;
        $elb->loadBalancer->createListeners($this->getParam('elbName'), new ListenerData(
            $this->getParam('lbPort'), $this->getParam('instancePort'),
            $this->getParam('protocol'), null, $this->getParam('certificateId')
        ));

        $this->response->success(_("New listener successfully created on load balancer"));
    }

    public function xDeregisterInstanceAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;
        $elb->loadBalancer->deregisterInstances($this->getParam('elbName'), $this->getParam('awsInstanceId'));
        $this->response->success(_("Instance successfully deregistered from the load balancer"));
    }

    public function instanceHealthAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;
        $info = $elb->loadBalancer->describeInstanceHealth($this->getParam('elbName'), $this->getParam('awsInstanceId'))->get(0);
        $this->response->page('ui/tools/aws/ec2/elb/instanceHealth.js', $info->toArray());
    }

    public function xDeleteSpAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;
        $elb->loadBalancer->deletePolicy($this->getParam('elbName'), $this->getParam('policyName'));
        $this->response->success(_("Stickiness policy successfully removed"));
    }

    public function xCreateSpAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;

        if ($this->getParam('policyType') == 'AppCookie') {
            $elb->loadBalancer->createAppCookieStickinessPolicy(
                $this->getParam('elbName'), $this->getParam('policyName'), $this->getParam('cookieSettings')
            );
        } else {
            $elb->loadBalancer->createLbCookieStickinessPolicy(
                $this->getParam('elbName'), $this->getParam('policyName'), $this->getParam('cookieSettings')
            );
        }
        $this->response->success(_("Stickiness policy successfully created"));
    }

    public function xAssociateSpAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;
        $policyName = $this->getParam('policyName');
        $elb->loadBalancer->setPoliciesOfListener(
            $this->getParam('elbName'), $this->getParam('elbPort'), empty($policyName) ? null : $policyName
        );
        $this->response->success(_("Stickiness policies successfully associated with listener"));
    }

    public function detailsAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;
        $lb = $elb->loadBalancer->describe($this->getParam('elbName'))->get(0);

        $arrLb = $lb->toArray();
        $policies = array();
        if (!empty($arrLb['policies']['appCookieStickinessPolicies'])) {
            foreach ($arrLb['policies']['appCookieStickinessPolicies'] as $member) {
                $member['policyType'] = 'AppCookie';
                $member['cookieSettings'] = $member['cookieName'];
                unset($member['cookieName']);
                $policies[] = $member;
            }
        }
        if (!empty($arrLb['policies']['lbCookieStickinessPolicies'])) {
            foreach ($arrLb['policies']['lbCookieStickinessPolicies'] as $member) {
                $member['policyType'] = 'LbCookie';
                $member['cookieSettings'] = $member['cookieExpirationPeriod'];
                unset($member['cookieExpirationPeriod']);
                $policies[] = $member;
            }
        }

        $arrLb['policies'] = $policies;

        $this->response->page('ui/tools/aws/ec2/elb/details.js', array('elb' => $arrLb));
    }

    public function xListElasticLoadBalancersAction()
    {
        $elb = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->elb;

        $rowz1 = array();
        /* @var $lb LoadBalancerDescriptionData */
        foreach ($elb->loadBalancer->describe() as $lb) {
            if (!$lb->dnsName) continue;

            $roleid = $this->db->GetOne("SELECT farm_roleid FROM farm_role_settings WHERE name=? AND value=?",
                array(DBFarmRole::SETTING_BALANCING_HOSTNAME, $lb->dnsName)
            );

            $farmId = false;
            $farmRoleId = false;
            $farmName = false;
            $roleName = false;

            if ($roleid) {
                try {
                    $DBFarmRole = DBFarmRole::LoadByID($roleid);
                    $farmId = $DBFarmRole->FarmID;
                    $farmRoleId = $roleid;
                    $farmName = $DBFarmRole->GetFarmObject()->Name;
                    $roleName = $DBFarmRole->GetRoleObject()->name;
                } catch (Exception $e) {}
            }

            try {
                $farmRoleService = FarmRoleService::findFarmRoleService($this->environment->id, $lb->loadBalancerName);
                if ($farmRoleService) {
                    $farmRoleId = $farmRoleService->getFarmRole()->ID;
                    $farmId = $farmRoleService->getFarmRole()->FarmID;
                    $roleName = $farmRoleService->getFarmRole()->GetRoleObject()->name;
                    $farmName = $farmRoleService->getFarmRole()->GetFarmObject()->Name;
                }
            } catch (Exception $e) {}

            $rowz1[] = array(
                "name"		 => $lb->loadBalancerName,
                "dtcreated"	 => $lb->createdTime->format('c'),
                "dnsName"	 => $lb->dnsName,
                "farmId"	 => $farmId,
                "farmRoleId" => $farmRoleId,
                "farmName"	 => $farmName,
                "roleName"	 => $roleName
            );
        }

        $response = $this->buildResponseFromData($rowz1, array('name', 'dnsname', 'farmName', 'roleName'));
        foreach($response['data'] as &$row) {
            $row['dtcreated'] = Scalr_Util_DateTime::convertTz($row['dtcreated']);
        }

        $this->response->data($response);
    }
}
