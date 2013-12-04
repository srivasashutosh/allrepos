<?php
class Scalr_UI_Controller_Account2_Users extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'userId';

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
        $this->response->page('ui/account2/users/view.js', array(
            'permissionsManage' => $this->user->getAccount()->isFeatureEnabled(Scalr_Limits::FEATURE_USERS_PERMISSIONS),
        ), array('ui/account2/dataconfig.js'), array('ui/account2/users/view.css'), array('account.users', 'account.teams', 'account.groups'));
    }

    public function xGroupActionHandlerAction()
    {
        $this->request->defineParams(array(
            'ids' => array('type' => 'json'), 'action'
        ));

        if (! ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()))
            throw new Scalr_Exception_InsufficientPermissions(); // check permissions

        $processed = array();
        $errors = array();
        foreach($this->getParam('ids') as $userId) {
            try {
                $user = Scalr_Account_User::init();
                $user->loadById($userId);

                if ($user->getAccountId() != $this->user->getAccountId())
                    continue; // check security

                switch($this->getParam('action')) {
                    case 'delete':
                        if ($user->getType() == Scalr_Account_User::TYPE_TEAM_USER && !$user->isTeamOwner()) {
                            // could delete only simple user, not team owner
                            $user->delete();
                            $processed[] = $user->getId();
                        } else {
                            throw new Scalr_Exception_Core('You couldn\'t delete team owner or account owner');
                        }
                        break;

                    case 'activate':
                        // account owner could do everything (except himself), team owner - only simple users, not team owners
                        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER && $user->getType() != Scalr_Account_User::TYPE_ACCOUNT_OWNER ||
                            $this->user->isTeamOwner() && $user->getType() == Scalr_Account_User::TYPE_TEAM_USER && !$user->isTeamOwner()
                        ) {
                            $user->status = Scalr_Account_User::STATUS_ACTIVE;
                            $user->save();
                            $processed[] = $user->getId();
                        } else {
                            throw new Scalr_Exception_Core('You couldn\'t change status of team owner or account owner');
                        }
                        break;

                    case 'deactivate':
                        // account owner could do everything (except himself), team owner - only simple users, not team owners
                        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER && $user->getType() != Scalr_Account_User::TYPE_ACCOUNT_OWNER ||
                            $this->user->isTeamOwner() && $user->getType() == Scalr_Account_User::TYPE_TEAM_USER && !$user->isTeamOwner()
                        ) {
                            $user->status = Scalr_Account_User::STATUS_INACTIVE;
                            $user->save();
                            $processed[] = $user->getId();
                        } else {
                            throw new Scalr_Exception_Core('You couldn\'t change status of team owner or account owner');
                        }
                        break;
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        $num = count($this->getParam('ids'));

        if (count($processed) == $num) {
            $this->response->success('All users processed');
        } else {
            array_walk($errors, function(&$item) { $item = '- ' . $item; });
            $this->response->warning(sprintf('Successfully processed only %d from %d users. <br>Such errors have occured:<br>%s', count($processed), $num, join($errors, '')));
        }

        $this->response->data(array('processed' => $processed));
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'teams' => array('type' => 'json'), 'action'
        ));

        $user = Scalr_Account_User::init();

        if (! $this->getParam('email'))
            throw new Scalr_Exception_Core('Email cannot be null');

        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()) {
            if ($this->getParam('id')) {
                $user->loadById($this->getParam('id'));

                if ($user->getAccountId() == $this->user->getAccountId()) {
                    if ($this->user->isTeamOwner() && $this->user->getId() != $user->getId()) {
                        if ($user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $user->isTeamOwner())
                            throw new Scalr_Exception_InsufficientPermissions();
                    }
                } else
                    throw new Scalr_Exception_InsufficientPermissions();

                $user->updateEmail($this->getParam('email'));
            } else {
                $this->user->getAccount()->validateLimit(Scalr_Limits::ACCOUNT_USERS, 1);
                $user->create($this->getParam('email'), $this->user->getAccountId());
                $user->type = Scalr_Account_User::TYPE_TEAM_USER;
                $newUser = true;
            }

            $password = $this->getParam('password');
            if ($password === '' || $newUser && !$password) {
                $password = $this->getCrypto()->sault(10);
                $sendResetLink = true;
            }
            if ($password) {
                $user->updatePassword($password);
            }

            if (in_array($this->getParam('status'), array(Scalr_Account_User::STATUS_ACTIVE, Scalr_Account_User::STATUS_INACTIVE)) &&
                $user->getType() != Scalr_Account_User::TYPE_ACCOUNT_OWNER
            )
                $user->status = $this->getParam('status');

            $user->fullname = $this->getParam('fullname');
            $user->comments = $this->getParam('comments');

            $user->save();

            $this->db->BeginTrans();
            try {
                $teams = $this->getParam('teams');
                if ($teams) {
                    foreach ($teams as $teamId => $teamData) {
                        if (($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner($teamId)) && !$user->isTeamOwner($teamId)) {
                            $team = Scalr_Account_Team::init()->loadById($teamId);
                            $team->removeUser($user->id);
                            if (in_array($teamData['permissions'], array(Scalr_Account_Team::PERMISSIONS_GROUPS, Scalr_Account_Team::PERMISSIONS_FULL))) {
                                $team->addUser($user->id, $teamData['permissions']);
                                if ($teamData['permissions'] == Scalr_Account_Team::PERMISSIONS_GROUPS) {
                                    $gr = array();
                                    foreach ($teamData['groups'] as $groupId) {
                                        if ($team->isTeamGroup($groupId)) {
                                            $gr[] = $groupId;
                                        }
                                    }
                                    $team->setUserGroups($user->id, $gr);
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $this->db->RollbackTrans();
                throw $e;
            }

            $this->db->CommitTrans();

            if ($this->getParam('enableApi')) {
                $keys = Scalr::GenerateAPIKeys();
                $user->setSetting(Scalr_Account_User::SETTING_API_ENABLED, true);
                $user->setSetting(Scalr_Account_User::SETTING_API_ACCESS_KEY, $keys['id']);
                $user->setSetting(Scalr_Account_User::SETTING_API_SECRET_KEY, $keys['key']);
            }

            if ($newUser) {

                if ($user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER)
                {
                    try {
                        $clientinfo = array(
                            'fullname'	=> $user->fullname,
                            'firstname' => $user->fullname,
                            'email'		=> $user->getEmail(),
                            'password'	=> $this->getParam('password')
                        );

                        $res = $this->getContainer()->mailer->sendTemplate(
                            SCALR_TEMPLATES_PATH . '/emails/welcome.eml',
                            array(
                                "{{client_firstname}}" => $clientinfo['firstname'],
                                "{{password}}"         => $clientinfo['password'],
                                "{{site_url}}"         => "http://{$_SERVER['HTTP_HOST']}"
                            ),
                            $user->getEmail()
                        );
                    } catch (Exception $e) {
                    }
                } elseif ($sendResetLink) {
                    try {
                        $hash = $this->getCrypto()->sault(10);

                        $user->setSetting(Scalr_Account::SETTING_OWNER_PWD_RESET_HASH, $hash);

                        $clientinfo = array(
                            'email' => $user->getEmail(),
                            'fullname'	=> $user->fullname
                        );

                        $res = $this->getContainer()->mailer->sendTemplate(
                            SCALR_TEMPLATES_PATH . '/emails/user_account_confirm.eml',
                            array(
                                "{{fullname}}" => $clientinfo['fullname'],
                                "{{pwd_link}}" => "https://{$_SERVER['HTTP_HOST']}/#/guest/updatePassword/?hash={$hash}"
                            ),
                            $clientinfo['email'],
                            $clientinfo['fullname']
                        );
                    } catch (Exception $e) {
                    }
                }
            }

            $teams = $this->db->getAll('
                SELECT account_teams.id, permissions FROM account_teams JOIN account_team_users
                ON account_teams.id = account_team_users.team_id WHERE account_team_users.user_id = ?
            ', array($user->id));

            $userTeams = array();
            foreach ($teams as $team) {
                $userTeams[$team['id']] = array('groups' => array(), 'permissions' => $team['permissions']);
                foreach (Scalr_Account_Team::init()->loadById($team['id'])->getUserGroups($user->id) as $group) {
                    $userTeams[$team['id']]['groups'][] = $group['id'];
                }
            }

            $this->response->data(array('user' => $user->getUserInfo(), 'teams' => $userTeams));
            $this->response->success('User successfully saved');
        } else
            throw new Scalr_Exception_InsufficientPermissions();
    }

    public function xRemoveAction()
    {
        $user = Scalr_Account_User::init();
        $user->loadById($this->getParam('userId'));

        if ($user->getAccountId() == $this->user->getAccountId() &&
            $user->getType() == Scalr_Account_User::TYPE_TEAM_USER &&
            !$user->isTeamOwner())
        {
            if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()) {
                $user->delete();
                $this->response->success('User successfully removed');
                return;
            }
        }

        throw new Scalr_Exception_InsufficientPermissions();
    }
}
