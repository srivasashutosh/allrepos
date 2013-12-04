<?php

class Scalr_Messaging_Msg_HostInit extends Scalr_Messaging_Msg {
    public $cryptoKey;
    public $sshPubKey;
    public $snmpPort;
    public $snmpCommunityName;

    public $serverIndex;

    function __construct ($cryptoKey = null, $sshPubKey = null) {
        parent::__construct();
        $this->cryptoKey = $cryptoKey;
        $this->sshPubKey = 	$sshPubKey;
    }
}