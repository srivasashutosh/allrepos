<?php

class DBQueueEventProcess implements \Scalr\System\Pcntl\ProcessInterface
{
    public $ThreadArgs;
    public $ProcessDescription = "Process events queue";
    public $Logger;
    public $IsDaemon = true;
    private $DaemonMtime;
    private $DaemonMemoryLimit = 20; // in megabytes

    public function __construct()
    {
        // Get Logger instance
        $this->Logger = Logger::getLogger(__CLASS__);

        $this->DaemonMtime = @filemtime(__FILE__);
    }

    public function OnStartForking()
    {
        $db = \Scalr::getDb();

        // Get pid of running daemon
        $pid = @file_get_contents(CACHEPATH . "/" . __CLASS__ . ".Daemon.pid");

        $this->Logger->info("Current daemon process PID: {$pid}");

        // Check is daemon already running or not
        if ($pid) {
            $Shell = new Scalr_System_Shell();

            // Set terminal width
            putenv("COLUMNS=400");

            // Execute command
            $ps = $Shell->queryRaw("ps ax -o pid,ppid,command | grep ' 1' | grep {$pid} | grep -v 'ps x' | grep DBQueueEvent");

            $this->Logger->info("Shell->queryRaw(): {$ps}");
            if ($ps) {
                // daemon already running
                $this->Logger->info("Daemon running. All ok!");
                return true;
            }
        }

        $rows = $db->Execute("SELECT * FROM events WHERE ishandled = '0' ORDER BY id ASC");
        while ($dbevent = $rows->FetchRow()) {
            try {
                //TODO: Initialize Event classes
                $Event = unserialize($dbevent['event_object']);
                if ($Event) {
                    Logger::getLogger(__CLASS__)->info(sprintf(_("Fire event %s for farm: %s"), $Event->GetName(), $Event->GetFarmID()));
                    // Fire event
                    Scalr::FireDeferredEvent($Event);
                }

                //$db->Execute("UPDATE events SET ishandled='1', event_object = '' WHERE id=?", array($dbevent['id']));
                $db->Execute("UPDATE events SET ishandled='1' WHERE id=?", array($dbevent['id']));
            }
            catch(Exception $e) {
                Logger::getLogger(__CLASS__)->fatal(sprintf(_("Cannot fire deferred event: %s"), $e->getMessage()));
            }
        }
    }

    public function OnEndForking()
    {

    }

    public function StartThread($eventId)
    {
        /*
        // Reconfigure observers;
        Scalr::ReconfigureObservers();

        //
        // Create pid file
        //
        @file_put_contents(CACHEPATH."/".__CLASS__.".Daemon.pid", posix_getpid());

        // Get memory usage on start
        $memory_usage = $this->GetMemoryUsage();
        $this->Logger->info("DBQueueEventProcess daemon started. Memory usage: {$memory_usage}M");

        // Get DB instance
        $db = \Scalr::getDb();

        $FarmObservers = array();

        while(true)
        {
            // Process tasks from Deferred event queue
            while ($Task = TaskQueue::Attach(QUEUE_NAME::DEFERRED_EVENTS)->Poll())
            {
                $Task->Run();
            }
            // Reset task
            TaskQueue::Attach(QUEUE_NAME::DEFERRED_EVENTS)->Reset();

            // Cleaning
            unset($current_memory_usage);
            unset($event);

            // Check memory usage
            $current_memory_usage = $this->GetMemoryUsage()-$memory_usage;
            if ($current_memory_usage > $this->DaemonMemoryLimit)
            {
                $this->Logger->warn("DBQueueEventProcess daemon reached memory limit {$this->DaemonMemoryLimit}M, Used:{$current_memory_usage}M");
                $this->Logger->warn("Restart daemon.");
                exit();
            }

            // Sleep for 60 seconds
            sleep(15);

            // Clear stat file cache
            clearstatcache();

            // Check daemon file for modifications.
            if ($this->DaemonMtime && $this->DaemonMtime < @filemtime(__FILE__))
            {
                $this->Logger->warn(__FILE__." - updated. Exiting for daemon reload.");
                exit();
            }
        }
        */
    }

    /**
     * Return current memory usage by process
     *
     * @return float
     */
    private function GetMemoryUsage()
    {
        return round(memory_get_usage(true)/1024/1024, 2);
    }
}
