<?php

class Scalr_Messaging_Service_LogQueueHandler implements Scalr_Messaging_Service_QueueHandler
{

    /**
     * @var \ADODB_mysqli
     */
    private $db;

    private $logger;

    private static $severityCodes = array(
        'DEBUG' => 1,
        'INFO' => 2,
        'WARN' => 3,
        'WARNING' => 3,
        'ERROR' => 4
    );

    function __construct ()
    {
        $this->db = \Scalr::getDb();
        $this->logger = Logger::getLogger(__CLASS__);
    }

    function accept($queue) {
        return $queue == "log";
    }

    function handle($queue, Scalr_Messaging_Msg $message, $rawMessage) {
        $dbserver = DBServer::LoadByID($message->getServerId());

        if ($message instanceOf Scalr_Messaging_Msg_ExecScriptResult) {
            try {
                $this->db->Execute("INSERT DELAYED INTO scripting_log SET
                    farmid = ?,
                    server_id = ?,
                    event = ?,
                    message = ?,
                    dtadded = NOW(),
                    script_name = ?,
                    event_server_id = ?,
                    exec_time = ?,
                    exec_exitcode = ?,
                    event_id = ?
                ", array(
                    $dbserver->farmId,
                    $message->getServerId(),
                    $message->eventName,
                    sprintf("STDERR: %s \n\n STDOUT: %s", base64_decode($message->stderr), base64_decode($message->stdout)),
                    $message->scriptName,
                    $message->eventServerId,
                    round($message->timeElapsed, 2),
                    $message->returnCode,
                    $message->eventId
                ));

                if ($message->meta[Scalr_Messaging_MsgMeta::SZR_VERSION])
                    DBServer::LoadByID($message->getServerId())->SetProperty(SERVER_PROPERTIES::SZR_VESION, $message->meta[Scalr_Messaging_MsgMeta::SZR_VERSION]);

            } catch (Exception $e) {
                $this->logger->fatal($e->getMessage());
            }

        } elseif ($message instanceof Scalr_Messaging_Msg_Log) {

            try {
                if ($message->meta[Scalr_Messaging_MsgMeta::SZR_VERSION])
                    DBServer::LoadByID($message->getServerId())->SetProperty(SERVER_PROPERTIES::SZR_VESION, $message->meta[Scalr_Messaging_MsgMeta::SZR_VERSION]);
            } catch (Exception $e) {}

            foreach ($message->entries as $entry) {
                try {
                    if (self::$severityCodes[$entry->level] < 3)
                        continue;

                    $this->db->Execute("INSERT DELAYED INTO logentries SET
                        serverid = ?,
                        message = ?,
                        severity = ?,
                        time = ?,
                        source = ?,
                        farmid = ?
                    ", array(
                        $message->getServerId(),
                        $entry->msg,
                        self::$severityCodes[$entry->level],
                        time(),
                        $entry->name,
                        $dbserver->farmId
                    ));

                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        } elseif ($message instanceof Scalr_Messaging_Msg_RebundleLog) {
            try {
                $this->db->Execute("INSERT INTO bundle_task_log SET
                    bundle_task_id = ?,
                    dtadded = NOW(),
                    message = ?
                ", array(
                    $message->bundleTaskId,
                    $message->message
                ));
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        } elseif ($message instanceof Scalr_Messaging_Msg_DeployLog) {
            try {
                $this->db->Execute("INSERT INTO dm_deployment_task_logs SET
                    `dm_deployment_task_id` = ?,
                    `dtadded` = NOW(),
                    `message` = ?
                ", array(
                    $message->deployTaskId,
                    $message->message
                ));
            } catch (Exception $e) {}
        } elseif ($message instanceof Scalr_Messaging_Msg_OperationDefinition) {
            try {
                if ($message->name == 'Execute scripts')
                    return;

                $this->db->Execute("INSERT INTO server_operations SET
                    `id` = ?,
                    `server_id` = ?,
                    `timestamp` = ?,
                    `status`	= ?,
                    `name` = ?,
                    `phases` = ?
                ", array(
                    $message->id,
                    $dbserver->serverId,
                    time(),
                    'running',
                    $message->name,
                    json_encode($message->phases)
                ));
            } catch (Exception $e) {}
        } elseif ($message instanceof Scalr_Messaging_Msg_OperationProgress) {
            try {

                if ($message->warning) {
                    $msg = $message->warning->message;
                    $trace = $message->warning->trace;
                    $handler = $message->warning->handler;
                }

                $this->db->Execute("INSERT INTO server_operation_progress SET
                    `operation_id` = ?,
                    `timestamp` = ?,
                    `phase` = ?,
                    `step` = ?,
                    `status` = ?,
                    `message`= ?,
                    `trace` = ?,
                    `handler` = ?,
                    `progress` = ?,
                    `stepno` = ?
                    ON DUPLICATE KEY UPDATE status = ?, progress = ?, trace = ?, handler = ?, message = ?
                ", array(
                    $message->id,
                    $message->getTimestamp(),
                    $message->phase,
                    $message->step,
                    $message->status,
                    $msg,
                    $trace,
                    $handler,
                    $message->progress,
                    $message->stepno,
                    //
                    $message->status,
                    $message->progress,
                    $trace,
                    $handler,
                    $msg
                ));
            } catch (Exception $e) {}
        }
    }
}