<?php

class HostUpEvent extends Event
{

    /**
     *
     * @var DBServer
     */
    public $DBServer;

    public $ReplUserPass;

    public function __construct(DBServer $DBServer, $ReplUserPass)
    {
        parent::__construct();
        $this->DBServer = $DBServer;
        $this->ReplUserPass = $ReplUserPass;
    }

    public function getTextDetails()
    {
        return "Instance {$this->DBServer->serverId} Public IP: {$this->DBServer->remoteIp} Internal IP: {$this->DBServer->localIp} is UP";
    }
}
