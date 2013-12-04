<?php

class FarmLaunchedEvent extends Event
{
    public $MarkInstancesAsActive;

    public function __construct($MarkInstancesAsActive)
    {
        parent::__construct();

        $this->MarkInstancesAsActive = $MarkInstancesAsActive;
    }

    public function getTextDetails()
    {
        return "Farm has been launched";
    }
}
