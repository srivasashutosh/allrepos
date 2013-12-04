<?php
class Scalr_UI_Controller_Account2 extends Scalr_UI_Controller
{

    public function addUiCacheKeyPatternChunk($chunk)
    {
        if ($chunk == 'account2')
            $chunk = 'account';

        $this->uiCacheKeyPattern .= "/{$chunk}";
    }

    public static function getApiDefinitions()
    {
        return array('xGetData');
    }

    public function xGetDataAction()
    {
        $this->request->defineParams(array(
            'stores' => array('type' => 'json'), 'action'
        ));

        $stores = array();
        foreach ($this->getParam('stores') as $storeName) {
            $method = 'get' . implode('', array_map('ucfirst', explode('.', strtolower($storeName)))) . 'List';
            if (method_exists($this, $method)) {
                $stores[$storeName] = $this->$method();
            }
        }

        $this->response->data(array(
            'stores' => $stores
        ));
    }

    public function getAccountEnvironmentsList()
    {
        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER) {
            $sql = "SELECT
                id,
                name,
                dt_added AS dtAdded,
                status
                FROM client_environments
                WHERE client_id = ?
            ";
            $params = array($this->user->getAccountId());
        } else {
            $sql = "SELECT
                client_environments.id,
                client_environments.name,
                client_environments.dt_added AS dtAdded,
                client_environments.status
                FROM client_environments
                JOIN account_team_envs ON client_environments.id = account_team_envs.env_id
                JOIN account_team_users ON account_team_envs.team_id = account_team_users.team_id
                WHERE client_environments.client_id = ?
                AND account_team_users.permissions = ?
                AND account_team_users.user_id = ?
                GROUP BY client_environments.id
            ";
            $params = array($this->user->getAccountId(), Scalr_Account_Team::PERMISSIONS_OWNER, $this->user->id);
        }

        $environments = $this->db->getAll($sql, $params);

        foreach ($environments as &$row) {
            $env = Scalr_Environment::init()->loadById($row['id']);
            $row['platforms'] = $env->getEnabledPlatforms();
            $row['teams'] = array();
            foreach ($env->getTeams() as $teamId) {
                if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner($teamId)) {
                    if ($this->getContainer()->config->get('scalr.auth_mode') == 'ldap') {
                        $team = new Scalr_Account_Team();
                        $team->loadById($teamId);
                        $row['teams'][] = $team->name;
                    } else {
                        $row['teams'][] = $teamId;
                    }
                }
            }
            $row['dtAdded'] = Scalr_Util_DateTime::convertTz($row['dtAdded']);
            $row[ENVIRONMENT_SETTINGS::TIMEZONE] = $env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::TIMEZONE);
        }

        return $environments;
    }

    public function getAccountUsersList()
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
            $row = Scalr_Account_User::init()->loadById($row['id'])->getUserInfo();
        }

        return $usersList;
    }

    public function getAccountTeamsList()
    {
        // account owner, team owner
        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()) {
            $sql = 'SELECT id, name FROM account_teams WHERE account_id = ?';
            $params = array($this->user->getAccountId());
        } else {
            // team user
            $sql = 'SELECT account_teams.id, name FROM account_teams
                JOIN account_team_users ON account_teams.id = account_team_users.team_id WHERE user_id = ?';
            $params = array($this->user->getId());
        }

        $rows = $this->db->getAll($sql, $params);
        $teams = array();
        foreach ($rows as &$row) {
            $team = Scalr_Account_Team::init()->loadById($row['id']);
            $users = $team->getUsers();
            foreach ($users as &$user) {
                $row['users'][] = array(
                    'id' => $user['id'],
                    'permissions' => $user['permissions'],
                    'groups' =>  $this->db->getCol('
                        SELECT account_groups.id FROM account_user_groups
                        JOIN account_groups ON account_groups.id = account_user_groups.group_id
                        WHERE account_user_groups.user_id = ? AND account_groups.team_id = ?', array(
                        $user['id'], $row['id']
                    ))
                );
            }
            $teams[] = $row;
        }
        return $teams;
    }

    public function getAccountGroupsList()
    {
        // account owner, team owner
        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()) {
            $sql = 'SELECT id, name FROM account_teams WHERE account_id= ? ';
            $params = array($this->user->getAccountId());
        } else {
            // team user
            $sql = 'SELECT account_teams.id, name FROM account_teams
                JOIN account_team_users ON account_teams.id = account_team_users.team_id WHERE user_id= ?';
            $params = array($this->user->getId());
        }

        $teams = $this->db->getAll($sql, $params);
        $groups = array();
        foreach ($teams as &$row) {
            if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner($row['id'])) {
                $team = Scalr_Account_Team::init()->loadById($row['id']);
                $teamGroups = $team->getGroups();
                foreach ($teamGroups as &$row1) {
                    $group = Scalr_Account_Group::init();
                    $group->loadById($row1['id']);
                    $groups[] = array(
                        'teamId' => $team->id,
                        'id' => $group->id,
                        'name' => $group->name,
                        'color' => $group->color,
                        'permissions' => $group->getPermissions()
                    );
                }
            }
        }
        return $groups;
    }


}
