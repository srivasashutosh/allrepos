<?php

class Scalr_Messaging_Msg {

    public $messageId;

    protected $messageName;

    public $meta = array();

    public $handlers = array();

    public $behaviour,
        $roleName,
        $localIp,
        $remoteIp,
        $serverIndex,
        $serverId,
        $cloudLocation,
        $farmRoleId;


    function __construct () {
        $this->messageId = Scalr::GenerateUID();
        $this->meta[Scalr_Messaging_MsgMeta::SCALR_VERSION] = SCALR_VERSION;
    }

    public function setServerMetaData(DBServer $dbServer) {
        $this->behaviour = $dbServer->GetFarmRoleObject()->GetRoleObject()->getBehaviors();
        $this->roleName = $dbServer->GetFarmRoleObject()->GetRoleObject()->name;
        $this->localIp = $dbServer->localIp;
        $this->remoteIp = $dbServer->remoteIp;
        $this->serverIndex = $dbServer->index;
        $this->serverId = $dbServer->serverId;
        $this->cloudLocation = $dbServer->GetCloudLocation();
        $this->farmRoleId = $dbServer->farmRoleId;
    }

    function setName($name) {
        if ($this->messageName === null)
            $this->messageName = $name;
    }

    function getName () {
        if ($this->messageName === null) {
            $this->messageName = substr(get_class($this), strlen(__CLASS__) + 1);
        }
        return $this->messageName;
    }

    function getTimestamp() {
        return strtotime($this->meta[Scalr_Messaging_MsgMeta::TIMESTAMP]);
    }

    function getServerId () {
        return $this->meta[Scalr_Messaging_MsgMeta::SERVER_ID];
    }

    function setServerId ($serverId) {
        $this->meta[Scalr_Messaging_MsgMeta::SERVER_ID] = $serverId;
    }

    static function getClassForName ($name) {

        if (class_exists(__CLASS__ . "_" . $name))
            return __CLASS__ . "_" . $name;
        else
            return __CLASS__;
    }
}