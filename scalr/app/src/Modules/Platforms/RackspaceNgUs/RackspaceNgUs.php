<?php

class Modules_Platforms_RackspaceNgUs extends Modules_Platforms_Openstack implements IPlatformModule
{

    const EXT_IS_ACCOUNT_MANAGED    = 'ext.is_account_managed';

    public function __construct($platform = SERVER_PLATFORMS::RACKSPACENG_US)
    {
        parent::__construct($platform);
    }


    public function getLocations()
    {
        return array(
            'ORD' => 'Rackspace US / ORD',
            'DFW' => 'Rackspace US / DFW'
        );
    }

    public function GetServerIPAddresses(DBServer $DBServer)
    {
        $client = $this->getOsClient($DBServer->GetEnvironmentObject(), $DBServer->GetProperty(OPENSTACK_SERVER_PROPERTIES::CLOUD_LOCATION));
        $result = $client->servers->getServerDetails($DBServer->GetProperty(OPENSTACK_SERVER_PROPERTIES::SERVER_ID));

        if ($result->accessIPv4)
            $remoteIp = $result->accessIPv4;

        if (!$remoteIp) {
            if (is_array($result->addresses->public))
                foreach ($result->addresses->public as $addr)
                    if ($addr->version == 4) {
                        $remoteIp = $addr->addr;
                        break;
                    }
        }

        if (is_array($result->addresses->private))
            foreach ($result->addresses->private as $addr)
                if ($addr->version == 4) {
                    $localIp = $addr->addr;
                    break;
                }

        if (!$localIp)
            $localIp = $remoteIp;

        return array(
            'localIp'   => $localIp,
            'remoteIp'  => $remoteIp
        );
    }
}
