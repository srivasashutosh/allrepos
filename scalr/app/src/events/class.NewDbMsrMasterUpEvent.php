<?php

class NewDbMsrMasterUpEvent extends Event
{

    /**
     * @var DBServer
     */
    public $DBServer;

    /**
     * @var DBServer
     */
    public $OldMasterDBServer;

    public function __construct(DBServer $DBServer)
    {
        parent::__construct();
        $this->DBServer = $DBServer;
    }

    public function getTextDetails()
    {
        return "New database master UP: {$this->DBServer->serverId} Public IP: {$this->DBServer->remoteIp} Internal IP: {$this->DBServer->localIp}";
    }
}
