<?php

class MySQLReplicationFailEvent extends Event
{

    /**
     *
     * @var DBInstance
     */
    public $DBServer;

    public function __construct(DBServer $DBServer)
    {
        parent::__construct();
        $this->DBServer = $DBServer;
    }

    public function getTextDetails()
    {
        return "Mysql replication fail on instance {$this->DBServer->serverId} Public IP: {$this->DBServer->remoteIp} Internal IP: {$this->DBServer->localIp} ";
    }
}
