<?php

class BeforeHostTerminateEvent extends Event
{
    /**
     *
     * @var DBServer
     */
    public $DBServer;

    public $ForceTerminate;

    public function __construct(DBServer $DBServer, $ForceTerminate = true)
    {
        parent::__construct();

        $this->DBServer = $DBServer;
        $this->ForceTerminate = $ForceTerminate;
    }
}
