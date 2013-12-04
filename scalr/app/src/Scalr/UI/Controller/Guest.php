<?php

class Scalr_UI_Controller_Guest extends Scalr_UI_Controller
{
    public function logoutAction()
    {
        Scalr_Session::destroy();
        $this->response->setRedirect('/');
    }

    public function hasAccess()
    {
        return true;
    }

    public function xInitAction()
    {
        $initParams = array();

        $initParams['extjs'] = array(
            $this->response->getModuleName("init.js"),
            $this->response->getModuleName("override.js"),
            $this->response->getModuleName("utils.js"),
            $this->response->getModuleName("ui-form.js"),
            $this->response->getModuleName("ui-grid.js"),
            $this->response->getModuleName("ui-plugins.js"),
            $this->response->getModuleName("ui.js")
        );

        $initParams['css'] = array(
            $this->response->getModuleName("ui.css")
        );

        $initParams['context'] = $this->getContext();

        $this->response->data(array('initParams' => $initParams));
    }

    public function getContext()
    {
        $data = array();
        if ($this->user) {
            $data['user'] = array(
                'userId' => $this->user->getId(),
                'clientId' => $this->user->getAccountId(),
                'userName' => $this->user->getEmail(),
                'gravatarHash' => $this->user->getGravatarHash(),
                'envId' => $this->getEnvironment() ? $this->getEnvironmentId() : 0,
                'envName'  => $this->getEnvironment() ? $this->getEnvironment()->name : '',
                'envVars' => $this->getEnvironment() ? $this->getEnvironment()->getPlatformConfigValue(Scalr_Environment::SETTING_UI_VARS) : '',
                'type' => $this->user->getType()
            );

            if ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN) {
                $data['farms'] = $this->db->getAll('SELECT id, name FROM farms WHERE env_id = ? ORDER BY name', array($this->getEnvironmentId()));

                if ($this->user->getAccountId() != 0) {
                    $data['flags'] = $this->user->getAccount()->getFeaturesList();
                    $data['user']['userIsTrial'] = $this->user->getAccount()->getSetting(Scalr_Account::SETTING_IS_TRIAL) == '1' ? true : false;
                } else {
                    $data['flags'] = array();
                }

                $data['flags']['platformEc2Enabled'] = !!$this->environment->isPlatformEnabled(SERVER_PLATFORMS::EC2);
                $data['flags']['platformCloudstackEnabled'] = !!$this->environment->isPlatformEnabled(SERVER_PLATFORMS::CLOUDSTACK);
                $data['flags']['platformIdcfEnabled'] = !!$this->environment->isPlatformEnabled(SERVER_PLATFORMS::IDCF);
                $data['flags']['platformUcloudEnabled'] = !!$this->environment->isPlatformEnabled(SERVER_PLATFORMS::UCLOUD);
                $data['flags']['platformRackspaceEnabled'] = !!$this->environment->isPlatformEnabled(SERVER_PLATFORMS::RACKSPACE);

                $data['flags']['billingExists'] = \Scalr::config('scalr.billing.enabled');
                $data['flags']['featureUsersPermissions'] = $this->user->getAccount()->isFeatureEnabled(Scalr_Limits::FEATURE_USERS_PERMISSIONS);

                $data['flags']['wikiUrl'] = \Scalr::config('scalr.ui.wiki_url');
                $data['flags']['supportUrl'] = \Scalr::config('scalr.ui.support_url');

                if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER) {
                    if (! $this->user->getAccount()->getSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED)) {
                        if (count($this->environment->getEnabledPlatforms()) == 0)
                            $data['flags']['needEnvConfig'] = Scalr_Environment::init()->loadDefault($this->user->getAccountId())->id;
                    }
                }

                $data['environments'] = $this->user->getEnvironments();

                if ($this->getEnvironment() && $this->user->isTeamOwner()) {
                    $data['user']['isTeamOwner'] = true;
                }
            }
        }

        $data['flags']['authMode'] = $this->getContainer()->config->get('scalr.auth_mode');
        return $data;
    }

    public function xGetContextAction()
    {
        $this->response->data($this->getContext());
    }

    /**
     * Accumulates emails in app/cache/.remind-me-later-emails file.
     * Registration from is in the http://scalr.net/l/re-invent-2012/
     */
    public function xRemindMeLaterAction()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->request->defineParams(array('email'));
        $email = $this->getParam('email');
        $file = APPPATH . '/cache/.remind-me-later-emails';
        $fp = fopen($file, 'a');
        if (!$fp) {
            $this->response->failure('Cannot open file for writing.');
            return;
        } else {
            fputcsv($fp, array(gmdate('c'), $email));
            fclose($fp);
        }
        $this->response->data(array('status' => 'ok'));
    }

    public function xCreateAccountAction()
    {
        if (!\Scalr::config('scalr.billing.enabled'))
            exit();

        $this->request->defineParams(array(
            'name', 'org', 'email', 'password', 'agreeTerms', 'newBilling',
            'country', 'phone', 'lastname', 'firstname', 'v', 'numServers'
        ));

        $Validator = new Scalr_Validator();

        if ($this->getParam('v') == 2) {
            if (!$this->getParam('firstname'))
                $err['firstname'] = _("First name required");

            if (!$this->getParam('lastname'))
                $err['lastname'] = _("Last name required");

            //if (!$this->getParam('org'))
            //    $err['org'] = _("Organization required");

            $name = $this->getParam('firstname') . " " . $this->getParam('lastname');

        } else {
            if (!$this->getParam('name'))
                $err['name'] = _("Account name required");

            $name = $this->getParam("name");
        }

        $password = $this->getParam('password');

        if (!$password)
            $password = $this->getCrypto()->sault(10);

        if ($Validator->validateEmail($this->getParam('email'), null, true) !== true)
            $err['email'] = _("Invalid E-mail address");

        if (strlen($password) < 6)
            $err['password'] = _("Password should be longer than 6 chars");

        // Check email
        $DBEmailCheck = $this->db->GetOne("SELECT COUNT(*) FROM account_users WHERE email=?", array($this->getParam('email')));

        if ($DBEmailCheck > 0)
            $err['email'] = _("E-mail already exists in database");

        if (!$this->getParam('agreeTerms'))
            $err['agreeTerms'] = _("You need to agree with terms and conditions");

        if (count($err) == 0) {
            $account = Scalr_Account::init();
            $account->name = $this->getParam("org") ? $this->getParam("org") : $name;
            $account->status = Scalr_Account::STATUS_ACTIVE;
            $account->save();

            $account->createEnvironment("Environment 1");

            $user = $account->createUser($this->getParam('email'), $password, Scalr_Account_User::TYPE_ACCOUNT_OWNER);
            $user->fullname = $name;
            $user->save();

            if ($this->getParam('v') == 2) {
                $user->setSetting('website.phone', $this->getParam('phone'));
                $user->setSetting('website.country', $this->getParam('country'));
                $user->setSetting('website.num_servers', $this->getParam('numServers'));
            }

            /**
             * Limits
             */
            try {
                $billing = new Scalr_Billing();
                $billing->loadByAccount($account);
                $billing->createSubscription(Scalr_Billing::PAY_AS_YOU_GO, "", "", "", "");
                /*******************/
            } catch (Exception $e) {
                $account->delete();
                header("Location: http://www.scalr.com/order/?error={$e->getMessage()}");
                exit();
            }

            if ($_COOKIE['__utmz']) {
                $gaParser = new Scalr_Service_GoogleAnalytics_Parser();

                $clientSettings[CLIENT_SETTINGS::GA_CAMPAIGN_CONTENT] = $gaParser->campaignContent;
                $clientSettings[CLIENT_SETTINGS::GA_CAMPAIGN_MEDIUM] = $gaParser->campaignMedium;
                $clientSettings[CLIENT_SETTINGS::GA_CAMPAIGN_NAME] = $gaParser->campaignName;
                $clientSettings[CLIENT_SETTINGS::GA_CAMPAIGN_SOURCE] = $gaParser->campaignSource;
                $clientSettings[CLIENT_SETTINGS::GA_CAMPAIGN_TERM] = $gaParser->campaignTerm;
                $clientSettings[CLIENT_SETTINGS::GA_FIRST_VISIT] = $gaParser->firstVisit;
                $clientSettings[CLIENT_SETTINGS::GA_PREVIOUS_VISIT] = $gaParser->previousVisit;
                $clientSettings[CLIENT_SETTINGS::GA_TIMES_VISITED] = $gaParser->timesVisited;
            }

            $clientSettings[CLIENT_SETTINGS::RSS_LOGIN] = $this->getParam('email');
            $clientSettings[CLIENT_SETTINGS::RSS_PASSWORD] = $this->getCrypto()->sault(10);

            foreach ($clientSettings as $k=>$v)
                $account->setSetting($k, $v);

            try {
                $this->db->Execute("INSERT INTO default_records SELECT null, '{$account->id}', rtype, ttl, rpriority, rvalue, rkey FROM default_records WHERE clientid='0'");
            } catch(Exception $e) {
            }

            $clientinfo = array(
                'fullname'	=> $name,
                'firstname'	=> ($this->getParam('firstname')) ? $this->getParam('firstname') : $name,
                'email'		=> $this->getParam('email'),
                'password'	=> $password
            );

            //Sends welcome email
            $this->getContainer()->mailer
                 ->setFrom('sales@scalr.com', 'Scalr')
                 ->setHtml()
                 ->sendTemplate(
                     SCALR_TEMPLATES_PATH . '/emails/welcome.html',
                     array(
                         '{{FirstName}}' => htmlspecialchars($clientinfo['firstname']),
                         '{{Password}}'  => htmlspecialchars($clientinfo['password']),
                     ),
                     $this->getParam('email')
                 )
            ;

            $user->getAccount()->setSetting(Scalr_Account::SETTING_IS_TRIAL, 1);

            //AutoLogin
            $user->updateLastLogin();
            Scalr_Session::create($user->getId());
            Scalr_Session::keepSession();
            $this->response->setRedirect("http://www.scalr.com/thanks.html");
        } else {
            $errors = array_values($err);
            $error = $errors[0];
            $this->response->setRedirect("http://www.scalr.com/order/?error={$error}");
        }
    }


    public function loginAction()
    {
        $this->response->page('ui/guest/login.js', array('loginAttempts' => 0));
    }

    protected $ldapGroups = null;

    private function loginUserGet()
    {
        $this->request->defineParams(array(
            'scalrLogin', 'scalrPass'
        ));

        if ($this->getParam('scalrLogin') != '' && $this->getParam('scalrPass') != '') {
            $login = $this->getParam('scalrLogin');

            if ($this->getContainer()->config->get('scalr.auth_mode') == 'ldap' && $login != 'admin') {
                $ldap = $this->getContainer()->ldap($login, $this->getParam('scalrPass'));
                $result = $ldap->isValidUser($login, $this->getParam('scalrPass'));
                if ($result) {
                    $name = strtok($login, '@');
                    $groups = $ldap->getUserGroups($name);
                    $this->ldapGroups = $groups;

                    foreach ($groups as $key => $name)
                        $groups[$key] = $this->db->qstr($name);

                    $userAvailableAccounts = array();

                    //For debugging purposes
                    $this->response->varDump(json_encode($groups));

                    // System users are not members of any group so if there is no groups then skip this.
                    if (count($groups) > 0) {
                        foreach ($this->db->GetAll('SELECT clients.id, clients.name FROM clients
                            JOIN client_environments ON client_environments.client_id = clients.id
                            JOIN account_team_envs ON account_team_envs.env_id = client_environments.id
                            JOIN account_teams ON account_teams.id = account_team_envs.team_id
                            WHERE account_teams.name IN(' . join(',', $groups) . ')') as $value) {
                                $userAvailableAccounts[$value['id']] = $value;
                        }
                    }

                    foreach ($this->db->GetAll(
                        'SELECT clients.id, clients.name FROM clients JOIN account_users ON account_users.account_id = clients.id WHERE account_users.email = ? AND account_users.type = ?',
                        array($login, Scalr_Account_User::TYPE_ACCOUNT_OWNER)) as $value) {
                            $userAvailableAccounts[$value['id']] = $value;
                    }
                    $userAvailableAccounts = array_values($userAvailableAccounts);

                    if (count($userAvailableAccounts) == 0)
                        throw new Scalr_Exception_Core('You don\'t have access to any account');

                    if (count($userAvailableAccounts) == 1) {
                        $accountId = $userAvailableAccounts[0]['id'];
                    } else {
                        $ids = array();
                        foreach ($userAvailableAccounts as $value)
                            $ids[] = $value['id'];

                        $accountId = $this->getParam('accountId');
                        if (!$accountId && !in_array($accountId, $ids)) {
                            $this->response->data(array(
                                'accounts' => $userAvailableAccounts
                            ));
                            throw new Exception();
                        }
                    }

                    $user = new Scalr_Account_User();
                    $user = $user->loadByEmail($login, $accountId);

                    if (! $user) {
                        $user = new Scalr_Account_User();
                        $user->type = Scalr_Account_User::TYPE_TEAM_USER;
                        $user->status = Scalr_Account_User::STATUS_ACTIVE;
                        $user->create($login, $accountId);
                    }

                } else {
                    throw new Exception("Incorrect login or password (1)");
                }
            } else {
                $userAvailableAccounts = $this->db->GetAll('SELECT account_users.id AS userId, clients.id, clients.name FROM account_users
                    LEFT JOIN clients ON clients.id = account_users.account_id WHERE account_users.email = ?', array($login));

                if (count($userAvailableAccounts) == 1) {
                    $user = new Scalr_Account_User();
                    $user->loadById($userAvailableAccounts[0]['userId']);

                } else if (count($userAvailableAccounts) > 1) {
                    $accountId = $this->getParam('accountId');
                    if ($accountId) {
                        foreach($userAvailableAccounts as $acc) {
                            if ($acc['id'] == $accountId) {
                                $user = new Scalr_Account_User();
                                $user->loadById($acc['userId']);
                                break;
                            }
                        }
                    } else {
                        $this->response->data(array(
                            'accounts' => $userAvailableAccounts
                        ));
                        throw new Exception();
                    }

                } else {
                    throw new Exception("Incorrect login or password (3)");
                }

                if ($user) {
                    // kaptcha
                    if ($user->loginattempts > 2) {
                        $text = file_get_contents('http://www.google.com/recaptcha/api/challenge?k=' . urlencode(SCALR_RECAPTCHA_PUBLIC_KEY));
                        $start = strpos($text, "challenge : '")+13;
                        $length = strpos($text, ",", $start)-$start;
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, 'http://www.google.com/recaptcha/api/verify?privatekey=' . urlencode(SCALR_RECAPTCHA_PRIVATE_KEY) .'&remoteip=my.scalr.net&challenge='.substr($text, $start, $length).'&response='.$this->getParam('scalrCaptcha'));
                        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                        $response = curl_exec($curl);
                        curl_close($curl);
                        if (preg_match('/false?.*/', $response)) {
                            $this->response->data(array(
                                'loginattempts' => $user->loginattempts
                            ));
                            throw new Exception();
                        }
                    }

                    if (! $user->checkPassword($this->getParam('scalrPass')))
                        throw new Exception("Incorrect login or password (1)");

                } else {
                    throw new Exception("Incorrect login or password (2)");
                }
            }

            // valid user, other checks
            if ($user->getSetting(Scalr_Account_User::SETTING_SECURITY_IP_WHITELIST)) {
                $ips = explode(',', $user->getSetting(Scalr_Account_User::SETTING_SECURITY_IP_WHITELIST));
                $inList = false;
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if($ip && preg_match('/^'.$ip.'$/', $_SERVER['REMOTE_ADDR']))
                        $inList = true;
                }
                if (!$inList)
                    throw new Exception('The IP address you are attempting to log in from isn\'t authorized');
            }

            return $user;
        } else {
            throw new Exception('Incorrect login or password (0)');
        }
    }

    /**
     * @param Scalr_Account_User $user
     */
    private function loginUserCreate($user)
    {
        $user->updateLastLogin();
        Scalr_Session::create($user->getId());

        if (Scalr::config('scalr.auth_mode') == 'ldap') {
            Scalr_Session::getInstance()->setLdapGroups($this->ldapGroups);
        } else {
            if ($this->getParam('scalrKeepSession') == 'on')
                Scalr_Session::keepSession();
        }

        $this->response->data(array('userId' => $user->getId()));
    }

    public function xLoginFakeAction()
    {
        $this->response->setBody(file_get_contents(APPPATH . '/www/login.html'));
    }

    public function xLoginAction()
    {
        $user = $this->loginUserGet();

        // check for 2-factor auth
        if (
            ($user->getAccountId() && $user->getAccount()->isFeatureEnabled(Scalr_Limits::FEATURE_2FA) || !$user->getAccountId()) &&
            ($user->getSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL) == 1)
        ) {
            $this->response->data(array(
                'tfa' => '#/guest/loginTfaGgl'
            ));
            return;
        }

        $this->loginUserCreate($user);
    }

    public function loginTfaGglAction()
    {
        $user = $this->loginUserGet();

        $this->response->page('ui/guest/loginTfaGgl.js', array(
            'valid' => (
                ($user->getAccountId() && $user->getAccount()->isFeatureEnabled(Scalr_Limits::FEATURE_2FA) || !$user->getAccountId()) &&
                ($user->getSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL) == 1)
            ),
            'authenticated' => is_object($this->user)
        ));
    }

    public function xLoginTfaGglAction()
    {
        $user = $this->loginUserGet();

        if (
            ($user->getAccountId() && $user->getAccount()->isFeatureEnabled(Scalr_Limits::FEATURE_2FA) || !$user->getAccountId()) &&
            ($user->getSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL) == 1)
        ) {
            $key = $this->getCrypto()->decrypt($user->getSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL_KEY), $this->cryptoKey);

            if ($this->getParam('tfaCode') && Scalr_Util_Google2FA::verifyKey($key, $this->getParam('tfaCode'))) {
                $this->loginUserCreate($user);
            } else {
                $this->response->failure('Invalid code');
            }
        } else {
            $this->response->failure('Two-factor authentication not enabled for this user');
        }
    }

    public function recoverPasswordAction()
    {
        $this->response->page('ui/guest/recoverPassword.js');
    }

    public function xResetPasswordAction()
    {
        $user = Scalr_Account_User::init()->loadByEmail($this->getParam('email'));

        if ($user) {
            $hash = $this->getCrypto()->sault(10);
            $user->setSetting(Scalr_Account::SETTING_OWNER_PWD_RESET_HASH, $hash);
            $clientinfo = array(
                'email'    => $user->getEmail(),
                'fullname' => $user->fullname,
            );

            // Send welcome E-mail
            $this->getContainer()->mailer->sendTemplate(
                SCALR_TEMPLATES_PATH . '/emails/password_change_confirm.eml',
                array(
                    '{{fullname}}' => $clientinfo['fullname'],
                    '{{link}}'     => "https://{$_SERVER['HTTP_HOST']}/#/guest/updatePassword/?hash={$hash}",
                ),
                $clientinfo['email'], $clientinfo['fullname']
            );

            $this->response->success("Confirmation email has been sent to you");
        } else {
            $this->response->failure("Specified e-mail not found in our database");
        }
    }

    public function updatePasswordAction()
    {
        $user = Scalr_Account_User::init()->loadBySetting(Scalr_Account::SETTING_OWNER_PWD_RESET_HASH, $this->getParam('hash'));
        $this->response->page('ui/guest/updatePassword.js', array('valid' => is_object($user), 'authenticated' => is_object($this->user)));
    }

    public function xUpdatePasswordAction()
    {
        $user = Scalr_Account_User::init()->loadBySetting(Scalr_Account::SETTING_OWNER_PWD_RESET_HASH, $this->getParam('hash'));

        if ($user) {
            $password = $this->getParam('password');
            $user->updatePassword($password);
            $user->save();

            $user->setSetting(Scalr_Account::SETTING_OWNER_PWD_RESET_HASH, "");

            //Scalr_Session::create($user->getAccountId(), $user->getId(), $user->getType());

            $this->response->success("Password has been reset. Please log in.");
        } else {
            $this->response->failure("Incorrect confirmation link");
        }
    }

    public function xPerpetuumMobileAction()
    {
        $result = array();

        if ($this->user) {
            if ($this->getParam('updateDashboard'))
                $result['updateDashboard'] = Scalr_UI_Controller::loadController('dashboard')->checkLifeCycle($this->getParam('updateDashboard'));

            //if (!Scalr_Session::getInstance()->isVirtual() && $this->getParam('uiStorage'))
            //    $this->user->setVar(Scalr_Account_User::VAR_UI_STORAGE, $this->getParam('uiStorage'));
        }

        $equal = $this->user && ($this->user->getId() == $this->getParam('userId')) &&
            (($this->getEnvironment() ? $this->getEnvironmentId() : 0) == $this->getParam('envId'));

        $result['equal'] = $equal;
        $result['isAuthenticated'] = $this->user ? true : false;

        $this->response->data($result);
    }

    public function xPostErrorAction()
    {
        $this->request->defineParams(array(
            'url', 'file', 'lineno', 'message'
        ));

        $this->response->success();

        $message = explode("\n", $this->getParam('message'));
        if (empty($message[0]))
            return;

        $this->db->Execute('INSERT INTO ui_errors (tm, file, lineno, url, short, message, browser, account_id, user_id) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE cnt = cnt + 1, tm = NOW()', array(
            $this->getParam('file'),
            $this->getParam('lineno'),
            $this->getParam('url'),
            $message[0],
            $this->getParam('message'),
            $_SERVER['HTTP_USER_AGENT'],
            $this->user ? $this->user->getAccountId() : '',
            $this->user ? $this->user->id : ''
        ));
    }
}
