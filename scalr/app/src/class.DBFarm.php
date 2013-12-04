<?php

class DBFarm
{
    const SETTING_CRYPTO_KEY				= 'crypto.key';

    const SETTING_SZR_UPD_REPOSITORY		= 'szr.upd.repository';
    const SETTING_SZR_UPD_SCHEDULE			= 'szr.upd.schedule';

    const SETTING_LOCK                      = 'lock';
    const SETTING_LOCK_COMMENT              = 'lock.comment';
    const SETTING_LOCK_BY                   = 'lock.by';
    const SETTING_LOCK_RESTRICT             = 'lock.restrict';
    const SETTING_LOCK_UNLOCK_BY            = 'lock.unlock.by';

    const SETTING_EC2_VPC_REGION            = 'ec2.vpc.region';
    const SETTING_EC2_VPC_ID                = 'ec2.vpc.id';

    const SETTING_TIMEZONE                  = 'timezone';

    public
        $ID,
        $ClientID,
        $EnvID,
        $Name,
        $Hash,
        $Status,
        $Comments,
        $RolesLaunchOrder,
        $ScalarizrCertificate,
        $TermOnSyncFail,

        $createdByUserId,
        $createdByUserEmail,
        $changedByUserId,
        $changedTime
    ;

    private $DB,
            $environment;

    private $SettingsCache = array();

    private static $FieldPropertyMap = array(
        'id' 			=> 'ID',
        'clientid'		=> 'ClientID',
        'env_id'		=> 'EnvID',
        'name'			=> 'Name',
        'hash'			=> 'Hash',
        'status'		=> 'Status',
        'comments'		=> 'Comments',
        'scalarizr_cert'=> 'ScalarizrCertificate',
        'farm_roles_launch_order'	=> 'RolesLaunchOrder',
        'term_on_sync_fail'	=> 'TermOnSyncFail',

        'created_by_id' 	=> 'createdByUserId',
        'created_by_email'	=> 'createdByUserEmail',
        'changed_by_id'     => 'changedByUserId',
        'changed_time'      => 'changedTime'
    );

    /**
     * Constructor
     * @param $instance_id
     * @return void
     */
    public function __construct($id = null)
    {
        $this->ID = $id;
        $this->DB = \Scalr::getDb();
    }

    public function __sleep()
    {
        return array_values(self::$FieldPropertyMap);
    }

    public function __wakeup()
    {
        $this->DB = \Scalr::getDb();
    }

    //TODO: Rewrite this terrible code.
    public static function create($name, Scalr_Account_User $user, $envId)
    {
        $account = $user->getAccount();
        $account->validateLimit(Scalr_Limits::ACCOUNT_FARMS, 1);

        $db = \Scalr::getDb();

        $dbFarm = new self();
        $dbFarm->Status = FARM_STATUS::TERMINATED;
        $dbFarm->ClientID = $account->id;
        $dbFarm->EnvID = $envId;

        $dbFarm->createdByUserId = $user->getId();
        $dbFarm->createdByUserEmail = $user->getEmail();
        $dbFarm->changedByUserId = $user->getId();
        $dbFarm->changedTime = microtime();

        $dbFarm->Name = $name;
        $dbFarm->RolesLaunchOrder = 0;
        $dbFarm->Comments = "";

        $dbFarm->save();

        $dbFarm->SetSetting(DBFarm::SETTING_CRYPTO_KEY, Scalr::GenerateRandomKey(40));

        return $dbFarm;
    }

    public function cloneFarm($name = false, Scalr_Account_User $user, $envId)
    {
        $account = $user->getAccount();
        $account->validateLimit(Scalr_Limits::ACCOUNT_FARMS, 1);

        $definition = $this->getDefinition();

        if (!$name) {
            if (!stristr($definition->name, "(clone"))
                $name = $definition->name . ' (clone #1)';
            else {
                preg_match("/^(.*?)\(clone \#([0-9]*)\)$/si", $definition->name, $matches);
                $name = trim($matches[1])." (clone #".($matches[2]+1).")";
            }
        }

        $dbFarm = self::create($name, $user, $envId);

        $dbFarm->createdByUserId = $user->id;
        $dbFarm->createdByUserEmail = $user->getEmail();

        foreach($definition->roles as $index => $role) {
            $dbFarmRole = $dbFarm->AddRole(DBRole::loadById($role->roleId), $role->platform, $role->cloudLocation, $index+1);
            $oldRoleSettings = $dbFarmRole->GetAllSettings();
            $dbFarmRole->applyDefinition($role, true);

            Scalr_Helpers_Dns::farmUpdateRoleSettings($dbFarmRole, $oldRoleSettings, $dbFarmRole->GetAllSettings());

            /**
             * Platfrom specified updates
             */
            if ($dbFarmRole->Platform == SERVER_PLATFORMS::EC2) {
                Modules_Platforms_Ec2_Helpers_Ebs::farmUpdateRoleSettings($dbFarmRole, $oldRoleSettings, $dbFarmRole->GetAllSettings());
                Modules_Platforms_Ec2_Helpers_Eip::farmUpdateRoleSettings($dbFarmRole, $oldRoleSettings, $dbFarmRole->GetAllSettings());
                Modules_Platforms_Ec2_Helpers_Elb::farmUpdateRoleSettings($dbFarmRole, $oldRoleSettings, $dbFarmRole->GetAllSettings());
            }

            $dbFarmRolesList[] = $dbFarmRole;
            $usedPlatforms[$role->platform] = 1;
        }

        if ($usedPlatforms[SERVER_PLATFORMS::EC2])
            Modules_Platforms_Ec2_Helpers_Ec2::farmSave($dbFarm, $dbFarmRolesList);

        if ($usedPlatforms[SERVER_PLATFORMS::EUCALYPTUS])
            Modules_Platforms_Eucalyptus_Helpers_Eucalyptus::farmSave($dbFarm, $dbFarmRolesList);

        if ($usedPlatforms[SERVER_PLATFORMS::CLOUDSTACK])
            Modules_Platforms_Cloudstack_Helpers_Cloudstack::farmSave($dbFarm, $dbFarmRolesList);

        $dbFarm->save();

        return $dbFarm;
    }

    public function getDefinition()
    {
        $farmDefinition = new stdClass();
        $farmDefinition->name = $this->Name;
        $farmDefinition->roles = array();
        foreach ($this->GetFarmRoles() as $dbFarmRole) {
            $farmDefinition->roles[] = $dbFarmRole->getDefinition();
        }

        return $farmDefinition;
    }

    /**
     * @return Scalr_Environment
     */
    public function GetEnvironmentObject()
    {
        if (!$this->environment)
            $this->environment = Scalr_Model::init(Scalr_Model::ENVIRONMENT)->loadById($this->EnvID);

        return $this->environment;
    }

    /**
     *
     * @param integer $role_id
     * @return DBFarmRole
     */
    public function GetFarmRoleByRoleID($role_id)
    {
        $db_role = $this->DB->GetRow("SELECT id FROM farm_roles WHERE farmid=? AND role_id=?", array($this->ID, $role_id));
        if (!$db_role)
            throw new Exception(sprintf(_("Role #%s not assigned to farm #%"), $role_id, $this->ID));

        return DBFarmRole::LoadByID($db_role['id']);
    }

    /**
     *
     * @param string $behavior
     * @return <boolean, DBFarmRole>
     */
    public function GetFarmRoleByBehavior($behavior)
    {
        $farmRoleId = $this->DB->GetOne("SELECT id FROM farm_roles WHERE role_id IN (SELECT role_id FROM role_behaviors WHERE behavior=?) AND farmid=?",
            array($behavior, $this->ID)
        );

        return ($farmRoleId) ? DBFarmRole::LoadByID($farmRoleId) : false;
    }


    /**
     * @return DBFarmRole[]
     */
    public function GetFarmRoles()
    {
        $db_roles = $this->DB->GetAll("SELECT id FROM farm_roles WHERE farmid=? ORDER BY launch_index ASC", array($this->ID));
        $retval = array();
        foreach ($db_roles as $db_role)
            $retval[] = DBFarmRole::LoadByID($db_role['id']);

        return $retval;
    }

    public function GetMySQLInstances($only_master = false, $only_slaves = false)
    {
        $mysql_farm_role_id = $this->DB->GetOne("SELECT id FROM farm_roles WHERE role_id IN (SELECT role_id FROM role_behaviors WHERE behavior=?) AND farmid=?",
            array(ROLE_BEHAVIORS::MYSQL, $this->ID)
        );
        if ($mysql_farm_role_id)
        {
            $servers = $this->GetServersByFilter(array('status' => array(SERVER_STATUS::RUNNING, SERVER_STATUS::INIT), 'farm_roleid' => $mysql_farm_role_id));
            $retval = array();
            foreach ($servers as $DBServer)
            {
                if ($only_master && $DBServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER))
                    $retval[] = $DBServer;
                elseif ($only_slaves && !$DBServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER))
                    $retval[] = $DBServer;
                elseif (!$only_master && !$only_slaves)
                    $retval[] = $DBServer;
            }
        }
        else
            $retval = array();

        return $retval;
    }

    public function GetServersByFilter($filter_args = array(), $ufilter_args = array())
    {
        $sql = "SELECT server_id FROM servers WHERE `farm_id`=?";
        $args = array($this->ID);
        foreach ((array)$filter_args as $k=>$v)
        {
            if (is_array($v))
            {
                foreach ($v as $vv)
                    array_push($args, $vv);

                $sql .= " AND `{$k}` IN (".implode(",", array_fill(0, count($v), "?")).")";
            }
            else
            {
                $sql .= " AND `{$k}`=?";
                array_push($args, $v);
            }
        }

        foreach ((array)$ufilter_args as $k=>$v)
        {
            if (is_array($v))
            {
                foreach ($v as $vv)
                    array_push($args, $vv);

                $sql .= " AND `{$k}` NOT IN (".implode(",", array_fill(0, count($v), "?")).")";
            }
            else
            {
                $sql .= " AND `{$k}`!=?";
                array_push($args, $v);
            }
        }

        $res = $this->DB->GetAll($sql, $args);

        $retval = array();
        foreach ((array)$res as $i)
        {
            if ($i['server_id'])
                $retval[] = DBServer::LoadByID($i['server_id']);
        }

        return $retval;
    }

    /**
     * Returns all farm settings
     * @return unknown_type
     */
    public function GetAllSettings()
    {
        $settings = $this->DB->GetAll("SELECT * FROM farm_settings WHERE farmid=?", array($this->ID));

        $retval = array();
        foreach ($settings as $setting)
            $retval[$setting['name']] = $setting['value'];

        $this->SettingsCache = array_merge($this->SettingsCache, $retval);

        return $retval;
    }

    /**
     * @return DBFarmRole
     * @param DBRole $DBRole
     */
    public function AddRole(DBRole $DBRole, $platform, $cloudLocation, $launchIndex)
    {
        $this->DB->Execute("INSERT INTO farm_roles SET
            farmid=?, role_id=?, reboot_timeout=?, launch_timeout=?, status_timeout = ?, launch_index = ?, platform = ?, cloud_location=?", array(
            $this->ID,
            $DBRole->id,
            300,
            300,
            600,
            $launchIndex,
            $platform,
            $cloudLocation
        ));

        $farm_role_id = $this->DB->Insert_ID();

        $DBFarmRole = new DBFarmRole($farm_role_id);
        $DBFarmRole->FarmID = $this->ID;
        $DBFarmRole->RoleID = $DBRole->id;
        $DBFarmRole->Platform = $platform;
        $DBFarmRole->CloudLocation = $cloudLocation;

        $default_settings = array(
            DBFarmRole::SETTING_SCALING_MIN_INSTANCES => 1,
            DBFarmRole::SETTING_SCALING_MAX_INSTANCES => 1,
            DBFarmRole::SETTING_SCALING_POLLING_INTERVAL => 2,
            DBFarmRole::SETTING_EXCLUDE_FROM_DNS => false,
            DBFarmRole::SETTING_BALANCING_USE_ELB => false,
            //DBFarmRole::SETTING_AWS_AVAIL_ZONE => 'x-scalr-diff',
            DBFarmRole::SETTING_AWS_INSTANCE_TYPE => $DBRole->instanceType
        );

        foreach ($default_settings as $k => $v)
            $DBFarmRole->SetSetting($k, $v);

        return $DBFarmRole;
    }

    /**
     * Set farm setting
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function SetSetting($name, $value)
    {
        $Reflect = new ReflectionClass($this);
        $consts = array_values($Reflect->getConstants());
        if (in_array($name, $consts))
        {
            $this->DB->Execute("REPLACE INTO farm_settings SET `farmid`=?, `name`=?, `value`=?",
                array($this->ID, $name, $value)
            );

            $this->SettingsCache[$name] = $value;

            return true;
        }
        else
            throw new Exception("Unknown farm setting '{$name}'");
    }

    /**
     * Get Farm setting by name
     * @param string $name
     * @return mixed
     */
    public function GetSetting($name)
    {
        if (!isset($this->SettingsCache[$name])) {
            $this->SettingsCache[$name] = $this->DB->GetOne("
                SELECT `value` FROM `farm_settings` WHERE `farmid`=? AND `name` = ?
            ", array(
                $this->ID,
                $name
            ));
        }

        return $this->SettingsCache[$name];
    }

    /**
     * Check if farm is locked
     *
     * @param $throwException
     * @return bool
     * @throws Exception
     */
    public function isLocked($throwException = true)
    {
        if ($this->GetSetting(DBFarm::SETTING_LOCK)) {
            $message = $this->GetSetting(DBFarm::SETTING_LOCK_COMMENT);

            try {
                $userName = Scalr_Account_User::init()->loadById($this->getSetting(DBFarm::SETTING_LOCK_BY))->getEmail();
            } catch(Exception $e) {
                $userName = $this->getSetting(DBFarm::SETTING_LOCK_BY);
            }

            if ($message)
                $message = sprintf(' with comment: \'%s\'', $message);

            if ($throwException)
                throw new Exception(sprintf('Farm was locked by %s%s. Please unlock it first.', $userName, $message));
            else
                return sprintf('Farm was locked by %s%s.', $userName, $message);
        }

        return false;
    }

    /**
     * @param $userId integer
     * @param $comment string
     * @param $restrict bool
     */
    public function lock($userId, $comment, $restrict)
    {
        $this->SetSetting(DBFarm::SETTING_LOCK, 1);
        $this->SetSetting(DBFarm::SETTING_LOCK_BY, $userId);
        $this->SetSetting(DBFarm::SETTING_LOCK_COMMENT, $comment);
        $this->SetSetting(DBFarm::SETTING_LOCK_UNLOCK_BY, '');

        if ($this->createdByUserId && $restrict)
            $this->SetSetting(DBFarm::SETTING_LOCK_RESTRICT, 1);
    }

    /**
     * @param $userId integer
     */
    public function unlock($userId)
    {
        $this->SetSetting(DBFarm::SETTING_LOCK, '');
        $this->SetSetting(DBFarm::SETTING_LOCK_BY, '');
        $this->SetSetting(DBFarm::SETTING_LOCK_UNLOCK_BY, $userId);
        $this->SetSetting(DBFarm::SETTING_LOCK_COMMENT, '');
        $this->SetSetting(DBFarm::SETTING_LOCK_RESTRICT, '');
    }

    /**
     * Load DBInstance by database id
     * @param $id
     * @return DBFarm
     */
    static public function LoadByID($id)
    {
        $db = \Scalr::getDb();

        $farm_info = $db->GetRow("SELECT * FROM farms WHERE id=?", array($id));
        if (!$farm_info)
            throw new Exception(sprintf(_("Farm ID#%s not found in database"), $id));

        $DBFarm = new DBFarm($id);

        foreach(self::$FieldPropertyMap as $k=>$v)
        {
            if (isset($farm_info[$k]))
                $DBFarm->{$v} = $farm_info[$k];
        }

        return $DBFarm;
    }

    static public function LoadByIDOnlyName($id)
    {
        $db = \Scalr::getDb();

        $farm_info = $db->GetRow("SELECT name FROM farms WHERE id=?", array($id));

        return $farm_info['name'] ? $farm_info['name'] : '*removed farm*';
    }

    public function save()
    {
        if (!$this->ID) {
            $this->ID = 0;
            $this->Hash = substr(md5(uniqid(rand(), true)),0, 14);

            //Такой хуйни быть не должно. Убрать НАХУЙ Scalr_UI_Request отсюда!
            if (!$this->ClientID)
                $this->ClientID = Scalr_UI_Request::getInstance()->getUser()->getAccountId();

            if (!$this->EnvID)
                $this->EnvID = Scalr_UI_Request::getInstance()->getEnvironment()->id;
        }

        if ($this->DB->GetOne('SELECT id FROM farms WHERE name = ? AND env_id = ? AND id != ?', array($this->Name, $this->EnvID, $this->ID)))
            throw new Exception('This name already used');

        if (!$this->ID)
        {
            $this->DB->Execute("INSERT INTO farms SET
                status		= ?,
                name		= ?,
                clientid	= ?,
                env_id		= ?,
                hash		= ?,
                created_by_id = ?,
                created_by_email = ?,
                changed_by_id = ?,
                changed_time = ?,
                dtadded		= NOW(),
                farm_roles_launch_order = ?,
                comments = ?
            ", array(
                FARM_STATUS::TERMINATED,
                $this->Name,
                $this->ClientID,
                $this->EnvID,
                $this->Hash,
                $this->createdByUserId,
                $this->createdByUserEmail,
                $this->changedByUserId,
                $this->changedTime,
                $this->RolesLaunchOrder,
                $this->Comments
            ));

            $this->ID = $this->DB->Insert_ID();
        }
        else
        {
            $this->DB->Execute("UPDATE farms SET
                name		= ?,
                status		= ?,
                farm_roles_launch_order = ?,
                term_on_sync_fail = ?,
                comments = ?,
                changed_by_id = ?,
                changed_time = ?
            WHERE id = ?
            ", array(
                $this->Name,
                $this->Status,
                $this->RolesLaunchOrder,
                $this->TermOnSyncFail,
                $this->Comments,
                $this->changedByUserId,
                $this->changedTime,
                $this->ID
            ));
        }
    }
}
