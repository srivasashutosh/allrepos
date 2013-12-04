<?php

class Scalr_Messaging_Msg_VhostReconfigure extends Scalr_Messaging_Msg {
    public $vhostName;
    public $isSslVhost;

    public $scripts = array();

    function __construct ($vhostName=null, $isSslVhost=null) {
        parent::__construct();
        $this->vhostName = $vhostName;
        $this->isSslVhost = $isSslVhost;
    }
}