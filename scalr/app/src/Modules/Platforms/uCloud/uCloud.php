<?php

class Modules_Platforms_uCloud extends Modules_Platforms_Cloudstack
{

    public function __construct()
    {
        parent::__construct(SERVER_PLATFORMS::UCLOUD);
    }

    public function getLocations() {
        try {
            $environment = Scalr_UI_Request::getInstance()->getEnvironment();
        }
        catch(Exception $e) {
            return array();
        }

        if (!$environment || !$environment->isPlatformEnabled($this->platform))
            return array();

        try {
            $cs = Scalr_Service_Cloud_Cloudstack::newCloudstack(
                $this->getConfigVariable(self::API_URL, $environment),
                $this->getConfigVariable(self::API_KEY, $environment),
                $this->getConfigVariable(self::SECRET_KEY, $environment),
                $this->platform
            );

            $products = $cs->listAvailableProductTypes();

            foreach ($products->producttypes as $product) {
                $retval[$product->zoneid] = "KT uCloud / {$product->zonedesc} ({$product->zoneid})";
            }

        } catch (Exception $e) {
            return array();
        }

        return $retval;
    }

    public function TerminateServer(DBServer $DBServer)
    {
        $cs = $this->getCloudStackClient($DBServer->GetEnvironmentObject(), $this->GetServerCloudLocation($DBServer));

        if (!$DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::IS_STOPPED_BEFORE_TERMINATE)) {
            $cs->stopVirtualMachine($DBServer->GetProperty(CLOUDSTACK_SERVER_PROPERTIES::SERVER_ID), true);
            $DBServer->SetProperty(CLOUDSTACK_SERVER_PROPERTIES::IS_STOPPED_BEFORE_TERMINATE, 1);
        }

        return parent::TerminateServer($DBServer);
    }
}
