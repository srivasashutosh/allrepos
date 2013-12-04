<?php

    class Scalr_Service_Cloud_Aws_Ec2_Client extends Scalr_Service_Cloud_Aws_Ec2_20120401_Client
    {
        /**
         *
         * @param string $accessKey
         * @param string $accessKeyId
         * @param string $region
         */
        public function __construct($region, $accessKey, $secretKey)
        {
              parent::__construct();

            $this->accessKey = $secretKey;
            $this->accessKeyId = $accessKey;
            $this->region = $region;

            $this->ec2Url = "https://ec2.{$this->region}.amazonaws.com";
        }
    }
?>
