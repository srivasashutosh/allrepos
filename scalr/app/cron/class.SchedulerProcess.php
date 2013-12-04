<?php

class SchedulerProcess implements \Scalr\System\Pcntl\ProcessInterface
{
    public $ThreadArgs;
    public $ProcessDescription = "Schedule manager";
    public $Logger;
    public $IsDaemon;

    public function __construct()
    {
        // Get Logger instance
        $this->Logger = Logger::getLogger(__CLASS__);
    }

    public function OnStartForking()
    {
        // start cron, which runs scripts by it's schedule queue
        try {
            $db = \Scalr::getDb();

            // set status to "finished" for active tasks, which ended or executed once
            $db->Execute("UPDATE scheduler SET `status` = ? WHERE
                `status` = ? AND (
                    CONVERT_TZ(`end_time`,'SYSTEM',`timezone`) < CONVERT_TZ(NOW(),'SYSTEM',`timezone`) OR
                    (CONVERT_TZ(`last_start_time`,'SYSTEM',`timezone`) < CONVERT_TZ(NOW(),'SYSTEM',`timezone`) AND `restart_every` = 0)
                )",
                array(Scalr_SchedulerTask::STATUS_FINISHED, Scalr_SchedulerTask::STATUS_ACTIVE)
            );

            // get active tasks: first run (condition and last_start_time is null), others (condition and last_start_time + interval * 0.9 < now())
            $taskList = $db->GetAll("SELECT *
                FROM scheduler
                WHERE
                    `status` = ? AND
                    (`end_time` IS NULL OR CONVERT_TZ(`end_time`,'SYSTEM',`timezone`) > CONVERT_TZ(NOW(),'SYSTEM',`timezone`)) AND
                    (`start_time` IS NULL OR CONVERT_TZ(`start_time`,'SYSTEM',`timezone`) <= CONVERT_TZ(NOW(),'SYSTEM',`timezone`)) AND
                    (
                        `last_start_time` IS NULL OR
                        `last_start_time` IS NOT NULL AND `start_time` IS NULL AND (CONVERT_TZ(last_start_time + INTERVAL restart_every MINUTE, 'SYSTEM', `timezone`) < CONVERT_TZ(NOW(),'SYSTEM',`timezone`)) OR
                        `last_start_time` IS NOT NULL AND `start_time` IS NOT NULL AND (CONVERT_TZ(last_start_time + INTERVAL (restart_every * 0.9) MINUTE, 'SYSTEM', `timezone`) < CONVERT_TZ(NOW(),'SYSTEM',`timezone`))
                    )
                ORDER BY IF (last_start_time, last_start_time, start_time), order_index ASC
            ", array(Scalr_SchedulerTask::STATUS_ACTIVE));

            if (!$taskList) {
                $this->Logger->info(_("There is no tasks to execute in scheduler table"));
                exit();
            }

            foreach ($taskList as $task) {
                // check account status (active or inactive)
                try {
                    if (Scalr_Account::init()->loadById($task['account_id'])->status != Scalr_Account::STATUS_ACTIVE)
                        continue;
                } catch (Exception $e) {
                    $this->Logger->info("Invalid scheduler task #{$task['id']}: {$e->getMessage()}");
                }

                if ($task['last_start_time'] && $task['start_time']) {
                    // try to auto-align time to start time
                    $startTime = new DateTime($task['start_time']);
                    $startTime->setTimezone(new DateTimeZone($task['timezone']));
                    $currentTime = new DateTime('now', new DateTimeZone($task['timezone']));

                    $offset = $startTime->getOffset() - $currentTime->getOffset();
                    $num = ($currentTime->getTimestamp() - $startTime->getTimestamp() - $offset) / ($task['restart_every'] * 60);

                    // num should be less than 0.5
                    if (floor($num) != round($num, 0, PHP_ROUND_HALF_UP))
                        continue;
                }

                $taskObj = new Scalr_SchedulerTask();
                $taskObj->loadById($task['id']);

                if ($taskObj->execute()) {
                    // convert_tz
                    $taskObj->updateLastStartTime();
                    $this->Logger->info(sprintf("Task {$taskObj->id} successfully sent"));
                }
            }
        } catch(Exception $e) {
            $this->Logger->warn(sprintf("Can't execute task {$task['id']}. Error message: %s", $e->getMessage()));
        }
    }

    public function OnEndForking()
    {

    }
    public function StartThread($queue_name)
    {

    }
}
