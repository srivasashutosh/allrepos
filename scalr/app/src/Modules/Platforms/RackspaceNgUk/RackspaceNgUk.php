<?php

class Modules_Platforms_RackspaceNgUk extends Modules_Platforms_RackspaceNgUs implements IPlatformModule
{

    public function __construct()
    {
        parent::__construct(SERVER_PLATFORMS::RACKSPACENG_UK);
    }

    public function getLocations()
    {
        return array(
            'LON' => 'Rackspace UK / LON'
        );
    }
}
