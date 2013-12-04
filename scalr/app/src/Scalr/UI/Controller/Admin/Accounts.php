<?php

class Scalr_UI_Controller_Admin_Accounts extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'accountId';

    public static function getApiDefinitions()
    {
        return array('xRemove', 'xSave', 'xListAccounts', 'xGetInfo');
    }

    public function hasAccess()
    {
        return $this->user && ($this->user->getType() == Scalr_Account_User::TYPE_SCALR_ADMIN);
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $this->response->page('ui/admin/accounts/view.js');
    }

    public function xListAccountsAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'json'),
            'accountId' => array('type' => 'int')
        ));

        $sql = "SELECT id, name, dtadded, status FROM clients WHERE :FILTER:";
        $args = array();

        if ($this->getParam('farmId')) {
            $sql .= ' AND id IN (SELECT clientid FROM farms WHERE id = ?)';
            $args[] = $this->getParam('farmId');
        }

        if ($this->getParam('owner')) {
            $sql .= ' AND id IN (SELECT account_id FROM account_users WHERE `type` = ? AND email LIKE ?)';
            $args[] = Scalr_Account_User::TYPE_ACCOUNT_OWNER;
            $args[] = '%' . $this->getParam('owner') . '%';
        }

        if ($this->getParam('user')) {
            $sql .= ' AND id IN (SELECT account_id FROM account_users WHERE email LIKE ?)';
            $args[] = '%' . $this->getParam('user') . '%';
        }

        if ($this->getParam('envId')) {
            $sql .= ' AND id IN (SELECT client_id FROM client_environments WHERE id = ?)';
            $args[] = $this->getParam('envId');
        }

        $response = $this->buildResponseFromSql2($sql, array('id', 'name', 'dtadded', 'status'), array('id', 'name'), $args);
        foreach ($response['data'] as &$row) {
            $account = Scalr_Account::init()->loadById($row['id']);

            try {
                $row['ownerEmail'] = $account->getOwner()->getEmail();
            } catch (Exception $e){
                $row['ownerEmail'] = '*No owner*';
            }
            $row['dtadded'] = Scalr_Util_DateTime::convertTz($row['dtadded']);

            $row['isTrial'] = (int)$account->getSetting(Scalr_Account::SETTING_IS_TRIAL);

            $limit = Scalr_Limits::init()->Load(Scalr_Limits::ACCOUNT_ENVIRONMENTS, $row['id']);
            $row['envs'] = $limit->getCurrentUsage();
            $row['limitEnvs'] = $limit->getLimitValue() > -1 ? $limit->getLimitValue() : '-';

            $limit = Scalr_Limits::init()->Load(Scalr_Limits::ACCOUNT_FARMS, $row['id']);
            $row['farms'] = $limit->getCurrentUsage();
            $row['limitFarms'] = $limit->getLimitValue() > -1 ? $limit->getLimitValue() : '-';

            $limit = Scalr_Limits::init()->Load(Scalr_Limits::ACCOUNT_USERS, $row['id']);
            $row['users'] = $limit->getCurrentUsage();
            $row['limitUsers'] = $limit->getLimitValue() > -1 ? $limit->getLimitValue() : '-';

            $limit = Scalr_Limits::init()->Load(Scalr_Limits::ACCOUNT_SERVERS, $row['id']);
            $row['servers'] = $limit->getCurrentUsage();
            $row['limitServers'] = $limit->getLimitValue() > -1 ? $limit->getLimitValue() : '-';

            $row['dnsZones'] = $this->db->GetOne("SELECT COUNT(*) FROM dns_zones WHERE client_id = ?", array($row['id']));
        }

        $this->response->data($response);
    }

    public function xRemoveAction()
    {
        $this->request->defineParams(array(
            'accounts' => array('type' => 'json')
        ));

        foreach ($this->getParam('accounts') as $dd) {
            Scalr_Account::init()->loadById($dd)->delete();
        }

        $this->response->success("Selected account(s) successfully removed");
    }

    public function createAction()
    {
        $this->response->page('ui/admin/accounts/edit.js', array(
            'account' => array(
                'id' => 0,
                'name' => '',
                'comments' => '',

                'limitEnv' => -1,
                'limitFarms' => -1,
                'limitServers' => -1,
                'limitUsers' => -1,

                'featureApi' => '1',
                'featureScripting' => '1',
                'featureCsm' => '1'
            )
        ));
    }

    public function getAccount()
    {
        $account = Scalr_Account::init()->loadById($this->getParam(self::CALL_PARAM_NAME));
        $result = array(
            'id' => $account->id,
            'name' => $account->name,
            'comments' => $account->comments,

            'limitEnv' => Scalr_Limits::init()->Load(Scalr_Limits::ACCOUNT_ENVIRONMENTS, $account->id)->getLimitValue(),
            'limitFarms' => Scalr_Limits::init()->Load(Scalr_Limits::ACCOUNT_FARMS, $account->id)->getLimitValue(),
            'limitServers' => Scalr_Limits::init()->Load(Scalr_Limits::ACCOUNT_SERVERS, $account->id)->getLimitValue(),
            'limitUsers' => Scalr_Limits::init()->Load(Scalr_Limits::ACCOUNT_USERS, $account->id)->getLimitValue()
        );

        if ($this->getContainer()->config->get('scalr.auth_mode') == 'ldap')
            $result['ownerEmail'] = $account->getOwner()->getEmail();

        return $result;
    }

    public function editAction()
    {
        $this->response->page('ui/admin/accounts/edit.js', array(
            'account' => $this->getAccount()
        ));
    }

    public function xGetInfoAction()
    {
        $this->response->data(array('account' => $this->getAccount()));
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'id' => array('type' => 'int'),
            'name' => array('type' => 'string', 'validator' => array(
                Scalr_Validator::NOHTML => true,
                Scalr_Validator::REQUIRED => true
            )),
            'comments' => array('type' => 'string')
        ));

        $account = Scalr_Account::init();

        if ($this->getContainer()->config->get('scalr.auth_mode') == 'ldap') {
            $this->request->defineParams(array(
                'ownerEmail' => array('type' => 'string', 'validator' => array(
                    Scalr_Validator::REQUIRED => true
                ))
            ));
        }

        if ($this->getParam('id')) {
            $account->loadById($this->getParam('id'));
        } else {
            $account->status = Scalr_Account::STATUS_ACTIVE;

            if ($this->getContainer()->config->get('scalr.auth_mode') == 'scalr') {
                $this->request->defineParams(array(
                    'ownerEmail' => array('type' => 'string', 'validator' => array(
                        Scalr_Validator::REQUIRED => true,
                        Scalr_Validator::EMAIL => true
                    )),
                    'ownerPassword' => array('type '=> 'string', 'validator' => array(
                        Scalr_Validator::MINMAX => array('min' => 6)
                    ))
                ));
            }
        }

        if (! $this->request->validate()->isValid()) {
            $this->response->failure();
            $this->response->data($this->request->getValidationErrors());
            return;
        }

        $this->db->BeginTrans();
        try {
            $account->name = $this->getParam('name');
            $account->comments = $this->getParam('comments');

            $account->save();

            $account->setLimits(array(
                Scalr_Limits::ACCOUNT_ENVIRONMENTS => $this->getParam('limitEnv'),
                Scalr_Limits::ACCOUNT_FARMS => $this->getParam('limitFarms'),
                Scalr_Limits::ACCOUNT_SERVERS => $this->getParam('limitServers'),
                Scalr_Limits::ACCOUNT_USERS => $this->getParam('limitUsers')
            ));

            if (!$this->getParam('id')) {
                $account->createEnvironment("default");
                $account->createUser($this->getParam('ownerEmail'), $this->getParam('ownerPassword'), Scalr_Account_User::TYPE_ACCOUNT_OWNER);
            }

            if ($this->getContainer()->config->get('scalr.auth_mode') == 'ldap' && $this->getParam('id')) {
                if ($this->getParam('ownerEmail') != $account->getOwner()->getEmail()) {
                    $prev = $account->getOwner();
                    $prev->type = Scalr_Account_User::TYPE_TEAM_USER;
                    $prev->save();

                    $user = new Scalr_Account_User();
                    if ($user->loadByEmail($this->getParam('ownerEmail'), $account->id)) {
                        $user->type = Scalr_Account_User::TYPE_ACCOUNT_OWNER;
                        $user->save();
                    } else {
                        $account->createUser($this->getParam('ownerEmail'), $this->getParam('ownerPassword'), Scalr_Account_User::TYPE_ACCOUNT_OWNER);
                    }
                }
            }

        } catch (Exception $e) {
            $this->db->RollbackTrans();
            throw $e;
        }

        $this->db->CommitTrans();
        $this->response->data(array('accountId' => $account->id));
    }

    public function xGetUsersAction()
    {
        $account = new Scalr_Account();
        $account->loadById($this->getParam('accountId'));

        $this->response->data(array(
            'users' => $account->getUsers()
        ));
    }

    public function xLoginAsAction()
    {
        if ($this->getParam('accountId')) {
            $account = new Scalr_Account();
            $account->loadById($this->getParam('accountId'));
            $user = $account->getOwner();
        } else {
            $user = new Scalr_Account_User();
            $user->loadById($this->getParam('userId'));
        }

        Scalr_Session::create($user->getId(), true);
        $this->response->success();
    }
}
