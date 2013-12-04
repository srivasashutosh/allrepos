<?php

use Scalr\Server\Alerts;

class Scalr_UI_Controller_Servers extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'serverId';

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function getList(array $status = array())
    {
        $retval = array();

        $sql = "SELECT * FROM servers WHERE env_id = ".$this->db->qstr($this->getEnvironmentId());
        if ($this->getParam('farmId'))
            $sql .= " AND farm_id = ".$this->db->qstr($this->getParam('farmId'));

        if ($this->getParam('farmRoleId'))
            $sql .= " AND farm_roleid = ".$this->db->qstr($this->getParam('farmRoleId'));

        if (!empty($status))
            $sql .= "AND status IN ('".implode("','", $status)."')";

        $s = $this->db->execute($sql);
        while ($server = $s->fetchRow()) {
            $retval[$server['server_id']] = $server;
        }

        return $retval;
    }

    public function xTroubleshootAction()
    {
        $this->request->defineParams(array(
            'serverID'
        ));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $dbServer->status = SERVER_STATUS::TROUBLESHOOTING;
        $dbServer->Save();


        // Send before host terminate to the server to detach all used volumes.
        $msg = new Scalr_Messaging_Msg_BeforeHostTerminate($dbServer);

        if ($dbServer->farmRoleId != 0) {
            foreach (Scalr_Role_Behavior::getListForFarmRole($dbServer->GetFarmRoleObject()) as $behavior)
                $msg = $behavior->extendMessage($msg, $dbServer);
        }
        $dbServer->SendMessage($msg);

        Scalr::FireEvent($dbServer->farmId, new HostDownEvent($dbServer));

        Scalr_Server_History::init($dbServer)->markAsTerminated("Troubleshooting mode");

        $this->response->success();
    }

    public function xGetHealthDetailsAction()
    {
        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $client = Scalr_Net_Scalarizr_Client::getClient(
            $dbServer,
            Scalr_Net_Scalarizr_Client::NAMESPACE_SYSTEM,
            $dbServer->getPort(DBServer::PORT_API)
        );

        $data = array();

        try {
            $la = $client->loadAverage();
            $data['la'] = number_format($la[0], 2);
        } catch (Exception $e) {}

        try {
            $mem = $client->memInfo();
            $data['memory'] = array('total' => round($mem->total_real / 1024 / 1024, 1), 'free' => round(($mem->total_free+$mem->cached) / 1024 / 1024, 1));
        } catch (Exception $e) {}

        try {
            $cpu1 = $client->cpuStat();
            sleep(1);
            $cpu2 = $client->cpuStat();

            $dif['user'] = $cpu2->user - $cpu1->user;
            $dif['nice'] = $cpu2->nice - $cpu1->nice;
            $dif['sys'] =  $cpu2->system - $cpu1->system;
            $dif['idle'] = $cpu2->idle - $cpu1->idle;
            $total = array_sum($dif);
            foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 1);
            $data['cpu'] = $cpu;
        } catch (Exception $e) {}

        $this->response->data(array('data' => $data));
    }

    public function xImportWaitHelloAction()
    {
        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        if ($dbServer->status != SERVER_STATUS::IMPORTING)
            throw new Exception('Server is not in importing state');

        $row = $this->db->GetRow("SELECT * FROM messages WHERE server_id = ? AND type = ?",
            array($dbServer->serverId, "in"));

        if ($row) {
            $bundleTaskId = $this->db->GetOne(
                "SELECT id FROM bundle_tasks WHERE server_id = ? ORDER BY dtadded DESC LIMIT 1",
                array($dbServer->serverId)
            );
        }

        if ($bundleTaskId) {
            $this->response->success('Communication successfully established. Role creation process has been initialized');
            $this->response->data(array('bundleTaskId' => $bundleTaskId));
        } else {
            $this->response->failure();
        }
    }

    public function xImportStartAction()
    {
        $validator = new Scalr_Validator();

        if (!$this->getParam('remoteIp') && !$this->getParam('behavior'))
            $newImport = true;
        else
            $newImport = false;

        if (!$newImport) {
            if ($validator->validateDomain($this->getParam('remoteIp')) === true) {
                $remoteIp = @gethostbyname($this->getParam('remoteIp'));
            } else {
                $remoteIp = $this->getParam('remoteIp');
            }

            if ($validator->validateIp($remoteIp) !== true)
                $err['remoteIp'] = 'Server IP address is incorrect';

            // Find server in the database
            $existingServer = $this->db->GetRow("SELECT * FROM servers WHERE remote_ip = ?", array($remoteIp));
            if ($existingServer["client_id"] == $this->user->getAccountId())
                $err['remoteIp'] = sprintf(_("Server %s is already in Scalr with a server_id: %s"), $remoteIp, $existingServer["server_id"]);
            else if ($existingServer)
                $err['remoteIp'] = sprintf(_("Server with selected IP address cannot be imported"));
        }

        if ($this->getParam('ipAddress')) {
            if ($validator->validateDomain($this->getParam('ipAddress')) === true)
                $remoteIp = @gethostbyname($this->getParam('ipAddress'));
            else
                $remoteIp = $this->getParam('ipAddresss');

            if ($validator->validateIp($remoteIp) !== true)
                $err['ipAddresss'] = 'Server IP address is incorrect';
        }

        if ($validator->validateNotEmpty($this->getParam('roleName')) !== true)
            $err['roleName'] = 'Role name cannot be empty';

        if (strlen($this->getParam('roleName')) < 3)
            $err['roleName'] = _("Role name should be greater than 3 chars");

        if (! preg_match("/^[A-Za-z0-9-]+$/si", $this->getParam('roleName')))
            $err['roleName'] = _("Role name is incorrect");

        if ($this->db->GetOne("SELECT id FROM roles WHERE name=? AND (env_id = '0' OR env_id = ?)",
            array($this->getParam('roleName'), $this->getEnvironmentId()))
        )
            $err['roleName'] = 'Selected role name is already used. Please select another one.';

        if (count($err) == 0) {
            $cryptoKey = Scalr::GenerateRandomKey(40);

            $creInfo = new ServerCreateInfo($this->getParam('platform'), null, 0, 0);
            $creInfo->clientId = $this->user->getAccountId();
            $creInfo->envId = $this->getEnvironmentId();
            $creInfo->farmId = (int)$this->getParam('farmId');
            $creInfo->remoteIp = $remoteIp;
            $creInfo->SetProperties(array(
                SERVER_PROPERTIES::SZR_IMPORTING_ROLE_NAME => $this->getParam('roleName'),
                SERVER_PROPERTIES::SZR_IMPORTING_BEHAVIOR => $this->getParam('behavior'),
                SERVER_PROPERTIES::SZR_KEY => $cryptoKey,
                SERVER_PROPERTIES::SZR_KEY_TYPE => SZR_KEY_TYPE::PERMANENT,
                SERVER_PROPERTIES::SZR_VESION => "0.14.0",
                SERVER_PROPERTIES::SZR_IMPORTING_OS_FAMILY => $this->getParam('os')
            ));

            if ($this->getParam('platform') == SERVER_PLATFORMS::EUCALYPTUS)
                $creInfo->SetProperties(array(EUCA_SERVER_PROPERTIES::REGION => $this->getParam('cloudLocation')));

            if ($this->getParam('platform') == SERVER_PLATFORMS::RACKSPACE)
                $creInfo->SetProperties(array(RACKSPACE_SERVER_PROPERTIES::DATACENTER => $this->getParam('cloudLocation')));

            if (in_array($this->getParam('platform'), array(SERVER_PLATFORMS::OPENSTACK, SERVER_PLATFORMS::RACKSPACENG_UK, SERVER_PLATFORMS::RACKSPACENG_US)))
                $creInfo->SetProperties(array(OPENSTACK_SERVER_PROPERTIES::CLOUD_LOCATION => $this->getParam('cloudLocation')));

            if ($this->getParam('platform') == SERVER_PLATFORMS::IDCF || $this->getParam('platform') == SERVER_PLATFORMS::CLOUDSTACK)
                $creInfo->SetProperties(array(CLOUDSTACK_SERVER_PROPERTIES::CLOUD_LOCATION => $this->getParam('cloudLocation')));

            if ($this->getParam('platform') == SERVER_PLATFORMS::NIMBULA)
                $creInfo->SetProperties(array(NIMBULA_SERVER_PROPERTIES::CLOUD_LOCATION => 'nimbula-default'));

            $dbServer = DBServer::Create($creInfo, true);
            $this->response->data(array('serverId' => $dbServer->serverId));
        } else {
            $this->response->failure();
            $this->response->data(array('errors' => $err));
        }
    }

    public function importCheckAction()
    {
        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        if ($dbServer->status != SERVER_STATUS::IMPORTING)
            throw new Exception('Server is not in importing state');

        $cryptoKey = $dbServer->GetKey();

        $baseurl = \Scalr::config('scalr.endpoint.scheme') . "://" .
                   \Scalr::config('scalr.endpoint.host');

        if (!$dbServer->remoteIp) {
            $platform = (in_array($dbServer->platform, array(SERVER_PLATFORMS::OPENSTACK, SERVER_PLATFORMS::RACKSPACENG_UK, SERVER_PLATFORMS::RACKSPACENG_US))) ? SERVER_PLATFORMS::OPENSTACK : $dbServer->platform;

            $options = array(
                'server-id' 	=> $dbServer->serverId,
                'role-name' 	=> $dbServer->GetProperty(SERVER_PROPERTIES::SZR_IMPORTING_ROLE_NAME),
                'crypto-key' 	=> $cryptoKey,
                'platform' 		=> $platform,
                'queryenv-url' 	=> $baseurl . "/query-env",
                'messaging-p2p.producer-url' => $baseurl . "/messaging",
                'env-id'		=> $dbServer->envId,
                'region'		=> $dbServer->GetCloudLocation(),
                'scalr-id'		=> SCALR_ID
            );

            $command = 'scalarizr --import -y';
        } else {
            $behavior = $dbServer->GetProperty(SERVER_PROPERTIES::SZR_IMPORTING_BEHAVIOR);

            $options = array(
                'server-id' 	=> $dbServer->serverId,
                'role-name' 	=> $dbServer->GetProperty(SERVER_PROPERTIES::SZR_IMPORTING_ROLE_NAME),
                'crypto-key' 	=> $cryptoKey,
                'platform' 		=> $dbServer->platform,
                'behaviour' 	=> $behavior == ROLE_BEHAVIORS::BASE ? '' : $behavior,
                'queryenv-url' 	=> $baseurl . "/query-env",
                'messaging-p2p.producer-url' => $baseurl . "/messaging",
                'env-id'		=> $dbServer->envId,
                'region'=> $dbServer->GetCloudLocation()
            );

            if ($dbServer->GetProperty(SERVER_PROPERTIES::SZR_IMPORTING_OS_FAMILY) != 'windows')
                $command = 'scalarizr --import -y';
            else
                $command = 'C:\Program Files\Scalarizr\scalarizr.bat --import -y';
        }

        foreach ($options as $k => $v) {
            $command .= sprintf(' -o %s=%s', $k, $v);
        }

        $this->response->page('ui/servers/import_step2.js', array(
            'serverId' => $this->getParam('serverId'),
            'cmd'	   => $command
        ));
    }

    public function import2Action()
    {

        $platforms = array();
        $env = Scalr_Environment::init()->loadById($this->getEnvironmentId());
        $enabledPlatforms = $env->getEnabledPlatforms();
        foreach (SERVER_PLATFORMS::getList() as $k => $v) {
            if (in_array($k, $enabledPlatforms)) {

                if ($k == 'rds')
                    continue;

                $platforms[] = array($k, $v);
                foreach (PlatformFactory::NewPlatform($k)->getLocations() as $lk=>$lv)
                    $locations[$k][] = array('id' => $lk, 'name' => $lv);
            }
        }
        unset($platforms['rds']);

        $this->response->page('ui/servers/import_step1_2.js', array(
            'platforms' 	=> $platforms,
            'locations'		=> $locations
        ));
    }

    public function xResendMessageAction()
    {
        $message = $this->db->GetRow("SELECT * FROM messages WHERE server_id=? AND messageid=?",array(
            $this->getParam('serverId'), $this->getParam('messageId')
        ));

        if ($message) {
            $serializer = new Scalr_Messaging_XmlSerializer();

            $msg = $serializer->unserialize($message['message']);

            $dbServer = DBServer::LoadByID($this->getParam('serverId'));
            $this->user->getPermissions()->validate($dbServer);

            if (in_array($dbServer->status, array(SERVER_STATUS::RUNNING, SERVER_STATUS::INIT))) {
                $this->db->Execute("UPDATE messages SET status=?, handle_attempts='0' WHERE id=?", array(MESSAGE_STATUS::PENDING, $message['id']));
                $dbServer->SendMessage($msg);
            }
            else
                throw new Exception("Scalr unable to re-send message. Server should be in running state.");

            $this->response->success('Message successfully re-sent to the server');
        } else {
            throw new Exception("Message not found");
        }
    }

    public function xListMessagesAction()
    {
        $this->request->defineParams(array(
            'serverId',
            'sort' => array('type' => 'string', 'default' => 'id'),
            'dir' => array('type' => 'string', 'default' => 'DESC')
        ));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $sql = "SELECT *, message_name as message_type FROM messages WHERE server_id='{$dbServer->serverId}'";
        $response = $this->buildResponseFromSql($sql, array("server_id", "message", "messageid"));

        foreach ($response["data"] as &$row) {

            if (!$row['message_type']) {
                preg_match("/^<\?xml [^>]+>[^<]*<message(.*?)name=\"([A-Za-z0-9_]+)\"/si", $row['message'], $matches);
                $row['message_type'] = $matches[2];
            }

            $row['message'] = '';
            $row['dtlasthandleattempt'] = Scalr_Util_DateTime::convertTz($row['dtlasthandleattempt']);
        }

        $this->response->data($response);
    }

    public function messagesAction()
    {
        $this->response->page('ui/servers/messages.js', array('serverId' => $this->getParam('serverId')));
    }

    public function viewAction()
    {
        $this->response->page('ui/servers/view.js');
    }

    public function sshConsoleAction()
    {
        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        if ($dbServer->remoteIp) {
            $dBFarm = $dbServer->GetFarmObject();
            $dbRole = DBRole::loadById($dbServer->roleId);

            $sshPort = $dbRole->getProperty(DBRole::PROPERTY_SSH_PORT);
            if (!$sshPort)
                $sshPort = 22;

            $cSshPort = $dbServer->GetProperty(SERVER_PROPERTIES::CUSTOM_SSH_PORT);
            if ($cSshPort)
                $sshPort = $cSshPort;

            $sshKey = Scalr_SshKey::init()->loadGlobalByFarmId(
                $dbServer->farmId,
                $dbServer->GetFarmRoleObject()->CloudLocation,
                $dbServer->platform
            );

            $this->response->page('ui/servers/sshconsole.js', array(
                'serverId' => $dbServer->serverId,
                'serverIndex' => $dbServer->index,
                'remoteIp' => $dbServer->remoteIp,
                'localIp' => $dbServer->localIp,
                'farmName' => $dBFarm->Name,
                'farmId' => $dbServer->farmId,
                'roleName' => $dbRole->name,
                'port' => $sshPort,
                'username' => $dbServer->platform == SERVER_PLATFORMS::GCE ? 'scalr' : 'root',
                "key" => base64_encode($sshKey->getPrivate())
            ));
        }
        else
            throw new Exception(_("Server not initialized yet"));
    }

    public function xServerCancelOperationAction()
    {
        $this->request->defineParams(array(
            'serverId'
        ));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $bt_id = $this->db->GetOne("SELECT id FROM bundle_tasks WHERE server_id=? AND
            prototype_role_id='0' AND status NOT IN (?,?,?)", array(
            $dbServer->serverId,
            SERVER_SNAPSHOT_CREATION_STATUS::FAILED,
            SERVER_SNAPSHOT_CREATION_STATUS::SUCCESS,
            SERVER_SNAPSHOT_CREATION_STATUS::CANCELLED
        ));
        if ($bt_id) {
            $BundleTask = BundleTask::LoadById($bt_id);
            $BundleTask->SnapshotCreationFailed("Server was terminated before snapshot was created.");
        }

        try {
            if ($dbServer->status == SERVER_STATUS::TEMPORARY) {
                if (PlatformFactory::NewPlatform($dbServer->platform)->IsServerExists($dbServer))
                    PlatformFactory::NewPlatform($dbServer->platform)->TerminateServer($dbServer);

                Scalr_Server_History::init($dbServer)->markAsTerminated("Cancelled snapshotting operation");
            }
        } catch (Exception $e) {}

        $dbServer->Delete();

        $this->response->success("Server was successfully canceled and removed from database");
    }

    public function xUpdateUpdateClientAction()
    {
        $this->request->defineParams(array(
            'serverId'
        ));

        if (!$this->db->GetOne("SELECT id FROM scripts WHERE id='3803' AND clientid='0'"))
            throw new Exception("Automatical scalarizr update doesn't supported by this scalr version");

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $scriptSettings = array(
            'version' => $this->db->GetOne("SELECT MAX(revision) FROM script_revisions WHERE scriptid='3803'"),
            'scriptid' => 3803,
            'timeout' => 300,
            'issync' => 0,
            'params' => serialize(array())
        );

        $message = new Scalr_Messaging_Msg_ExecScript("Manual");

        $script = Scalr_Scripting_Manager::prepareScript($scriptSettings, $dbServer);

        $itm = new stdClass();
        // Script
        $itm->asynchronous = ($script['issync'] == 1) ? '0' : '1';
        $itm->timeout = $script['timeout'];
        $itm->name = $script['name'];
        $itm->body = $script['body'];

        $message->scripts = array($itm);

        $dbServer->SendMessage($message);

        $this->response->success('Scalarizr update-client update successfully initiated');
    }

    public function xUpdateAgentAction()
    {
        $this->request->defineParams(array(
            'serverId'
        ));

        if (!$this->db->GetOne("SELECT id FROM scripts WHERE id='2102' AND clientid='0'"))
            throw new Exception("Automatical scalarizr update doesn't supported by this scalr version");

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $scriptSettings = array(
            'version' => $this->db->GetOne("SELECT MAX(revision) FROM script_revisions WHERE scriptid='2102'"),
            'scriptid' => 2102,
            'timeout' => 300,
            'issync' => 0,
            'params' => serialize(array())
        );

        $message = new Scalr_Messaging_Msg_ExecScript("Manual");

        $script = Scalr_Scripting_Manager::prepareScript($scriptSettings, $dbServer);

        $itm = new stdClass();
        // Script
        $itm->asynchronous = ($script['issync'] == 1) ? '0' : '1';
        $itm->timeout = $script['timeout'];
        $itm->name = $script['name'];
        $itm->body = $script['body'];

        $message->scripts = array($itm);

        $dbServer->SendMessage($message);

        $this->response->success('Scalarizr update successfully initiated. Please wait a few minutes and then refresh the page');
    }

    public function xListServersAction()
    {
        $this->request->defineParams(array(
            'roleId' => array('type' => 'int'),
            'farmId' => array('type' => 'int'),
            'farmRoleId' => array('type' => 'int'),
            'serverId',
            'hideTerminated' => array('type' => 'bool'),
            'sort' => array('type' => 'json')
        ));

        $sql = 'SELECT servers.*, farms.name AS farm_name, roles.name AS role_name FROM servers LEFT JOIN farms ON servers.farm_id = farms.id
                LEFT JOIN roles ON roles.id = servers.role_id WHERE servers.env_id = ? AND :FILTER:';
        $args = array($this->getEnvironmentId());

        if ($this->getParam('cloudServerId')) {
            $sql = str_replace('WHERE', 'LEFT JOIN server_properties ON servers.server_id = server_properties.server_id WHERE', $sql);
            $sql .= ' AND (';

            $sql .= 'server_properties.name = ? AND server_properties.value = ?';
            $args[] = CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID;
            $args[] = $this->getParam('cloudServerId');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = EC2_SERVER_PROPERTIES::INSTANCE_ID;
            $args[] = $this->getParam('cloudServerId');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = EUCA_SERVER_PROPERTIES::INSTANCE_ID;
            $args[] = $this->getParam('cloudServerId');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = GCE_SERVER_PROPERTIES::SERVER_ID;
            $args[] = $this->getParam('cloudServerId');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = OPENSTACK_SERVER_PROPERTIES::SERVER_ID;
            $args[] = $this->getParam('cloudServerId');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = RACKSPACE_SERVER_PROPERTIES::SERVER_ID;
            $args[] = $this->getParam('cloudServerId');

            $sql .= ')';
        }

        if ($this->getParam('cloudServerLocation')) {
            if (! strstr($sql, 'LEFT JOIN server_properties ON servers.server_id = server_properties.server_id'))
                $sql = str_replace('WHERE', 'LEFT JOIN server_properties ON servers.server_id = server_properties.server_id WHERE', $sql);
            $sql .= ' AND (';

            $sql .= 'server_properties.name = ? AND server_properties.value = ?';
            $args[] = CLOUDSTACK_SERVER_PROPERTIES::CLOUD_LOCATION;
            $args[] = $this->getParam('cloudServerLocation');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = EC2_SERVER_PROPERTIES::REGION;
            $args[] = $this->getParam('cloudServerLocation');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = EUCA_SERVER_PROPERTIES::REGION;
            $args[] = $this->getParam('cloudServerLocation');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = GCE_SERVER_PROPERTIES::CLOUD_LOCATION;
            $args[] = $this->getParam('cloudServerLocation');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = OPENSTACK_SERVER_PROPERTIES::CLOUD_LOCATION;
            $args[] = $this->getParam('cloudServerLocation');

            $sql .= ' OR server_properties.name = ? AND server_properties.value = ?';
            $args[] = RACKSPACE_SERVER_PROPERTIES::DATACENTER;
            $args[] = $this->getParam('cloudServerLocation');

            $sql .= ')';
        }

        if ($this->getParam('farmId')) {
            $sql .= " AND farm_id=?";
            $args[] = $this->getParam('farmId');
        }

        if ($this->getParam('farmRoleId')) {
            $sql .= " AND farm_roleid=?";
            $args[] = $this->getParam('farmRoleId');
        }

        if ($this->getParam('roleId')) {
            $sql .= " AND role_id=?";
            $args[] = $this->getParam('roleId');
        }

        if ($this->getParam('serverId')) {
            $sql .= " AND server_id=?";
            $args[] = $this->getParam('serverId');
        }

        if ($this->getParam('hideTerminated')) {
            $sql .= ' AND servers.status != ?';
            $args[] = SERVER_STATUS::TERMINATED;
        }

        $response = $this->buildResponseFromSql2($sql, array('platform', 'farm_name', 'role_name', 'index', 'server_id', 'remote_ip', 'local_ip', 'uptime'),
            array('servers.server_id', 'farm_id', 'farms.name', 'remote_ip', 'local_ip', 'servers.status'), $args);

        foreach ($response["data"] as &$row) {
            try {
                $dbServer = DBServer::LoadByID($row['server_id']);

                $row['cloud_server_id'] = $dbServer->GetCloudServerID();

                if (in_array($dbServer->status, array(SERVER_STATUS::RUNNING, SERVER_STATUS::INIT))) {
                    $row['cluster_role'] = "";
                    if ($dbServer->GetFarmRoleObject()->GetRoleObject()->getDbMsrBehavior() || $dbServer->GetFarmRoleObject()->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {

                        $isMaster = ($dbServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER) || $dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER));
                        $row['cluster_role'] = ($isMaster) ? 'Master' : 'Slave';

                        if ($isMaster && $dbServer->GetFarmRoleObject()->GetSetting(Scalr_Db_Msr::SLAVE_TO_MASTER) || $dbServer->GetFarmRoleObject()->GetSetting(DBFarmRole::SETTING_MYSQL_SLAVE_TO_MASTER)) {
                            $row['cluster_role'] = 'Promoting';
                        }
                    }
                }

                $row['cloud_location'] = $dbServer->GetCloudLocation();
                if ($dbServer->platform == SERVER_PLATFORMS::EC2) {
                    $loc = $dbServer->GetProperty(EC2_SERVER_PROPERTIES::AVAIL_ZONE);
                    if ($loc && $loc != 'x-scalr-diff')
                        $row['cloud_location'] .= "/".substr($loc, -1, 1);
                }

                if ($dbServer->platform == SERVER_PLATFORMS::EC2) {
                    $row['has_eip'] = $this->db->GetOne("SELECT id FROM elastic_ips WHERE server_id = ?", array($dbServer->serverId));
                }

                if ($dbServer->GetFarmRoleObject()->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MONGODB)) {
                    $shardIndex = $dbServer->GetProperty(Scalr_Role_Behavior_MongoDB::SERVER_SHARD_INDEX);
                    $replicaSetIndex = $dbServer->GetProperty(Scalr_Role_Behavior_MongoDB::SERVER_REPLICA_SET_INDEX);
                    $row['cluster_position'] = "{$shardIndex}-{$replicaSetIndex}";
                }
            }
            catch(Exception $e){  }

            $rebooting = $this->db->GetOne("SELECT value FROM server_properties WHERE server_id=? AND `name`=?", array(
                $row['server_id'], SERVER_PROPERTIES::REBOOTING
            ));
            if ($dbServer->status == SERVER_STATUS::RUNNING) {
                if ($rebooting)
                    $row['status'] = "Rebooting";

                $subStatus = $dbServer->GetProperty(SERVER_PROPERTIES::SUB_STATUS);
                if ($subStatus) {
                    $row['status'] = ucfirst($subStatus);
                }
            }


            $row['is_szr'] = $dbServer->IsSupported("0.5");
            $row['initDetailsSupported'] = $dbServer->IsSupported("0.7.181");

            if ($dbServer->GetProperty(SERVER_PROPERTIES::SZR_IS_INIT_FAILED) && in_array($dbServer->status, array(SERVER_STATUS::INIT, SERVER_STATUS::PENDING)))
                $row['isInitFailed'] = 1;

            $launchError = $dbServer->GetProperty(SERVER_PROPERTIES::LAUNCH_ERROR);
            if ($launchError)
                $row['launch_error'] = "1";

            $serverAlerts = new Alerts($dbServer);

            $row['agent_version'] = $dbServer->GetProperty(SERVER_PROPERTIES::SZR_VESION);
            $row['agent_update_needed'] = $dbServer->IsSupported("0.7") && !$dbServer->IsSupported("0.7.189");
            $row['agent_update_manual'] = !$dbServer->IsSupported("0.5");
            $row['os_family'] = $dbServer->GetOsFamily();
            $row['flavor'] = $dbServer->GetFlavor();
            $row['alerts'] = $serverAlerts->getActiveAlertsCount();
            if (!$row['flavor'])
                $row['flavor'] = '';

            if ($dbServer->status == SERVER_STATUS::RUNNING) {
                $tm = (int)$dbServer->GetProperty(SERVER_PROPERTIES::INITIALIZED_TIME);

                if (!$tm)
                    $tm = (int)strtotime($row['dtadded']);

                if ($tm > 0) {
                    $row['uptime'] = Scalr_Util_DateTime::getHumanReadableTimeout(time() - $tm, false);
                }
            }
            else
                $row['uptime'] = '';

            $r_dns = $this->db->GetOne("SELECT value FROM farm_role_settings WHERE farm_roleid=? AND `name`=?", array(
                $row['farm_roleid'], DBFarmRole::SETTING_EXCLUDE_FROM_DNS
            ));

            $row['excluded_from_dns'] = (!$dbServer->GetProperty(SERVER_PROPERTIES::EXCLUDE_FROM_DNS) && !$r_dns) ? false : true;
        }

        $this->response->data($response);
    }

    public function xListServersUpdateAction()
    {
        $this->request->defineParams(array(
            'servers' => array('type' => 'json')
        ));

        $retval = array();
        $sql = array();
        foreach ($this->getParam('servers') as $serverId)
            $sql[] = $this->db->qstr($serverId);

        if (count($sql)) {
            $servers = $this->db->Execute('SELECT server_id, status, remote_ip, local_ip FROM servers WHERE server_id IN (' . join($sql, ',') . ') AND env_id = ?', array($this->getEnvironmentId()));
            while ($server = $servers->FetchRow()) {

                $rebooting = $this->db->GetOne("SELECT value FROM server_properties WHERE server_id=? AND `name`=?", array(
                    $server['server_id'], SERVER_PROPERTIES::REBOOTING
                ));
                if ($rebooting) {
                    $server['status'] = "Rebooting";
                }

                $subStatus =  $this->db->GetOne("SELECT value FROM server_properties WHERE server_id=? AND `name`=?", array(
                    $server['server_id'], SERVER_PROPERTIES::SUB_STATUS
                ));
                if ($subStatus) {
                    $server['status'] = ucfirst($subStatus);
                }

                $retval[$server['server_id']] = $server;
            }
        }

        $this->response->data(array(
            'servers' => $retval
        ));
    }

    public function xSzrUpdateAction()
    {
        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $port = $dbServer->GetProperty(SERVER_PROPERTIES::SZR_UPDC_PORT);
        if (!$port)
            $port = 8008;

        $updateClient = new Scalr_Net_Scalarizr_UpdateClient($dbServer, $port, 30);
        $status = $updateClient->updateScalarizr();

        $this->response->success('Scalarizr successfully updated to the latest version');
    }

    public function xSzrRestartAction()
    {
        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $port = $dbServer->GetProperty(SERVER_PROPERTIES::SZR_UPDC_PORT);
        if (!$port)
            $port = 8008;

        $updateClient = new Scalr_Net_Scalarizr_UpdateClient($dbServer, $port, 30);
        $status = $updateClient->restartScalarizr();

        $this->response->success('Scalarizr successfully restarted');
    }

    public function extendedInfoAction()
    {
        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $baseurl = $this->getContainer()->config('scalr.endpoint.scheme') . "://" .
                   $this->getContainer()->config('scalr.endpoint.host');

        $info = PlatformFactory::NewPlatform($dbServer->platform)->GetServerExtendedInformation($dbServer);
        $form = array(
            array(
                'xtype' => 'container',
                'layout' => array(
                    'type' => 'hbox',
                    'align' => 'stretchmax'
                ),
                'cls' => 'x-container-form-item',
                'hideLabel' => true,
                'items' => array(
                    array(
                    'xtype' => 'fieldset',
                    'title' => 'General',
                    'flex'  => 1,
                    'defaults' => array(
                        'labelWidth' => 100
                    ),
                    'items' => array(
                        array(
                            'xtype' => 'displayfield',
                            'fieldLabel' => 'Server ID',
                            'value' => $dbServer->serverId
                        ),
                        array(
                            'xtype' => 'displayfield',
                            'fieldLabel' => 'Platform',
                            'value' => $dbServer->platform
                        ),
                        array(
                            'xtype' => 'displayfield',
                            'fieldLabel' => 'Remote IP',
                            'value' => ($dbServer->remoteIp) ? $dbServer->remoteIp : ''
                        ),
                        array(
                            'xtype' => 'displayfield',
                            'fieldLabel' => 'Local IP',
                            'value' => ($dbServer->localIp) ? $dbServer->localIp : ''
                        ),
                        array(
                            'xtype' => 'displayfield',
                            'fieldLabel' => 'Status',
                            'value' => $dbServer->status
                        ),
                        array(
                            'xtype' => 'displayfield',
                            'fieldLabel' => 'Index',
                            'value' => $dbServer->index
                        ),
                        array(
                            'xtype' => 'displayfield',
                            'fieldLabel' => 'Added at',
                            'value' => Scalr_Util_DateTime::convertTz($dbServer->dateAdded)
                        )
                    )
                ))
            )
        );

        /***** Scalr agent *****/
        if ($dbServer->status == SERVER_STATUS::RUNNING &&
              $dbServer->GetProperty(SERVER_PROPERTIES::SUB_STATUS) != 'stopped' &&
              $dbServer->GetOsFamily() != 'windows') {
            try {
                $port = $dbServer->GetProperty(SERVER_PROPERTIES::SZR_UPDC_PORT);
                if (!$port)
                    $port = 8008;

                $updateClient = new Scalr_Net_Scalarizr_UpdateClient($dbServer, $port);
                $status = $updateClient->getStatus();
            } catch (Exception $e) {
                $oldUpdClient = stristr($e->getMessage(), "Method not found");
                $error = $e->getMessage();
            }

            if ($status) {
                $items = array(
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Scalarizr status',
                        'value' => $status->service_status == 'running' ? "<span style='color:green;'>Running</span>" : "<span style='color:red;'>".ucfirst($status->service_status)."</span>"
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Version',
                        'value' => $status->installed
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Repository',
                        'value' => ucfirst($status->repository)
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Last update',
                        'value' => Scalr_Util_DateTime::convertTz($status->executed_at)
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Last update status',
                        'value' => $status->error ? "<span style='color:red;'>Error: ".nl2br($status->error)."</span>" : "<span style='color:green;'>Success</span>"
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Next update',
                        'value' => ($status->installed != $status->candidate) ? "Update to <b>{$status->candidate}</b> scheduled on <b>".Scalr_Util_DateTime::convertTz($status->scheduled_on)."</b>" : "Scalarizr is up to date"
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Schedule',
                        'value' => $status->schedule
                    ),
                    array(
                        'xtype' => 'fieldcontainer',
                        'layout' => 'hbox',
                        'hideLabel' => true,
                        'items' => array(
                            array(
                                'xtype' => 'button',
                                'itemId' => 'updateSzrBtn',
                                'text' => 'Update scalarizr now',
                                'disabled' => ($status->installed == $status->candidate),
                                'flex' => 1
                            ),
                            array(
                                'xtype' => 'button',
                                'itemId' => 'restartSzrBtn',
                                'text' => 'Restart scalarizr',
                                'flex' => 1,
                                'margin' => '0 0 0 5'
                            )
                        )
                    )
                );
            } else {
                if ($oldUpdClient) {
                    $items = array(array(
                        'xtype' => 'button',
                        'itemId' => 'upgradeUpdClientBtn',
                        'text' => 'Upgrade scalarizr upd-client',
                        'flex' => 1
                    ));
                } else {
                    $items = array(array(
                        'xtype' => 'displayfield',
                        'hideLabel' => true,
                        'value' => "<span style='color:red;'>Scalarizr status is not available: {$error}</span>"
                    ));
                }
            }

            $form[0]['items'][] = array(
                'xtype' => 'fieldset',
                'labelWidth' => 240,
                'flex'   => 1,
                'margin' => '0 0 0 10',
                'title' => 'Scalr agent status',
                'items' => $items
            );
        }
        /***** Scalr agent *****/


        $it = array();
        if (is_array($info) && count($info)) {
            foreach ($info as $name => $value) {
                $it[] = array(
                    'xtype' => 'displayfield',
                    'fieldLabel' => $name,
                    'value' => $value
                );
            }
        } else {
            $it[] = array(
                'xtype' => 'displayfield',
                'hideLabel' => true,
                'value' => 'Platform specific details not available for this server'
            );
        }

        $form[] = array(
            'xtype' => 'fieldset',
            'labelWidth' => 240,
            'title' => 'Platform specific details',
            'collapsible' => true,
            'collapsed' => false,
            'items' => $it
        );

        if (count($dbServer->GetAllProperties())) {
            $it = array();
            foreach ($dbServer->GetAllProperties() as $name => $value) {
                $it[] = array(
                    'xtype' => 'displayfield',
                    'fieldLabel' => $name,
                    'value' => $value
                );
            }

            $form[] = array(
                'xtype' => 'fieldset',
                'title' => 'Scalr internal server properties',
                'collapsible' => true,
                'collapsed' => true,
                'labelWidth' => 220,
                'items' => $it
            );
        }

        if (!$dbServer->IsSupported('0.5'))
        {
            $authKey = $dbServer->GetKey();
            if (!$authKey) {
                $authKey = Scalr::GenerateRandomKey(40);
                $dbServer->SetProperty(SERVER_PROPERTIES::SZR_KEY, $authKey);
            }

            $dbServer->SetProperty(SERVER_PROPERTIES::SZR_KEY_TYPE, SZR_KEY_TYPE::PERMANENT);

            $form[] = array(
                'xtype' => 'fieldset',
                'title' => 'Upgrade from ami-scripts to scalarizr',
                'labelWidth' => 220,
                'items' => array(
                    'xtype' => 'textarea',
                    'hideLabel' => true,
                    'readOnly' => true,
                    'anchor' => '-20',
                    'value' => sprintf("wget " . $baseurl . "/storage/scripts/amiscripts-to-scalarizr.py && python amiscripts-to-scalarizr.py -s %s -k %s -o queryenv-url=%s -o messaging_p2p.producer_url=%s",
                        $dbServer->serverId,
                        $authKey,
                        $baseurl . "/query-env",
                        $baseurl . "/messaging"
                    )
            ));
        }

        $this->response->page('ui/servers/extendedinfo.js', $form);
    }

    public function consoleOutputAction()
    {
        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $output = PlatformFactory::NewPlatform($dbServer->platform)->GetServerConsoleOutput($dbServer);

        if ($output) {
            $output = trim(base64_decode($output));
            $output = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $output);
            $output = nl2br($output);

            $output = str_replace("\033[74G", "</span>", $output);
            $output = str_replace("\033[39;49m", "</span>", $output);
            $output = str_replace("\033[80G <br />", "<span style='padding-left:20px;'></span>", $output);
            $output = str_replace("\033[80G", "<span style='padding-left:20px;'>&nbsp;</span>", $output);
            $output = str_replace("\033[31m", "<span style='color:red;'>", $output);
            $output = str_replace("\033[33m", "<span style='color:brown;'>", $output);
        } else
            $output = 'Console output not available yet';

        $this->response->page('ui/servers/consoleoutput.js', array(
            'name' => $dbServer->serverId,
            'content' => $output
        ));
    }

    public function xServerExcludeFromDnsAction()
    {
        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $dbServer->SetProperty(SERVER_PROPERTIES::EXCLUDE_FROM_DNS, 1);

        $zones = DBDNSZone::loadByFarmId($dbServer->farmId);
        foreach ($zones as $DBDNSZone)
        {
            $DBDNSZone->updateSystemRecords($dbServer->serverId);
            $DBDNSZone->save();
        }

        $this->response->success("Server successfully removed from DNS");
    }

    public function xServerIncludeInDnsAction()
    {
        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $dbServer->SetProperty(SERVER_PROPERTIES::EXCLUDE_FROM_DNS, 0);

        $zones = DBDNSZone::loadByFarmId($dbServer->farmId);
        foreach ($zones as $DBDNSZone)
        {
            $DBDNSZone->updateSystemRecords($dbServer->serverId);
            $DBDNSZone->save();
        }

        $this->response->success("Server successfully added to DNS");
    }

    public function xServerCancelAction()
    {
        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $bt_id = $this->db->GetOne("SELECT id FROM bundle_tasks WHERE server_id=? AND
            prototype_role_id='0' AND status NOT IN (?,?,?)", array(
            $dbServer->serverId,
            SERVER_SNAPSHOT_CREATION_STATUS::FAILED,
            SERVER_SNAPSHOT_CREATION_STATUS::SUCCESS,
            SERVER_SNAPSHOT_CREATION_STATUS::CANCELLED
        ));

        if ($bt_id) {
            $BundleTask = BundleTask::LoadById($bt_id);
            $BundleTask->SnapshotCreationFailed("Server was cancelled before snapshot was created.");
        }

        $dbServer->Delete();
        $this->response->success("Server successfully cancelled and removed from database.");
    }

    public function xServerRebootServersAction()
    {
        $this->request->defineParams(array(
            'servers' => array('type' => 'json')
        ));

        foreach ($this->getParam('servers') as $serverId) {
            try {
                $dbServer = DBServer::LoadByID($serverId);
                $this->user->getPermissions()->validate($dbServer);

                PlatformFactory::NewPlatform($dbServer->platform)->RebootServer($dbServer);
            }
            catch (Exception $e) {}
        }

        $this->response->success();
    }

    public function xServerTerminateServersAction()
    {
        $this->request->defineParams(array(
            'servers' => array('type' => 'json'),
            'descreaseMinInstancesSetting' => array('type' => 'bool'),
            'forceTerminate' => array('type' => 'bool')
        ));

        foreach ($this->getParam('servers') as $serverId) {
            $dbServer = DBServer::LoadByID($serverId);
            $this->user->getPermissions()->validate($dbServer);

            if (! $this->getParam('forceTerminate')) {
                Logger::getLogger(LOG_CATEGORY::FARM)->info(new FarmLogMessage($dbServer->farmId,
                    sprintf("Scheduled termination for server %s (%s). It will be terminated in 3 minutes.",
                        $dbServer->serverId,
                        $dbServer->remoteIp
                )
                ));
            }

            Scalr::FireEvent($dbServer->farmId, new BeforeHostTerminateEvent($dbServer, $this->getParam('forceTerminate')));

            Scalr_Server_History::init($dbServer)->markAsTerminated("Manually terminated via UI");
        }

        if ($this->getParam('descreaseMinInstancesSetting')) {
            $servers = $this->getParam('servers');
            $dbServer = DBServer::LoadByID($servers[0]);
            $dbFarmRole = $dbServer->GetFarmRoleObject();

            $minInstances = $dbFarmRole->GetSetting(DBFarmRole::SETTING_SCALING_MIN_INSTANCES);
            if ($minInstances > count($servers)) {
                $dbFarmRole->SetSetting(DBFarmRole::SETTING_SCALING_MIN_INSTANCES,
                    $minInstances - count($servers)
                );
            } else {
                $dbFarmRole->SetSetting(DBFarmRole::SETTING_SCALING_MIN_INSTANCES,
                    1
                );
            }
        }

        $this->response->success();
    }

    public function xServerGetLaAction()
    {
        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        if (!$dbServer->IsSupported('0.13.0')) {
            $la = "Unknown";
        } else {
            try {
                $szrClient = Scalr_Net_Scalarizr_Client::getClient(
                    $dbServer,
                    Scalr_Net_Scalarizr_Client::NAMESPACE_SYSTEM,
                    $dbServer->getPort(DBServer::PORT_API)
                );

                $la = $szrClient->loadAverage();
                if ($la[0] !== null && $la[0] !== false)
                    $la = number_format($la[0], 2);
                else
                    $la = "Unknown";
            } catch (Exception $e) {
                $la = "Unknown";
            }
        }

        $this->response->data(array('la' => $la));
    }

    public function createSnapshotAction()
    {
        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $dbFarmRole = $dbServer->GetFarmRoleObject();

        if ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
            $this->response->warning("You are about to synchronize MySQL instance. The bundle will not include DB data. <a href='#/dbmsr/status?farmId={$dbServer->farmId}&type=mysql'>Click here if you wish to bundle and save DB data</a>.");

            if (!$dbServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER)) {
                $dbSlave = true;
            }
        }

        $dbMsrBehavior = $dbFarmRole->GetRoleObject()->getDbMsrBehavior();
        if ($dbMsrBehavior) {
            $this->response->warning("You are about to synchronize DB instance. The bundle will not include DB data. <a href='#/dbmsr/status?farmId={$dbServer->farmId}&type={$dbMsrBehavior}'>Click here if you wish to bundle and save DB data</a>.");

            if (!$dbServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER)) {
                $dbSlave = true;
            }
        }

        //Check for already running bundle on selected instance
        $chk = $this->db->GetOne("SELECT id FROM bundle_tasks WHERE server_id=? AND status NOT IN ('success', 'failed')",
            array($dbServer->serverId)
        );

        if ($chk)
            throw new Exception(sprintf(_("This server is already synchonizing. <a href='#/bundletasks/%s/logs'>Check status</a>."), $chk));

        if (!$dbServer->IsSupported("0.2-112"))
            throw new Exception(sprintf(_("You cannot create snapshot from selected server because scalr-ami-scripts package on it is too old.")));

        //Check is role already synchronizing...
        $chk = $this->db->GetOne("SELECT server_id FROM bundle_tasks WHERE prototype_role_id=? AND status NOT IN ('success', 'failed')", array(
            $dbServer->roleId
        ));

        if ($chk && $chk != $dbServer->serverId) {
            try {
                $bDBServer = DBServer::LoadByID($chk);
            }
            catch(Exception $e) {}

            if ($bDBServer->farmId == $dbServer->farmId)
                throw new Exception(sprintf(_("This role is already synchonizing. <a href='#/bundletasks/%s/logs'>Check status</a>."), $chk));
        }

        $roleName = $dbServer->GetFarmRoleObject()->GetRoleObject()->name;
        $this->response->page('ui/servers/createsnapshot.js', array(
            'serverId' 	=> $dbServer->serverId,
            'platform'	=> $dbServer->platform,
            'dbSlave'	=> $dbSlave,
            'isVolumeSizeSupported'=> (int)$dbServer->IsSupported('0.7'),
            'farmId' => $dbServer->farmId,
            'farmName' => $dbServer->GetFarmObject()->Name,
            'roleName' => $roleName,
            'replaceNoReplace' => "<b>DO NOT REPLACE</b> any roles on any farms, just create new one.</td>",
            'replaceFarmReplace' => "Replace role '{$roleName}' with new one <b>ONLY</b> on current farm '{$dbServer->GetFarmObject()->Name}'</td>",
            'replaceAll' => "Replace role '{$roleName}' with new one on <b>ALL MY FARMS</b> <span style=\"font-style:italic;font-size:11px;\">(You will be able to bundle role with the same name. Old role will be renamed.)</span></td>"
        ));
    }

    public function xServerCreateSnapshotAction()
    {
        $this->request->defineParams(array(
            'rootVolumeSize' => array('type' => 'int')
        ));

        if (! $this->getParam('serverId'))
            throw new Exception(_('Server not found'));

        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $err = array();

        if (strlen($this->getParam('roleName')) < 3)
            $err[] = _("Role name should be greater than 3 chars");

        if (! preg_match("/^[A-Za-z0-9-]+$/si", $this->getParam('roleName')))
            $err[] = _("Role name is incorrect");

        $roleinfo = $this->db->GetRow("SELECT * FROM roles WHERE name=? AND (env_id=? OR env_id='0')", array($this->getParam('roleName'), $dbServer->envId, $dbServer->roleId));
        if ($this->getParam('replaceType') != SERVER_REPLACEMENT_TYPE::REPLACE_ALL) {
            if ($roleinfo)
                $err[] = _("Specified role name is already used by another role. You can use this role name only if you will replace old on on ALL your farms.");
        } else {
            if ($roleinfo && $roleinfo['env_id'] == 0)
                $err[] = _("Selected role name is reserved and cannot be used for custom role");
        }

        //Check for already running bundle on selected instance
        $chk = $this->db->GetOne("SELECT id FROM bundle_tasks WHERE server_id=? AND status NOT IN ('success', 'failed')",
            array($dbServer->serverId)
        );

        if ($chk)
            $err[] = sprintf(_("Server '%s' is already synchonizing."), $dbServer->serverId);

        //Check is role already synchronizing...
        $chk = $this->db->GetOne("SELECT server_id FROM bundle_tasks WHERE prototype_role_id=? AND status NOT IN ('success', 'failed')", array(
            $dbServer->roleId
        ));

        if ($chk && $chk != $dbServer->serverId) {
            try	{
                $bDBServer = DBServer::LoadByID($chk);
                if ($bDBServer->farmId == $DBServer->farmId)
                    $err[] = sprintf(_("Role '%s' is already synchonizing."), $dbServer->GetFarmRoleObject()->GetRoleObject()->name);
            } catch(Exception $e) {}
        }

        if ($dbServer->GetFarmRoleObject()->NewRoleID)
            $err[] = sprintf(_("Role '%s' is already synchonizing."), $dbServer->GetFarmRoleObject()->GetRoleObject()->name);

        if (count($err))
            throw new Exception(nl2br(implode('\n', $err)));

        $ServerSnapshotCreateInfo = new ServerSnapshotCreateInfo(
            $dbServer,
            $this->getParam('roleName'),
            $this->getParam('replaceType'),
            false,
            $this->getParam('roleDescription'),
            $this->getParam('rootVolumeSize'),
            $this->getParam('noServersReplace') == 'on' ? true : false
        );
        $BundleTask = BundleTask::Create($ServerSnapshotCreateInfo);

        $protoRole = DBRole::loadById($dbServer->roleId);

        $BundleTask->save();


        $this->response->success("Bundle task successfully created. <a href='#/bundletasks/{$BundleTask->id}/logs'>Click here to check status.</a>");
    }
}
