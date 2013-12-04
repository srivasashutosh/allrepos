<?php

class RebundleFailedEvent extends Event
{

    /**
     * @var DBServer
     */
    public $DBServer;

    public $BundleTaskID;

    public function __construct(DBServer $DBServer, $BundleTaskID, $LastErrorMessage)
    {
        parent::__construct();
        $this->DBServer = $DBServer;
        $this->BundleTaskID = $BundleTaskID;
        $this->LastErrorMessage = $LastErrorMessage;
    }

    public function getTextDetails()
    {
        return "Rebundle started on instance {$this->DBServer->remoteIp} ({$this->DBServer->serverId}) for farm #{$this->DBServer->farmId} failed.";
    }
}
