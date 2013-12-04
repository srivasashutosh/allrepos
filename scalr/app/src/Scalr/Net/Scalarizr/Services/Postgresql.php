<?php

class Scalr_Net_Scalarizr_Services_Postgresql extends Scalr_Net_Scalarizr_Client
{
    public function __construct(DBServer $dbServer, $port = 8010) {
        $this->namespace = "postgresql";
        parent::__construct($dbServer, $port);
    }

    public function replicationStatus()
    {
        return $this->request("replication_status")->result;
    }
}