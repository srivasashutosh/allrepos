<?php

class Scalr_UI_Controller_Scripts extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'scriptId';

    public function hasAccess()
    {
        return true;
    }

    public static function getPermissionDefinitions()
    {
        return array();
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        if ($this->getParam(self::CALL_PARAM_NAME)) {
            $script = new Scalr_Script();
            $script->loadById($this->getParam(self::CALL_PARAM_NAME));

            if ($script->accountId)
                $this->user->getPermissions()->validate($script);

            $content = array();
            $revision = array();
            foreach ($script->getRevisions() as $rev) {
                $revision[] = $rev;
                $content[$rev['revision']] = $rev['script'];
            }

            $this->response->page('ui/scripts/viewcontent.js', array(
                'script' => $script,
                'content' => $content,
                'revision' => $revision,
                'latest' => $script->getLatestRevision()
            ), array('codemirror/codemirror.js'), array('codemirror/codemirror.css'));
        } else
            $this->response->page('ui/scripts/view.js');
    }

    public function xGetScriptContentAction()
    {
        $this->request->defineParams(array(
            'scriptId' => array('type' => 'int'),
            'version' => array('type' => 'int')
        ));

        $script = new Scalr_Script();
        $script->loadById($this->getParam('scriptId'));
        if ($script->accountId)
            $this->user->getPermissions()->validate($script);

        $rev = $script->getRevision($this->getParam('version'));

        $this->response->data(array(
            'script' => $rev['script']
        ));
    }

    public function xRemoveAction()
    {
        $this->request->defineParams(array(
            'scripts' => array('type' => 'json')
        ));
        $errors = array();

        foreach ($this->getParam('scripts') as $scriptId) {
            try {
                $script = new Scalr_Script();
                $script->loadById($scriptId);

                if ($script->accountId)
                    $this->user->getPermissions()->validate($script);
                elseif ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN)
                    throw new Scalr_Exception_InsufficientPermissions();

                $script->delete();
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (count($errors))
            $this->response->warning('Script(s) successfully removed, but some errors were occured:<br>' . implode('<br>', $errors));
        else
            $this->response->success('Script(s) successfully removed');
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'id' => array('type' => 'int'),
            'name' => array('type' => 'string', 'validator' => array(
                Scalr_Validator::REQUIRED => true
            )),
            'isSync' => array('type' => 'int'),
            'saveCurrentRevision' => array('type' => 'int'),
            'version' => array('type' => 'int'),
            'description', 'script'
        ));

        if (! $this->request->validate()->isValid()) {
            $this->response->failure();
            $this->response->data($this->request->getValidationErrors());
            return;
        }

        $scriptText = str_replace("\r\n", "\n", $this->getParam('script'));

        /*
        $nonascii = array();
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            $lineNum = $i+1;
            if (preg_match('/[^(\x20-\x7F)]+/', $line, $matches, PREG_OFFSET_CAPTURE) > 0) {
                $pos = $matches[0][1]+1;
                $nonascii[] = "line: {$lineNum} position: {$pos}";
            }
        }
        if (count($nonascii) > 0)
            throw new Exception("Found non ASCII symbols in the script at ".implode(", ", $nonascii).". Please remove them.");
        */

        $script = new Scalr_Script();
        if ($this->getParam('id')) {
            $script->loadById($this->getParam('id'));

            if ($script->accountId)
                $this->user->getPermissions()->validate($script);
            elseif ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN)
                throw new Scalr_Exception_InsufficientPermissions();
        }

        $script->name = htmlspecialchars($this->getParam('name'));
        $script->description = htmlspecialchars($this->getParam('description'));
        $script->accountId = $this->user->getAccountId();
        $script->save();
        $script->setRevision($scriptText, $this->getParam('saveCurrentRevision') == '1' ? $this->getParam('version') : NULL);

        $this->response->success('Script successfully saved');
    }

    public function xForkAction()
    {
        $this->request->defineParams(array(
            'scriptId' => array('type' => 'int')
        ));

        if (! $this->getParam('newName'))
            throw new Scalr_Exception_Core('Name cannot be null');

        $script = new Scalr_Script();
        $script->loadById($this->getParam('scriptId'));

        if ($script->accountId)
            $this->user->getPermissions()->validate($script);

        $script->fork(htmlspecialchars($this->getParam('newName')), $this->user->getAccountId());
        $this->response->success('Script successfully forked');
    }

    public function editAction()
    {
        $this->request->defineParams(array(
            'scriptId' => array('type' => 'int'),
            'version' => array('type' => 'int')
        ));

        $vars = Scalr_Scripting_Manager::getScriptingBuiltinVariables();

        $script = new Scalr_Script();
        $script->loadById($this->getParam('scriptId'));
        if ($script->accountId)
            $this->user->getPermissions()->validate($script);
        elseif ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN)
            throw new Scalr_Exception_InsufficientPermissions();

        $revision = $script->getRevision($this->getParam('version'));

        $this->response->page('ui/scripts/create.js', array(
            'script' => array(
                'id' => $script->id,
                'name' => $script->name,
                'description' => $script->description,
                'isSync' => $script->isSync,
                'script' => $revision['script'],
                'version' => $revision['revision']
            ),

            'versions' => range(1, $script->getLatestRevision()),
            'latestVersion' => $script->getLatestRevision(),
            'variables' => "%" . implode("%, %", array_keys($vars)) . "%"

        ), array('codemirror/codemirror.js'), array('codemirror/codemirror.css'));
    }

    public function createAction()
    {
        $vars = Scalr_Scripting_Manager::getScriptingBuiltinVariables();

        $this->response->page('ui/scripts/create.js', array(
            'versions'		=> array(1),
            'latestVersion' => 0,
            'variables'		=> "%" . implode("%, %", array_keys($vars)) . "%"
        ), array('codemirror/codemirror.js'), array('codemirror/codemirror.css'));
    }

    public function xListScriptsAction()
    {
        $this->request->defineParams(array(
            'scriptId', 'origin',
            'sort' => array('type' => 'json', 'default' => array('property' => 'name', 'direction' => 'desc'))
        ));

        $args = array();
        $sql = "SELECT
                scripts.id,
                scripts.name,
                scripts.description,
                scripts.clientid as accountId,
                MAX(script_revisions.dtcreated) as dtUpdated, MAX(script_revisions.revision) AS version FROM scripts
            INNER JOIN script_revisions ON script_revisions.scriptid = scripts.id
            WHERE :FILTER:";

        if ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN) {
            if ($this->getParam('origin') == 'Shared') {
                $sql .= ' AND (clientid = 0)';
            } else if ($this->getParam('origin') == 'Custom') {
                $sql .= ' AND (clientid = ?)';
                $args[] = $this->user->getAccountId();
            } else {
                $sql .= ' AND (clientid = 0 OR clientid = ?)';
                $args[] = $this->user->getAccountId();
            }
        } else {
            $sql .= ' AND clientid = 0';
        }

        $sql .= ' GROUP BY script_revisions.scriptid';
        $response = $this->buildResponseFromSql2($sql, array('id', 'name', 'description', 'dtUpdated'), array('scripts.name', 'scripts.description'), $args);
        foreach ($response['data'] as &$row) {
            $row['dtUpdated'] = Scalr_Util_DateTime::convertTz($row["dtUpdated"]);
        }

        $this->response->data($response);
    }

    public function getList()
    {
        $scripts = array();
        $sql = "SELECT id, name, description, issync FROM scripts WHERE clientid = 0 OR clientid = ? ORDER BY name ASC";

        foreach ($this->db->GetAll($sql, array($this->user->getAccountId())) as $script) {
            $dbVersions = $this->db->Execute("SELECT * FROM script_revisions WHERE scriptid=? ORDER BY revision ASC", array($script['id']));

            if ($dbVersions->RecordCount() > 0) {
                $revisions = array();
                while ($version = $dbVersions->FetchRow()) {
                    $revisions[] = array('revision' => $version['revision'], 'revisionName' => $version['revision'], 'fields' => unserialize($version['variables']));
                }

                $scripts[] = array(
                    'id'			=> $script['id'],
                    'name'			=> $script['name'],
                    'description'	=> $script['description'],
                    'issync'		=> $script['issync'],
                    'timeout'		=> (($script['issync'] == 1) ?
                                       \Scalr::config('scalr.script.timeout.sync') :
                                       \Scalr::config('scalr.script.timeout.async')),
                    'revisions'		=> $revisions,
                );
            }
        }

        return $scripts;
    }

    public function getScriptingData()
    {
        $retval = array('events' => EVENT_TYPE::getScriptingEvents(), 'scripts' => $this->getList());

        try {
            $envId = $this->getEnvironmentId();
            if ($envId) {
                $events = $this->db->Execute("SELECT * FROM event_definitions WHERE env_id = ?", array($envId));
                while ($event = $events->FetchRow()) {
                    $retval['events'][$event['name']] = $event['description'];
                }
            }
        } catch (Exception $e) {}

        return $retval;
    }

    // TODO: remove
    public function getFarmRolesAction()
    {
        $this->request->defineParams(array(
            'allValue' => array('type' => 'bool')
        ));

        $farmRolesController = self::loadController('Roles', 'Scalr_UI_Controller_Farms');
        if (is_null($farmRolesController))
            throw new Exception('Controller Farms_Roles not created');

        $farmRoles = $farmRolesController->getList();
        if (count($farmRoles) && $this->getParam('allValue'))
            $farmRoles[0] = array('id' => 0, 'name' =>'On all roles');

        $this->response->data(array(
            'farmRoles' => $farmRoles
        ));
    }

    // TODO: remove
    public function getServersAction()
    {
        $this->request->defineParams(array(
            'allValue' => array('type' => 'bool')
        ));

        $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
        $dbFarm = DBFarm::LoadById($dbFarmRole->FarmID);
        $servers = array();

        $this->user->getPermissions()->validate($dbFarm);

        foreach ($dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING)) as $key => $value)
            $servers[$value->serverId] = "{$value->remoteIp} ({$value->localIp})";

        if (count($servers) && $this->getParam('allValue'))
            $servers[0] = 'On all servers';

        $this->response->data(array(
            'servers' => $servers
        ));
    }

    public function executeAction()
    {
        $farmId = $this->getParam('farmId');
        $farmRoleId = $this->getParam('farmRoleId');
        $serverId = $this->getParam('serverId');
        $scriptId = $this->getParam('scriptId');
        $eventName = $this->getParam('eventName');

        $scripts = $this->getList();

        if ($eventName) {
            $scriptInfo = $this->db->GetRow("SELECT * FROM farm_role_scripts WHERE event_name=?", array($eventName));
            if (!$scriptInfo)
                throw new Exception("Scalr unable to find script execution options for used link");

            $farmId = $scriptInfo['farmid'];
            $farmRoleId = $scriptInfo['farm_roleid'];

            $scriptId = $scriptInfo['scriptid'];
        }

        $farmWidget = self::loadController('Farms', 'Scalr_UI_Controller')->getFarmWidget(array(
            'farmId' => $farmId,
            'farmRoleId' => $farmRoleId,
            'serverId' => $serverId
        ), array('addAll', 'addAllFarm', 'requiredFarm'));

        $this->response->page('ui/scripts/execute.js', array(
            'farmWidget' => $farmWidget,

            'scripts' => $scripts,
            'farmId' => $farmId,
            'farmRoleId' => $farmRoleId,
            'serverId' => $serverId,
            'scriptId' => $scriptId,

            'scriptIsSync' =>  $scriptInfo['issync'],
            'scriptTimeout' => $scriptInfo['timeout'],
            'scriptVersion' => $scriptInfo['version'],
            'scriptOptions' => @unserialize($scriptInfo['params'])
        ));
    }

    public function xExecuteAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'farmRoleId' => array('type' => 'int'),
            'serverId' => array('type' => 'string'),
            'scriptId' => array('type' => 'int'),
            'scriptIsSync' => array('type' => 'int'),
            'scriptTimeout' => array('type' => 'int'),
            'scriptVersion' => array('type' => 'int'),
            'scriptOptions' => array('type' => 'array'),
            'createMenuLink' => array('type' => 'int')
        ));

        $eventName = Scalr_Scripting_Manager::generateEventName('CustomEvent');

        if ($this->getParam('serverId')) {
            $dbServer = DBServer::LoadByID($this->getParam('serverId'));
            $this->user->getPermissions()->validate($dbServer);

            $target = Scalr_Script::TARGET_INSTANCE;
            $serverId = $dbServer->serverId;
            $farmRoleId = $dbServer->farmRoleId;
            $farmId = $dbServer->farmId;

        } else if ($this->getParam('farmRoleId')) {
            $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
            $this->user->getPermissions()->validate($dbFarmRole);

            $target = Scalr_Script::TARGET_ROLE;
            $farmRoleId = $dbFarmRole->ID;
            $farmId = $dbFarmRole->FarmID;

        } else if (!$this->getParam('farmId')) {
            $target = Scalr_Script::TARGET_ALL;
        } else {
            $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
            $this->user->getPermissions()->validate($dbFarm);

            $target = Scalr_Script::TARGET_FARM;
            $farmId = $dbFarm->ID;
        }

        if (! $this->getParam('eventName')) {
            if ($this->getParam('createMenuLink')) {
                $this->db->Execute("INSERT INTO farm_role_scripts SET
                    scriptid	= ?,
                    farmid		= ?,
                    farm_roleid	= ?,
                    params		= ?,
                    event_name	= ?,
                    target		= ?,
                    version		= ?,
                    timeout		= ?,
                    issync		= ?,
                    ismenuitem	= ?
                ", array(
                    $this->getParam('scriptId'),
                    (int)$farmId,
                    (int)$farmRoleId,
                    serialize($this->getParam('scriptOptions')),
                    $eventName,
                    $target,
                    $this->getParam('scriptVersion'),
                    $this->getParam('scriptTimeout'),
                    $this->getParam('scriptIsSync'),
                    $this->getParam('createMenuLink')
                ));
            }

            $farmScriptId = $this->db->Insert_ID();

            $executeScript = true;
        } else {

            $info = $this->db->Execute("SELECT farmid FROM farm_role_scripts WHERE event_name=?", array($this->getParam('eventName')));
            if ($info['farmid'] != $dbFarm->ID)
                throw new Exception("You cannot change farm for script shortcut");

            if ($this->getParam('isShortcut')) {
                $this->db->Execute("UPDATE farm_role_scripts SET
                    scriptid	= ?,
                    farm_roleid	= ?,
                    params		= ?,
                    target		= ?,
                    version		= ?,
                    timeout		= ?,
                    issync		= ?
                WHERE event_name = ? AND farmid = ?
                ", array(
                    $this->getParam('scriptId'),
                    (int)$farmRoleId,
                    serialize($this->getParam('scriptOptions')),
                    $target,
                    $this->getParam('scriptVersion'),
                    $this->getParam('scriptTimeout'),
                    $this->getParam('scriptIsSync'),
                    $this->getParam('eventName'),
                    $farmId
                ));
            }

            if (!$this->getParam('isShortcut'))
                $executeScript = true;
        }

        if ($executeScript) {
            switch($target) {
                case Scalr_Script::TARGET_FARM:
                    $servers = $this->db->GetAll("SELECT server_id FROM servers WHERE status IN (?,?) AND farm_id=?",
                        array(SERVER_STATUS::INIT, SERVER_STATUS::RUNNING, $farmId)
                    );
                    break;
                case Scalr_Script::TARGET_ROLE:
                    $servers = $this->db->GetAll("SELECT server_id FROM servers WHERE status IN (?,?) AND farm_roleid=?",
                        array(SERVER_STATUS::INIT, SERVER_STATUS::RUNNING, $farmRoleId)
                    );
                    break;
                case Scalr_Script::TARGET_INSTANCE:
                    $servers = $this->db->GetAll("SELECT server_id FROM servers WHERE status IN (?,?) AND server_id=?",
                        array(SERVER_STATUS::INIT, SERVER_STATUS::RUNNING, $serverId)
                    );
                    break;
                case Scalr_Script::TARGET_ALL:
                    $servers = $this->db->GetAll("SELECT server_id FROM servers WHERE status IN (?,?) AND env_id = ?",
                        array(SERVER_STATUS::INIT, SERVER_STATUS::RUNNING, $this->getEnvironmentId())
                    );
                    break;
            }

            $scriptSettings = array(
                'version' => $this->getParam('scriptVersion'),
                'scriptid' => $this->getParam('scriptId'),
                'timeout' => $this->getParam('scriptTimeout'),
                'issync' => $this->getParam('scriptIsSync'),
                'params' => serialize($this->getParam('scriptOptions'))
            );

            // send message to start executing task (starts script)
            if (count($servers) > 0) {
                foreach ($servers as $server) {
                    $DBServer = DBServer::LoadByID($server['server_id']);

                    $msg = new Scalr_Messaging_Msg_ExecScript("Manual");

                    $script = Scalr_Scripting_Manager::prepareScript($scriptSettings, $DBServer);

                    $itm = new stdClass();
                    // Script
                    $itm->asynchronous = ($script['issync'] == 1) ? '0' : '1';
                    $itm->timeout = $script['timeout'];
                    $itm->name = $script['name'];
                    $itm->body = $script['body'];

                    $msg->scripts = array($itm);

                    try {
                        $msg->globalVariables = array();
                        $globalVariables = new Scalr_Scripting_GlobalVariables($DBServer->envId);
                        $vars = $globalVariables->listVariables($DBServer->roleId, $DBServer->farmId, $DBServer->farmRoleId);
                        foreach ($vars as $k => $v)
                            $msg->globalVariables[] = (object)array('name' => $k, 'value' => $v);

                    } catch (Exception $e) {}

                    $DBServer->SendMessage($msg, false, true);
                }
            }
        }

        $this->response->success('Script execution has been queued. Script will be executed on selected instance(s) within couple of minutes.');
    }
}
