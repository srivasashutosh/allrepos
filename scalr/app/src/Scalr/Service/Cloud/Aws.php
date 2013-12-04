<?php

class Scalr_Service_Cloud_Aws
{

    /**
     *
     * Amazon IAM Client
     * @param string $access_key
     * @param string $secret_key
     * @return Scalr_Service_Cloud_Aws_Iam_Client
     */
    public static function newIam($access_key, $secret_key)
    {
        $iam = new Scalr_Service_Cloud_Aws_Iam_Client($secret_key, $access_key);
        return $iam;
    }
}