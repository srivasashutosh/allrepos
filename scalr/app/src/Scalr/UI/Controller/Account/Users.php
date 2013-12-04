<?php
class Scalr_UI_Controller_Account_Users extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'userId';

    public static function getApiDefinitions()
    {
        return array('xListUsers', 'xGetInfo', 'xGetApiKeys', 'xSave', 'xRemove');
    }

    protected function fillUserInfo(&$row)
    {
        $user = Scalr_Account_User::init();
        $user->loadById($row['id']);

        $row['status'] = $user->status;
        $row['email'] = $user->getEmail();
        $row['fullname'] = $user->fullname;
        $row['dtcreated'] = Scalr_Util_DateTime::convertTz($user->dtcreated);
        $row['dtlastlogin'] = $user->dtlastlogin ? Scalr_Util_DateTime::convertTz($user->dtlastlogin) : 'Never';
        $row['type'] = $user->type;
        $row['comments'] = $user->comments;

        $row['teams'] = $user->getTeams();
        $row['is2FaEnabled'] = $user->getSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL) == '1' ? true : false;
        $row['password'] = $row['password'] ? true : false;

        switch ($row['type']) {
            case Scalr_Account_User::TYPE_ACCOUNT_OWNER:
                $row['type'] = 'Account Owner';
                break;
            default:
                $row['type'] = $user->isTeamOwner() ? 'Team Owner' : 'Team User';
                break;
        }
    }

    public function getList()
    {
        $sql = '';
        $params = array();

        // account owner, team owner
        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()) {
            $sql = 'SELECT account_users.id FROM account_users WHERE account_id = ?';
            $params[] = $this->user->getAccountId();
        } else {
            // team user
            $teams = $this->user->getTeams();
            if (! count($teams))
                throw new Exception('You are not belongs to any team');

            $sql = 'SELECT account_users.id FROM account_users JOIN account_team_users ON account_users.id = account_team_users.user_id WHERE account_id= ?';
            $params[] = $this->user->getAccountId();

            foreach ($this->user->getTeams() as $team) {
                $r[] = 'account_team_users.team_id = ?';
                $params[] = $team['id'];
            }

            $sql .= ' AND (' . implode(' OR ', $r) . ')';
        }

        $usersList = $this->db->getAll($sql, $params);

        foreach ($usersList as &$row) {
            $this->fillUserInfo($row);
        }

        return $usersList;
    }

    public function xGetListAction()
    {
        $this->response->data(array('usersList' => $this->getList()));
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

    public function xListUsersAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'json')
        ));

        // account owner, team owner
        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner())
            $sql = "SELECT account_users.id, status, email, fullname, dtcreated, dtlastlogin, type, comments FROM account_users
                LEFT JOIN account_team_users ON account_users.id = account_team_users.user_id
                LEFT JOIN account_user_groups ON account_users.id = account_user_groups.user_id
                WHERE account_id='" . $this->user->getAccountId() . "'";
        else {
            // team user
            $teams = $this->user->getTeams();
            if (! count($teams))
                throw new Exception('You are not belongs to any team');

            $sql = 'SELECT account_users.id, status, email, fullname, dtcreated, dtlastlogin, type, comments FROM account_users
                JOIN account_team_users ON account_users.id = account_team_users.user_id
                LEFT JOIN account_user_groups ON account_users.id = account_user_groups.user_id
                WHERE account_id="' . $this->user->getAccountId() . '"';

            foreach ($this->user->getTeams() as $team)
                $r[] = 'account_team_users.team_id = "' . $team['id'] . '"';

            $sql .= ' AND (' . implode(' OR ', $r) . ')';
        }

        if ($this->getParam('teamId'))
            $sql .= ' AND account_team_users.team_id = ' . $this->db->qstr($this->getParam('teamId'));

        if ($this->getParam('userId'))
            $sql .= ' AND account_users.id = ' . $this->db->qstr($this->getParam('userId'));

        if ($this->getParam('groupPermissionId'))
            $sql .= ' AND account_user_groups.group_id = ' . $this->db->qstr($this->getParam('groupPermissionId'));

        $response = $this->buildResponseFromSql($sql, array('email', 'fullname'));
        foreach ($response["data"] as &$row) {
            $user = Scalr_Account_User::init();
            $user->loadById($row['id']);

            $row['dtcreated'] = Scalr_Util_DateTime::convertTz($row["dtcreated"]);
            $row['dtlastlogin'] = $row['dtlastlogin'] ? Scalr_Util_DateTime::convertTz($row["dtlastlogin"]) : 'Never';
            $row['teams'] = $user->getTeams();
            $row['is2FaEnabled'] = $user->getSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL) == '1' ? true : false;

            switch ($row['type']) {
                case Scalr_Account_User::TYPE_ACCOUNT_OWNER:
                    $row['type'] = 'Account Owner';
                    break;
                default:
                    $row['type'] = $user->isTeamOwner() ? 'Team Owner' : 'Team User';
                    break;
            }
        }
        $this->response->data($response);
    }

    public function getUser($obj = false)
    {
        $user = Scalr_Account_User::init();
        $user->loadById($this->getParam('userId'));

        if ($user->getAccountId() == $this->user->getAccountId() &&
            ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()))
        {
            if ($this->user->isTeamOwner() && $this->user->getId() != $user->getId()) {
                if ($user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $user->isTeamOwner())
                    throw new Scalr_Exception_InsufficientPermissions();
            }

            if ($obj)
                return $user;
            else
                return array(
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'fullname' => $user->fullname,
                    'status' => $user->status,
                    'comments' => $user->comments
                );
        } else
            throw new Scalr_Exception_InsufficientPermissions();
    }

    public function xGetInfoAction()
    {
        $this->response->data(array('user' => $this->getUser()));
    }

    public function xGetApiKeysAction()
    {
        $user = $this->getUser(true);

        if ($user->getSetting(Scalr_Account_User::SETTING_API_ENABLED) == 1) {
            $this->response->data(array(
                'accessKey' => $user->getSetting(Scalr_Account_User::SETTING_API_ACCESS_KEY),
                'secretKey' => $user->getSetting(Scalr_Account_User::SETTING_API_SECRET_KEY)
            ));
        } else {
            $this->response->failure('Api not enabled for this user');
        }
    }

    public function xSaveAction()
    {
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

            if (!$this->getParam('password')) {
                $password = $this->getCrypto()->sault(10);
                $sendResetLink = true;
            }
            else
                $password = $this->getParam('password');

            if ($password != '******')
                $user->updatePassword($password);

            if (in_array($this->getParam('status'), array(Scalr_Account_User::STATUS_ACTIVE, Scalr_Account_User::STATUS_INACTIVE)) &&
                $user->getType() != Scalr_Account_User::TYPE_ACCOUNT_OWNER
            )
                $user->status = $this->getParam('status');

            $user->fullname = $this->getParam('fullname');
            $user->comments = $this->getParam('comments');

            $user->save();

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

                        // Send welcome E-mail
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

                        // Send reset password E-mail
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

            $this->response->data(array('user' => array(
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'fullname' => $user->fullname
            )));
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
