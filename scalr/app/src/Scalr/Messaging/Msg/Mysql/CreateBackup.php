<?php

class Scalr_Messaging_Msg_Mysql_CreateBackup extends Scalr_Messaging_Msg {

    public $rootPassword;

    public $scripts = array();

    function __construct ($rootPassword) {
        parent::__construct();
        $this->rootPassword = $rootPassword;
    }
}