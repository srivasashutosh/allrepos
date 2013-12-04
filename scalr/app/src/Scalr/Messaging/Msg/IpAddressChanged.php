<?php

class Scalr_Messaging_Msg_IpAddressChanged extends Scalr_Messaging_Msg {
    public $newRemoteIp;
    public $newLocalIp;

    function __construct ($newRemoteIp=null, $newLocalIp=null) {
        parent::__construct();
        $this->newRemoteIp = $newRemoteIp;
        $this->newLocalIp = $newLocalIp;
    }
}