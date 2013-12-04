<?php

class Scalr_Net_Scalarizr_Services_Sysinfo extends Scalr_Net_Scalarizr_Client
{
    public function __construct(DBServer $dbServer, $port = 8010) {
        $this->namespace = "sysinfo";
        parent::__construct($dbServer, $port);
    }

    public function statvfs(array $mountpoints) {
        $params = new stdClass();
        $params->mpoints = $mountpoints;

        return $this->request("statvfs", $params)->result;
    }
}