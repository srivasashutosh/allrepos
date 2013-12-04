<?php

class IPAddressChangedEvent extends Event
{

    /**
     *
     * @var DBServer
     */
    public $DBServer;

    public $NewIPAddress;

    public $NewLocalIPAddress;

    public function __construct(DBServer $DBServer, $NewIPAddress, $NewLocalIPAddress = null)
    {
        parent::__construct();
        $this->DBServer = $DBServer;
        $this->NewIPAddress = $NewIPAddress;
        $this->NewLocalIPAddress = $NewLocalIPAddress;
    }

    public static function GetScriptingVars()
    {
        return array(
            "new_ip_address" => "NewIPAddress",
            "new_local_ip_address" => "NewLocalIPAddress"
        );
    }

    public function getTextDetails()
    {
        return "IP address for instance {$this->DBServer->serverId} has been changed to {$this->NewIPAddress}";
    }
}
