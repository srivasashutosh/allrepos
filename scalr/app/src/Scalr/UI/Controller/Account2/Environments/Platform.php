<?php

use Scalr\Service\OpenStack\OpenStack;
use Scalr\Service\OpenStack\OpenStackConfig;

class Scalr_UI_Controller_Account2_Environments_Platform extends Scalr_UI_Controller
{
    /**
     *
     * @var Scalr_Environment
     */
    private $env;
    private $checkVarError;

    public function init()
    {
        $this->env = Scalr_Environment::init()->loadById($this->getParam(Scalr_UI_Controller_Environments::CALL_PARAM_NAME));
        $this->user->getPermissions()->validate($this->env);

        if (! ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER || $this->user->isTeamUserInEnvironment($this->env->id, Scalr_Account_Team::PERMISSIONS_OWNER)))
            throw new Scalr_Exception_InsufficientPermissions();
    }

    public static function getApiDefinitions()
    {
        return array('xSaveEc2', 'xSaveRackspace', 'xSaveNimbula', 'xSaveCloudstack', 'xSaveOpenstack', 'xSaveEucalyptus');
    }

    private function checkVar($name, $type, $requiredError = '', $group = '', $noFileTrim = false)
    {
        $varName = str_replace('.', '_', ($group != '' ? $name . '.' . $group : $name));
        $errorName = $group != '' ? $name . '.' . $group : $name;

        switch ($type) {
            case 'int':
                if ($this->getParam($varName)) {
                    return intval($this->getParam($varName));
                } else {
                    $value = $this->env->getPlatformConfigValue($name, true, $group);
                    if (!$value && $requiredError)
                        $this->checkVarError[$errorName] = $requiredError;

                    return $value;
                }
                break;

            case 'string':
                if ($this->getParam($varName)) {
                    return $this->getParam($varName);
                } else {
                    $value = $this->env->getPlatformConfigValue($name, true, $group);
                    if ($value == '' && $requiredError)
                        $this->checkVarError[$errorName] = $requiredError;

                    return $value;
                }
                break;

            case 'password':
                if ($this->getParam($varName) && $this->getParam($varName) != '******') {
                    return $this->getParam($varName);
                } else {
                    $value = $this->env->getPlatformConfigValue($name, true, $group);
                    if ($value == '' && $requiredError)
                        $this->checkVarError[$errorName] = $requiredError;

                    return $value;
                }
                break;

            case 'bool':
                return $this->getParam($varName) ? 1 : 0;

            case 'file':
                if (isset($_FILES[$varName]['tmp_name']) && ($value = @file_get_contents($_FILES[$varName]['tmp_name'])) != '') {
                    return ($noFileTrim) ? $value : trim($value);
                } else {
                    $value = $this->env->getPlatformConfigValue($name, true, $group);
                    if ($value == '' && $requiredError)
                        $this->checkVarError[$errorName] = $requiredError;

                    return $value;
                }
                break;
        }
    }

    public function gceAction()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::GCE, $this->env->getEnabledPlatforms())) {
            $params['gce.is_enabled'] = true;
            $params[Modules_Platforms_GoogleCE::CLIENT_ID] = $this->env->getPlatformConfigValue(Modules_Platforms_GoogleCE::CLIENT_ID);
            $params[Modules_Platforms_GoogleCE::PROJECT_ID] = $this->env->getPlatformConfigValue(Modules_Platforms_GoogleCE::PROJECT_ID);
            $params[Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME] = $this->env->getPlatformConfigValue(Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME);
            $params[Modules_Platforms_GoogleCE::KEY] = $this->env->getPlatformConfigValue(Modules_Platforms_GoogleCE::KEY) != '' ? 'Uploaded' : '';
        }

        $this->response->page('ui/account2/environments/platform/gce.js', array(
            'env' => array(
                    'id' => $this->env->id,
                    'name' => $this->env->name
            ),
            'params' => $params
        ));
    }

    public function xSaveGceAction()
    {
        $pars = array();
        $enabled = false;

        if ($this->getParam('gce_is_enabled')) {
            $enabled = true;

            $pars[Modules_Platforms_GoogleCE::CLIENT_ID] = trim($this->checkVar(Modules_Platforms_GoogleCE::CLIENT_ID, 'string', "GCE Cient ID required"));
            $pars[Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME] = trim($this->checkVar(Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME, 'string', "GCE email (service account name) required"));
            $pars[Modules_Platforms_GoogleCE::PROJECT_ID] = trim($this->checkVar(Modules_Platforms_GoogleCE::PROJECT_ID, 'password', "GCE Project ID required"));
            $pars[Modules_Platforms_GoogleCE::KEY] = base64_encode($this->checkVar(Modules_Platforms_GoogleCE::KEY, 'file', "GCE Private Key required", null, true));

            if (! count($this->checkVarError)) {
                if (
                    $pars[Modules_Platforms_GoogleCE::CLIENT_ID] != $this->env->getPlatformConfigValue(Modules_Platforms_GoogleCE::CLIENT_ID) or
                    $pars[Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME] != $this->env->getPlatformConfigValue(Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME) or
                    $pars[Modules_Platforms_GoogleCE::PROJECT_ID] != $this->env->getPlatformConfigValue(Modules_Platforms_GoogleCE::PROJECT_ID) or
                    $pars[Modules_Platforms_GoogleCE::KEY] != $this->env->getPlatformConfigValue(Modules_Platforms_GoogleCE::KEY)
                ) {
                    try {
                        $client = new Google_Client();
                        $client->setApplicationName("Scalr GCE");
                        $client->setScopes(array('https://www.googleapis.com/auth/compute'));

                        $key = base64_decode($pars[Modules_Platforms_GoogleCE::KEY]);
                        $client->setAssertionCredentials(new Google_AssertionCredentials(
                            $pars[Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME],
                            array('https://www.googleapis.com/auth/compute'),
                            $key
                        ));

                        $client->setUseObjects(true);
                        $client->setClientId($pars[Modules_Platforms_GoogleCE::CLIENT_ID]);

                        $gce = new Google_ComputeService($client);

                        $gce->zones->listZones($pars[Modules_Platforms_GoogleCE::PROJECT_ID]);

                    } catch (Exception $e) {
                        throw new Exception(_("Provided GCE credentials are incorrect: ({$e->getMessage()})"));
                    }
                }
            } else {
                $this->response->failure();
                $this->response->data(array('errors' => $this->checkVarError));
                return;
            }
        }

        $this->db->BeginTrans();
        try {
            $this->env->enablePlatform(SERVER_PLATFORMS::GCE, $enabled);

            if ($enabled)
                $this->env->setPlatformConfig($pars);

            if (! $this->user->getAccount()->getSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED))
                $this->user->getAccount()->setSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED, time());

            $this->response->success('Environment saved');
            $this->response->data(array('enabled' => $enabled));
        } catch (Exception $e) {
            $this->db->RollbackTrans();
            throw new Exception(_("Failed to save GCE settings: {$e->getMessage()}"));
        }
        $this->db->CommitTrans();
    }

    public function ec2Action()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::EC2, $this->env->getEnabledPlatforms())) {
            $params['ec2.is_enabled'] = true;
            $params[Modules_Platforms_Ec2::ACCOUNT_ID] = $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::ACCOUNT_ID);
            $params[Modules_Platforms_Ec2::ACCESS_KEY] = $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::ACCESS_KEY);
            $params[Modules_Platforms_Ec2::SECRET_KEY] = $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::SECRET_KEY) != '' ? '******' : '';
            $params[Modules_Platforms_Ec2::PRIVATE_KEY] = $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::PRIVATE_KEY) != '' ? 'Uploaded' : '';
            $params[Modules_Platforms_Ec2::CERTIFICATE] = $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::CERTIFICATE) != '' ? 'Uploaded' : '';
        }

        $this->response->page('ui/account2/environments/platform/ec2.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params
        ));
    }

    public function xSaveEc2Action()
    {
        $pars = array();
        $enabled = false;

        if ($this->getParam('ec2_is_enabled')) {
            $enabled = true;

            $pars[Modules_Platforms_Ec2::ACCOUNT_ID] = $this->checkVar(Modules_Platforms_Ec2::ACCOUNT_ID, 'string', "AWS Account Number required");

            if (! is_numeric($pars[Modules_Platforms_Ec2::ACCOUNT_ID]) || strlen($pars[Modules_Platforms_Ec2::ACCOUNT_ID]) != 12)
                //$err[Modules_Platforms_Ec2::ACCOUNT_ID] = _("AWS numeric account ID required (See <a href='/faq.html'>FAQ</a> for info on where to get it).");
                $this->checkVarError[Modules_Platforms_Ec2::ACCOUNT_ID] = _("AWS Account Number should be numeric");
            else
                $pars[Modules_Platforms_Ec2::ACCOUNT_ID] = preg_replace("/[^0-9]+/", "", $pars[Modules_Platforms_Ec2::ACCOUNT_ID]);

            $pars[Modules_Platforms_Ec2::ACCESS_KEY] = $this->checkVar(Modules_Platforms_Ec2::ACCESS_KEY, 'string', "AWS Access Key required");
            $pars[Modules_Platforms_Ec2::SECRET_KEY] = $this->checkVar(Modules_Platforms_Ec2::SECRET_KEY, 'password', "AWS Access Key required");
            $pars[Modules_Platforms_Ec2::PRIVATE_KEY] = trim($this->checkVar(Modules_Platforms_Ec2::PRIVATE_KEY, 'file'));
            $pars[Modules_Platforms_Ec2::CERTIFICATE] = trim($this->checkVar(Modules_Platforms_Ec2::CERTIFICATE, 'file'));

            // user can mull certificate and private key, check it
            if (strpos($pars[Modules_Platforms_Ec2::PRIVATE_KEY], 'BEGIN CERTIFICATE') !== FALSE &&
                strpos($pars[Modules_Platforms_Ec2::CERTIFICATE], 'BEGIN PRIVATE KEY') !== FALSE) {
                // swap it
                $key = $pars[Modules_Platforms_Ec2::PRIVATE_KEY];
                $pars[Modules_Platforms_Ec2::PRIVATE_KEY] = $pars[Modules_Platforms_Ec2::CERTIFICATE];
                $pars[Modules_Platforms_Ec2::CERTIFICATE] = $key;
            }


            if (!count($this->checkVarError)) {
                if (
                    $pars[Modules_Platforms_Ec2::ACCOUNT_ID] != $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::ACCOUNT_ID) or
                    $pars[Modules_Platforms_Ec2::ACCESS_KEY] != $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::ACCESS_KEY) or
                    $pars[Modules_Platforms_Ec2::SECRET_KEY] != $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::SECRET_KEY) or
                    $pars[Modules_Platforms_Ec2::PRIVATE_KEY] != $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::PRIVATE_KEY) or
                    $pars[Modules_Platforms_Ec2::CERTIFICATE] != $this->env->getPlatformConfigValue(Modules_Platforms_Ec2::CERTIFICATE)
                ) {
                    $aws = $this->env->aws(
                        \Scalr\Service\Aws::REGION_US_EAST_1,
                        $pars[Modules_Platforms_Ec2::ACCESS_KEY],
                        $pars[Modules_Platforms_Ec2::SECRET_KEY],
                        !empty($pars[Modules_Platforms_Ec2::CERTIFICATE]) ? $pars[Modules_Platforms_Ec2::CERTIFICATE] : null,
                        !empty($pars[Modules_Platforms_Ec2::PRIVATE_KEY]) ? $pars[Modules_Platforms_Ec2::PRIVATE_KEY] : null
                    );
                    
                    if (!empty($pars[Modules_Platforms_Ec2::CERTIFICATE]) || !empty($pars[Modules_Platforms_Ec2::PRIVATE_KEY])) {
                        try {
                            $aws->validateCertificateAndPrivateKey();
                        } catch (Exception $e) {
                            throw new Exception(_("Incorrect format of X.509 certificate or private key. Make sure that you are using files downloaded from AWS profile. ({$e->getMessage()})"));
                        }
                    }
                    
                    try {
                        $buckets = $aws->s3->bucket->getList();
                    } catch (Exception $e) {
                        throw new Exception(sprintf(_("Failed to verify your EC2 access key and secret key: %s"), $e->getMessage()));
                    }
                }
            } else {
                $this->response->failure();
                $this->response->data(array('errors' => $this->checkVarError));
                return;
            }
        }

        $this->db->BeginTrans();
        try {
            $this->env->enablePlatform(SERVER_PLATFORMS::EC2, $enabled);

            if ($enabled)
                $this->env->setPlatformConfig($pars);

            if (! $this->user->getAccount()->getSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED))
                $this->user->getAccount()->setSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED, time());

            $this->db->CommitTrans();
        } catch (Exception $e) {
            $this->db->RollbackTrans();
            throw new Exception(_("Failed to save AWS settings: {$e->getMessage()}"));
        }

        try {
            if ($this->user->getAccount()->getSetting(Scalr_Account::SETTING_IS_TRIAL) == 1) {
                if ($this->db->GetOne("SELECT COUNT(*) FROM farms WHERE clientid = ?", array($this->user->getAccountId())) == 0) {
                    //Create demo farm
                    try {
                        $dbFarm = DBFarm::LoadByID(9670); // LAMP-PROTOTYPE
                        $dbFarm->cloneFarm('My First LAMP Farm', $this->user, $this->getEnvironmentId());
                        $demoFarm = true;
                    } catch (Exception $e) {
                        throw new Exception("Demo farm creation failed: {$e->getMessage()}");
                    }
                }
            }
        } catch (Exception $e) {}

        $this->response->success('Environment saved');
        $this->response->data(array('enabled' => $enabled, 'demoFarm' => $demoFarm));
    }

    public function rackspaceAction()
    {
        $params = array();

        $rows = $this->db->GetAll('SELECT * FROM client_environment_properties WHERE env_id = ? AND name LIKE "rackspace.%" AND `group` != "" GROUP BY `group', $this->env->id);
        foreach ($rows as $value) {
            $cloud = $value['group'];
            $params[$cloud] = array(
                Modules_Platforms_Rackspace::USERNAME => $this->env->getPlatformConfigValue(Modules_Platforms_Rackspace::USERNAME, true, $cloud),
                Modules_Platforms_Rackspace::API_KEY => $this->env->getPlatformConfigValue(Modules_Platforms_Rackspace::API_KEY, true, $cloud),
                Modules_Platforms_Rackspace::IS_MANAGED => $this->env->getPlatformConfigValue(Modules_Platforms_Rackspace::IS_MANAGED, true, $cloud),
            );
        }

        $this->response->page('ui/account2/environments/platform/rackspace.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params
        ));
    }

    public function xSaveRackspaceAction()
    {
        $pars = array();
        $enabled = false;
        $locations = array('rs-ORD1', 'rs-LONx');

        foreach ($locations as $location) {
            if ($this->getParam("rackspace_is_enabled_{$location}")) {
                $enabled = true;

                $pars[$location][Modules_Platforms_Rackspace::USERNAME] = $this->checkVar(Modules_Platforms_Rackspace::USERNAME, 'string', "Username required", $location);
                $pars[$location][Modules_Platforms_Rackspace::API_KEY] = $this->checkVar(Modules_Platforms_Rackspace::API_KEY, 'string', "API Key required", $location);
                $pars[$location][Modules_Platforms_Rackspace::IS_MANAGED] = $this->checkVar(Modules_Platforms_Rackspace::IS_MANAGED, 'bool', "", $location);
            }
            else {
                $pars[$location][Modules_Platforms_Rackspace::USERNAME] = false;
                $pars[$location][Modules_Platforms_Rackspace::API_KEY] = false;
                $pars[$location][Modules_Platforms_Rackspace::IS_MANAGED] = false;
            }
        }

        if (count($this->checkVarError)) {
            $this->response->failure();
            $this->response->data(array('errors' => $this->checkVarError));
        } else {
            $this->db->BeginTrans();
            try {
                $this->env->enablePlatform(SERVER_PLATFORMS::RACKSPACE, $enabled);

                foreach ($pars as $cloud => $prs)
                    $this->env->setPlatformConfig($prs, true, $cloud);

                if (! $this->user->getAccount()->getSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED))
                    $this->user->getAccount()->setSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED, time());

                $this->response->success('Environment saved');
                $this->response->data(array('enabled' => $enabled));
            } catch (Exception $e) {
                $this->db->RollbackTrans();
                throw new Exception(_('Failed to save Rackspace settings'));
            }
            $this->db->CommitTrans();
        }
    }

    public function nimbulaAction()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::NIMBULA, $this->env->getEnabledPlatforms())) {
            $params['nimbula.is_enabled'] = true;
            $params[Modules_Platforms_Nimbula::API_URL] = $this->env->getPlatformConfigValue(Modules_Platforms_Nimbula::API_URL);
            $params[Modules_Platforms_Nimbula::USERNAME] = $this->env->getPlatformConfigValue(Modules_Platforms_Nimbula::USERNAME);
            $params[Modules_Platforms_Nimbula::PASSWORD] = $this->env->getPlatformConfigValue(Modules_Platforms_Nimbula::PASSWORD);
        }

        $this->response->page('ui/account2/environments/platform/nimbula.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params
        ));
    }

    public function xSaveNimbulaAction()
    {
        $pars = array();
        $enabled = false;

        if ($this->getParam('nimbula_is_enabled')) {
            $enabled = true;

            $pars[Modules_Platforms_Nimbula::API_URL] = $this->checkVar(Modules_Platforms_Nimbula::API_URL, 'string', 'API URL required');
            $pars[Modules_Platforms_Nimbula::USERNAME] = $this->checkVar(Modules_Platforms_Nimbula::USERNAME, 'string', 'Username required');
            $pars[Modules_Platforms_Nimbula::PASSWORD] = $this->checkVar(Modules_Platforms_Nimbula::PASSWORD, 'string', 'Password required');
        }

        if (count($this->checkVarError)) {
            $this->response->failure();
            $this->response->data(array('errors' => $this->checkVarError));
        } else {
            $this->db->BeginTrans();
            try {
                $this->env->enablePlatform(SERVER_PLATFORMS::NIMBULA, $enabled);

                if ($enabled)
                    $this->env->setPlatformConfig($pars);

                if (! $this->user->getAccount()->getSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED))
                    $this->user->getAccount()->setSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED, time());

                $this->response->success('Environment saved');
                $this->response->data(arraY('enabled' => $enabled));
            } catch (Exception $e) {
                $this->db->RollbackTrans();
                throw new Exception(_('Failed to save Nimbula settings'));
            }
            $this->db->CommitTrans();
        }
    }

    private function getOpenStackDetails($platform)
    {
        $params["{$platform}.is_enabled"] = true;
        $params[Modules_Platforms_Openstack::KEYSTONE_URL] = $this->env->getPlatformConfigValue("{$platform}." . Modules_Platforms_Openstack::KEYSTONE_URL);
        $params[Modules_Platforms_Openstack::USERNAME] = $this->env->getPlatformConfigValue("{$platform}." . Modules_Platforms_Openstack::USERNAME);
        $params[Modules_Platforms_Openstack::PASSWORD] = $this->env->getPlatformConfigValue("{$platform}." . Modules_Platforms_Openstack::PASSWORD);
        $params[Modules_Platforms_Openstack::API_KEY] = $this->env->getPlatformConfigValue("{$platform}." . Modules_Platforms_Openstack::API_KEY);
        $params[Modules_Platforms_Openstack::TENANT_NAME] = $this->env->getPlatformConfigValue("{$platform}." . Modules_Platforms_Openstack::TENANT_NAME);

        return $params;
    }

    public function openstackAction()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::OPENSTACK, $this->env->getEnabledPlatforms())) {
            $params = $this->getOpenStackDetails(SERVER_PLATFORMS::OPENSTACK);
        }

        $this->response->page('ui/account2/environments/platform/openstack.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params,
            'platformName' => 'Openstack',
            'platform' => 'openstack'
        ));
    }

    public function rackspacengusAction()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::RACKSPACENG_US, $this->env->getEnabledPlatforms())) {
            $params = $this->getOpenStackDetails(SERVER_PLATFORMS::RACKSPACENG_US);
        }

        $this->response->page('ui/account2/environments/platform/openstack.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params,
            'platformName' => 'Rackspace Open Cloud (US)',
            'platform' => 'rackspacengus'
        ));
    }

    public function rackspacengukAction()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::RACKSPACENG_UK, $this->env->getEnabledPlatforms())) {
            $params = $this->getOpenStackDetails(SERVER_PLATFORMS::RACKSPACENG_UK);
        }

        $this->response->page('ui/account2/environments/platform/openstack.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params,
            'platformName' => 'Rackspace Open Cloud (UK)',
            'platform' => 'rackspacenguk'
        ));
    }

    /**
     * Gets unified platform variable name
     *
     * @param   string  $name  mnemonic name
     * @return  string  Returns full platform variable name
     */
    private function getOpenStackOption($name)
    {
        return $this->getParam('platform') . "." . constant("Modules_Platforms_Openstack::" . $name);
    }

    public function xSaveOpenstackAction()
    {
        $pars = array();
        $enabled = false;
        $platform = $this->getParam('platform');

        if ($this->getParam("{$platform}_is_enabled")) {
            $enabled = true;

            $pars[$this->getOpenStackOption('KEYSTONE_URL')] = $this->checkVar(Modules_Platforms_Openstack::KEYSTONE_URL, 'string', 'KeyStone URL required');
            $pars[$this->getOpenStackOption('USERNAME')] = $this->checkVar(Modules_Platforms_Openstack::USERNAME, 'string', 'Username required');
            $pars[$this->getOpenStackOption('PASSWORD')] = $this->checkVar(Modules_Platforms_Openstack::PASSWORD, 'string');
            $pars[$this->getOpenStackOption('API_KEY')] = $this->checkVar(Modules_Platforms_Openstack::API_KEY, 'string');
            $pars[$this->getOpenStackOption('TENANT_NAME')] = $this->checkVar(Modules_Platforms_Openstack::TENANT_NAME, 'string');
            if (empty($this->checkVarError) &&
                empty($pars[$this->getOpenStackOption('PASSWORD')]) &&
                empty($pars[$this->getOpenStackOption('API_KEY')])) {
                $this->checkVarError['API_KEY'] = 'Either API Key or password must be provided.';
            }
        }

        if (count($this->checkVarError)) {
            $this->response->failure();
            $this->response->data(array('errors' => $this->checkVarError));
        } else {
            if ($this->getParam($platform . "_is_enabled")) {
                $os = new OpenStack(new OpenStackConfig(
                    $pars[$this->getOpenStackOption('USERNAME')],
                    $pars[$this->getOpenStackOption('KEYSTONE_URL')],
                    'fake-region',
                    $pars[$this->getOpenStackOption('API_KEY')],
                    null, // Closure callback for token
                    null, // Auth token. We should be assured about it right now
                    $pars[$this->getOpenStackOption('PASSWORD')],
                    $pars[$this->getOpenStackOption('TENANT_NAME')]
                ));
                //It throws an exception on failure
                $os->listZones();
            }

            $this->db->BeginTrans();
            try {
                $this->env->enablePlatform($platform, $enabled);

                if ($enabled) {
                    $this->env->setPlatformConfig($pars);
                } else {
                    $this->env->setPlatformConfig(array(
                        "{$platform}." . Modules_Platforms_Openstack::AUTH_TOKEN => false
                    ));
                }

                if (! $this->user->getAccount()->getSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED))
                    $this->user->getAccount()->setSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED, time());

                $this->response->success('Environment saved');
                $this->response->data(array('enabled' => $enabled));
            } catch (Exception $e) {
                $this->db->RollbackTrans();
                throw new Exception(_('Failed to save '.ucfirst($platform).' settings'));
            }
            $this->db->CommitTrans();
        }
    }

    private function getCloudStackDetails($platform)
    {
        $params["{$platform}.is_enabled"] = true;
        $params[Modules_Platforms_Cloudstack::API_URL] = $this->env->getPlatformConfigValue("{$platform}." . Modules_Platforms_Cloudstack::API_URL);
        $params[Modules_Platforms_Cloudstack::API_KEY] = $this->env->getPlatformConfigValue("{$platform}." . Modules_Platforms_Cloudstack::API_KEY);
        $params[Modules_Platforms_Cloudstack::SECRET_KEY] = $this->env->getPlatformConfigValue("{$platform}." . Modules_Platforms_Cloudstack::SECRET_KEY);

        return $params;
    }

    public function cloudstackAction()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::CLOUDSTACK, $this->env->getEnabledPlatforms())) {
            $params = $this->getCloudStackDetails(SERVER_PLATFORMS::CLOUDSTACK);
        }

        $this->response->page('ui/account2/environments/platform/cloudstack.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params,
            'platformName' => 'Cloudstack',
            'platform' => 'cloudstack'
        ));
    }

    public function idcfAction()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::IDCF, $this->env->getEnabledPlatforms())) {
            $params = $this->getCloudStackDetails(SERVER_PLATFORMS::IDCF);
        }

        $this->response->page('ui/account2/environments/platform/cloudstack.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params,
            'platformName' => 'IDCF',
            'platform' => 'idcf'
        ));
    }

    public function ucloudAction()
    {
        $params = array();

        if (in_array(SERVER_PLATFORMS::UCLOUD, $this->env->getEnabledPlatforms())) {
            $params = $this->getCloudStackDetails(SERVER_PLATFORMS::UCLOUD);
        }

        $this->response->page('ui/account2/environments/platform/cloudstack.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params,
            'platformName' => 'KT uCloud',
            'platform' => 'ucloud'
        ));
    }

    public function xSaveCloudstackAction()
    {
        $pars = array();
        $enabled = false;
        $platform = $this->getParam('platform');

        if ($this->getParam("{$platform}_is_enabled")) {
            $enabled = true;

            $pars["{$platform}." . Modules_Platforms_Cloudstack::API_URL] = $this->checkVar(Modules_Platforms_Cloudstack::API_URL, 'string', 'API URL required');
            $pars["{$platform}." . Modules_Platforms_Cloudstack::API_KEY] = $this->checkVar(Modules_Platforms_Cloudstack::API_KEY, 'string', 'API key required');
            $pars["{$platform}." . Modules_Platforms_Cloudstack::SECRET_KEY] = $this->checkVar(Modules_Platforms_Cloudstack::SECRET_KEY, 'string', 'Secret key required');
        }

        if (count($this->checkVarError)) {
            $this->response->failure();
            $this->response->data(array('errors' => $this->checkVarError));
        } else {

            if ($this->getParam("{$platform}_is_enabled")) {
                $cs = Scalr_Service_Cloud_Cloudstack::newCloudstack(
                    $pars["{$platform}." . Modules_Platforms_Cloudstack::API_URL],
                    $pars["{$platform}." . Modules_Platforms_Cloudstack::API_KEY],
                    $pars["{$platform}." . Modules_Platforms_Cloudstack::SECRET_KEY],
                    $platform
                );
                $accounts = $cs->listAccounts();
                foreach ($accounts as $account) {
                    foreach ($account->user as $user) {
                        if ($user->apikey == $pars["{$platform}." . Modules_Platforms_Cloudstack::API_KEY]) {
                            $dPars["{$platform}." . Modules_Platforms_Cloudstack::ACCOUNT_NAME] = $user->account;
                            $dPars["{$platform}." . Modules_Platforms_Cloudstack::DOMAIN_NAME] = $user->domain;
                            $dPars["{$platform}." . Modules_Platforms_Cloudstack::DOMAIN_ID] = $user->domainid;
                        }
                    }
                }

                if (!$dPars["{$platform}." . Modules_Platforms_Cloudstack::ACCOUNT_NAME])
                    throw new Exception("Cannot determine account name for provided keys");
            }

            $this->db->BeginTrans();
            try {
                $this->env->enablePlatform($platform, $enabled);

                if ($enabled) {
                    $this->env->setPlatformConfig($pars);
                    $this->env->setPlatformConfig($dPars, false);
                } else {
                    $this->env->setPlatformConfig(array(
                        "{$platform}." . Modules_Platforms_Cloudstack::ACCOUNT_NAME => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::API_KEY => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::API_URL => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::DOMAIN_ID => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::DOMAIN_NAME => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::SECRET_KEY => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::SHARED_IP => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::SHARED_IP_ID => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::SHARED_IP_INFO => false,
                        "{$platform}." . Modules_Platforms_Cloudstack::SZR_PORT_COUNTER => false
                    ));
                }

                if (! $this->user->getAccount()->getSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED))
                    $this->user->getAccount()->setSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED, time());

                $this->response->success('Environment saved');
                $this->response->data(array('enabled' => $enabled));
            } catch (Exception $e) {
                $this->db->RollbackTrans();
                throw new Exception(_('Failed to save '.ucfirst($platform).' settings'));
            }
            $this->db->CommitTrans();
        }
    }

    public function eucalyptusAction()
    {
        $params = array();

        $rows = $this->db->GetAll('SELECT * FROM client_environment_properties WHERE env_id = ? AND name LIKE "eucalyptus.%" AND `group` != "" GROUP BY `group`', $this->env->id);
        foreach ($rows as $value) {
            $cloud = $value['group'];
            $params[$cloud] = array(
                Modules_Platforms_Eucalyptus::ACCOUNT_ID => $this->env->getPlatformConfigValue(Modules_Platforms_Eucalyptus::ACCOUNT_ID, true, $cloud),
                Modules_Platforms_Eucalyptus::ACCESS_KEY => $this->env->getPlatformConfigValue(Modules_Platforms_Eucalyptus::ACCESS_KEY, true, $cloud),
                Modules_Platforms_Eucalyptus::EC2_URL => $this->env->getPlatformConfigValue(Modules_Platforms_Eucalyptus::EC2_URL, true, $cloud),
                Modules_Platforms_Eucalyptus::S3_URL => $this->env->getPlatformConfigValue(Modules_Platforms_Eucalyptus::S3_URL, true, $cloud),
                Modules_Platforms_Eucalyptus::SECRET_KEY => $this->env->getPlatformConfigValue(Modules_Platforms_Eucalyptus::SECRET_KEY, true, $cloud) != '' ? '******' : false,
                Modules_Platforms_Eucalyptus::PRIVATE_KEY => $this->env->getPlatformConfigValue(Modules_Platforms_Eucalyptus::PRIVATE_KEY, true, $cloud) != '' ? 'Uploaded' : '',
                Modules_Platforms_Eucalyptus::CLOUD_CERTIFICATE => $this->env->getPlatformConfigValue(Modules_Platforms_Eucalyptus::CLOUD_CERTIFICATE, true, $cloud) != '' ? 'Uploaded' : '',
                Modules_Platforms_Eucalyptus::CERTIFICATE => $this->env->getPlatformConfigValue(Modules_Platforms_Eucalyptus::CERTIFICATE, true, $cloud) != '' ? 'Uploaded' : ''
            );
        }

        $this->response->page('ui/account2/environments/platform/eucalyptus.js', array(
            'env' => array(
                'id' => $this->env->id,
                'name' => $this->env->name
            ),
            'params' => $params
        ));
    }

    public function xSaveEucalyptusAction()
    {
        $this->request->defineParams(array(
            'clouds' => array('type' => 'json')
        ));

        $pars = array();
        $enabled = false;

        $clouds = $this->getParam('clouds');
        $cloudsDeleted = array();
        if (count($clouds)) {
            $enabled = true;

            foreach ($clouds as $cloud) {
                $pars[$cloud][Modules_Platforms_Eucalyptus::ACCOUNT_ID] = $this->checkVar(Modules_Platforms_Eucalyptus::ACCOUNT_ID, 'string', "Account ID required", $cloud);
                $pars[$cloud][Modules_Platforms_Eucalyptus::ACCESS_KEY] = $this->checkVar(Modules_Platforms_Eucalyptus::ACCESS_KEY, 'string', "Access Key required", $cloud);
                $pars[$cloud][Modules_Platforms_Eucalyptus::EC2_URL] = $this->checkVar(Modules_Platforms_Eucalyptus::EC2_URL, 'string', "EC2 URL required", $cloud);
                $pars[$cloud][Modules_Platforms_Eucalyptus::S3_URL] = $this->checkVar(Modules_Platforms_Eucalyptus::S3_URL, 'string', "S3 URL required", $cloud);
                $pars[$cloud][Modules_Platforms_Eucalyptus::SECRET_KEY] = $this->checkVar(Modules_Platforms_Eucalyptus::SECRET_KEY, 'password', "Secret Key required", $cloud);
                $pars[$cloud][Modules_Platforms_Eucalyptus::PRIVATE_KEY] = $this->checkVar(Modules_Platforms_Eucalyptus::PRIVATE_KEY, 'file', "x.509 Private Key required", $cloud);
                $pars[$cloud][Modules_Platforms_Eucalyptus::CERTIFICATE] = $this->checkVar(Modules_Platforms_Eucalyptus::CERTIFICATE, 'file', "x.509 Certificate required", $cloud);
                $pars[$cloud][Modules_Platforms_Eucalyptus::CLOUD_CERTIFICATE] = $this->checkVar(Modules_Platforms_Eucalyptus::CLOUD_CERTIFICATE, 'file', "x.509 Cloud Certificate required", $cloud);
            }
        }

        // clear old cloud locations
        foreach ($this->db->GetAll('SELECT * FROM client_environment_properties WHERE env_id = ? AND name LIKE "eucalyptus.%" AND `group` != "" GROUP BY `group', $this->env->id) as $key => $value) {
            if (! in_array($value['group'], $clouds))
                $cloudsDeleted[] = $value['group'];
        }

        if (count($this->checkVarError)) {
            $this->response->failure();
            $this->response->data(array('errors' => $this->checkVarError));
        } else {
            $this->db->BeginTrans();
            try {
                $this->env->enablePlatform(SERVER_PLATFORMS::EUCALYPTUS, $enabled);

                foreach ($cloudsDeleted as $key => $cloud)
                    $this->db->Execute('DELETE FROM client_environment_properties WHERE env_id = ? AND `group` = ? AND name LIKE "eucalyptus.%"', array($this->env->id, $cloud));

                foreach ($pars as $cloud => $prs)
                    $this->env->setPlatformConfig($prs, true, $cloud);

                if (! $this->user->getAccount()->getSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED))
                    $this->user->getAccount()->setSetting(Scalr_Account::SETTING_DATE_ENV_CONFIGURED, time());

                $this->response->success(_('Environment saved'));
                $this->response->data(array('enabled' => $enabled));
            } catch (Exception $e) {
                $this->db->RollbackTrans();
                throw new Exception(_('Failed to save Eucalyptus settings'));
            }
            $this->db->CommitTrans();
        }
    }
}
