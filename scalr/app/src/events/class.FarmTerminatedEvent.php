<?php

class FarmTerminatedEvent extends Event
{
    public $RemoveZoneFromDNS;
    public $KeepElasticIPs;
    public $TermOnSyncFail;
    public $KeepEBS;
    public $ForceTerminate;

    public function __construct($RemoveZoneFromDNS, $KeepElasticIPs, $TermOnSyncFail, $KeepEBS, $ForceTerminate = true)
    {
        parent::__construct();

        $this->RemoveZoneFromDNS = $RemoveZoneFromDNS;
        $this->KeepElasticIPs = $KeepElasticIPs;
        $this->TermOnSyncFail = $TermOnSyncFail;
        $this->KeepEBS = $KeepEBS;
        $this->ForceTerminate = $ForceTerminate;
    }

    public function getTextDetails()
    {
        return "Farm has been terminated";
    }
}
