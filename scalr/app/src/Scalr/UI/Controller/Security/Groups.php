<?php

use Scalr\Service\Aws\Ec2\DataType\SecurityGroupFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\SecurityGroupData;
use Scalr\Service\Aws\Ec2\DataType\IpPermissionList;
use Scalr\Service\Aws\Ec2\DataType\IpPermissionData;
use Scalr\Service\Aws\Ec2\DataType\IpRangeList;
use Scalr\Service\Aws\Ec2\DataType\IpRangeData;
use Scalr\Service\Aws\Ec2\DataType\UserIdGroupPairList;
use Scalr\Service\Aws\Ec2\DataType\UserIdGroupPairData;

class Scalr_UI_Controller_Security_Groups extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'securityGroupId';

    public static function getPermissionDefinitions()
    {
        return array();
    }

    /**
    * View roles listView with filters
    */
    public function viewAction()
    {
        if (!$this->getParam('platform'))
            throw new Exception ('Platform should be specified');

        $this->response->page('ui/security/groups/view.js', array(
            'locations' => self::loadController('Platforms')->getCloudLocations(array($this->getParam('platform')), false)
        ));
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'rules' => array('type' => 'json'),
            'sgRules' => array('type' => 'json')
        ));

        if ($this->getParam('farmRoleId'))
            $securityGroupName = "scalr-role.{$this->getParam('farmRoleId')}";
        else
            $securityGroupId = $this->getParam('securityGroupId');

        $info = $this->getRules($securityGroupId, $securityGroupName);
        $securityGroupName = $info['name'];
        $securityGroupId = $info['id'];

        $newRules = $this->getParam('rules');
        foreach ($newRules as $r) {
            if (!$r['id']) {
                $rule = "{$r['ipProtocol']}:{$r['fromPort']}:{$r['toPort']}:{$r['cidrIp']}";
                $id = md5($rule);
                if (!$info['rules'][$id]) {
                    $addRulesSet[] = $r;
                    if ($r['comment']) {
                        $this->db->Execute("REPLACE INTO `comments` SET `env_id` = ?, `comment` = ?, `sg_name` = ?, `rule` = ?", array(
                            $this->getEnvironmentId(), $r['comment'], $securityGroupName, $rule
                        ));
                    }
                }
            }
        }

        $sgNewRules = $this->getParam('sgRules');
        foreach ($sgNewRules as $r) {
            if (!$r['id']) {
                $rule = "{$r['ipProtocol']}:{$r['fromPort']}:{$r['toPort']}:{$r['sg']}";
                $id = md5($rule);
                if (!$info['sgRules'][$id]) {
                    $addSgRulesSet[] = $r;
                    if ($r['comment']) {
                        $this->db->Execute("REPLACE INTO `comments` SET `env_id` = ?, `comment` = ?, `sg_name` = ?, `rule` = ?", array(
                                $this->getEnvironmentId(), $r['comment'], $securityGroupName, $rule
                        ));
                    }
                }
            }
        }

        foreach ($info['rules'] as $r) {
            $found = false;
            foreach ($newRules as $nR) {
                if ($nR['id'] == $r['id'])
                    $found = true;
            }

            if (!$found)
                $remRulesSet[] = $r;
        }

        foreach ($info['sgRules'] as $r) {
            $found = false;
            foreach ($sgNewRules as $nR) {
                if ($nR['id'] == $r['id'])
                    $found = true;
            }

            if (!$found)
                $remSgRulesSet[] = $r;
        }

        if (count($addRulesSet) > 0 || count($addSgRulesSet) > 0)
            $this->updateRules(array('ip' => $addRulesSet, 'sg' => $addSgRulesSet), 'add', $securityGroupId);

        if (count($remRulesSet) > 0 || count($remSgRulesSet) > 0)
            $this->updateRules(array('ip' => $remRulesSet, 'sg' => $remSgRulesSet), 'remove', $securityGroupId);

        $this->response->success("Security group successfully saved");
    }

    public function editAction()
    {
        if ($this->getParam('farmRoleId'))
            $securityGroupName = "scalr-role.{$this->getParam('farmRoleId')}";
        else
            $securityGroupId = $this->getParam('securityGroupId');

        $this->request->setParams(array('securityGroupId' => $securityGroupId));

        $info = $this->getRules($securityGroupId, $securityGroupName);
        $securityGroupName = $info['name'];
        foreach ($info['rules'] as &$rule) {
            $rule['comment'] = $this->db->GetOne("SELECT `comment` FROM `comments` WHERE `env_id` = ? AND `rule` = ? AND `sg_name` = ?", array(
                $this->getEnvironmentId(), $rule['rule'], $securityGroupName
            ));
            if (!$rule['comment'])
                $rule['comment'] = "";
        }

        foreach ($info['sgRules'] as &$rule) {
            $rule['comment'] = $this->db->GetOne("SELECT `comment` FROM `comments` WHERE `env_id` = ? AND `rule` = ? AND `sg_name` = ?", array(
                    $this->getEnvironmentId(), $rule['rule'], $securityGroupName
            ));
            if (!$rule['comment'])
                $rule['comment'] = "";
        }

        $this->response->page('ui/security/groups/edit.js', array(
            'securityGroupId' => $securityGroupId,
            'rules'           => $info['rules'],
            'sgRules'         => $info['sgRules'],
            'accountId'       => $this->environment->getPlatformConfigValue(Modules_Platforms_Ec2::ACCOUNT_ID)
        ));
    }

    public function xRemoveAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $this->request->defineParams(array(
            'groups' => array('type' => 'json')
        ));

        $cnt = 0;
        foreach ($this->getParam('groups') as $groupId) {
            $cnt++;
            $aws->ec2->securityGroup->delete($groupId);
        }

        $this->response->success('Selected security group' . ($cnt > 1 ? 's have' : ' has') . ' been successfully removed');
    }

    public function xListGroupsAction()
    {
        if (!$this->getParam('platform'))
            throw new Exception ('Platform should be specified');

        switch ($this->getParam('platform')) {
            case SERVER_PLATFORMS::EC2:
                $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

                if (!$this->getParam('showAll')) {
                    //Show only scalr security groups
                    $fltGroupName = array('*scalr-farm.*', '*scalr-role.*');
                    if (\Scalr::config('scalr.aws.security_group_prefix')) {
                        $fltGroupName[] = '*' . \Scalr::config('scalr.aws.security_group_prefix') . '*';
                    }
                    $filter = array(array(
                        'name'  => SecurityGroupFilterNameType::groupName(),
                        'value' => $fltGroupName,
                    ));
                } else {
                    $filter = null;
                }
                $sgList = $aws->ec2->securityGroup->describe(null, null, $filter);
                $rowz = array();
                /* @var $sg SecurityGroupData */
                foreach ($sgList as $sg) {
                    $rowz[] = array(
                        'id'          => $sg->groupId,
                        'name'        => $sg->groupName,
                        'description' => $sg->groupDescription,
                        'vpcId'       => $sg->vpcId,
                        'owner'       => $sg->ownerId
                    );
                }

                break;
        }

        $response = $this->buildResponseFromData($rowz, array('id', 'name', 'description'));

        if (!empty($response['data'])) {
            $cache = array();
            foreach ($response['data'] as &$row) {
                preg_match_all('/^scalr-(role|farm)\.([0-9]+)$/si', $row['name'], $matches);
                if ($matches[1][0] == 'role') {
                    $id = $matches[2][0];
                    try {
                        $dbFarmRole = DBFarmRole::LoadByID($id);
                        $row['farm_id'] = $dbFarmRole->FarmID;
                        $row['farm_roleid'] = $dbFarmRole->ID;

                        if (!isset($cache['farms'][$dbFarmRole->FarmID])) {
                            $cache['farms'][$dbFarmRole->FarmID] = $dbFarmRole->GetFarmObject()->Name;
                        }
                        $row['farm_name'] = $cache['farms'][$dbFarmRole->FarmID];

                        if (!isset($cache['roles'][$dbFarmRole->RoleID])) {
                            $cache['roles'][$dbFarmRole->RoleID] = $dbFarmRole->GetRoleObject()->name;
                        }
                        $row['role_name'] = $cache['roles'][$dbFarmRole->RoleID];
                    } catch (Exception $e) {}
                }

                if ($matches[1][0] == 'farm') {
                    $id = $matches[2][0];

                    try {
                        $dbFarm = DBFarm::LoadByID($id);
                        $row['farm_id'] = $dbFarm->ID;

                        if (!isset($cache['farms'][$dbFarm->ID])) {
                            $cache['farms'][$dbFarm->ID] = $dbFarm->Name;
                        }
                        $row['farm_name'] = $cache['farms'][$dbFarm->ID];

                    } catch (Exception $e) {}
                }
            }
        }

        $this->response->data($response);
    }

    private function getRules($securityGroupId = null, $securityGroupName = null)
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $rules = array();
        $sgRules = array();
        switch ($this->getParam('platform')) {
            case SERVER_PLATFORMS::EC2:
                /* @var $sgInfo SecurityGroupData */
                $sgInfo = $aws->ec2->securityGroup->describe($securityGroupName, $securityGroupId)->get(0);

                /* @var $rule IpPermissionData */
                foreach ($sgInfo->ipPermissions as $rule) {
                    /* @var $ipRange IpRangeData */
                    foreach ($rule->ipRanges as $ipRange) {
                        $r = array(
                            'ipProtocol' => $rule->ipProtocol,
                            'fromPort'   => $rule->fromPort,
                            'toPort'     => $rule->toPort,
                        );
                        $r['cidrIp'] = $ipRange->cidrIp;
                        $r['rule'] = "{$r['ipProtocol']}:{$r['fromPort']}:{$r['toPort']}:{$r['cidrIp']}";
                        $r['id'] = md5($r['rule']);

                        if (!isset($rules[$r['id']])) {
                            $rules[$r['id']] = $r;
                        }
                    }
                    /* @var $group UserIdGroupPairData */
                    foreach ($rule->groups as $group) {
                        $r = array(
                            'ipProtocol' => $rule->ipProtocol,
                            'fromPort'   => $rule->fromPort,
                            'toPort'     => $rule->toPort
                        );
                        $r['sg'] =  $group->userId . '/' . $group->groupName;
                        $r['rule'] = "{$r['ipProtocol']}:{$r['fromPort']}:{$r['toPort']}:{$r['sg']}";
                        $r['id'] = md5($r['rule']);

                        if (!isset($sgRules[$r['id']])) {
                            $sgRules[$r['id']] = $r;
                        }
                    }
                }
            break;
        }

        return array(
            "id"      => $sgInfo->groupId,
            "name"    => $sgInfo->groupName,
            "rules"   => $rules,
            "sgRules" => $sgRules
        );
    }

    private function updateRules(array $rules, $method, $securityGroupId)
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        switch ($this->getParam('platform')) {
            case SERVER_PLATFORMS::EC2:
                $ipPermissionList = new IpPermissionList();
                foreach ($rules['ip'] as $rule) {
                    $ipPermissionList->append(new IpPermissionData(
                        $rule['ipProtocol'],
                        $rule['fromPort'],
                        $rule['toPort'],
                        new IpRangeList(new IpRangeData($rule['cidrIp'])),
                        null
                    ));
                }
                foreach ($rules['sg'] as $rule) {
                    $chunks = explode("/", $rule['sg']);
                    $userId = $chunks[0];
                    $name = $chunks[1];
                    $ipPermissionList->append(new IpPermissionData(
                        $rule['ipProtocol'],
                        $rule['fromPort'],
                        $rule['toPort'],
                        null,
                        new UserIdGroupPairList(new UserIdGroupPairData($userId, null, $name))
                    ));
                }
                if ($method == 'add') {
                    $aws->ec2->securityGroup->authorizeIngress($ipPermissionList, $securityGroupId);
                } else {
                    $aws->ec2->securityGroup->revokeIngress($ipPermissionList, $securityGroupId);
                }
                break;
        }
    }
}
