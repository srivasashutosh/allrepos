<?php

class Modules_Platforms_Idcf extends Modules_Platforms_Cloudstack
{

    public function __construct()
    {
        parent::__construct(SERVER_PLATFORMS::IDCF);
    }

    public function getLocations() {

        try {
            $environment = Scalr_UI_Request::getInstance()->getEnvironment();

            if ($environment && $environment->isPlatformEnabled($this->platform))
            {
                $cs = Scalr_Service_Cloud_Cloudstack::newCloudstack(
                    $this->getConfigVariable(self::API_URL, $environment),
                    $this->getConfigVariable(self::API_KEY, $environment),
                    $this->getConfigVariable(self::SECRET_KEY, $environment),
                    $this->platform
                );

                $retval = array();
                foreach ($cs->listZones() as $zone)
                    $retval[$zone->name] = strtoupper($this->platform)." / {$zone->name}";

                return $retval;
            }
        }
        catch(Exception $e) {}

        return array(
            "jp-east-t1v"	=> "IDCF / jp-east-t1v",
            "jp-east-f2v"	=> "IDCF / jp-east-f2v",
        );
    }

    public function PutAccessData(DBServer $DBServer, Scalr_Messaging_Msg $message)
    {
        $put = false;
        $put |= $message instanceof Scalr_Messaging_Msg_Rebundle;
        $put |= $message instanceof Scalr_Messaging_Msg_BeforeHostUp;
        $put |= $message instanceof Scalr_Messaging_Msg_HostInitResponse;
        $put |= $message instanceof Scalr_Messaging_Msg_Mysql_PromoteToMaster;
        $put |= $message instanceof Scalr_Messaging_Msg_Mysql_NewMasterUp;
        $put |= $message instanceof Scalr_Messaging_Msg_Mysql_CreateDataBundle;
        $put |= $message instanceof Scalr_Messaging_Msg_Mysql_CreateBackup;

        $put |= $message instanceof Scalr_Messaging_Msg_DbMsr_PromoteToMaster;
        $put |= $message instanceof Scalr_Messaging_Msg_DbMsr_CreateDataBundle;
        $put |= $message instanceof Scalr_Messaging_Msg_DbMsr_CreateBackup;
        $put |= $message instanceof Scalr_Messaging_Msg_DbMsr_NewMasterUp;

        if ($put) {
            $environment = $DBServer->GetEnvironmentObject();
            $accessData = new stdClass();
            $accessData->apiKey = $this->getConfigVariable(self::API_KEY, $environment);
            $accessData->secretKey = $this->getConfigVariable(self::SECRET_KEY, $environment);

            $apiUrl = $this->getConfigVariable(self::API_URL, $environment);
            if ($apiUrl == 'https://apis.i.noahcloud.jp/portal/client/api')
                $accessData->apiUrl = "https://api.noahcloud.jp/portal/client/api";
            else
                $accessData->apiUrl = $apiUrl;

            $message->platformAccessData = $accessData;
        }
    }
}
