<?php

class Scalr_Uservoice
{
    private $voiceServerUrl = 'https://scalr.uservoice.com';
    private static $Voice;
    public $forumId;
    public $key;
    public $secret;

    public static function getUservoice()
    {
        self::$Voice = new Scalr_Uservoice();
        return self::$Voice;
    }

    public function __construct()
    {
        $this->forumId = '101991';
        $this->key = 'MNJTs3KnkdIB0s6TzSI75g';
        $this->secret = 'MCvhURgPukrmuUqXvuTkjLhRQTajumj84FHLUTijruQ';
    }

    public function getListSuggests()
    {
        $str = '/api/v1/forums/';
        $str .= $this->forumId;
        $str .= '/suggestions.json?per_page=20&client='.$this->key;
        $retval = $this->request($str, "GET");
        return $retval;
    }

    private function request($path, $method, $data="")
    {
        $data = trim($data);
        $httpRequest = new HttpRequest();

        $fullUrl = "{$this->voiceServerUrl}{$path}";
        $httpRequest->setUrl($fullUrl);

        $httpRequest->setMethod(constant("HTTP_METH_{$method}"));
        $httpRequest->send();
        if($httpRequest->getResponseCode() == 404)
            throw new Exception("Client not found or parameters are not valid");
        else if ($httpRequest->getResponseCode() == 200) {
            $data = $httpRequest->getResponseData();
            $retval = ($data['body']) ? json_decode($data['body']) : true;
        }
        else if ($httpRequest->getResponseCode() > 400) {
            $data = $httpRequest->getResponseData();
            $msg = ($data['body']) ? json_decode($data['body']) : "";
            if (is_array($msg->error))
                $msg = $msg->error[0];
            elseif ($msg->error)
                $msg = $msg->error;
            else
                $msg = "Unknown error. Error code: {$httpRequest->getResponseCode()}";

            throw new Exception("Request to userVoice server failed with error: {$msg} ({$method} {$path})");
        } else {
            throw new Exception("Unexpected situation. Response code {$httpRequest->getResponseCode()}");
        }
        return $retval;
    }
}


/*  Scalr
Key: MNJTs3KnkdIB0s6TzSI75g
Secret: MCvhURgPukrmuUqXvuTkjLhRQTajumj84FHLUTijruQ
Trusted: Yes
URL:
Callback URL:
Request Token URL: https://scalr.uservoice.com/oauth/request_token
Access Token URL: https://scalr.uservoice.com/oauth/access_token
Authorize URL: https://scalr.uservoice.com/oauth/authorize
 */