<?php

class Scalr_UI_Controller_Tools_Rackspace extends Scalr_UI_Controller
{
    public function xListLimitsAction()
    {
        $cloudLocation = $this->getParam('cloudLocation');

        $cs = Scalr_Service_Cloud_Rackspace::newRackspaceCS(
            $this->environment->getPlatformConfigValue(Modules_Platforms_Rackspace::USERNAME, true, $cloudLocation),
            $this->environment->getPlatformConfigValue(Modules_Platforms_Rackspace::API_KEY, true, $cloudLocation),
            $cloudLocation
        );

        $limits = $cs->limits();
        $l = array();
        foreach ($limits->limits->rate as $limit) {

            $limit->resetTime = Scalr_Util_DateTime::convertTz(date("c", $limit->resetTime));

            $l[] = (array)$limit;
        }

        $response = $this->buildResponseFromData($l, array());

        $this->response->data($response);
    }

    public function limitsAction()
    {
        $locations = $p = PlatformFactory::NewPlatform(SERVER_PLATFORMS::RACKSPACE)->getLocations();

        $this->response->page('ui/tools/rackspace/limits.js', array(
                'locations'	=> self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::RACKSPACE, false)
        ));
    }
}
