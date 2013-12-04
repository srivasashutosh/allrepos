<?php

class Scalr_UI_Controller_Account2_Groups extends Scalr_UI_Controller
{

    public static function getApiDefinitions()
    {
        return array('xSave', 'xRemove');
    }

    public function hasAccess()
    {
        if (parent::hasAccess()) {
            return $this->user->getAccount()->isFeatureEnabled(Scalr_Limits::FEATURE_USERS_PERMISSIONS) &&
                   ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()) ? true : false;
        } else
            return false;
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $this->response->page('ui/account2/groups/view.js', array(
            'permissions' => $this->getPermissions(SRCPATH . '/Scalr/UI/Controller'),
        ), array('ui/account2/dataconfig.js'), array('ui/account2/groups/view.css'), array('account.groups', 'account.teams'));
    }

    public function xRemoveAction()
    {
        $team = Scalr_Account_Team::init();
        $team->loadById($this->getParam('teamId'));
        $this->user->getPermissions()->validate($team);
        if (! ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $team->isTeamOwner($this->user->getId())))
            throw new Scalr_Exception_InsufficientPermissions();

        $group = Scalr_Account_Group::init();
        $group->loadById($this->getParam('groupId'));
        if ($group->teamId != $team->id)
            throw new Scalr_Exception_InsufficientPermissions();

        $group->delete();

        $this->response->success();
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'access' => array('type' => 'array'),
            'permission' => array('type' => 'array'),
            'controller' => array('type' => 'array')
        ));

        $team = Scalr_Account_Team::init()->loadById($this->getParam('teamId'));
        $this->user->getPermissions()->validate($team);
        if (! ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $team->isTeamOwner($this->user->getId())))
            throw new Scalr_Exception_InsufficientPermissions();

        $permissions = $this->getPermissions(SRCPATH . '/Scalr/UI/Controller');
        $rules = array();
        $access = $this->getParam('access');
        $perms = $this->getParam('permission');
        $controller = $this->getParam('controller');

        foreach ($controller as $key => $value) {
            if (array_key_exists($key, $permissions)) {
                $rules[$key] = array();

                if ($access[$key] == 'FULL')
                    $rules[$key][] = 'FULL';
                else if ($access[$key] == 'VIEW') {
                    $rules[$key][] = 'VIEW';
                    if (isset($perms[$key])) {
                        foreach ($perms[$key] as $k => $val) {
                            if (in_array($k, $permissions[$key]))
                                $rules[$key][] = $k;
                        }
                    }
                }
            }
        }
        foreach ($rules as $key => $value) {
            $rules[$key] = implode(',', $value);
        }

        $group = Scalr_Account_Group::init();
        if ($groupId = $this->getParam('id')) {
            $group->loadById($groupId);
        } else {
            $group->id = 0;
            $group->isActive = 1;
            $group->teamId = $team->id;
        }
        $group->name = $this->getParam('name');
        $group->color = $this->getParam('color');
        $group->save();
        $group->setPermissions($rules);

        $this->response->data(array('group' => array(
            'id' => $group->id,
            'name' => $group->name,
            'color' => $group->color,
            'teamId' =>  $group->teamId,
            'permissions' => $group->getPermissions()
        )));
        $this->response->success('ACL successfully saved');
    }

    /*
     * Permission Groups
     */
    public function getPermissions($path)
    {
        $result = array();
        foreach(scandir($path) as $p) {
            if ($p == '.' || $p == '..' || $p == '.svn')
                continue;

            $p1 = $path . '/' . $p;

            if (is_dir($p1)) {
                $result = array_merge($result, $this->getPermissions($p1));
                continue;
            }

            $p1 = str_replace(SRCPATH . '/', '', $p1);
            $p1 = str_replace('.php', '', $p1);
            $p1 = str_replace('/', '_', $p1);

            if (method_exists($p1, 'getPermissionDefinitions'))
                $result[str_replace('Scalr_UI_Controller_', '', $p1)] = array_values(array_unique(array_values($p1::getPermissionDefinitions())));
        }

        return $result;
    }

}
