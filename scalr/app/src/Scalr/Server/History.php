<?php

class Scalr_Server_History
{
    public
        $id,
        $serverId,
        $clientId,
        $envId,
        $farmId,
        $farmRoleId,
        $serverIndex,
        $cloudServerId,
        $dateLaunched,
        $dateTerminated,
        $dateTerminatedScalr,
        $launchReason,
        $terminateReason,
        $shutdownConfirmed,
        $platfrom,
        $type;

    const SHUTDOWN_CONFIRMATION_STATE_NAN = '0';
    const SHUTDOWN_CONFIRMATION_STATE_CONFIRMED = '1';
    const SHUTDOWN_CONFIRMATION_STATE_PENDING = '2';

    public static $dbPropertyMap = array(
        'id' => 'id',
        'client_id' => 'clientId',
        'server_id' => 'serverId',
        'cloud_server_id' => 'cloudServerId',
        'dtlaunched' => 'dateLaunched',
        'dtterminated' => 'dateTerminated',
        'dtterminated_scalr' => 'dateTerminatedScalr',
        'launch_reason'  => 'launchReason',
        'terminate_reason' => 'terminateReason',
        'platform' => 'platform',
        'type' => 'type',
        'env_id' => 'envId',
        'farm_id' => 'farmId',
        'farm_roleid' => 'farmRoleId',
        'server_index' 	=> 'serverIndex',
        'shutdown_confirmed' => 'shutdownConfirmed'
    );

    public static function init(DBServer $dbServer)
    {
        $serverHistory = new self();

        $db = \Scalr::getDb();
        $info = $db->GetRow("SELECT * FROM servers_history WHERE server_id = ?", array($dbServer->serverId));
        if (!$info) {
            $serverHistory->clientId = $dbServer->clientId;
            $serverHistory->serverId = $dbServer->serverId;
            $serverHistory->envId	= $dbServer->envId;
            $serverHistory->farmId   = $dbServer->farmId;
            $serverHistory->farmRoleId = $dbServer->farmRoleId;
            $serverHistory->serverIndex = $dbServer->index;
            $serverHistory->platform = $dbServer->platform;
            $serverHistory->shutdownConfirmed = self::SHUTDOWN_CONFIRMATION_STATE_NAN;

            $serverHistory->save();

        } else {
            foreach (self::$dbPropertyMap as $dbProp => $objProp) {
                $serverHistory->{$objProp} = $info[$dbProp];
            }

            $serverHistory->envId	= $dbServer->envId;
            $serverHistory->farmId   = $dbServer->farmId;
            $serverHistory->farmRoleId = $dbServer->farmRoleId;
            $serverHistory->serverIndex = $dbServer->index;
        }

        if (!$serverHistory->cloudServerId)
        {
            switch ($serverHistory->platform) {
                case SERVER_PLATFORMS::EC2:
                    $serverHistory->type = $dbServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_TYPE);
                    $serverHistory->cloudServerId = $dbServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID);
                    break;
                case SERVER_PLATFORMS::RACKSPACE:
                    $serverHistory->type = $dbServer->GetProperty(RACKSPACE_SERVER_PROPERTIES::FLAVOR_ID);
                    $serverHistory->cloudServerId = $dbServer->GetProperty(RACKSPACE_SERVER_PROPERTIES::SERVER_ID);
                    break;
            }
        }

        unset($db);

        return $serverHistory;
    }

    public function __construct()
    {
        $this->db = \Scalr::getDb();
    }

    public function setLaunchReason($reason)
    {
        $this->launchReason = $reason;
        $this->dateLaunched = date("Y-m-d H:i:s");
        $this->save();
    }

    public function markAsTerminated($reason, $terminatedInCloud = false)
    {
        $this->terminateReason = $reason;
        $this->dateTerminatedScalr = date("Y-m-d H:i:s");

        if ($terminatedInCloud)
            $this->dateTerminated = date("Y-m-d H:i:s");


        $this->shutdownConfirmed = self::SHUTDOWN_CONFIRMATION_STATE_PENDING;
        $this->save();
    }

    public function save()
    {
        $set = array();
        $bind = array();

        foreach (self::$dbPropertyMap as $field => $value) {
            if ($field == 'id')
                continue;

            $val = $this->{$value};

            $set[] = "`{$field}` = ?";
            $bind[] = $val;
        }
        $set = implode(', ', $set);

        try {
            if ($this->id) {
                // Perform Update
                $bind[] = $this->id;
                $this->db->Execute("UPDATE servers_history SET {$set} WHERE id = ?", $bind);
            } else {
                // Perform Insert
                $this->db->Execute("INSERT INTO servers_history SET {$set}", $bind);

                if (!$this->id)
                    $this->id = $this->db->Insert_ID();
            }
        } catch (Exception $e) {
            throw new Exception (sprintf(_("Cannot save server history record. Error: %s"), $e->getMessage()), $e->getCode());
        }

        return $this;
    }
}
