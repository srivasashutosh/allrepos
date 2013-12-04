<?php

class Scalr_UI_Controller_Core extends Scalr_UI_Controller
{
    public function hasAccess()
    {
        return true;
    }

    public function supportAction()
    {
        if ($this->user) {
            $args = array(
                "name"		=> $this->user->fullname,
                "AccountID" => $this->user->getAccountId(),
                "email"		=> $this->user->getEmail(),
                "expires" => date("D M d H:i:s O Y", time()+120)
            );

            $token = Scalr_Util_CryptoTool::generateTenderMultipassToken(json_encode($args));

            $this->response->setRedirect("http://support.scalr.net/?sso={$token}");
        } else {
            $this->response->setRedirect("/");
        }
    }

    public function apiAction()
    {
        if (! $this->user->getSetting(Scalr_Account_User::SETTING_API_ACCESS_KEY) ||
            ! $this->user->getSetting(Scalr_Account_User::SETTING_API_SECRET_KEY)
        ) {
            $keys = Scalr::GenerateAPIKeys();

            $this->user->setSetting(Scalr_Account_User::SETTING_API_ACCESS_KEY, $keys['id']);
            $this->user->setSetting(Scalr_Account_User::SETTING_API_SECRET_KEY, $keys['key']);
        }

        $params[Scalr_Account_User::SETTING_API_ENABLED] = $this->user->getSetting(Scalr_Account_User::SETTING_API_ENABLED) == 1 ? true : false;
        $params[Scalr_Account_User::SETTING_API_IP_WHITELIST] = (string)$this->user->getSetting(Scalr_Account_User::SETTING_API_IP_WHITELIST);
        $params[Scalr_Account_User::SETTING_API_ACCESS_KEY] = $this->user->getSetting(Scalr_Account_User::SETTING_API_ACCESS_KEY);
        $params[Scalr_Account_User::SETTING_API_SECRET_KEY] = $this->user->getSetting(Scalr_Account_User::SETTING_API_SECRET_KEY);

        $this->response->page('ui/core/api.js', $params);
    }

    public function disasterAction()
    {
        $this->response->page('ui/core/disaster.js');
    }

    public function troubleshootAction()
    {
        $this->response->page('ui/core/troubleshoot.js');
    }

    public function xSaveApiSettingsAction()
    {
        $apiEnabled = $this->getParam(str_replace(".", "_", Scalr_Account_User::SETTING_API_ENABLED)) == 'on' ? true : false;
        $ipWhitelist = $this->getParam(str_replace(".", "_", Scalr_Account_User::SETTING_API_IP_WHITELIST));

        $this->user->setSetting(Scalr_Account_User::SETTING_API_ENABLED, $apiEnabled);
        $this->user->setSetting(Scalr_Account_User::SETTING_API_IP_WHITELIST, $ipWhitelist);

        $this->response->success('API settings successfully saved');
    }

    public function xRegenerateApiKeysAction()
    {
        $keys = Scalr::GenerateAPIKeys();

        $this->user->setSetting(Scalr_Account_User::SETTING_API_ACCESS_KEY, $keys['id']);
        $this->user->setSetting(Scalr_Account_User::SETTING_API_SECRET_KEY, $keys['key']);

        $this->response->success('Keys have been regenerated');
        $this->response->data(array('keys' => $keys));
    }

    public function securityAction()
    {
        $params = array(
            'email' => $this->user->getEmail(),
            'security_2fa' => $this->user->getAccount()->isFeatureEnabled(Scalr_Limits::FEATURE_2FA),
            'security_2fa_ggl' => $this->user->getSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL) ? '1' : '',
            'security_ip_whitelist' => strval($this->user->getSetting(Scalr_Account_User::SETTING_SECURITY_IP_WHITELIST))
        );

        $this->response->page('ui/core/security.js', $params);
    }

    public function xSecuritySaveAction()
    {
        $this->request->defineParams(array(
            'password' => array('type' => 'string'),
            'cpassword' => array('type' => 'string')
        ));

        if (!$this->getParam('password'))
            $err['password'] = "Password is required";

        if ($this->getParam('password') != $this->getParam('cpassword'))
            $err['cpassword'] = "Two passwords are not equal";

        if (count($err) == 0) {
            $updateSession = false;

            if ($this->getParam('password') != '******') {
                $this->user->updatePassword($this->getParam('password'));
                $updateSession = true;
            }

            $this->user->setSetting(Scalr_Account_User::SETTING_SECURITY_IP_WHITELIST, trim($this->getParam('security_ip_whitelist')));

            $this->user->fullname = $this->getParam("fullname");
            $this->user->save();

            if ($updateSession)
                Scalr_Session::create($this->user->getId());

            $this->response->success('Secuity settings successfully updated');
        }
        else {
            $this->response->failure();
            $this->response->data(array('errors' => $err));
        }
    }

    public function xSettingsDisable2FaGglAction()
    {
        $this->user->setSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL, 0);
        $this->user->setSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL_KEY, '');

        $this->response->success();
    }

    public function xSettingsEnable2FaGglAction()
    {
        if ($this->getParam('qr') && $this->getParam('code')) {
            if (Scalr_Util_Google2FA::verifyKey($this->getParam('qr'), $this->getParam('code'))) {
                $this->user->setSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL, 1);
                $this->user->setSetting(Scalr_Account_User::SETTING_SECURITY_2FA_GGL_KEY,
                    $this->getCrypto()->encrypt($this->getParam('qr'), $this->cryptoKey)
                );

                $this->response->success('Two-factor authentication enabled');
            } else {
                $this->response->data(array('errors' => array(
                    'code' => 'Invalid code'
                )));
                $this->response->failure();
            }
        } else {
            $this->response->failure('Invalid data');
        }
    }


    public function settingsAction()
    {
        $panel = $this->user->getDashboard($this->getEnvironmentId());

        $params = array(
            'gravatar_email' => $this->user->getSetting(Scalr_Account_User::SETTING_GRAVATAR_EMAIL) ? $this->user->getSetting(Scalr_Account_User::SETTING_GRAVATAR_EMAIL) : '',
            'gravatar_hash' => $this->user->getGravatarHash(),
            'rss_login' => $this->user->getSetting(Scalr_Account_User::SETTING_RSS_LOGIN),
            'rss_pass' => $this->user->getSetting(Scalr_Account_User::SETTING_RSS_PASSWORD),
            'timezone' => $this->user->getSetting(Scalr_Account_User::SETTING_UI_TIMEZONE),
            'timezones_list' => Scalr_Util_DateTime::getTimezones(),
            'user_email' => $this->user->getEmail(),
            'user_fullname' => $this->user->fullname,
            'dashboard_columns' => count($panel['configuration'])
        );

        $this->response->page('ui/core/settings.js', $params);
    }

    public function xSaveSettingsAction()
    {
        $this->request->defineParams(array(
            'rss_login', 'rss_pass', 'default_environment'
        ));

        $rssLogin = htmlspecialchars($this->getParam('rss_login'));
        $rssPass = htmlspecialchars($this->getParam('rss_pass'));

        if ($rssLogin != '' || $rssPass != '') {
            if (strlen($rssLogin) < 6)
                $err['rss_login'] = "RSS feed login must be 6 chars or more";

            if (strlen($rssPass) < 6)
                $err['rss_pass'] = "RSS feed password must be 6 chars or more";
        }

        if (count($err)) {
            $this->response->failure();
            $this->response->data(array('errors' => $err));
            return;
        }

        $panel = $this->user->getDashboard($this->getEnvironmentId());
        if ($this->getParam('dashboard_columns') > count($panel['configuration'])) {
            while ($this->getParam('dashboard_columns') > count($panel['configuration'])) {
                $panel['configuration'][] = array();
            }
        }
        if ($this->getParam('dashboard_columns') < count($panel['configuration'])) {
            for ($i = count($panel['configuration']); $i > $this->getParam('dashboard_columns'); $i--) {
                foreach($panel['configuration'][$i-1] as $widg) {
                    $panel['configuration'][0][] = $widg;
                }
                unset($panel['configuration'][$i-1]);
            }
        }
        $this->user->setDashboard($this->getEnvironmentId(), $panel);

        $panel = self::loadController('Dashboard')->fillDash($panel);

        $this->user->setSetting(Scalr_Account_User::SETTING_RSS_LOGIN, $rssLogin);
        $this->user->setSetting(Scalr_Account_User::SETTING_RSS_PASSWORD, $rssPass);
        $this->user->setSetting(Scalr_Account_User::SETTING_UI_TIMEZONE, $this->getParam('timezone'));

        $gravatarEmail = htmlspecialchars($this->getParam('gravatar_email'));
        $this->user->setSetting(Scalr_Account_User::SETTING_GRAVATAR_EMAIL, $gravatarEmail);

        $this->user->fullname = htmlspecialchars($this->getParam('user_fullname'));
        $this->user->save();

        $this->response->success('Settings successfully updated');
        $this->response->data(array('panel' => $panel, 'gravatarHash' => $this->user->getGravatarHash()));
    }

    public function variablesAction()
    {
        $vars = new Scalr_Scripting_GlobalVariables($this->getEnvironmentId());
        $this->response->page('ui/core/variables.js', array('variables' => json_encode($vars->getValues())), array('ui/core/variablefield.js'), array('ui/core/variablefield.css'));
    }

    public function xSaveVariablesAction()
    {
        $this->request->defineParams(array(
            'variables' => array('type' => 'json')
        ));

        $vars = new Scalr_Scripting_GlobalVariables($this->getEnvironmentId());
        $vars->setValues($this->getParam('variables'));

        $this->response->success('Variables saved');
    }

    public function xChangeEnvironmentAction()
    {
        $env = Scalr_Environment::init()->loadById($this->getParam('envId'));

        foreach ($this->user->getEnvironments() as $e) {
            if ($env->id == $e['id']) {
                Scalr_Session::getInstance()->setEnvironmentId($e['id']);

                if (! Scalr_Session::getInstance()->isVirtual())
                    $this->user->setSetting(Scalr_Account_User::SETTING_UI_ENVIRONMENT, $e['id']);

                $this->response->success();
                return;
            }
        }

        throw new Scalr_Exception_InsufficientPermissions();
    }
}
