<?php

class EBSVolumeAttachedEvent extends Event
{
    public $DeviceName;
    public $VolumeID;

    /**
     *
     * @var DBServer
     */
    public $DBServer;

    public function __construct(DBServer $DBServer, $DeviceName, $VolumeID)
    {
        parent::__construct();

        $this->DBServer = $DBServer;
        $this->DeviceName = $DeviceName;
        $this->VolumeID = $VolumeID;
    }

    public static function GetScriptingVars()
    {
        return array("device_name" => "DeviceName", "volume_id" => "VolumeID");
    }

    public function getTextDetails()
    {
        return "EBS volume {$this->VolumeID} successfully atatched as {$this->DeviceName}";
    }
}
