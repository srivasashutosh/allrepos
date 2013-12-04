<?php

class Scalr_Messaging_Msg_BlockDeviceMounted extends Scalr_Messaging_Msg {

    public $volumeId;
    public $deviceName;
    public $isArray;
    public $name;
    public $mountpoint;

    function __construct ($volumeId = null, $deviceName = null, $mountpoint = null, $isArray = null, $name = null) {
        parent::__construct();

        $this->volumeId = $volumeId;
        $this->deviceName = $deviceName;
        $this->mountpoint = $mountpoint;
        $this->isArray = $isArray;
        $this->name = $name;
    }
}