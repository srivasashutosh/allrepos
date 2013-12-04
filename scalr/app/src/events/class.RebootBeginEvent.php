<?php

class RebootBeginEvent extends Event
{

    /**
     *
     * @var DBServer
     */
    public $DBServer;

    public function __construct(DBServer $DBServer)
    {
        parent::__construct();
        $this->DBServer = $DBServer;
    }

    public function getTextDetails()
    {
        return "Instance {$this->DBServer->serverId} Public IP: {$this->DBServer->remoteIp} Internal IP: {$this->DBServer->localIp} is going to reboot ";
    }
}
