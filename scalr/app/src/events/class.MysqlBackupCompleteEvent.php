<?php

class MysqlBackupCompleteEvent extends Event
{

    /**
     * @var DBServer
     */
    public $DBServer;

    public $Operation;

    public $SnapshotInfo;

    public function __construct(DBServer $DBServer, $Operation, $SnapInfo = array())
    {
        parent::__construct();
        $this->DBServer = $DBServer;
        $this->Operation = $Operation;
        $this->SnapshotInfo = $SnapInfo;
    }

    public function getTextDetails()
    {
        return "MySQL backup complete";
    }
}
