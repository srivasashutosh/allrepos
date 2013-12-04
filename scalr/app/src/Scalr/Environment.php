<?php

use Scalr\DependencyInjection\Container;

/**
 * Scalr_Environment class
 *
 * Following phpdocumentor comments have been derived from Scalr\DependencyInjection class:
 *
 * @property string $awsRegion
 *           The AWS region derived from user's environment.
 *
 * @property string $awsSecretAccessKey
 *           The AWS sercret access key taken from user's environment.
 *
 * @property string $awsAccessKeyId
 *           The Aws access key id taken from user's environment.
 *
 * @property string $awsAccountNumber
 *           The Aws account number.
 *
 * @property \Scalr_Session $session
 *           The Scalr Session isntance.
 *
 * @property \Scalr\Service\Cloudyn $cloudyn
 *           The Cloudyn instance for the current user
 *
 * @property \Scalr\Service\Aws $aws
 *           The Aws instance for the last instantiated user's environment.
 *
 * @property \Scalr_UI_Request $request
 *           The Scalr_UI_Request instance.
 *
 * @property \Scalr_Account_User $user
 *           The Scalr_Account_User instance which is property for the request.
 *
 * @property \Scalr\Logger\AuditLog $auditLog
 *           The AuditLog.
 *
 * @property \Scalr\Logger\LoggerStorageInterface $auditLogStorage
 *           The AuditLogStorage
 *
 * @property \Scalr\SimpleMailer $mailer
 *           Returns the new instance of the SimpleMailer class.
 *           This is not a singletone.
 *
 * @property \Scalr\System\Config\Yaml $config
 *           Gets configuration
 *
 * @property \ADODB_mysqli $adodb
 *           Gets an ADODB mysqli Connection object
 *
 * @property \ADODB_mysqli $dnsdb
 *           Gets an ADODB mysqli Connection to PDNS Database
 *
 *
 * @method   mixed config()
 *           config(string $name)
 *           Gets config value for the dot notation access key
 *
 * @method   \Scalr\Service\OpenStack\OpenStack openstack()
 *           openstack($platform, $region)
 *           Gets an Openstack instance for the current environment
 *
 * @method   \Scalr_Environment loadById()
 *           loadById($id)
 *           Loads Scalr_Environment object using unique identifier.
 *
 * @method   \ADODB_mysqli adodb()
 *           adodb()
 *           Gets an ADODB mysqli Connection object
 *
 * @method   \Scalr\Net\Ldap\LdapClient ldap()
 *           ldap($user, $password)
 *           Gets Ldap client. If user and pass are not specified for scalr.connections.ldap section in the config
 *           the user and password which are specified for calling this method will be used.
 */
class Scalr_Environment extends Scalr_Model
{

    protected $dbTableName = "client_environments";
    protected $dbPropertyMap = array(
        'id'		=> 'id',
        'name'		=> array('property' => 'name', 'is_filter' => true),
        'client_id'	=> array('property' => 'clientId', 'is_filter' => true),
        'dt_added'	=> array('property' => 'dtAdded', 'createSql' => 'NOW()', 'type' => 'datetime', 'update' => false),
        'status'    => 'status'
    );

    public
        $id,
        $name,
        $clientId,
        $dtAdded,
        $status;

    private $cache = array();

    /**
     * Encrypted variables list
     *
     * It looks like array(variable => true)
     * This array is initialized by the getEncryptedVariables method
     * and should not be used directly.
     *
     * @var array
     */
    private static $encryptedVariables;

    const SETTING_TIMEZONE = 'timezone';
    const SETTING_UI_VARS  = 'ui.vars';

    const STATUS_ACTIVE    = 'Active';
    const STATUS_INACTIVE  = 'Inactive';

    /**
     * {@inheritdoc}
     * @see Scalr_Model::__construct()
     */
    public function __construct ($id = null)
    {
        parent::__construct($id);
        if ($id !== null) {
            $this->getContainer()->environment = $this;
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr_Model::loadBy()
     */
    public function loadBy($info)
    {
        $object = parent::loadBy($info);
        $this->getContainer()->environment = $object;
        return $object;
    }

    /**
     * @param  string  $id  serviceid
     * @return mixed
     */
    public function __get($id)
    {
        if ($this->getContainer()->initialized($id)) {
            return $this->get($id);
        }
        throw new \RuntimeException(sprintf('Missing property "%s" for the %s.', $id, get_class($this)));
    }

    /**
     * {@inheritdoc}
     * @see Scalr_Model::__call()
     */
    public function __call($name, $arguments)
    {
        if ($this->getContainer()->initialized($name, true)) {
            return call_user_func_array(array($this->getContainer(), $name), $arguments);
        } else {
            return parent::__call($name, $arguments);
        }
    }

    /**
     * Gets an Amazon Web Service (Aws) factory instance
     *
     * This method ensures that aws instance will always be from the
     * correct environment scope.
     *
     * @param   string|\DBServer|\DBFarmRole|\DBEBSVolume $awsRegion optional
     *          The region or object that has environment itself
     *
     * @param   string  $awsAccessKeyId     optional The AccessKeyId
     * @param   string  $awsSecretAccessKey optional The SecretAccessKey
     * @param   string  $certificate        optional Contains x.509 certificate
     * @param   string  $privateKey         optional The private key for the certificate
     * @return  \Scalr\Service\Aws Returns Aws instance
     */
    public function aws($awsRegion = null, $awsAccessKeyId = null, $awsSecretAccessKey = null,
                        $certificate = null, $privateKey = null)
    {
        $arguments = func_get_args();
        if (count($arguments) <= 1) {
            $arguments[0] = isset($arguments[0]) ? $arguments[0] : null;
            $arguments[1] = $this;
        }
        return $this->__call('aws', $arguments);
    }

    /**
     * Gets an service or parameter by its id.
     *
     * @param   string  $serviceid
     * @return  mixed
     * @throws  \RuntimeException
     */
    public function get($serviceid)
    {
        if ($this->getContainer()->initialized($serviceid)) {
            return $this->getContainer()->get($serviceid);
        }
        throw new \RuntimeException(sprintf('Service "%s" has not been initialized.', $serviceid));
    }

    /**
     * Init
     * @param   string $className
     * @return  Scalr_Environment
     */
    public static function init($className = null) {
        return parent::init();
    }

    public function create($name, $clientId)
    {
        $this->id = 0;
        $this->name = $name;
        $this->clientId = $clientId;
        $this->status = self::STATUS_ACTIVE;
        $this->save();
        return $this;
    }

    protected function encryptValue($value)
    {
        return $this->getCrypto()->encrypt($value, $this->cryptoKey);
    }

    protected function decryptValue($value)
    {
        return $this->getCrypto()->decrypt($value, $this->cryptoKey);
    }

    public function loadDefault($clientId)
    {
        // TODO: rewrite Scalr_Environment::loadDefault($clientId) for user-based
        $info = $this->db->GetRow("SELECT * FROM client_environments WHERE client_id = ?", array($clientId));
        if (! $info)
            throw new Exception(sprintf(_('Default environment for clientId #%s not found'), $clientId));

        return $this->loadBy($info);
    }

    /**
     * Gets client_environment_properties value.
     *
     * @param   string     $key       Property name.
     * @param   bool       $encrypted optional This value is ignored and never taken into account
     * @param   string     $group     optional Group name.
     * @return  mixed      Returns config value on success or NULL if value does not exist.
     */
    public function getPlatformConfigValue($key, $encrypted = true, $group = '')
    {
        $varlinks = self::getLinkedVariables();
        if (!isset($this->cache[$group]) || !array_key_exists($key, $this->cache[$group])) {
            $mustBeEncrypted = self::getEncryptedVariables();
            $keys = isset($varlinks[$key]) ? self::getLinkedVariables($varlinks[$key]) : array($key);
            $args = array_merge(array($this->id, $group), $keys);
            $res = $this->db->GetAssoc("
                SELECT name, value
                FROM client_environment_properties
                WHERE env_id = ? AND `group` = ?
                AND name IN (" . join(', ', array_fill(0, count($keys), '?')) . ")
            ", $args, true, true);
            foreach ($keys as $k) {
                $value = isset($res[$k]) ? $res[$k] : null;
                if (isset($mustBeEncrypted[$k]) && $value !== null) {
                    $value = $this->decryptValue($value);
                }
                $this->cache[$group][$k] = $value !== false ? $value : null;
            }
        }

        return $this->cache[$group][$key];
    }

    public function isPlatformEnabled($platform)
    {
        // constant from SERVER_PLATFORMS class
        return $this->getPlatformConfigValue($platform . '.is_enabled', false);
    }

    public function getEnabledPlatforms()
    {
        $enabled = array();
        foreach (array_keys(SERVER_PLATFORMS::getList()) as $value) {
            if ($this->isPlatformEnabled($value))
                $enabled[] = $value;
        }
        return $enabled;
    }

    public function getLocations()
    {
        if (!$this->cache['locations']) {
            $this->cache['locations'] = array();
            foreach ($this->getEnabledPlatforms() as $platform) {
                $locs = call_user_func(array("Modules_Platforms_".ucfirst($platform), "getLocations"));
                foreach ($locs as $k => $v)
                    $this->cache['locations'][$k] = $v;
            }
        }

        krsort($this->cache['locations']);

        return $this->cache['locations'];
    }

    public function enablePlatform($platform, $enabled = true)
    {
        $props = array($platform . '.is_enabled' => $enabled ? 1 : 0);
        if (!$enabled) {
            foreach ($this->getLinkedVariables($platform) as $key)
                $props[$key] = null;
        }

        $this->setPlatformConfig($props, false);
        $this->cache['locations'] = null;
    }

    /**
     * Saves platform config value to database.
     *
     * This operation will update client_environment_properties table or delete if value is null.
     *
     * @param   array        $props    List of properties with its values keypairs to save.
     * @param   bool         $encrypt  optional This value is ignored and never taken into account.
     * @param   string       $group    Group
     * @throws  Exception
     */
    public function setPlatformConfig($props, $encrypt = true, $group = '')
    {
        $mustBeEncrypted = self::getEncryptedVariables();
        $updates = array();
        foreach ($props as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $updates[$key2] = ($value2 === false ? null : $value2);
                }
            } else {
                $updates[$key] = ($value === false ? null : $value);
            }
        }
        foreach ($updates as $key => $value) {
            //Updates the cache
            $this->cache[$group][$key] = $value;
            if ($value === false)
                $value = 0;

            if (isset($mustBeEncrypted[$key]) && $value !== null) {
                $value = $this->encryptValue($value);
            }

            try {
                if ($value === null) {
                    $this->db->Execute("
                        DELETE FROM client_environment_properties
                        WHERE env_id = ? AND name = ? AND `group` = ?
                    ", array($this->id, $key, $group));
                } else {
                    $this->db->Execute("
                        INSERT INTO client_environment_properties
                        SET env_id = ?, name = ?, value = ?, `group` = ?
                        ON DUPLICATE KEY UPDATE value = ?
                    ", array($this->id, $key, $value, $group, $value));
                }
            } catch (Exception $e) {
                throw new Exception (sprintf(_("Cannot update record. Error: %s"), $e->getMessage()), $e->getCode());
            }
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr_Model::save()
     */
    public function save($forceInsert = false)
    {
        if ($this->db->GetOne('SELECT id FROM client_environments WHERE name = ? AND client_id = ? AND id != ?', array($this->name, $this->clientId, $this->id)))
            throw new Exception('This name already used');

        parent::save();
    }

    /**
     * {@inheritdoc}
     * @see Scalr_Model::delete()
     */
    public function delete($id = null)
    {
        if ($this->db->GetOne("SELECT COUNT(*) FROM farms WHERE env_id = ?", array($this->id)))
            throw new Exception("Cannot remove environment. You need to remove all your farms first.");

        if ($this->db->GetOne("SELECT COUNT(*) FROM client_environments WHERE client_id = ?", array($this->clientId)) < 2)
            throw new Exception('At least one environment should be in account. You cannot remove the last one.');

        parent::delete();

        try {
            $this->db->Execute("DELETE FROM client_environment_properties WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM apache_vhosts WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM autosnap_settings WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM bundle_tasks WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM dm_applications WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM dm_deployment_tasks WHERE env_id=?", array($this->id));

            $this->db->Execute("DELETE FROM dm_sources WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM dns_zones WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM ec2_ebs WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM elastic_ips WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM farms WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM roles WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM servers WHERE env_id=?", array($this->id));
            $this->db->Execute("DELETE FROM ssh_keys WHERE env_id=?", array($this->id));

            $this->db->Execute('DELETE FROM `account_team_envs` WHERE env_id = ?', array($this->id));

        } catch (Exception $e) {
            throw new Exception (sprintf(_("Cannot delete record. Error: %s"), $e->getMessage()), $e->getCode());
        }
    }

    public function getTeams()
    {
        return $this->db->getCol('SELECT team_id FROM `account_team_envs` WHERE env_id = ?', array($this->id));
    }

    public function clearTeams()
    {
        $this->db->Execute('DELETE FROM `account_team_envs` WHERE env_id = ?', array($this->id));
    }

    public function addTeam($teamId)
    {
        $team = Scalr_Account_Team::init()->loadById($teamId);

        if ($team->accountId == $this->clientId) {
            $this->removeTeam($teamId);
            $this->db->Execute('INSERT INTO `account_team_envs` (team_id, env_id) VALUES(?,?)', array(
                $teamId, $this->id
            ));
        } else
            throw new Exception('This team doesn\'t belongs to this account');
    }

    public function removeTeam($teamId)
    {
        $this->db->Execute('DELETE FROM `account_team_envs` WHERE env_id = ? AND team_id = ?', array($this->id, $teamId));
    }

    /**
     * Gets the list of the variables which need to be encrypted when we store them to database.
     *
     * @return  array Returns the array of variables looks like array(variablename => true);
     */
    private static function getEncryptedVariables()
    {
        if (!isset(self::$encryptedVariables)) {
            $cfg = array(
                SERVER_PLATFORMS::CLOUDSTACK . "." . Modules_Platforms_Cloudstack::API_KEY,
                SERVER_PLATFORMS::CLOUDSTACK . "."  . Modules_Platforms_Cloudstack::API_URL,
                SERVER_PLATFORMS::CLOUDSTACK . "."  . Modules_Platforms_Cloudstack::SECRET_KEY,

                SERVER_PLATFORMS::IDCF . "." . Modules_Platforms_Idcf::API_KEY,
                SERVER_PLATFORMS::IDCF . "." . Modules_Platforms_Idcf::API_URL,
                SERVER_PLATFORMS::IDCF . "." . Modules_Platforms_Idcf::SECRET_KEY,

                SERVER_PLATFORMS::UCLOUD . "." . Modules_Platforms_uCloud::API_KEY,
                SERVER_PLATFORMS::UCLOUD . "." . Modules_Platforms_uCloud::API_URL,
                SERVER_PLATFORMS::UCLOUD . "." . Modules_Platforms_uCloud::SECRET_KEY,

                SERVER_PLATFORMS::OPENSTACK . "." . Modules_Platforms_Openstack::API_KEY,
                SERVER_PLATFORMS::OPENSTACK . "." . Modules_Platforms_Openstack::AUTH_TOKEN,
                SERVER_PLATFORMS::OPENSTACK . "." . Modules_Platforms_Openstack::KEYSTONE_URL,
                SERVER_PLATFORMS::OPENSTACK . "." . Modules_Platforms_Openstack::PASSWORD,
                SERVER_PLATFORMS::OPENSTACK . "." . Modules_Platforms_Openstack::TENANT_NAME,
                SERVER_PLATFORMS::OPENSTACK . "." . Modules_Platforms_Openstack::USERNAME,

                SERVER_PLATFORMS::RACKSPACENG_UK . "." . Modules_Platforms_Openstack::API_KEY,
                SERVER_PLATFORMS::RACKSPACENG_UK . "." . Modules_Platforms_Openstack::AUTH_TOKEN,
                SERVER_PLATFORMS::RACKSPACENG_UK . "." . Modules_Platforms_Openstack::KEYSTONE_URL,
                SERVER_PLATFORMS::RACKSPACENG_UK . "." . Modules_Platforms_Openstack::PASSWORD,
                SERVER_PLATFORMS::RACKSPACENG_UK . "." . Modules_Platforms_Openstack::TENANT_NAME,
                SERVER_PLATFORMS::RACKSPACENG_UK . "." . Modules_Platforms_Openstack::USERNAME,

                SERVER_PLATFORMS::RACKSPACENG_US . "." . Modules_Platforms_Openstack::API_KEY,
                SERVER_PLATFORMS::RACKSPACENG_US . "." . Modules_Platforms_Openstack::AUTH_TOKEN,
                SERVER_PLATFORMS::RACKSPACENG_US . "." . Modules_Platforms_Openstack::KEYSTONE_URL,
                SERVER_PLATFORMS::RACKSPACENG_US . "." . Modules_Platforms_Openstack::PASSWORD,
                SERVER_PLATFORMS::RACKSPACENG_US . "." . Modules_Platforms_Openstack::TENANT_NAME,
                SERVER_PLATFORMS::RACKSPACENG_US . "." . Modules_Platforms_Openstack::USERNAME,

                Modules_Platforms_Ec2::ACCESS_KEY,
                Modules_Platforms_Ec2::ACCOUNT_ID,
                Modules_Platforms_Ec2::CERTIFICATE,
                Modules_Platforms_Ec2::PRIVATE_KEY,
                Modules_Platforms_Ec2::SECRET_KEY,

                Modules_Platforms_Eucalyptus::ACCESS_KEY,
                Modules_Platforms_Eucalyptus::ACCOUNT_ID,
                Modules_Platforms_Eucalyptus::CERTIFICATE,
                Modules_Platforms_Eucalyptus::CLOUD_CERTIFICATE,
                Modules_Platforms_Eucalyptus::EC2_URL,
                Modules_Platforms_Eucalyptus::PRIVATE_KEY,
                Modules_Platforms_Eucalyptus::S3_URL,
                Modules_Platforms_Eucalyptus::SECRET_KEY,

                Modules_Platforms_GoogleCE::ACCESS_TOKEN,
                Modules_Platforms_GoogleCE::CLIENT_ID,
                Modules_Platforms_GoogleCE::KEY,
                Modules_Platforms_GoogleCE::PROJECT_ID,
                Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME,

                Modules_Platforms_Nimbula::API_URL,
                Modules_Platforms_Nimbula::IMAGE_LIST_ENTRY_VALUE,
                Modules_Platforms_Nimbula::PASSWORD,
                Modules_Platforms_Nimbula::USERNAME,

                Modules_Platforms_Rackspace::API_KEY,
                Modules_Platforms_Rackspace::IS_MANAGED,
                Modules_Platforms_Rackspace::USERNAME,
            );
            self::$encryptedVariables = array_fill_keys($cfg, true);
        }
        return self::$encryptedVariables;
    }

    /**
     * Gets array of the linked variables
     *
     * @param   string  $linkid  optional If provided it will return variables from given group id
     * @return  array If linkid is null it will return array looks like array(variable => linkid).
     *                If linkid is provided it will return list of the linked variables
     *                from the specified group. array(variable1, variable2, ..., variableN)
     */
    private static function getLinkedVariables($linkid = null)
    {
        static $ret = array(), $rev = array();
        if (empty($ret)) {
            //Performs at once
            $ret = array(
                SERVER_PLATFORMS::EC2 => array(
                    Modules_Platforms_Ec2::ACCESS_KEY,
                    Modules_Platforms_Ec2::SECRET_KEY,
                    Modules_Platforms_Ec2::CERTIFICATE,
                    Modules_Platforms_Ec2::PRIVATE_KEY,
                    Modules_Platforms_Ec2::ACCOUNT_ID,
                ),
                SERVER_PLATFORMS::GCE => array(
                    Modules_Platforms_GoogleCE::ACCESS_TOKEN,
                    Modules_Platforms_GoogleCE::CLIENT_ID,
                    Modules_Platforms_GoogleCE::KEY,
                    Modules_Platforms_GoogleCE::PROJECT_ID,
                    Modules_Platforms_GoogleCE::RESOURCE_BASE_URL,
                    Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME
                ),
                'enabledPlatforms' => array()
            );

            foreach (array_keys(SERVER_PLATFORMS::getList()) as $value) {
                $ret['enabledPlatforms'][] = "{$value}.is_enabled";
            }

            foreach (array(SERVER_PLATFORMS::IDCF,
                SERVER_PLATFORMS::UCLOUD,
                SERVER_PLATFORMS::CLOUDSTACK) as $platform) {
                $ret[$platform] = array(
                    $platform . "." . Modules_Platforms_Cloudstack::ACCOUNT_NAME,
                    $platform . "." . Modules_Platforms_Cloudstack::API_KEY,
                    $platform . "." . Modules_Platforms_Cloudstack::API_URL,
                    $platform . "." . Modules_Platforms_Cloudstack::DOMAIN_ID,
                    $platform . "." . Modules_Platforms_Cloudstack::DOMAIN_NAME,
                    $platform . "." . Modules_Platforms_Cloudstack::SECRET_KEY,
                    $platform . "." . Modules_Platforms_Cloudstack::SHARED_IP,
                    $platform . "." . Modules_Platforms_Cloudstack::SHARED_IP_ID,
                    $platform . "." . Modules_Platforms_Cloudstack::SHARED_IP_INFO,
                    $platform . "." . Modules_Platforms_Cloudstack::SZR_PORT_COUNTER
                );
            }

            foreach (array(SERVER_PLATFORMS::OPENSTACK,
                           SERVER_PLATFORMS::RACKSPACENG_UK,
                           SERVER_PLATFORMS::RACKSPACENG_US) as $platform) {
                $ret[$platform] = array(
                    $platform . "." . Modules_Platforms_Openstack::API_KEY,
                    $platform . "." . Modules_Platforms_Openstack::AUTH_TOKEN,
                    $platform . "." . Modules_Platforms_Openstack::KEYSTONE_URL,
                    $platform . "." . Modules_Platforms_Openstack::PASSWORD,
                    $platform . "." . Modules_Platforms_Openstack::TENANT_NAME,
                    $platform . "." . Modules_Platforms_Openstack::USERNAME,
                );
            }
            //Computes fast access keys
            foreach ($ret as $platform => $linkedKeys) {
                foreach ($linkedKeys as $variable) {
                    $rev[$variable] = $platform;
                }
            }
        }
        return $linkid !== null ? (isset($ret[$linkid]) ? $ret[$linkid] : null) : $rev;
    }
}