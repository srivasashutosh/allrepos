<?php

class Scalr_UI_Controller_Platforms_Gce extends Scalr_UI_Controller
{
    public function xGetOptionsAction()
    {
        $p = PlatformFactory::NewPlatform(SERVER_PLATFORMS::GCE);

        $client = new Google_Client();
        $client->setApplicationName("Scalr GCE");
        $client->setScopes(array('https://www.googleapis.com/auth/compute'));

        $key = base64_decode($this->environment->getPlatformConfigValue(Modules_Platforms_GoogleCE::KEY));
        $client->setAssertionCredentials(new Google_AssertionCredentials(
            $this->environment->getPlatformConfigValue(Modules_Platforms_GoogleCE::SERVICE_ACCOUNT_NAME),
            array('https://www.googleapis.com/auth/compute'),
            $key
        ));

        $client->setUseObjects(true);
        $client->setClientId($this->environment->getPlatformConfigValue(Modules_Platforms_GoogleCE::CLIENT_ID));

        $projectId = $this->environment->getPlatformConfigValue(Modules_Platforms_GoogleCE::PROJECT_ID);

        $gceClient = new Google_ComputeService($client);

        $data['types'] = array();
        $data['dbTypes'] = array();
        $types = $gceClient->machineTypes->listMachineTypes($projectId);
        foreach ($types->items as $item) {
            $isEphemeral = (substr($item->name, -2) == '-d');

            if ($isEphemeral) {
                $data['dbTypes'][] = array(
                    'name' => $item->name,
                    'description' => "{$item->name} ({$item->description})"
                );
            }

            $data['types'][] = array(
                'name' => $item->name,
                'description' => "{$item->name} ({$item->description})"
            );
        }

        $data['zones'] = array();
        $zones = $gceClient->zones->listZones($projectId);
        foreach ($zones->items as $item) {
            $data['zones'][] = array(
                'name' => $item->name,
                'description' => $item->description,
                'state' => $item->status
            );
        }

        $data['networks'] = array();
        $networks = $gceClient->networks->listNetworks($projectId);
        foreach ($networks->items as $item) {
            $data['networks'][] = array(
                'name' => $item->name,
                'description' => "{$item->description} ({$item->IPv4Range})"
            );
        }


        $this->response->data(array('data' => $data));
    }
}
