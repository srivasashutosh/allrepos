<?php

class Scalr_UI_Controller_Tools_Cloudstack_Snapshots extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'snapshotId';

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        if ($this->getParam('platform')) {
            $locations = self::loadController('Platforms')->getCloudLocations(array($this->getParam('platform')), false);
        } else {
            $locations = self::loadController('Platforms')->getCloudLocations(array(SERVER_PLATFORMS::CLOUDSTACK, SERVER_PLATFORMS::IDCF, SERVER_PLATFORMS::UCLOUD), false);
        }

        $this->response->page('ui/tools/cloudstack/snapshots/view.js', array(
            'locations'	=> $locations
        ));
    }

    public function xRemoveAction()
    {
        $this->request->defineParams(array(
            'snapshotId' => array('type' => 'json'),
            'cloudLocation'
        ));

        $platformLocations = self::loadController('Platforms')->getEnabledPlatforms(true);
        foreach ($platformLocations as $p => $details) {
            foreach ($details['locations'] as $key => $loc) {
                if ($key == $this->getParam('cloudLocation')) {
                    $platformName = $p;
                    break;
                }
            }

            if ($platformName)
                break;
        }

        $platform = PlatformFactory::NewPlatform($platformName);

        $cs = Scalr_Service_Cloud_Cloudstack::newCloudstack(
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_URL, $this->environment),
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_KEY, $this->environment),
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::SECRET_KEY, $this->environment),
            $platformName
        );

        foreach ($this->getParam('snapshotId') as $snapshotId) {
            $cs->deleteSnapshot($snapshotId);
        }

        $this->response->success('Volume(s) successfully removed');
    }

    public function xListSnapshotsAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'json', 'default' => array('property' => 'volumeId', 'direction' => 'ASC')),
            'volumeId'
        ));

        $platformLocations = self::loadController('Platforms')->getEnabledPlatforms(true);
        foreach ($platformLocations as $p => $details) {
            foreach ($details['locations'] as $key => $loc) {
                if ($key == $this->getParam('cloudLocation')) {
                    $platformName = $p;
                    break;
                }
            }

            if ($platformName)
                break;
        }

        $platform = PlatformFactory::NewPlatform($platformName);

        $cs = Scalr_Service_Cloud_Cloudstack::newCloudstack(
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_URL, $this->environment),
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_KEY, $this->environment),
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::SECRET_KEY, $this->environment),
            $platformName
        );

        $snapshots = $cs->listSnapshots();

        $snaps = array();
        foreach ($snapshots as $pk=>$pv)
        {
            if ($this->getParam('snapshotId') && $this->getParam('snapshotId') != $pv->id)
                continue;

            $item = array(
                'snapshotId'	=> $pv->id,
                'type'	=> $pv->snapshottype,
                'volumeId' => $pv->volumeid,
                'volumeType' => $pv->volumetype,
                'createdAt' => $pv->created,
                'intervalType' => $pv->intervaltype,
                'state'	=> $pv->state
            );

            $snaps[] = $item;
        }

        $response = $this->buildResponseFromData($snaps, array('snapshotId', 'volumeId', 'state'));

        $this->response->data($response);
    }
}
