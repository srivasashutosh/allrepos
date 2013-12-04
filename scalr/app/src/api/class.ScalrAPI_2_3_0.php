<?php

    class ScalrAPI_2_3_0 extends ScalrAPI_2_2_0
    {
        public function FireCustomEvent($ServerID, $EventName, array $Params = array())
        {
            $isEventExist = $this->db->GetOne("
                SELECT id FROM event_definitions
                WHERE name = ? AND env_id = ?
            ", array(
                $EventName,
                $this->Environment->id
            ));

            $dbServer = DBServer::LoadByID($ServerID);
            if ($dbServer->envId != $this->Environment->id)
                throw new Exception(sprintf("Server ID #%s not found", $ServerID));

            if ($isEventExist) {
                $event = new CustomEvent($dbServer, $EventName, (array)$Params);
            } else
                throw new Exception(sprintf("Event %s is not defined", $EventName));

            Scalr::FireEvent($dbServer->farmId, $event);

            $response = $this->CreateInitialResponse();
            $response->EventID = $event->GetEventID();

            return $response;
        }

        public function FarmGetDetails($FarmID) {
            $response = parent::FarmGetDetails($FarmID);

            try {
                $DBFarm = DBFarm::LoadByID($FarmID);
                if ($DBFarm->EnvID != $this->Environment->id)
                    throw new Exception("N");
            }
            catch(Exception $e) {
                throw new Exception(sprintf("Farm #%s not found", $FarmID));
            }

            $response->ID = $DBFarm->ID;
            $response->Name = $DBFarm->Name;
            $response->IsLocked = $DBFarm->GetSetting(DBFarm::SETTING_LOCK);
            if ($response->IsLocked == 1) {
                $response->LockComment = $DBFarm->GetSetting(DBFarm::SETTING_LOCK_COMMENT);
                try {
                    $response->LockedBy = Scalr_Account_User::init()->loadById($DBFarm->GetSetting(DBFarm::SETTING_LOCK_BY))->fullname;
                } catch (Exception $e) {}
            }

            foreach ($response->FarmRoleSet->Item as &$item) {

                $dbFarmRole = DBFarmRole::LoadByID($item->ID);

                $item->IsScalingEnabled = $dbFarmRole->GetSetting(DBFarmRole::SETTING_SCALING_ENABLED);
                $item->{"ScalingAlgorithmSet"} = new stdClass();
                $item->{"ScalingAlgorithmSet"}->Item = array();

                $metrics = $this->DB->GetAll("SELECT metric_id, name, dtlastpolled FROM `farm_role_scaling_metrics`
                        INNER JOIN scaling_metrics ON scaling_metrics.id = farm_role_scaling_metrics.metric_id WHERE farm_roleid = ?", array(
                    $item->ID
                ));
                foreach ($metrics as $metric) {
                    $itm = new stdClass();
                    $itm->MetricID =  $metric['id'];
                    $itm->MetricName =  $metric['name'];
                    $itm->DateLastPolled = $metric['dtlastpolled'];

                    $item->{"ScalingAlgorithmSet"}->Item[] = $itm;
                }
            }

            return $response;
        }

        public function FarmGetDnsEndpoints($FarmID)
        {
            try {
                $DBFarm = DBFarm::LoadByID($FarmID);
                if ($DBFarm->EnvID != $this->Environment->id)
                    throw new Exception("N");
            }
            catch(Exception $e) {
                throw new Exception(sprintf("Farm #%s not found", $FarmID));
            }

            $response = $this->CreateInitialResponse();

            $haveMysqlRole = (bool)$this->DB->GetOne("SELECT id FROM farm_roles WHERE role_id IN (SELECT role_id FROM role_behaviors WHERE behavior IN (?,?,?)) AND farmid=?",
                    array(ROLE_BEHAVIORS::MYSQL, ROLE_BEHAVIORS::MYSQL2, ROLE_BEHAVIORS::PERCONA, $FarmID)
            );
            if ($haveMysqlRole) {
                $response->mysql = new stdClass();
                $response->mysql->master = new stdClass();
                $response->mysql->master->private = "int.master.mysql.{$DBFarm->Hash}.scalr-dns.net";
                $response->mysql->master->public = "ext.master.mysql.{$DBFarm->Hash}.scalr-dns.net";
                $response->mysql->slave = new stdClass();
                $response->mysql->slave->private = "int.slave.mysql.{$DBFarm->Hash}.scalr-dns.net";
                $response->mysql->slave->public = "ext.slave.mysql.{$DBFarm->Hash}.scalr-dns.net";
            }

            $havePgRole = (bool)$this->DB->GetOne("SELECT id FROM farm_roles WHERE role_id IN (SELECT role_id FROM role_behaviors WHERE behavior=?) AND farmid=?",
                    array(ROLE_BEHAVIORS::POSTGRESQL, $FarmID)
            );
            if ($havePgRole) {
                $response->postgresql = new stdClass();
                $response->postgresql->master = new stdClass();
                $response->postgresql->master->private = "int.master.postgresql.{$DBFarm->Hash}.scalr-dns.net";
                $response->postgresql->master->public = "ext.master.postgresql.{$DBFarm->Hash}.scalr-dns.net";
                $response->postgresql->slave = new stdClass();
                $response->postgresql->slave->private = "int.slave.postgresql.{$DBFarm->Hash}.scalr-dns.net";
                $response->postgresql->slave->public = "ext.slave.postgresql.{$DBFarm->Hash}.scalr-dns.net";
            }

            $haveRedisRole = (bool)$this->DB->GetOne("SELECT id FROM farm_roles WHERE role_id IN (SELECT role_id FROM role_behaviors WHERE behavior=?) AND farmid=?",
                    array(ROLE_BEHAVIORS::REDIS, $FarmID)
            );
            if ($haveRedisRole) {
                $response->redis = new stdClass();
                $response->redis->master = new stdClass();
                $response->redis->master->private = "int.master.redis.{$DBFarm->Hash}.scalr-dns.net";
                $response->redis->master->public = "ext.master.redis.{$DBFarm->Hash}.scalr-dns.net";
                $response->redis->slave = new stdClass();
                $response->redis->slave->private = "int.slave.redis.{$DBFarm->Hash}.scalr-dns.net";
                $response->redis->slave->public = "ext.slave.redis.{$DBFarm->Hash}.scalr-dns.net";
            }

            return $response;
        }

        public function FarmClone($FarmID)
        {
            $response = $this->CreateInitialResponse();

            try
            {
                $DBFarm = DBFarm::LoadByID($FarmID);
                if ($DBFarm->EnvID != $this->Environment->id)
                    throw new Exception("N");
            }
            catch(Exception $e)
            {
                throw new Exception(sprintf("Farm #%s not found", $FarmID));
            }

            $farm = $DBFarm->cloneFarm(null, $this->user, $this->Environment->id);
            $response->FarmID = $farm->ID;

            return $response;
        }

        public function ScriptingLogsList($FarmID, $ServerID = null, $EventID = null, $StartFrom = 0, $RecordsLimit = 20)
        {
            $farminfo = $this->DB->GetRow("SELECT clientid FROM farms WHERE id=? AND env_id=?",
                array($FarmID, $this->Environment->id)
            );

            if (!$farminfo)
                throw new Exception(sprintf("Farm #%s not found", $FarmID));

            $sql = "SELECT * FROM scripting_log WHERE farmid='{$FarmID}'";
            if ($ServerID)
                $sql .= " AND server_id=".$this->DB->qstr($ServerID);

            if ($EventID)
                $sql .= " AND event_id=".$this->DB->qstr($EventID);

            $total = $this->DB->GetOne(preg_replace('/\*/', 'COUNT(*)', $sql, 1));

            $sql .= " ORDER BY id DESC";

            $start = $StartFrom ? (int) $StartFrom : 0;
            $limit = $RecordsLimit ? (int) $RecordsLimit : 20;
            $sql .= " LIMIT {$start}, {$limit}";

            $response = $this->CreateInitialResponse();
            $response->TotalRecords = $total;
            $response->StartFrom = $start;
            $response->RecordsLimit = $limit;
            $response->LogSet = new stdClass();
            $response->LogSet->Item = array();

            $rows = $this->DB->Execute($sql);
            while ($row = $rows->FetchRow())
            {
                $itm = new stdClass();
                $itm->ServerID = $row['server_id'];
                $itm->Message = $row['message'];
                $itm->Timestamp = strtotime($row['dtadded']);
                $itm->ScriptName = $row['script_name'];
                $itm->ExecTime = $row['exec_time'];
                $itm->ExecExitCode = $row['exec_exitcode'];

                if (stristr($row['event'], 'CustomEvent'))
                    $itm->Event = "Manual";
                elseif (stristr($row['event'], 'APIEvent'))
                    $itm->Event = "API";
                else
                    $itm->Event = $row['event'];

                $response->LogSet->Item[] = $itm;
            }

            return $response;
        }

        public function FarmRoleParametersList($FarmRoleID)
        {
            try
            {
                $DBFarmRole = DBFarmRole::LoadByID($FarmRoleID);
                $DBFarm = DBFarm::LoadByID($DBFarmRole->FarmID);
                if ($DBFarm->EnvID != $this->Environment->id)
                    throw new Exception("N");
            }
            catch(Exception $e)
            {
                throw new Exception(sprintf("FarmRole ID #%s not found", $FarmRoleID));
            }

            $response = $this->CreateInitialResponse();
            $response->ParamSet = new stdClass();
            $response->ParamSet->Item = array();

            $rParams = $DBFarmRole->GetRoleObject()->getParameters();
            foreach ($rParams as $p) {
                $val = $this->DB->GetOne("SELECT value FROM farm_role_options WHERE farm_roleid = ? AND hash = ?", array($DBFarmRole->ID, $p['hash']));
                if (!$val)
                    $val = $p['defval'];

                $itm = new stdClass();
                $itm->{"Name"} = $p['hash'];
                $itm->{"Value"} = $val;
                $itm->{"FullName"} = $p['name'];

                $response->ParamSet->Item[] = $itm;
            }

            return $response;
        }

        public function FarmRoleUpdateParameterValue($FarmRoleID, $ParamName, $ParamValue) {
            try
            {
                $DBFarmRole = DBFarmRole::LoadByID($FarmRoleID);
                $DBFarm = DBFarm::LoadByID($DBFarmRole->FarmID);
                if ($DBFarm->EnvID != $this->Environment->id)
                    throw new Exception("N");
            }
            catch(Exception $e)
            {
                throw new Exception(sprintf("FarmRole ID #%s not found", $FarmRoleID));
            }

            $updated = false;
            $rParams = $DBFarmRole->GetRoleObject()->getParameters();
            foreach ($rParams as $p) {
                if ($p['hash'] == $ParamName) {
                    $id = $this->DB->GetOne("SELECT id FROM farm_role_options WHERE farm_roleid = ? AND hash = ?", array($DBFarmRole->ID, $p['hash']));
                    if ($id) {
                        $updated = true;
                        $this->DB->Execute("UPDATE farm_role_options SET
                            value       = ? WHERE id = ?
                        ", array($ParamValue, $id));
                    } else {
                        $updated = true;
                        $this->DB->Execute("INSERT INTO farm_role_options SET
                            farmid      = ?,
                            farm_roleid = ?,
                            name        = ?,
                            value       = ?,
                            hash        = ?
                            ON DUPLICATE KEY UPDATE name = ?
                        ", array($DBFarm->ID, $DBFarmRole->ID, $p['name'], $ParamValue, $p['hash'], $p['name']));
                    }
                }
            }

            if (!$updated)
                throw new Exception(sprintf("Parameter '%s' not defined for specified role", array($ParamName)));

            $response = $this->CreateInitialResponse();
            $response->Result = true;

            return $response;
        }

        public function EnvironmentsList()
        {
            $response = $this->CreateInitialResponse();
            $response->EnvironmentSet = new stdClass();
            $response->EnvironmentSet->Item = array();


            $envs = $this->user->getEnvironments();
            foreach ($envs as $env)
            {
                $itm = new stdClass();
                $itm->{"ID"} = $env['id'];
                $itm->{"Name"} = $env['name'];

                $response->EnvironmentSet->Item[] = $itm;
            }

            return $response;
        }

        public function ServerGetExtendedInformation($ServerID)
        {
            $DBServer = DBServer::LoadByID($ServerID);
            if ($DBServer->envId != $this->Environment->id)
                throw new Exception(sprintf("Server ID #%s not found", $ServerID));

            $response = $this->CreateInitialResponse();

            $info = PlatformFactory::NewPlatform($DBServer->platform)->GetServerExtendedInformation($DBServer);

            $response->ServerInfo = new stdClass();
            $scalrProps = array(
                'ServerID' => $DBServer->serverId,
                'Platform' => $DBServer->platform,
                'RemoteIP' => ($DBServer->remoteIp) ? $DBServer->remoteIp : '' ,
                'LocalIP' => ($DBServer->localIp) ? $DBServer->localIp : '' ,
                'Status' => $DBServer->status,
                'Index' => $DBServer->index,
                'AddedAt' => $DBServer->dateAdded
            );
            foreach ($scalrProps as $k=>$v) {
                $response->ServerInfo->{$k} = $v;
            }


            $response->PlatformProperties = new stdClass();
            if (is_array($info) && count($info)) {
                foreach ($info as $name => $value) {
                    $name = str_replace(".", "_", $name);
                    $name = preg_replace("/[^A-Za-z0-9_-]+/", "", $name);

                    if ($name == 'MonitoringCloudWatch')
                        continue;

                    $response->PlatformProperties->{$name} = $value;
                }
            }

            $response->ScalrProperties = new stdClass();
            if (count($DBServer->GetAllProperties())) {
                $it = array();
                foreach ($DBServer->GetAllProperties() as $name => $value) {
                    $name = preg_replace("/[^A-Za-z0-9-]+/", "", $name);
                    $response->ScalrProperties->{$name} = $value;
                }
            }

            return $response;
        }

        public function GlobalVariableSet($ParamName, $ParamValue, $FarmRoleID = 0, $FarmID = 0)
        {
            try {
                if ($FarmRoleID != 0) {
                    $DBFarmRole = DBFarmRole::LoadByID($FarmRoleID);
                    $DBFarm = DBFarm::LoadByID($DBFarmRole->FarmID);
                    $scope = Scalr_Scripting_GlobalVariables::SCOPE_FARMROLE;
                } elseif ($FarmID != 0) {
                    $DBFarm = DBFarm::LoadByID($FarmID);
                    $scope = Scalr_Scripting_GlobalVariables::SCOPE_FARM;
                    $FarmRoleID = 0;
                } else
                    throw new Exception ("FarmID or FarmRoleID should be specitied");

                if ($DBFarm->EnvID != $this->Environment->id)
                    throw new Exception("N");
            }
            catch(Exception $e) {
                throw new Exception(sprintf("FarmRole ID #%s not found", $FarmRoleID));
            }

            $globalVariables = new Scalr_Scripting_GlobalVariables($this->Environment->id, $scope);
            $globalVariables->setValues(
                array(array(
                    'name' 	=> $ParamName,
                    'value'	=> $ParamValue,
                    'flagFinal' => 0,
                    'flagRequired' => 0,
                    'scope' => $scope
                )),
                0,
                $DBFarm->ID,
                $FarmRoleID
            );

            $response = $this->CreateInitialResponse();
            $response->Result = true;

            return $response;
        }

        public function GlobalVariablesList($ServerID = null, $FarmID = null, $FarmRoleID = null, $RoleID = null)
        {
            $response = $this->CreateInitialResponse();
            $response->VariableSet = new stdClass();
            $response->VariableSet->Item = array();

            if ($ServerID) {
                $DBServer = DBServer::LoadByID($ServerID);
                if ($DBServer->envId != $this->Environment->id)
                    throw new Exception(sprintf("Server ID #%s not found", $ServerID));

                $globalVariables = new Scalr_Scripting_GlobalVariables($this->Environment->id, Scalr_Scripting_GlobalVariables::SCOPE_FARMROLE);
                $vars = $globalVariables->listVariables($DBServer->roleId, $DBServer->farmId, $DBServer->farmRoleId);
            } elseif ($FarmID) {
                $DBFarm = DBFarm::LoadByID($FarmID);
                if ($DBFarm->EnvID != $this->Environment->id)
                    throw new Exception(sprintf("Farm ID #%s not found", $FarmID));

                $globalVariables = new Scalr_Scripting_GlobalVariables($this->Environment->id, Scalr_Scripting_GlobalVariables::SCOPE_FARM);
                $vars = $globalVariables->listVariables(null, $DBFarm->ID, null);
            } elseif ($RoleID) {
                $DBRole = DBRole::LoadByID($RoleID);
                if ($DBRole->envId != $this->Environment->id)
                    throw new Exception(sprintf("Role ID #%s not found", $RoleID));

                $globalVariables = new Scalr_Scripting_GlobalVariables($this->Environment->id, Scalr_Scripting_GlobalVariables::SCOPE_ROLE);
                $vars = $globalVariables->listVariables($RoleID, null, null);
            } elseif ($FarmRoleID) {
                $DBFarmRole = DBFarmRole::LoadByID($FarmRoleID);
                if ($DBFarmRole->GetFarmObject()->EnvID != $this->Environment->id)
                    throw new Exception(sprintf("FarmRole ID #%s not found", $FarmRoleID));

                $globalVariables = new Scalr_Scripting_GlobalVariables($this->Environment->id, Scalr_Scripting_GlobalVariables::SCOPE_FARMROLE);
                $vars = $globalVariables->listVariables($DBFarmRole->RoleID, $DBFarmRole->FarmID, $DBFarmRole->ID);
            } else {
                $globalVariables = new Scalr_Scripting_GlobalVariables($this->Environment->id, Scalr_Scripting_GlobalVariables::SCOPE_ENVIRONMENT);
                $vars = $globalVariables->listVariables();
            }

            foreach ($vars as $k => $v) {
                $itm = new stdClass();
                $itm->{"Name"} = $k;
                $itm->{"Value"} = $v;

                $response->VariableSet->Item[] = $itm;
            }

            return $response;
        }

        public function DmSourcesList()
        {
            $response = $this->CreateInitialResponse();
            $response->SourceSet = new stdClass();
            $response->SourceSet->Item = array();

            $rows = $this->DB->Execute("SELECT * FROM dm_sources WHERE env_id=?", array($this->Environment->id));
            while ($row = $rows->FetchRow())
            {
                $itm = new stdClass();
                $itm->{"ID"} = $row['id'];
                $itm->{"Type"} = $row['type'];
                $itm->{"URL"} = $row['url'];
                $itm->{"AuthType"} = $row['auth_type'];

                $response->SourceSet->Item[] = $itm;
            }

            return $response;
        }

        public function DmSourceCreate($Type, $URL, $AuthLogin=null, $AuthPassword=null)
        {

            $source = Scalr_Model::init(Scalr_Model::DM_SOURCE);

            $authInfo = new stdClass();
            if ($Type == Scalr_Dm_Source::TYPE_SVN)
            {
                $authInfo->login = $AuthLogin;
                $authInfo->password	= $AuthPassword;
                $authType = Scalr_Dm_Source::AUTHTYPE_PASSWORD;
            }

            if (Scalr_Dm_Source::getIdByUrlAndAuth($URL, $authInfo))
                throw new Exception("Source already exists in database");

            $source->envId = $this->Environment->id;

            $source->url = $URL;
            $source->type = $Type;
            $source->authType = $authType;
            $source->setAuthInfo($authInfo);

            $source->save();

            $response = $this->CreateInitialResponse();
            $response->SourceID = $source->id;

            return $response;
        }

        public function DmApplicationCreate($Name, $SourceID, $PreDeployScript=null, $PostDeployScript=null)
        {
            $application = Scalr_Model::init(Scalr_Model::DM_APPLICATION);
            $application->envId = $this->Environment->id;

            if (Scalr_Dm_Application::getIdByNameAndSource($Name, $SourceID))
                throw new Exception("Application already exists in database");

            $application->name = $Name;
            $application->sourceId = $SourceID;

            $application->setPreDeployScript($PreDeployScript);
            $application->setPostDeployScript($PostDeployScript);

            $application->save();

            $response = $this->CreateInitialResponse();
            $response->ApplicationID = $application->id;

            return $response;
        }

        public function DmApplicationsList()
        {
            $response = $this->CreateInitialResponse();
            $response->ApplicationSet = new stdClass();
            $response->ApplicationSet->Item = array();

            $rows = $this->DB->Execute("SELECT * FROM dm_applications WHERE env_id=?", array($this->Environment->id));
            while ($row = $rows->FetchRow())
            {
                $itm = new stdClass();
                $itm->{"ID"} = $row['id'];
                $itm->{"SourceID"} = $row['dm_source_id'];
                $itm->{"Name"} = $row['name'];
                //$itm->{"PreDeployScript"} = $row['pre_deploy_script'];
                //$itm->{"PostDeployScript"} = $row['post_deploy_script'];

                $response->ApplicationSet->Item[] = $itm;
            }

            return $response;
        }

        public function DmDeploymentTasksList($FarmRoleID = null, $ApplicationID = null, $ServerID = null)
        {
            $sql = "SELECT id FROM dm_deployment_tasks WHERE status !='".Scalr_Dm_DeploymentTask::STATUS_ARCHIVED."' AND env_id = '{$this->Environment->id}'";
            if ($FarmRoleID)
                $sql .= ' AND farm_role_id = ' . $this->DB->qstr($FarmRoleID);

            if ($ApplicationID)
                $sql .= ' AND dm_application_id = ' . $this->DB->qstr($ApplicationID);

            if ($ServerID)
                $sql .= ' AND server_id = ' . $this->DB->qstr($ServerID);

            $response = $this->CreateInitialResponse();
            $response->DeploymentTasksSet = new stdClass();
            $response->DeploymentTasksSet->Item = array();

            $rows = $this->DB->Execute($sql);
            while ($task = $rows->FetchRow()) {
                $deploymentTask = Scalr_Model::init(Scalr_Model::DM_DEPLOYMENT_TASK)->loadById($task['id']);

                $itm = new stdClass();
                $itm->ServerID = $deploymentTask->serverId;
                $itm->DeploymentTaskID = $deploymentTask->id;
                $itm->FarmRoleID = $deploymentTask->farmRoleId;
                $itm->RemotePath = $deploymentTask->remotePath;
                $itm->Status = $deploymentTask->status;

                $response->DeploymentTasksSet->Item[] = $itm;
            }

            return $response;
        }

        public function DmDeploymentTaskGetLog($DeploymentTaskID, $StartFrom = 0, $RecordsLimit = 20)
        {
            $deploymentTask = Scalr_Model::init(Scalr_Model::DM_DEPLOYMENT_TASK)->loadById($DeploymentTaskID);
            if ($deploymentTask->envId != $this->Environment->id)
                throw new Exception(sprintf("Deployment task #%s not found", $DeploymentTaskID));

            $response = $this->CreateInitialResponse();

            $sql = "SELECT * FROM dm_deployment_task_logs WHERE dm_deployment_task_id = " . $this->DB->qstr($DeploymentTaskID);

            $total = $this->DB->GetOne(preg_replace('/\*/', 'COUNT(*)', $sql, 1));

            $sql .= " ORDER BY id DESC";

            $start = $StartFrom ? (int) $StartFrom : 0;
            $limit = $RecordsLimit ? (int) $RecordsLimit : 20;
            $sql .= " LIMIT {$start}, {$limit}";

            $response = $this->CreateInitialResponse();
            $response->TotalRecords = $total;
            $response->StartFrom = $start;
            $response->RecordsLimit = $limit;
            $response->LogSet = new stdClass();
            $response->LogSet->Item = array();

            $rows = $this->DB->Execute($sql);
            while ($row = $rows->FetchRow())
            {
                $itm = new stdClass();
                $itm->Message = $row['message'];
                $itm->Timestamp = strtotime($row['dtadded']);

                $response->LogSet->Item[] = $itm;
            }

            return $response;
        }

        public function DmDeploymentTaskGetStatus($DeploymentTaskID)
        {
            $deploymentTask = Scalr_Model::init(Scalr_Model::DM_DEPLOYMENT_TASK)->loadById($DeploymentTaskID);
            if ($deploymentTask->envId != $this->Environment->id)
                throw new Exception(sprintf("Deployment task #%s not found", $DeploymentTaskID));

            $response = $this->CreateInitialResponse();
            $response->DeploymentTaskStatus = $deploymentTask->status;
            if ($deploymentTask->status == Scalr_Dm_DeploymentTask::STATUS_FAILED)
                $response->FailureReason = $deploymentTask->lastError;

            return $response;
        }

        public function DmApplicationDeploy($ApplicationID, $FarmRoleID, $RemotePath)
        {
            $application = Scalr_Model::init(Scalr_Model::DM_APPLICATION)->loadById($ApplicationID);
            if ($application->envId != $this->Environment->id)
                throw new Exception("Aplication not found in database");

            $dbFarmRole = DBFarmRole::LoadByID($FarmRoleID);
            if ($dbFarmRole->GetFarmObject()->EnvID != $this->Environment->id)
                throw new Exception("Farm Role not found in database");

            $servers = $dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING));

            if (count($servers) == 0)
                throw new Exception("There is no running servers on selected farm/role");

            $response = $this->CreateInitialResponse();
            $response->DeploymentTasksSet = new stdClass();
            $response->DeploymentTasksSet->Item = array();

            foreach ($servers as $dbServer) {
                $taskId = Scalr_Dm_DeploymentTask::getId($ApplicationID, $dbServer->serverId, $RemotePath);
                $deploymentTask = Scalr_Model::init(Scalr_Model::DM_DEPLOYMENT_TASK);

                if (!$taskId) {

                    try {
                        if (!$dbServer->IsSupported("0.7.38"))
                            throw new Exception("Scalr agent installed on this server doesn't support deployments. Please update it to the latest version");

                        $deploymentTask->create(
                            $FarmRoleID,
                            $ApplicationID,
                            $dbServer->serverId,
                            Scalr_Dm_DeploymentTask::TYPE_API,
                            $RemotePath,
                            $this->Environment->id
                        );
                    } catch (Exception $e) {
                        $itm = new stdClass();
                        $itm->ServerID = $dbServer->serverId;
                        $itm->ErrorMessage = $e->getMessage();

                        $response->DeploymentTasksSet->Item[] = $itm;

                        continue;
                    }
                } else {
                    $deploymentTask->loadById($taskId);
                    $deploymentTask->status = Scalr_Dm_DeploymentTask::STATUS_PENDING;
                    $deploymentTask->log("Re-deploying application. Status: pending");
                    $deploymentTask->save();
                }

                $itm = new stdClass();
                $itm->ServerID = $dbServer->serverId;
                $itm->DeploymentTaskID = $deploymentTask->id;
                $itm->FarmRoleID = $deploymentTask->farmRoleId;
                $itm->RemotePath = $deploymentTask->remotePath;
                $itm->Status = $deploymentTask->status;

                $response->DeploymentTasksSet->Item[] = $itm;
            }

            return $response;
        }
    }
?>