<?php

class Scalr_Messaging_Msg_DbMsr_NewMasterUp extends Scalr_Messaging_Msg_DbMsr {
    public $dbType;

    function __construct ($dbType = null) {
        parent::__construct();
        $this->dbType = $dbType;
    }
}