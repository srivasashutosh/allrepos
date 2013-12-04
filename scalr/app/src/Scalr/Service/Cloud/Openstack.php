<?php

    class Scalr_Service_Cloud_Openstack
    {
        public static function newNovaCC($apiUrl, $authUser, $authKey, $project = "", $version='2.0')
        {
            switch ($version) {
                case "1.1":
                    require_once 'Openstack/v1.1/Client.php';
                    return new Scalr_Service_Cloud_Openstack_v1_1_Client($authUser, $authKey, $apiUrl, $project);
                    break;
                case "2.0":
                    require_once 'Openstack/v2.0/Client.php';
                    return new Scalr_Service_Cloud_Openstack_v2_0_Client($authUser, $authKey, $apiUrl, $project);
                    break;
                default:
                    throw new Exception("Openstack verison {$version} is not supported");
                    break;
            }
        }
    }
?>
