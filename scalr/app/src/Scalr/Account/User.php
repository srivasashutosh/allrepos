<?php

class Scalr_Account_User extends Scalr_Model
{
    protected $dbTableName = 'account_users';
    protected $dbPrimaryKey = "id";
    protected $dbMessageKeyNotFound = "User #%s not found in database";

    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'Inactive';

    const TYPE_SCALR_ADMIN = 'ScalrAdmin';
    const TYPE_ACCOUNT_OWNER = 'AccountOwner';
    const TYPE_TEAM_USER = 'TeamUser';

    const SETTING_API_ACCESS_KEY 	= 'api.access_key';
    const SETTING_API_SECRET_KEY 	= 'api.secret_key';
    const SETTING_API_ENABLED 		= 'api.enabled';
    const SETTING_API_IP_WHITELIST 	= 'api.ip.whitelist';

    const SETTING_RSS_LOGIN 	= 'rss.login';
    const SETTING_RSS_PASSWORD 	= 'rss.password';

    const SETTING_UI_ENVIRONMENT = 'ui.environment'; // last used
    const SETTING_UI_TIMEZONE = 'ui.timezone';

    const SETTING_GRAVATAR_EMAIL = 'gravatar.email';

    const SETTING_SECURITY_IP_WHITELIST 	= 'security.ip.whitelist';
    const SETTING_SECURITY_2FA_GGL = 'security.2fa.ggl';
    const SETTING_SECURITY_2FA_GGL_KEY = 'security.2fa.ggl.key';

    const VAR_UI_STORAGE = 'ui.storage';

    protected $dbPropertyMap = array(
        'id'			=> 'id',
        'account_id'	=> 'accountId',
        'status'		=> 'status',
        'email'			=> array('property' => 'email', 'is_filter' => true),
        'fullname'		=> 'fullname',
        'password' 		=> array('property' => 'password'),
        'type'			=> 'type',
        'dtcreated'		=> array('property' => 'dtCreated', 'createSql' => 'NOW()', 'type' => 'datetime', 'update' => false),
        'dtlastlogin'	=> array('property' => 'dtLastLogin', 'type' => 'datetime'),
        'comments'		=> 'comments',
        'loginattempts' => 'loginattempts',
    );

    public
        $status,
        $fullname,
        $dtcreated,
        $dtlastlogin,
        $type,
        $comments,
        $loginattempts;

    protected
        $email,
        $password,
        $accountId;


    protected $account;
    protected $permissions;

    /**
     *
     * @return Scalr_Account_User
     */
    public static function init($className = null)
    {
        return parent::init();
    }

    /**
     *
     * @return Scalr_Account
     */
    public function loadBySetting($name, $value)
    {
        $id = $this->db->GetOne("SELECT user_id FROM account_user_settings WHERE name = ? AND value = ?",
            array($name, $value)
        );
        if (!$id)
            return false;
        else
            return $this->loadById($id);
    }

    public function loadByApiAccessKey($accessKey)
    {
        return $this->loadBySetting(Scalr_Account_User::SETTING_API_ACCESS_KEY, $accessKey);
    }

    /**
     *
     * @return Scalr_Account_User
     */
    public function loadByEmail($email, $accountId = null)
    {
        if ($accountId)
            $info = $this->db->GetRow("SELECT * FROM account_users WHERE `email` = ? AND account_id = ?",
                array($email, $accountId)
            );
        else
            $info = $this->db->GetRow("SELECT * FROM account_users WHERE `email` = ?",
                array($email)
            );

        if (!$info)
            return false;
        else
            return $this->loadBy($info);
    }

    /**
     *
     * @return Scalr_Permissions
     */
    public function getPermissions()
    {
        if (! $this->permissions)
            $this->permissions = new Scalr_Permissions($this);

        return $this->permissions;
    }

    public function create($email, $accountId)
    {
        $this->id = 0;
        $this->accountId = $accountId;

        if ($this->isEmailExists($email))
            throw new Exception('Uh oh. Seems like that email is already in use. Try another?');

        $this->email = $email;

        $this->save();
        $this->setSetting(Scalr_Account_User::SETTING_GRAVATAR_EMAIL, $email);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see Scalr_Model::delete()
     */
    public function delete($id = null)
    {
        if ($this->type == Scalr_Account_User::TYPE_ACCOUNT_OWNER)
            throw new Exception('You cannot remove Account Owner');

        parent::delete();

        $this->db->Execute('DELETE FROM `account_team_users` WHERE user_id = ?', array($this->id));
        $this->db->Execute('DELETE FROM `account_user_groups` WHERE user_id = ?', array($this->id));
        $this->db->Execute('DELETE FROM `account_user_settings` WHERE user_id = ?', array($this->id));
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return Scalr_Account
     */
    public function getAccount()
    {
        if (!$this->account)
            $this->account = Scalr_Account::init()->loadById($this->accountId);

        return $this->account;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getGravatarHash()
    {
        return md5(strtolower(trim($this->getSetting(Scalr_Account_User::SETTING_GRAVATAR_EMAIL))));
    }

    public function updateEmail($email)
    {
        if ($email && ($email == $this->email || !$this->isEmailExists($email)))
            $this->email = $email;
        else
            throw new Exception('Uh oh. Seems like that email is already in use. Try another?');
    }

    /**
     * Returns user setting value by name
     *
     * @param string $name
     * @return mixed $value
     */
    public function getSetting($name)
    {
        $r = $this->db->GetOne("SELECT value FROM account_user_settings WHERE user_id=? AND `name`=?",
            array($this->id, $name)
        );

        return $r == 'false' ? '' : $r;
    }

    /**
     * Set user setting
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setSetting($name, $value)
    {
        $this->db->Execute("REPLACE INTO account_user_settings SET `name`=?, `value`=?, user_id=?",
            array($name, $value, $this->id)
        );
    }

    /**
     * Returns user var value by name
     *
     * @param string $name
     * @return mixed $value
     */
    public function getVar($name)
    {
        $r = $this->db->GetOne("SELECT value FROM account_user_vars WHERE user_id=? AND `name`=?",
            array($this->id, $name)
        );

        return $r == 'false' ? '' : $r;
    }

    /**
     * Set user var
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setVar($name, $value)
    {
        $this->db->Execute("REPLACE INTO account_user_vars SET `name`=?, `value`=?, user_id=?",
            array($name, $value, $this->id)
        );
    }

    /**
     * Get user dashboard
     * @param $envId
     * @return array
     */
    public function getDashboard($envId)
    {
        $obj = unserialize($this->db->GetOne("SELECT value FROM account_user_dashboard WHERE `user_id` = ? AND `env_id` = ?",
            array($this->id, $envId)
        ));

        if (! is_array($obj)) {
            $obj = array('configuration' => array(), 'flags' => array(), 'widgets' => array());
            $this->setDashboard($envId, $obj);
            $obj = $this->getDashboard($envId);
        }

        return $obj;
    }

    /**
     * Set user dashboard
     * @param integer $envId
     * @param array $value
     * @throws Scalr_Exception_Core
     */
    public function setDashboard($envId, $value)
    {
        // check consistency
        $usedWidgets = array();
        if (is_array($value) &&
            isset($value['configuration']) && is_array($value['configuration']) &&
            isset($value['flags']) && is_array($value['flags'])
        ) {
            $configuration = array();
            foreach ($value['configuration'] as $col) {
                if (is_array($col)) {
                    $column = array();
                    foreach ($col as $wid) {
                        if (is_array($wid) && isset($wid['name'])) {
                            $usedWidgets[] = $wid['name'];
                            array_push($column, $wid);
                        }
                    }
                    array_push($configuration, $column);
                }
            }

            $value['configuration'] = $configuration;
            $value['widgets'] = array_unique($usedWidgets);
        } else {
            throw new Scalr_Exception_Core('Invalid configuration for dashboard');
        }

        $this->db->Execute("REPLACE INTO account_user_dashboard SET `value` = ?, `user_id` = ?, `env_id` = ?",
            array(serialize($value), $this->id, $envId)
        );
    }

    /**
     * Add widget to dashboard
     * @param int $envId
     * @param array $widgetConfig
     * @param int $columnNumber
     * @param int $position
     */
    public function addDashboardWidget($envId, $widgetConfig, $columnNumber = 0, $position = 0)
    {
        $dashboard = $this->getDashboard($envId);

        // we could use maximum only last column, do not create new one
        $columnNumber = $columnNumber >= count($dashboard['configuration']) ? count($dashboard['configuration']) - 1 : $columnNumber;
        $column = $dashboard['configuration'][$columnNumber];
        $position = $position > count($column) ? count($column) : $position;
        $column = array_merge(array_slice($column, 0, $position), array($widgetConfig), array_slice($column, $position));
        $dashboard['configuration'][$columnNumber] = $column;

        $this->setDashboard($envId, $dashboard);
    }

    public function updatePassword($pwd)
    {
        $this->password = $this->getCrypto()->hash(trim($pwd));
    }

    /**
     * @param $pwd
     * @return bool
     */
    public function checkPassword($pwd)
    {
        if ($this->password != $this->getCrypto()->hash($pwd)) {
            $this->updateLoginAttempt(1);
            return false;
        }
        else {
            $this->updateLoginAttempt();
            return true;
        }
    }

    public function updateLoginAttempt($loginattempt = NULL)
    {
        if($loginattempt)
            $this->db->Execute('UPDATE `account_users` SET loginattempts = loginattempts + ? WHERE id = ?', array($loginattempt, $this->id));
        else
            $this->db->Execute('UPDATE `account_users` SET loginattempts = 0 WHERE id = ?', array($this->id));
    }

    public function updateLastLogin()
    {
        $this->db->Execute('UPDATE `account_users` SET dtlastlogin = NOW() WHERE id = ?', array($this->id));
    }

    public function isEmailExists($email)
    {
        return $this->db->getOne('SELECT * FROM `account_users` WHERE email = ? AND account_id = ?', array($email, $this->accountId)) ? true : false;
    }

    public function getTeams()
    {
        return $this->db->getAll('SELECT account_teams.id, account_teams.name FROM account_teams JOIN account_team_users
            ON account_teams.id = account_team_users.team_id WHERE account_team_users.user_id = ?', array($this->id));
    }

    public function getLdapTeams()
    {
        $session = Scalr_Session::getInstance();
        if ($session->getUserId() != $this->id)
            throw new Exception('Illegal use. You can\'t get ldap teams for this user.');

        $teams = array();
        foreach($session->getLdapGroups() as $name) {
            $teamId = $this->db->GetOne('SELECT id FROM account_teams WHERE name = ? AND account_id = ?', array($name, $this->accountId));
            if ($teamId)
                $teams[] = $teamId;
        }

        return $teams;
    }

    public function getEnvironments()
    {
        if ($this->type == self::TYPE_ACCOUNT_OWNER) {
            return $this->db->getAll('SELECT id, name FROM client_environments WHERE client_id = ?', array(
                $this->getAccountId()
            ));
        } else {
            if ($this->getContainer()->config->get('scalr.auth_mode') == 'ldap') {
                $teams = $this->getLdapTeams();
            } else {
                $teams = array();
                foreach ($this->getTeams() as $team)
                    $teams[] = $team['id'];
            }

            if (count($teams))
                return $this->db->getAll('SELECT client_environments.id, client_environments.name FROM client_environments
                    JOIN account_team_envs ON client_environments.id = account_team_envs.env_id WHERE team_id IN (' . implode(',', $teams) . ')
                    GROUP BY client_environments.id
                ');
        }

        return array();
    }

    /**
     * Get default environment (or given) and check access to it
     * @param integer $envId
     * @return Scalr_Environment
     * @throws Scalr_Exception_Core
     */
    public function getDefaultEnvironment($envId = 0)
    {
        try {
            if ($envId) {
                $environment = Scalr_Environment::init()->loadById($envId);

                if (! $this->getPermissions()->check($environment)) {
                    $envId = 0;
                }

            } else {
                $envId = (int) $this->getSetting(Scalr_Account_User::SETTING_UI_ENVIRONMENT);

                if ($envId) {
                    $environment = Scalr_Environment::init()->loadById($envId);
                    if (! $this->getPermissions()->check($environment)) {
                        $envId = 0;
                    }
                }
            }
        } catch (Exception $e) {
            $envId = 0;
        }

        if (! $envId) {
            $envs = $this->getEnvironments();

            if (count($envs)) {
                $environment = Scalr_Environment::init()->loadById($envs[0]['id']);
            } else
                throw new Scalr_Exception_Core('You don\'t have access to any environment.');
        }

        $this->getPermissions()->validate($environment);
        return $environment;
    }

    public function getGroupPermissions($envId)
    {
        $result = array();
        $permissions = $this->db->getAll(
            'SELECT controller, permissions FROM account_group_permissions
            JOIN account_groups ON account_group_permissions.group_id = account_groups.id WHERE account_groups.is_active = 1 AND team_id IN(
                SELECT account_team_users.team_id FROM account_team_users JOIN account_team_envs
                ON account_team_users.team_id = account_team_envs.team_id WHERE user_id = ? AND env_id = ?
            ) AND account_groups.id IN(
                SELECT group_id FROM account_user_groups WHERE user_id = ?
            )',
            array($this->id, $envId, $this->id)
        );

        foreach ($permissions as $perm) {
            $c = $perm['controller'];
            if (isset($result[$c])) {
                if (in_array('FULL', $result[$c]))
                    continue;
                elseif ($perm['permissions'] == 'FULL')
                    $result[$c] = array('FULL');
                else
                    $result[$c] = array_unique(array_merge($result[$c], explode(',', $perm['permissions'])));

            } else
                $result[$c] = explode(',', $perm['permissions']);
        }

        return $result;
    }

    public function isTeamOwner($teamId = null)
    {
        if ($teamId)
            return $this->db->getOne('SELECT permissions FROM `account_team_users` WHERE user_id = ? AND team_id = ? AND permissions = "owner"', array($this->id, $teamId)) == Scalr_Account_Team::PERMISSIONS_OWNER ? true : false;
        else
            return $this->db->getOne('SELECT permissions FROM `account_team_users` WHERE user_id = ? AND permissions = "owner"', array($this->id)) == Scalr_Account_Team::PERMISSIONS_OWNER ? true : false;
    }

    public function isTeamUserInEnvironment($envId, $permissions)
    {
        $all = $this->db->getCol('SELECT permissions FROM account_team_users
            JOIN account_team_envs ON account_team_users.team_id = account_team_envs.team_id
            WHERE user_id = ? AND env_id = ?', array($this->id, $envId));

        return in_array($permissions, $all) ? true : false;
    }

    public function getUserInfo()
    {
        $info['id'] = $this->id;
        $info['status'] = $this->status;
        $info['email'] = $this->getEmail();
        $info['fullname'] = $this->fullname;
        $info['dtcreated'] = Scalr_Util_DateTime::convertTz($this->dtCreated);
        $info['dtlastlogin'] = $this->dtLastLogin ? Scalr_Util_DateTime::convertTz($this->dtLastLogin) : 'Never';
        $info['dtlastloginhr'] = $this->dtLastLogin ? Scalr_Util_DateTime::getFuzzyTimeString($this->dtLastLogin) : 'Never';
        $info['gravatarhash'] = $this->getGravatarHash();
        $info['type'] = $this->type;
        $info['comments'] = $this->comments;

        $info['is2FaEnabled'] = $this->getSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL) == '1' ? true : false;
        $info['password'] = $this->password ? true : false;

        switch ($info['type']) {
            case Scalr_Account_User::TYPE_ACCOUNT_OWNER:
                $info['type'] = 'Account Owner';
                break;
            default:
                $info['type'] = $this->isTeamOwner() ? 'Team Owner' : 'Team User';
                break;
        }
        return $info;

    }
}
