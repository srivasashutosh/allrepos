<?php

class Scalr_UI_Controller_Account2_Teams extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'teamId';

    public static function getApiDefinitions()
    {
        return array('xSave', 'xRemove');
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $this->response->page('ui/account2/teams/view.js', array(
            'permissionsManage' => $this->user->getAccount()->isFeatureEnabled(Scalr_Limits::FEATURE_USERS_PERMISSIONS),
        ), array('ui/account2/dataconfig.js'), array('ui/account2/teams/view.css'), array('account.environments', 'account.users', 'account.teams', 'account.groups'));
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'envs' => array('type' => 'json'),
            'users' => array('type' => 'json'),
            'teamName', 'teamOwner'
        ));

        if (! $this->getParam('teamName'))
            throw new Exception('Team name should not be empty');

        $team = Scalr_Account_Team::init();
        if ($this->getParam('teamId')) {
            $team->loadById($this->getParam('teamId'));
            if (! ($team->accountId == $this->user->getAccountId() &&
                    ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner($team->id))
            ))
                throw new Scalr_Exception_InsufficientPermissions();
        } else {
            if ($this->user->getType() != Scalr_Account_User::TYPE_ACCOUNT_OWNER)
                throw new Scalr_Exception_InsufficientPermissions();
            $team->accountId = $this->user->getAccountId();
        }

        $this->db->BeginTrans();

        try {
            if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER) {
                $team->name = $this->getParam('teamName');
            }
            $team->save();

            if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER) {
                $team->clearUsers();
                foreach ($this->getParam('users') as $user) {
                    $team->addUser($user['id'], $user['permissions']);

                    $gr = array();
                    if ($user['permissions'] == Scalr_Account_Team::PERMISSIONS_GROUPS) {
                        if (isset($user['groups']) && is_array($user['groups'])) {
                            foreach ($user['groups'] as $value) {
                                $groupId = is_array($value)?$value['id']:$value;
                                if ($team->isTeamGroup($groupId))
                                    $gr[] = $groupId;
                            }
                        }
                    }
                    $team->clearUserGroups($user['id']);
                    $team->setUserGroups($user['id'], $gr);
                }
                if ($this->getParam('envs')) {
                    $team->clearEnvironments();
                    foreach ($this->getParam('envs') as $id)
                        $team->addEnvironment($id);
                }
            } else {
                $owner = $team->getOwner();
                $team->clearUsers();
                foreach ($this->getParam('users') as $user) {
                    if (! in_array($user['permissions'], array(
                        Scalr_Account_Team::PERMISSIONS_FULL,
                        Scalr_Account_Team::PERMISSIONS_GROUPS,
                        Scalr_Account_Team::PERMISSIONS_OWNER
                    )))
                        $user['permissions'] = Scalr_Account_Team::PERMISSIONS_GROUPS;

                    if ($user['permissions'] == Scalr_Account_Team::PERMISSIONS_OWNER && $user['id'] != $owner->id)
                        $user['permissions'] = Scalr_Account_Team::PERMISSIONS_GROUPS;

                    $team->addUser($user['id'], $user['permissions']);

                    $gr = array();
                    if ($user['permissions'] == Scalr_Account_Team::PERMISSIONS_GROUPS) {
                        foreach ($user['groups'] as $value) {
                            $groupId = is_array($value)?$value['id']:$value;
                            if ($team->isTeamGroup($groupId))
                                $gr[] = $groupId;
                        }
                    }
                    $team->clearUserGroups($user['id']);
                    $team->setUserGroups($user['id'], $gr);
                }
            }
        } catch (Exception $e) {
            $this->db->RollbackTrans();
            throw $e;
        }

        $this->db->CommitTrans();

        $users = $team->getUsers();
        foreach ($users as &$user) {
            $user = array(
                'id' => $user['id'],
                'permissions' => $user['permissions'],
                'groups' =>  $this->db->getCol('
                    SELECT account_groups.id FROM account_user_groups
                    JOIN account_groups ON account_groups.id = account_user_groups.group_id
                    WHERE account_user_groups.user_id = ? AND account_groups.team_id = ?', array(
                    $user['id'], $team->id
                ))
            );
        }

        $this->response->data(array('team' => array(
            'id' => $team->id,
            'name' => $team->name,
            'users' => $users
        )));
        $this->response->success('Team successfully saved');
    }

    public function xRemoveAction()
    {
        $team = Scalr_Account_Team::init();
        $team->loadById($this->getParam('teamId'));
        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER && $team->accountId == $this->user->getAccountId())
            $team->delete();
        else
            throw new Scalr_Exception_InsufficientPermissions();

        $this->response->success('Team successfully removed');
    }

}
