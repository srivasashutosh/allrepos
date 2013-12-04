<?php

    class Scalr_Service_Cloud_Cloudstack
    {
        public static function newCloudstack($endpoint, $apiKey, $secretKey, $platform = 'cloudstack')
        {
            return new Scalr_Service_Cloud_Cloudstack_Client($endpoint, $apiKey, $secretKey, $platform);
        }
    }
