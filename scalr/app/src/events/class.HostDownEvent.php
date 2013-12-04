<?php

class HostDownEvent extends Event
{

    /**
     * @var DBServer
     */
    public $DBServer;

    /**
     *
     * @var DBServer
     */
    public $replacementDBServer;

    public function __construct(DBServer $DBServer)
    {
        parent::__construct();
        $this->DBServer = $DBServer;
        $r_server = \Scalr::getDb()->GetRow("SELECT server_id FROM servers WHERE replace_server_id=?", array(
            $DBServer->serverId
        ));
        if ($r_server) {
            $this->replacementDBServer = DBServer::LoadByID($r_server['server_id']);
        }
    }

    public function getTextDetails()
    {
        return "Instance {$this->DBServer->serverId} ({$this->DBServer->remoteIp}) Internal IP: {$this->DBServer->localIp} terminated";
    }
}
