<?php

class Scalr_Messaging_Msg_Mysql_NewMasterUp extends Scalr_Messaging_Msg {
    public $snapPlacement;

    function __construct ($snapPlacement = null) {
        parent::__construct();
        $this->snapPlacement = $snapPlacement;
    }
}