<?php

class Scalr_UI_Controller_Account2_Environments extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'envId';

    private $checkVarError;

    public static function getApiDefinitions()
    {
        return array('xCreate', 'xSave', 'xRemove');
    }

    public function hasAccess()
    {
        if (parent::hasAccess()) {
            return ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner()) ? true : false;
        } else
            return false;
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $platforms = SERVER_PLATFORMS::GetList();

        if (!$this->request->getHeaderVar('Interface-Beta')) {
            unset($platforms[SERVER_PLATFORMS::UCLOUD]);
        }

        $allowedClouds = (array) \Scalr::config('scalr.allowed_clouds');
        foreach ($platforms as $platform => $details) {
            if (!in_array($platform, $allowedClouds))
                unset($platforms[$platform]);
        }

        $this->response->page('ui/account2/environments/view.js', array(
            'platforms' => $platforms
        ), array('ui/account2/dataconfig.js'), array('ui/account2/environments/view.css'), array('account.environments', 'account.teams'));
    }

    public function xRemoveAction()
    {
        if ($this->user->getType() != Scalr_Account_User::TYPE_ACCOUNT_OWNER)
            throw new Scalr_Exception_InsufficientPermissions();

        $env = Scalr_Environment::init()->loadById($this->getParam('envId'));
        $this->user->getPermissions()->validate($env);
        $env->delete();

        if ($env->id == $this->getEnvironmentId())
            Scalr_Session::getInstance()->setEnvironmentId(null); // reset

        $this->response->success("Environment successfully removed");
        $this->response->data(array('env' => array('id' => $env->id), 'flagReload' => $env->id == $this->getEnvironmentId() ? true : false));
    }

    private function checkVar($name, $type, $env, $requiredError = '', $group = '')
    {
        $varName = str_replace('.', '_', ($group != '' ? $name . '.' . $group : $name));

        switch ($type) {
            case 'int':
                if ($this->getParam($varName)) {
                    return intval($this->getParam($varName));
                } else {
                    $value = $env->getPlatformConfigValue($name, true, $group);
                    if (!$value && $requiredError)
                        $this->checkVarError[$name] = $requiredError;

                    return $value;
                }
                break;

            case 'string':
                if ($this->getParam($varName)) {
                    return $this->getParam($varName);
                } else {
                    $value = $env->getPlatformConfigValue($name, true, $group);
                    if ($value == '' && $requiredError)
                        $this->checkVarError[$name] = $requiredError;

                    return $value;
                }
                break;

            case 'password':
                if ($this->getParam($varName) && $this->getParam($varName) != '******') {
                    return $this->getParam($varName);
                } else {
                    $value = $env->getPlatformConfigValue($name, true, $group);
                    if ($value == '' && $requiredError)
                        $this->checkVarError[$name] = $requiredError;

                    return $value;
                }
                break;

            case 'bool':
                return $this->getParam($varName) ? 1 : 0;
        }
    }

    public function xSaveAction()
    {
        $params = array(
            'envId' => array('type' => 'int'),
            'teams' => array('type' => 'json')
        );
        if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER) {
            $params['name'] = array('type' => 'string', 'validator' => array(
                Scalr_Validator::REQUIRED => true,
                Scalr_Validator::NOHTML => true
            ));
        }
        $this->request->defineParams($params);

        $this->request->validate();
        if ($this->request->isValid()) {
            $isNew = false;
            if (!$this->getParam('envId')) {//create new environment
                if ($this->user->getType() != Scalr_Account_User::TYPE_ACCOUNT_OWNER)
                    throw new Scalr_Exception_InsufficientPermissions();

                $this->user->getAccount()->validateLimit(Scalr_Limits::ACCOUNT_ENVIRONMENTS, 1);
                $env = $this->user->getAccount()->createEnvironment($this->getParam('name'));
                $isNew = true;
            } else {
                $env = Scalr_Environment::init()->loadById($this->getParam('envId'));
            }

            $this->user->getPermissions()->validate($env);
            if (! ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamUserInEnvironment($env->id, Scalr_Account_Team::PERMISSIONS_OWNER)))
                throw new Scalr_Exception_InsufficientPermissions();

            //set name and status
            if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER) {
                $env->name = $this->getParam('name');
            }
            $env->status = $this->getParam('status') == Scalr_Environment::STATUS_ACTIVE ? Scalr_Environment::STATUS_ACTIVE : Scalr_Environment::STATUS_INACTIVE;
            $env->save();

            //set timezone
            $pars = array();
            $pars[ENVIRONMENT_SETTINGS::TIMEZONE] = $this->checkVar(ENVIRONMENT_SETTINGS::TIMEZONE, 'string', $env, "Timezone required");
            $env->setPlatformConfig($pars);

            if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER) {
                //set teams
                $env->clearTeams();
                if ($this->getContainer()->config->get('scalr.auth_mode') == 'ldap') {
                    foreach ($this->getParam('teams') as $name) {
                        $name = trim($name);
                        if ($name) {
                            $id = $this->db->GetOne('SELECT id FROM account_teams WHERE name = ? AND account_id = ?', array($name, $this->user->getAccountId()));
                            if (! $id) {
                                $team = new Scalr_Account_Team();
                                $team->name = $name;
                                $team->accountId = $this->user->getAccountId();
                                $team->save();
                                $id = $team->id;
                            }

                            $env->addTeam($id);
                        }
                    }
                    // remove unused teams
                    $ids = $this->db->GetAll('SELECT account_teams.id FROM account_teams LEFT JOIN account_team_envs ON account_team_envs.team_id = account_teams.id
                        WHERE ISNULL(account_team_envs.env_id) AND account_teams.account_id = ?', array($this->user->getAccountId()));

                    foreach ($ids as $id) {
                        $team = new Scalr_Account_Team();
                        $team->loadById($id['id']);
                        $team->delete();
                    }
                } else {
                    foreach ($this->getParam('teams') as $id)
                        $env->addTeam($id);
                }
            }

            $this->response->success($isNew?'Environment successfully created':'Environment saved');

            $env = Scalr_Environment::init()->loadById($env->id);//reload env to be sure we have actual params

            $teams = array();
            foreach ($env->getTeams() as $teamId) {
                if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamOwner($teamId)) {
                    if ($this->getContainer()->config->get('scalr.auth_mode') == 'ldap') {
                        $team = new Scalr_Account_Team();
                        $team->loadById($teamId);
                        $teams[] = $team->name;
                    } else {
                        $teams[] = $teamId;
                    }
                }
            }

            $this->response->data(array(
                'env' => array(
                    'id' => $env->id,
                    'name' => $env->name,
                    'status' => $env->status,
                    'platforms' => $env->getEnabledPlatforms(),
                    'timezone' => $env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::TIMEZONE),
                    'teams' => $teams
                )
            ));

        } else {
            $this->response->failure($this->request->getValidationErrorsMessage());
        }
    }
}
