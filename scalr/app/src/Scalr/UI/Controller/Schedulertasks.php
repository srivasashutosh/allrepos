<?php

class Scalr_UI_Controller_Schedulertasks extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'schedulerTaskId';

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
        $this->response->page('ui/schedulertasks/view.js');
    }

    public function createAction()
    {
        $this->response->page('ui/schedulertasks/create.js', array(
            'farmWidget' => self::loadController('Farms')->getFarmWidget(array(), 'addAll'),
            'timezones' => Scalr_Util_DateTime::getTimezones(),
            'scripts' => self::loadController('Scripts')->getList(),
            'defaultTimezone' => $this->user->getSetting(Scalr_Account_User::SETTING_UI_TIMEZONE)
        ));
    }

    public function editAction()
    {
        $this->request->defineParams(array(
            'schedulerTaskId' => array('type' => 'int')
        ));

        //$DBFarmRole->FarmID;
        $task = Scalr_SchedulerTask::init();
        $task->loadById($this->getParam(self::CALL_PARAM_NAME));
        $this->user->getPermissions()->validate($task);

        $taskValues = array(
            'targetId' => $task->targetId,
            'targetType' => $task->targetType,
            'id' => $task->id,
            'name' => $task->name,
            'type' => $task->type,
            'comments' => htmlspecialchars_decode($task->comments),
            'config' => $task->config,
            'startTime' => $task->startTime ? Scalr_Util_DateTime::convertDateTime(new DateTime($task->startTime), $task->timezone)->format('Y-m-d H:i') : '',
            'endTime' => $task->endTime ? Scalr_Util_DateTime::convertDateTime(new DateTime($task->endTime), $task->timezone)->format('Y-m-d H:i') : '',
            'restartEvery' => $task->restartEvery,
            'timezone' => $task->timezone
        );

        $farmWidget = array();

        switch($task->targetType) {
            case Scalr_SchedulerTask::TARGET_FARM:
                $farmWidget = self::loadController('Farms')->getFarmWidget(array(
                    'farmId' => $task->targetId
                ), 'addAll');
            break;

            case Scalr_SchedulerTask::TARGET_ROLE:
                $farmWidget = self::loadController('Farms')->getFarmWidget(array(
                    'farmRoleId' => $task->targetId
                ), 'addAll');
                break;

            case Scalr_SchedulerTask::TARGET_INSTANCE:
                try {
                    $DBServer = DBServer::LoadByFarmRoleIDAndIndex($task->targetId, $task->targetServerIndex);
                    $farmWidget = self::loadController('Farms')->getFarmWidget(array(
                        'serverId' => $DBServer->serverId
                    ), 'addAll');
                } catch (Exception $e) {
                    $farmWidget = self::loadController('Farms')->getFarmWidget(array(
                        'farmRoleId' => $task->targetId
                    ), 'addAll');
                }
                break;

            default: break;
        }

        $this->response->page('ui/schedulertasks/create.js', array(
            'farmWidget' => $farmWidget,
            'timezones' => Scalr_Util_DateTime::getTimezones(),
            'scripts' => self::loadController('Scripts')->getList(),
            'defaultTimezone' => $this->user->getSetting(Scalr_Account_User::SETTING_UI_TIMEZONE),
            'task' => $taskValues
        ));
    }

    public function xListTasksAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'json')
        ));

        $sql = 'SELECT id, name, type, comments, target_id as targetId, target_server_index as targetServerIndex, target_type as targetType, start_time as startTime,
            end_time as endTime, last_start_time as lastStartTime, restart_every as restartEvery, config, order_index as orderIndex,
            status, timezone FROM `scheduler` WHERE `env_id` = ? AND :FILTER:';

        $response = $this->buildResponseFromSql2(
            $sql,
            array('id', 'name', 'type', 'startTime', 'endTime', 'lastStartTime', 'timezone', 'orderIndex', 'status', 'timezone'),
            array('id', 'name'),
            array($this->getEnvironmentId())
        );

        foreach ($response['data'] as &$row) {
            switch($row['targetType']) {
                case Scalr_SchedulerTask::TARGET_FARM:
                    try {
                        $DBFarm = DBFarm::LoadByID($row['targetId']);
                        $row['targetName'] = $DBFarm->Name;
                    } catch ( Exception  $e) {}
                    break;

                case Scalr_SchedulerTask::TARGET_ROLE:
                    try {
                        $DBFarmRole = DBFarmRole::LoadByID($row['targetId']);
                        $row['targetName'] = $DBFarmRole->GetRoleObject()->name;
                        $row['targetFarmId'] = $DBFarmRole->FarmID;
                        $row['targetFarmName'] = $DBFarmRole->GetFarmObject()->Name;
                    } catch (Exception $e) {}
                    break;

                case Scalr_SchedulerTask::TARGET_INSTANCE:
                    try {
                        $DBServer = DBServer::LoadByFarmRoleIDAndIndex($row['targetId'], $row['targetServerIndex']);
                        $row['targetName'] = "({$DBServer->remoteIp})";
                        $DBFarmRole = $DBServer->GetFarmRoleObject();
                        $row['targetFarmId'] = $DBServer->farmId;
                        $row['targetFarmName'] = $DBFarmRole->GetFarmObject()->Name;
                        $row['targetRoleId'] = $DBServer->farmRoleId;
                        $row['targetRoleName'] = $DBFarmRole->GetRoleObject()->name;
                    } catch(Exception $e) {}
                    break;

                default: break;
            }

            $row['type'] = Scalr_SchedulerTask::getTypeByName($row['type']);
            $row['startTime'] = $row['startTime'] ? Scalr_Util_DateTime::convertTz($row['startTime']) : 'Now';
            $row['endTime'] = $row['endTime'] ? Scalr_Util_DateTime::convertTz($row['endTime']) : 'Never';
            $row['lastStartTime'] = $row['lastStartTime'] ? Scalr_Util_DateTime::convertTz($row['lastStartTime']) : '';

            $row['config'] = unserialize($row['config']);
            $row['config']['scriptName'] = $this->db->GetOne("SELECT name FROM scripts WHERE id = ? and clientid = ?", array($row['config']['scriptId'], $this->user->getAccountId()));
        }

        $this->response->data($response);
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'id' => array('type' => 'integer'),
            'name' => array('type' => 'string', 'validator' => array(
                Scalr_Validator::REQUIRED => true,
                Scalr_Validator::NOHTML => true
            )),
            'type' => array('type' => 'string', 'validator' => array(
                Scalr_Validator::RANGE => array(
                    Scalr_SchedulerTask::SCRIPT_EXEC,
                    Scalr_SchedulerTask::LAUNCH_FARM,
                    Scalr_SchedulerTask::TERMINATE_FARM
                ),
                Scalr_Validator::REQUIRED => true
            )),
            'startTime', 'endTime', 'restartEvery',
            'timezone' => array('type' => 'string', 'validator' => array(
                Scalr_Validator::REQUIRED => true
            )),
            'farmId' => array('type' => 'integer'),
            'farmRoleId' => array('type' => 'integer'),
            'serverId' => array('type' => 'string')
        ));

        $task = Scalr_SchedulerTask::init();
        if ($this->getParam('id')) {
            $task->loadById($this->getParam('id'));
            $this->user->getPermissions()->validate($task);
        } else {
            $task->accountId = $this->user->getAccountId();
            $task->envId = $this->getEnvironmentId();
            $task->status = Scalr_SchedulerTask::STATUS_ACTIVE;
        }

        $this->request->validate();
        $params = array();

        $timezone = new DateTimeZone($this->getParam('timezone'));
        $startTm = $this->getParam('startTime') ? new DateTime($this->getParam('startTime'), $timezone) : NULL;
        $endTm = $this->getParam('endTime') ? new DateTime($this->getParam('endTime'), $timezone) : NULL;

        if ($startTm)
            Scalr_Util_DateTime::convertDateTime($startTm, NULL);

        if ($endTm)
            Scalr_Util_DateTime::convertDateTime($endTm, NULL);

        if ($startTm && $endTm && $endTm < $startTm)
            $this->request->addValidationErrors('endTimeDate', array('End time must be greater then start time'));

        $curTm = new DateTime();
        if ($startTm && $startTm < $curTm && !$task->id)
            $this->request->addValidationErrors('startTimeDate', array('Start time must be greater then current time'));

        switch ($this->getParam('type')) {
            case Scalr_SchedulerTask::SCRIPT_EXEC:
                if($this->getParam('serverId')) {
                    $dbServer = DBServer::LoadByID($this->getParam('serverId'));
                    $this->user->getPermissions()->validate($dbServer);

                    $task->targetId = $dbServer->GetFarmRoleObject()->ID;
                    $task->targetServerIndex = $dbServer->index;
                    $task->targetType = Scalr_SchedulerTask::TARGET_INSTANCE;
                }
                else {
                    if($this->getParam('farmRoleId')) {
                        $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));
                        $this->user->getPermissions()->validate($dbFarmRole);
                        $task->targetId = $dbFarmRole->ID;
                        $task->targetType = Scalr_SchedulerTask::TARGET_ROLE;
                    }
                    else {
                        if ($this->getParam('farmId')) {
                            $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
                            $this->user->getPermissions()->validate($dbFarm);
                            $task->targetId = $dbFarm->ID;
                            $task->targetType = Scalr_SchedulerTask::TARGET_FARM;
                        }
                        else {
                            $this->request->addValidationErrors('farmId', array('Farm ID is required'));
                        }
                    }
                }
                if ($this->getParam('scriptId')) {
                    // TODO: check scriptId and other vars
                    $params['scriptId'] = $this->getParam('scriptId');
                    $params['scriptIsSync'] = $this->getParam('scriptIsSync');
                    $params['scriptTimeout'] = $this->getParam('scriptTimeout');
                    $params['scriptVersion'] = $this->getParam('scriptVersion');
                    $params['scriptOptions'] = $this->getParam('scriptOptions');
                } else {
                    $this->request->addValidationErrors('scriptId', array('Script ID is required'));
                }

                break;

            case Scalr_SchedulerTask::LAUNCH_FARM:
                if ($this->getParam('farmId')) {
                    $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
                    $this->user->getPermissions()->validate($dbFarm);
                    $task->targetId = $dbFarm->ID;
                    $task->targetType = Scalr_SchedulerTask::TARGET_FARM;
                } else {
                    $this->request->addValidationErrors('farmId', array('Farm ID is required'));
                }
                break;

            case Scalr_SchedulerTask::TERMINATE_FARM:
                if ($this->getParam('farmId')) {
                    $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
                    $this->user->getPermissions()->validate($dbFarm);
                    $task->targetId = $dbFarm->ID;
                    $task->targetType = Scalr_SchedulerTask::TARGET_FARM;
                } else {
                    $this->request->addValidationErrors('farmId', array('Farm ID is required'));
                }
                $params['deleteDNSZones'] = $this->getParam('deleteDNSZones');
                $params['deleteCloudObjects'] = $this->getParam('deleteCloudObjects');
                break;
        }

        if (! $this->request->isValid()) {
            $this->response->failure();
            $this->response->data($this->request->getValidationErrors());
            return;
        }

        $task->name = $this->getParam('name');
        $task->type = $this->getParam('type');
        $task->comments = htmlspecialchars($this->getParam('comments'));
        $task->timezone = $this->getParam('timezone');
        $task->startTime = $startTm ? $startTm->format('Y-m-d H:i:s') : NULL;
        $task->endTime = $endTm ? $endTm->format('Y-m-d H:i:s') : NULL;
        $task->restartEvery = $this->getParam('restartEveryReal') ? $this->getParam('restartEveryReal') : $this->getParam('restartEvery');
        $task->config = $params;

        $task->save();
        $this->response->success();
    }

    public function xActivateAction()
    {
        $this->request->defineParams(array(
            'tasks' => array('type' => 'json')
        ));

        foreach ($this->getParam('tasks') as $taskId) {
            $task = Scalr_SchedulerTask::init()->loadById($taskId);
            $this->user->getPermissions()->validate($task);

            if ($task->status == Scalr_SchedulerTask::STATUS_FINISHED)
                continue;

            $task->status = Scalr_SchedulerTask::STATUS_ACTIVE;
            $task->save();
        }

        $this->response->success("Selected task(s) successfully activated");
    }

    public function xSuspendAction()
    {
        $this->request->defineParams(array(
            'tasks' => array('type' => 'json')
        ));

        foreach ($this->getParam('tasks') as $taskId) {
            $task = Scalr_SchedulerTask::init()->loadById($taskId);
            $this->user->getPermissions()->validate($task);

            if ($task->status == Scalr_SchedulerTask::STATUS_FINISHED)
                continue;

            $task->status = Scalr_SchedulerTask::STATUS_SUSPENDED;
            $task->save();
        }

        $this->response->success("Selected task(s) successfully suspended");
    }

    public function xExecuteAction()
    {
        $this->request->defineParams(array(
            'tasks' => array('type' => 'json')
        ));
        $executed = array();

        foreach ($this->getParam('tasks') as $taskId) {
            $task = new Scalr_SchedulerTask();
            $task->loadById($taskId);
            $this->user->getPermissions()->validate($task);

            if ($task->status != Scalr_SchedulerTask::STATUS_ACTIVE)
                continue;

            if ($task->execute(true))
                $executed[] = $task->name;
        }

        if (count($executed))
            $this->response->success("Task(s): " . implode($executed, ', ') . " successfully executed");
        else
            $this->response->warning('Target of task could not be found');
    }

    public function xDeleteAction()
    {
        $this->request->defineParams(array(
            'tasks' => array('type' => 'json')
        ));

        foreach ($this->getParam('tasks') as $taskId) {
            $task = Scalr_SchedulerTask::init()->loadById($taskId);
            $this->user->getPermissions()->validate($task);
            $task->delete();
        }

        $this->response->success("Selected task(s) successfully removed");
    }
}
