<?php

class CheckRecoveredEvent extends Event
{
    public $dBServer;
    public $check;


    public function __construct(DBServer $dBServer, $check)
    {
        parent::__construct();
        $this->dBServer = $dBServer;
        $this->check = $check;
    }

    public static function GetScriptingVars()
    {
        return array("check" => "Check ID");
    }
}