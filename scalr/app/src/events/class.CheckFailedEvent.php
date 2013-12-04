<?php

class CheckFailedEvent extends Event
{

    public $dBServer;
    public $check;
    public $details;


    public function __construct(DBServer $dBServer, $check, $details)
    {
        parent::__construct();
        $this->dBServer = $dBServer;
        $this->check = $check;
        $this->details = $details;
    }

    public static function GetScriptingVars()
    {
        return array("check" => "Check ID", "details" => "Details");
    }
}