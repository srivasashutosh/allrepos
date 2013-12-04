<?php

class ServerSnapshotDetails
{
    public function getOsName()
    {

    }

    public function getSoftwareList()
    {

    }
}

class BundleTask
{
    public $id;
    public $clientId;
    public $serverId;
    public $envId;
    public $replaceType;
    public $prototypeRoleId;
    public $status;
    public $platform;
    public $roleName;
    public $failureReason;
    public $bundleType;
    public $removePrototypeRole;
    public $dateAdded;
    public $dateStarted;
    public $dateFinished;
    public $snapshotId;
    public $description;
    public $roleId;
    public $farmId;
    public $cloudLocation;

    public $osFamily;
    public $osName;
    public $osVersion;

    /**
     * @var \ADODB_mysqli
     */
    private $Db;

    private $tz;
    private $metaData;

    private static $FieldPropertyMap = array(
        'id'			=> 'id',
        'client_id'		=> 'clientId',
        'env_id'		=> 'envId',
        'prototype_role_id'	=> 'prototypeRoleId',
        'server_id' 	=> 'serverId',
        'replace_type' 	=> 'replaceType',
        'status'		=> 'status',
        'platform'		=> 'platform',
        'rolename'		=> 'roleName',
        'failure_reason'=> 'failureReason',
        'remove_proto_role'	=> 'removePrototypeRole',
        'bundle_type'	=> 'bundleType',
        'dtadded'		=> 'dateAdded',
        'dtstarted'		=> 'dateStarted',
        'dtfinished'	=> 'dateFinished',
        'snapshot_id'	=> 'snapshotId',
        'description'	=> 'description',
        'role_id'		=> 'roleId',
        'farm_id'		=> 'farmId',
        'cloud_location'=> 'cloudLocation',
        'meta_data'		=> 'metaData',
        'os_family'		=> 'osFamily',
        'os_name'		=> 'osName',
        'os_version'	=> 'osVersion'
    );

    public function __construct($id)
    {
        $this->id = $id;
        $this->Db = \Scalr::getDb();
    }

    public function Log($message)
    {
        if ($this->id)
        {
            try
            {
                $this->Db->Execute("INSERT INTO bundle_task_log SET
                    bundle_task_id	= ?,
                    dtadded			= NOW(),
                    message			= ?
                ", array($this->id, $message));
            }
            catch(ADODB_Exception $e){}
        }
    }

    public function setDate($dt)
    {
        switch ($dt)
        {
            case "finished":

                $this->dateFinished = date("Y-m-d H:i:s");

                break;

            case "added":

                $this->dateAdded = date("Y-m-d H:i:s");

                break;

            case "started":

                if (!$this->dateStarted)
                   $this->dateStarted = date("Y-m-d H:i:s");

                break;
        }
    }

    public static function GenerateRoleName($DBFarmRole, $DBServer)
    {
        $db = \Scalr::getDb();

        $n = $DBFarmRole->GetRoleObject()->name;
        preg_match('/^([A-Za-z0-9-]+)-([0-9]+)-([0-9]+)$/si', $n, $m);
        if ($m[0] == $n)
        {
            if (date("Ymd") != $m[2])
            {
                $name = "{$m[1]}-".date("Ymd")."-01";
                $i = 1;
            }
            else
            {
                $s = $m[3]++;
                $i = $s;
                $s = ($s < 10) ? "0{$s}" : $s;
                $name = "{$m[1]}-{$m[2]}-{$s}";
            }
        }
        else
        {
            $name = "{$n}-".date("Ymd")."-01";
            $i = 1;
        }

        $role = $db->GetOne("SELECT id FROM roles WHERE name=? AND env_id=?", array($name, $DBServer->envId));
        if ($role)
        {
            while ($role)
            {
                   $i++;
                preg_match('/^([A-Za-z0-9-]+)-([0-9]+)-([0-9]+)$/si', $name, $m);
                $s = ($i < 10) ? "0{$i}" : $i;
                $name = "{$m[1]}-{$m[2]}-{$s}";

                $role = $db->GetOne("SELECT id FROM roles WHERE name=? AND env_id=?", array($name, $DBServer->envId));
            }
        }

        return $name;
    }

    /**
     * @return ServerSnapshotDetails
     * Enter description here ...
     */
    public function getSnapshotDetails()
    {
        return unserialize($this->metaData);
    }

    public function getOsDetails()
    {
        $retval = new stdClass();

        switch ($this->osFamily) {
            //TODO: Investigate real values for generation/version/name
            /*
            $family = 'windows';
            $generation = '2008';
            $version = '2008';
            $name = 'Windows 2008 Server';
             */
            case "windows":
                $retval->family = "windows";
                $retval->generation = $this->osFamily;
                $retval->version = $this->osFamily;
                $retval->name = $this->osFamily;
                break;
            case "ubuntu":
                $retval->family = $this->osFamily;
                $retval->generation = $this->osVersion;
                $retval->version = $this->osVersion;
                $retval->name = "Ubuntu {$retval->version} ".ucfirst($this->osName);
                break;
            case "centos":
                $retval->family = $this->osFamily;
                $retval->generation = (int)substr($this->osVersion, 0, 1);
                $retval->version = $this->osVersion;
                $retval->name = "CentOS {$retval->version} Final";
                break;
            case "amazon":
                $retval->family = $this->osFamily;
                $retval->generation = (int)substr($this->osVersion, 0, 1);
                $retval->version = $this->osVersion;
                $retval->name = "Amazon Linux {$this->osName}";
                break;
            case "gcel":
                $retval->family = $this->osFamily;
                $retval->generation = $this->osVersion;
                $retval->version = $this->osVersion;
                $retval->name = "GCEL 12.04";
                break;
            case "oel":
                $retval->family = $this->osFamily;
                $retval->generation = (int)substr($this->osVersion, 0, 1);
                $retval->version = $this->osVersion;
                $retval->name = "Oracle Enterprise Linux Server {$this->osVersion}";
                if ($retval->generation == 5)
                    $retval->name .= " Tikanga";
                elseif ($retval->generation == 6)
                    $retval->name .= " Santiago";
                break;
            case "redhat":
                $retval->family = $this->osFamily;
                $retval->generation = (int)substr($this->osVersion, 0, 1);
                $retval->version = $this->osVersion;
                $retval->name = "Redhat {$this->osVersion}";
                if ($retval->generation == 5)
                    $retval->name .= " Tikanga";
                elseif ($retval->generation == 6)
                    $retval->name .= " Santiago";
                break;
            case "debian":
                $retval->family = $this->osFamily;
                $retval->generation = (int)substr($this->osVersion, 0, 1);
                $retval->version = $this->osVersion;
                $retval->name = "Debian {$this->osVersion}";
                if ($retval->generation == 5)
                    $retval->name .= " Lenny";
                elseif ($retval->generation == 6)
                    $retval->name .= " Squeeze";
                break;
        }

        return $retval;
       }

    public function setMetaData($data)
    {
        $currentMetaData = $this->getSnapshotDetails();
        $data = array_merge((array)$currentMetaData, $data);

        $this->metaData = serialize($data);
    }

    public function SnapshotCreationComplete($snapshotId, $metaData=array())
    {
        $this->snapshotId = $snapshotId;
        $this->status = SERVER_SNAPSHOT_CREATION_STATUS::CREATING_ROLE;
        $this->setMetaData($metaData);

        $this->Log(sprintf(_("Snapshot creation complete. SnapshotID: '%s'. Bundle task status changed to: %s"),
            $snapshotId, $this->status
        ));

        $this->Save();
    }

    public function SnapshotCreationFailed($failed_reason)
    {
        if ($this->status == SERVER_SNAPSHOT_CREATION_STATUS::REPLACING_SERVERS)
            $replacingServers = true;

        $this->status = SERVER_SNAPSHOT_CREATION_STATUS::FAILED;

        $this->failureReason = $failed_reason;

        if ($this->farmId)
        {
            try {
                $DBFarm = DBFarm::LoadByID($this->farmId);
                if ($DBFarm->Status == FARM_STATUS::SYNCHRONIZING && !$DBFarm->TermOnSyncFail)
                {
                    $this->Db->Execute("UPDATE farms SET status=? WHERE id=?", array(
                        FARM_STATUS::RUNNING,
                        $this->farmId
                    ));

                    $this->Log(sprintf(_("Farm status set to Running")));
                }
            } catch (Exception $e) {}
        }

        try {
            $dbServer = DBServer::LoadByID($this->serverId);

            if ($replacingServers) {
                try {
                    $dbFarmRole = $dbServer->GetFarmRoleObject();

                    $dbFarmRole->NewRoleID = NULL;
                    $dbFarmRole->Save();

                    $dbServer->roleId = $dbFarmRole->RoleID;
                    $dbServer->Save();

                    $this->Db->Execute("UPDATE servers SET status=? WHERE role_id = ? AND replace_server_id IS NOT NULL AND farm_roleid=?", array(
                        SERVER_STATUS::PENDING_TERMINATE,
                        $this->roleId,
                        $dbFarmRole->ID
                    ));

                } catch (Exception $e) {
                    //TODO:
                }
            }

            /** Terminate server **/
            if ($dbServer->status == SERVER_STATUS::TEMPORARY) {
                try {
                    if (!$dbServer->GetProperty(SERVER_PROPERTIES::SZR_IMPORTING_LEAVE_ON_FAIL) && $dbServer->GetCloudServerID()) {
                        $this->Log(sprintf(_("Terminating temporary server...")));
                        PlatformFactory::NewPlatform($dbServer->platform)->TerminateServer($dbServer);
                        $dbServer->status = SERVER_STATUS::PENDING_TERMINATE;
                        $dbServer->save();
                        Scalr_Server_History::init($dbServer)->markAsTerminated("RoleBuilder temporary server", true);
                    }
                } catch (Exception $e) {}
            }

            if ($dbServer->status == SERVER_STATUS::IMPORTING) {
                $this->Log(sprintf(_("Removing import server record from database...")));
                $dbServer->Delete();
            }
        } catch (Exception $e) {}

        $this->Log(sprintf(_("Snapshot creation failed. Reason: %s. Bundle task status changed to: %s"), $failed_reason, $this->status));

        $this->Save();
    }

    private function Unbind () {
        $row = array();
        foreach (self::$FieldPropertyMap as $field => $property) {
            $row[$field] = $this->{$property};
        }

        return $row;
    }

    function Save () {

        $row = $this->Unbind();
        unset($row['id']);

        // Prepare SQL statement
        $set = array();
        $bind = array();
        foreach ($row as $field => $value) {
            $set[] = "`$field` = ?";
            $bind[] = $value;
        }
        $set = join(', ', $set);

        try	{
            // Perform Update
            $bind[] = $this->id;
            $this->Db->Execute("UPDATE bundle_tasks SET $set WHERE id = ?", $bind);

        } catch (Exception $e) {
            throw new Exception ("Cannot save bundle task. Error: " . $e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * @param ServerSnapshotCreateInfo $ServerSnapshotCreateInfo
     * @return BundleTask
     */
    public static function Create(ServerSnapshotCreateInfo $ServerSnapshotCreateInfo, $isRoleBuilder = false)
    {
        $db = \Scalr::getDb();

        $db->Execute("INSERT INTO bundle_tasks SET
            client_id	= ?,
            env_id		= ?,
            server_id	= ?,
            farm_id		= ?,
            prototype_role_id	= ?,
            replace_type		= ?,
            remove_proto_role	= ?,
            status		= ?,
            platform	= ?,
            rolename	= ?,
            description	= ?,
            cloud_location = ?
        ", array(
            $ServerSnapshotCreateInfo->DBServer->clientId,
            $ServerSnapshotCreateInfo->DBServer->envId,
            $ServerSnapshotCreateInfo->DBServer->serverId,
            $ServerSnapshotCreateInfo->DBServer->farmId,
            $ServerSnapshotCreateInfo->DBServer->roleId,
            $ServerSnapshotCreateInfo->replaceType,
            (int)$ServerSnapshotCreateInfo->removePrototypeRole,
            (!$isRoleBuilder) ? SERVER_SNAPSHOT_CREATION_STATUS::PENDING : SERVER_SNAPSHOT_CREATION_STATUS::STARING_SERVER,
            $ServerSnapshotCreateInfo->DBServer->platform,
            $ServerSnapshotCreateInfo->roleName,
            $ServerSnapshotCreateInfo->description,
            $ServerSnapshotCreateInfo->DBServer->GetCloudLocation()
        ));

        $bundleTaskId = $db->Insert_Id();

        $task = self::LoadById($bundleTaskId);

        $metaData = array();

        if ($ServerSnapshotCreateInfo->rootVolumeSize)
            $metaData['rootVolumeSize'] = $ServerSnapshotCreateInfo->rootVolumeSize;

        if ($ServerSnapshotCreateInfo->noServersReplace)
            $metaData['noServersReplace'] = 1;


        $task->setMetaData($metaData);
        $task->setDate('added');

        $task->save();

        $task->Log(sprintf(_("Bundle task created. ServerID: %s, FarmID: %s, Platform: %s."),
            $ServerSnapshotCreateInfo->DBServer->serverId,
            ($ServerSnapshotCreateInfo->DBServer->farmId) ? $ServerSnapshotCreateInfo->DBServer->farmId : '-',
            $ServerSnapshotCreateInfo->DBServer->platform
        ));

        $task->Log(sprintf(_("Bundle task status: %s"),
            $task->status
        ));

        if ($task->status == SERVER_SNAPSHOT_CREATION_STATUS::PENDING) {
            //TODO:
        }
        else {
            $task->Log(sprintf(_("Waiting for server...")));
        }

        return $task;
    }

    /**
     *
     * @param integer $id
     * @return BundleTask
     */
    public static function LoadById($id)
    {
        $db = \Scalr::getDb();

        $taskinfo = $db->GetRow("SELECT * FROM bundle_tasks WHERE id=?", array($id));
        if (!$taskinfo)
            throw new Exception(sprintf(_("Bundle task ID#%s not found in database"), $id));

        $task = new BundleTask($id);
        foreach(self::$FieldPropertyMap as $k=>$v)
        {
            if (isset($taskinfo[$k]))
                $task->{$v} = $taskinfo[$k];
        }

        return $task;
    }
}
