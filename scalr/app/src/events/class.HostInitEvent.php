<?php

class HostInitEvent extends Event
{

    /**
     * @var DBServer
     */
    public $DBServer;

    public $InternalIP;

    public $ExternalIP;

    public $PublicKey;

    public function __construct(DBServer $DBServer, $InternalIP, $ExternalIP, $PublicKey)
    {
        parent::__construct();
        $this->DBServer = $DBServer;
        $this->InternalIP = $InternalIP;
        $this->ExternalIP = $ExternalIP;
        $this->PublicKey = $PublicKey;
    }

    public function getTextDetails()
    {
        return "Instance {$this->DBServer->serverId} Public IP: {$this->DBServer->remoteIp} Internal IP: {$this->DBServer->localIp} initialized ";
    }
}
