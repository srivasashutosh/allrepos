<?php

class Scalr_Net_Scalarizr_Services_Service extends Scalr_Net_Scalarizr_Client
{
    public function __construct(DBServer $dbServer, $port = 8010) {
        $this->namespace = "service";
        parent::__construct($dbServer, $port);
    }

    public function getPreset($behavior)
    {
        $params = new stdClass();
        $params->behavior = $behavior;

        return $this->request("get_preset", $params);
    }

    public function setPreset($behavior, $settings)
    {
        $params = new stdClass();
        $params->behavior = $behavior;
        $params->values = $settings;

        return $this->request("set_preset", $params);
    }
}