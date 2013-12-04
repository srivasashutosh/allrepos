<?php

class Scalr_Net_Scalarizr_Services_System extends Scalr_Net_Scalarizr_Client
{
    public function __construct(DBServer $dbServer, $port = 8010) {
        $this->namespace = "system";
        parent::__construct($dbServer, $port);
    }

    public function callAuthShutdownHook() {
        return $this->request("call_auth_shutdown_hook")->result;
    }

    public function scalingMetrics() {
        return $this->request("scaling_metrics")->result;
    }

    public function blockDevices() {
        return $this->request("block_devices")->result;
    }

    public function statvfs(array $mountpoints) {
        $params = new stdClass();
        $params->mpoints = $mountpoints;

        return $this->request("statvfs", $params)->result;
    }

    public function loadAverage()
    {
        return $this->request("load_average")->result;
    }

    public function memInfo()
    {
        return $this->request("mem_info")->result;
    }

    public function cpuStat()
    {
        return $this->request("cpu_stat")->result;
    }

    public function dist()
    {
        return $this->request("dist")->result;
    }
}