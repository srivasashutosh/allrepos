<?php

use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupIngressRequestData;

class Scalr_UI_Controller_Tools_Aws_Rds_Sg extends Scalr_UI_Controller
{
    /**
     * Gets AWS Client for the current environment
     *
     * @return \Scalr\Service\Aws Returns Aws client for current environment
     */
    protected function getAwsClient()
    {
        return $this->getEnvironment()->aws($this->getParam('cloudLocation'));
    }

    public function viewAction()
    {
        $this->response->page('ui/tools/aws/rds/sg/view.js', array(
            'locations'	=> self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false)
        ));
    }

    public function xListAction()
    {
        $sGroups = $this->getAwsClient()->rds->dbSecurityGroup->describe()->toArray(true);
        $response = $this->buildResponseFromData($sGroups, array('DBSecurityGroupDescription', 'DBSecurityGroupName'));
        $this->response->data($response);
    }

    public function xCreateAction()
    {
        $this->getAwsClient()->rds->dbSecurityGroup->create(
            $this->getParam('dbSecurityGroupName'), $this->getParam('dbSecurityGroupDescription')
        );
        $this->response->success("DB security group successfully created");
    }

    public function xDeleteAction()
    {
        $this->getAwsClient()->rds->dbSecurityGroup->delete($this->getParam('dbSgName'));
        $this->response->success("DB security group successfully removed");
    }

    public function editAction()
    {
        $aws = $this->getAwsClient();

        /* @var $group \Scalr\Service\Aws\Rds\DataType\DBSecurityGroupData */
        $group = $aws->rds->dbSecurityGroup->describe($this->getParam('dbSgName'))->get(0);

        $ipRules = array();
        $groupRules = array();
        if (!empty($group->iPRanges) && count($group->iPRanges)) {
            $ipRules = $group->iPRanges->toArray(true);
        }
        if (!empty($group->eC2SecurityGroups) && count($group->eC2SecurityGroups)) {
            $groupRules = $group->eC2SecurityGroups->toArray(true);
        }

        $rules = array('ipRules' => $ipRules, 'groupRules' => $groupRules);

        $this->response->page('ui/tools/aws/rds/sg/edit.js', array('rules' => $rules));
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'rules' => array('type' => 'json')
        ));

        $aws = $this->getAwsClient();

        /* @var $group \Scalr\Service\Aws\Rds\DataType\DBSecurityGroupData */
        $group = $aws->rds->dbSecurityGroup->describe($this->getParam('dbSgName'))->get(0);

        $rules = array();

        if (!empty($group->iPRanges)) {
            foreach ($group->iPRanges as $r) {
                $r = $r->toArray(true);
                $r['id'] = md5($r['CIDRIP']);
                $rules[$r['id']] = $r;
            }
        }
        if (!empty($group->eC2SecurityGroups)) {
            foreach ($group->eC2SecurityGroups as $r) {
                $r = $r->toArray(true);
                $r['id'] = md5($r['EC2SecurityGroupName'] . $r['EC2SecurityGroupOwnerId']);
                $rules[$r['id']] = $r;
            }
        }

        foreach ($rules as $id => $r) {
            $found = false;
            foreach ($this->getParam('rules') as $rule) {
                if ($rule['Type'] == 'CIDR IP')
                    $rid = md5($rule['CIDRIP']);
                else
                    $rid = md5($rule['EC2SecurityGroupName'] . $rule['EC2SecurityGroupOwnerId']);

                if ($id == $rid) {
                    $found = true;
                }
            }

            if (!$found) {
                $request = new DBSecurityGroupIngressRequestData($this->getParam('dbSgName'));
                if ($r['CIDRIP']) {
                    $request->cIDRIP = $r['CIDRIP'];
                } else {
                    $request->eC2SecurityGroupName = $r['EC2SecurityGroupName'];
                    $request->eC2SecurityGroupOwnerId = $r['EC2SecurityGroupOwnerId'];
                }
                $aws->rds->dbSecurityGroup->revokeIngress($request);
                unset($request);
            }
        }

        foreach ($this->getParam('rules') as $rule){
            if ($rule['Status'] == 'new') {
                $request = new DBSecurityGroupIngressRequestData($this->getParam('dbSgName'));
                if ($rule['Type'] == 'CIDR IP') {
                    $request->cIDRIP = $rule['CIDRIP'];
                } else {
                    $request->eC2SecurityGroupName = $r['EC2SecurityGroupName'];
                    $request->eC2SecurityGroupOwnerId = $r['EC2SecurityGroupOwnerId'];
                }
                $aws->rds->dbSecurityGroup->authorizeIngress($request);
                unset($request);
            }
        }
        $this->response->success("DB security group successfully updated");
    }
}