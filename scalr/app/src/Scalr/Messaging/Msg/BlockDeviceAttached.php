<?php

class Scalr_Messaging_Msg_BlockDeviceAttached extends Scalr_Messaging_Msg {
    public $volumeId;
    public $deviceName;

    function __construct ($volumeId = null, $deviceName = null) {
        parent::__construct();
        $this->volumeId = $volumeId;
        $this->deviceName = $deviceName;
    }
}