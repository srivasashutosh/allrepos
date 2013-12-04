<?php

class Scalr_UI_Controller_Platforms_Cloudstack extends Scalr_UI_Controller
{
    public function  buildSorter($key) {
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }

    public function xGetOfferingsListAction()
    {
        $platform = PlatformFactory::NewPlatform($this->getParam('platform'));

        $cs = Scalr_Service_Cloud_Cloudstack::newCloudstack(
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_URL, $this->getEnvironment()),
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::API_KEY, $this->getEnvironment()),
            $platform->getConfigVariable(Modules_Platforms_Cloudstack::SECRET_KEY, $this->getEnvironment()),
            $this->getParam('platform')
        );

        if ($this->getParam('platform') == SERVER_PLATFORMS::UCLOUD) {
            $data = array();
            $disks = array();
            $types = array();

            foreach ($cs->listAvailableProductTypes()->producttypes as $product) {
                if (!$types[$product->serviceofferingid]) {
                    $data['serviceOfferings'][] = array(
                        'id' => (string)$product->serviceofferingid,
                        'name' => $product->serviceofferingdesc
                    );

                    $types[$product->serviceofferingid] = true;
                }

                usort($data['serviceOfferings'], $this->buildSorter('name'));
                $data['serviceOfferings'] = array_reverse($data['serviceOfferings']);

                if (!$disks[$product->diskofferingid]) {
                    $data['diskOfferings'][] = array(
                        'id' => (string)$product->diskofferingid,
                        'name' => $product->diskofferingdesc
                    );
                    $disks[$product->diskofferingid] = true;
                }
            }

            $ipAddresses = $cs->listPublicIpAddresses();
            foreach ($ipAddresses->publicipaddress as $address) {
                $data['ipAddresses'][] = array(
                    'id' => (string)$address->id,
                    'name' => $address->ipaddress
                );
            }
        } else {
            $data = array();
            foreach ($cs->listServiceOfferings() as $offering) {

                $data['serviceOfferings'][] = array(
                    'id' => (string)$offering->id,
                    'name' => $offering->displaytext
                );
            }

            $accountName = $platform->getConfigVariable(Modules_Platforms_Cloudstack::ACCOUNT_NAME, $this->getEnvironment(), false);
            $domainId = $platform->getConfigVariable(Modules_Platforms_Cloudstack::DOMAIN_ID, $this->getEnvironment(), false);

            $networks = $cs->listNetworks($this->getParam('cloudLocation'), $accountName, $domainId);

            $data['networks'][] = array(
                'id' => '',
                'name' => 'Do not use network offering'
            );

            foreach ($networks as $network) {
                $data['networks'][] = array(
                        'id' => (string)$network->id,
                        'name' => "{$network->id}: {$network->name} ({$network->networkdomain})"
                );
            }

            $ipAddresses = $cs->listPublicIpAddresses(null, $accountName, null, $domainId, null, null, null, null, null, null, $this->getParam('cloudLocation'));
            $data['ipAddresses'][] = array(
                'id' => "",
                'name' => "Use system defaults"
            );
            foreach ($ipAddresses->publicipaddress as $address) {
                $data['ipAddresses'][] = array(
                    'id' => (string)$address->id,
                    'name' => $address->ipaddress
                );
            }
        }

        $this->response->data(array('data' => $data));
    }
}
