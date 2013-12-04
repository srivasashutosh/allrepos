<?php
namespace Scalr\Server;

class Alerts
{
    const METRIC_SCALARIZR_CONNECTIVITY = 10001;
    const METRIC_SCALARIZR_UPD_CLIENT_CONNECTIVITY = 10002;

    const METRIC_AWS_SYSTEM_STATUS = 20003;
    const METRIC_AWS_INSTANCE_STATUS = 20004;

    const METRIC_SERVICE_MYSQL_BACKUP_FAILED = 30001;
    const METRIC_SERVICE_MYSQL_BUNDLE_FAILED = 30002;
    const METRIC_SERVICE_MYSQL_REPLICATION_FAILED = 30003;

    //TODO: Add metrics for other services

    const STATUS_FAILED = 'failed';
    const STATUS_RESOLVED = 'resolved';

    /**
     * @var \DBServer $dbServer;
     */
    private $dbServer;

    public function __construct(\DBServer $dbServer)
    {
        $this->dbServer = $dbServer;
        $this->db = \Scalr::getDb();
    }

    public static function getMetricName($metric)
    {
        switch ($metric) {
            case self::METRIC_SCALARIZR_CONNECTIVITY:
                return "Scalr <-> Scalarizr communication";
                break;
            case self::METRIC_SCALARIZR_UPD_CLIENT_CONNECTIVITY:
                return "Scalr <-> Scalarizr Update Client communication";
                break;
            case self::METRIC_AWS_INSTANCE_STATUS:
                    return "AWS instance reachability check";
                break;
            case self::METRIC_AWS_SYSTEM_STATUS:
                    return "AWS system reachability check";
                break;
        }
    }

    public function getActiveAlertsCount()
    {
        return (int)$this->db->GetOne("SELECT COUNT(*) FROM `server_alerts` WHERE
            `server_id` = ? AND `status` = ?
        ", array(
            $this->dbServer->serverId,
            self::STATUS_FAILED
        ));
    }

    public function hasActiveAlert($metric) {
        return (int)$this->db->GetOne("SELECT `id` FROM `server_alerts` WHERE
            `server_id` = ? AND `metric` = ? AND `status` = ?
        ", array(
            $this->dbServer->serverId,
            $metric,
            self::STATUS_FAILED
        )) > 0 ? true : false;
    }

    public function updateLastCheckTime($metric)
    {
        $this->db->Execute("UPDATE `server_alerts` SET `dtlastcheck` = NOW() WHERE
            `server_id` = ? AND `metric` = ? AND `status` = ?
        ", array(
            $this->dbServer->serverId,
            $metric,
            self::STATUS_FAILED
        ));
    }

    public function createAlert($metric, $details) {
        $this->db->Execute("INSERT INTO `server_alerts` SET
            `env_id` = ?,
            `farm_id` = ?,
            `farm_roleid` = ?,
            `server_index` = ?,
            `server_id` = ?,
            `metric` = ?,
            `dtoccured` = NOW(),
            `details` = ?,
            `status` = ?
        ", array(
            $this->dbServer->envId,
            $this->dbServer->farmId,
            $this->dbServer->farmRoleId,
            $this->dbServer->index,
            $this->dbServer->serverId,
            $metric,
            $details,
            self::STATUS_FAILED
        ));
    }

    public function solveAlert($metric = null) {

        if ($metric) {
            $this->db->Execute("UPDATE `server_alerts` SET `status` = ?, `dtsolved` = NOW() WHERE
                `server_id` = ? AND `metric` = ? AND `status` = ?
            ", array(
                self::STATUS_RESOLVED,
                $this->dbServer->serverId,
                $metric,
                self::STATUS_FAILED
            ));
        } else {
            $this->db->Execute("UPDATE `server_alerts` SET `status` = ?, `dtsolved` = NOW() WHERE
                `server_id` = ? AND `status` = ?
            ", array(
                self::STATUS_RESOLVED,
                $this->dbServer->serverId,
                self::STATUS_FAILED
            ));
        }
    }
}
