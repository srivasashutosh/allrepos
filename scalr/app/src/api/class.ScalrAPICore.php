<?php

abstract class ScalrAPICore
{
    const HASH_ALGO = 'SHA256';

    protected $Request;

    /**
     * @var Scalr_Environment
     */
    protected $Environment;

    /**
     * @var \ADODB_mysqli
     */
    protected $DB;

    /**
     * @var Scalr_Account_User
     */
    protected $user;

    public $Version;

    protected $debug = array();

    protected $LastTransactionID;

    /**
     * DI Container instance
     *
     * @var \Scalr\DependencyInjection\Container
     */
    private $container;

    function __construct($version)
    {
        $this->container = \Scalr::getContainer();
        $this->DB = $this->container->adodb;
        $this->Version = $version;
    }

    /**
     * Gets DI Container
     *
     * @return \Scalr\DependencyInjection\Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    protected function insensitiveUksort($a,$b) {
        return strtolower($a)>strtolower($b);
    }

    private function AuthenticateLdap($request)
    {
        if (!isset($request['Login']) || empty($request['Login']))
            throw new Exception("Login is missing");

        if (!isset($request['Password']) || empty($request['Password']))
            throw new Exception("Password is missing");

        if (!isset($request['EnvID']) || empty($request['EnvID']))
            throw new Exception("Environment ID is missing");

        if (\Scalr::config('scalr.auth_mode') != 'ldap')
            throw new Exception("LDAP auth not enabled on Scalr");

        $ldap = \Scalr::getContainer()->ldap($request['Login'], $request['Password']);
        $result = $ldap->isValidUser($request['Login'], $request['Password']);
        if ($result) {
            //TODO:
            $this->Environment = Scalr_Environment::init()->loadById($request['EnvID']);

            //Get User
            try {
                $this->user = Scalr_Account_User::init()->loadByEmail($request['Login'], $this->Environment->clientId);
            } catch (Exception $e) {}

            if (!$this->user)
                throw new Exception("{$request['Login']} has no access to environment #{$request['EnvID']}");

            $this->user->getPermissions()->validate($this->Environment);
        }
        else
            throw new Exception("Incorrect login or password (1)");
    }

    private function AuthenticateRESTv3($request)
    {
        if (!$request['Signature'])
            throw new Exception("Signature is missing");

        if (!$request['KeyID'])
            throw new Exception("KeyID is missing");

        if (!$request['Timestamp'] && !$request['TimeStamp'])
            throw new Exception("Timestamp is missing");

        if ($request['Timestamp'])
            $request['TimeStamp'] = $request['Timestamp'];

        //You mustn't do urldecode here in the next API version!
        $string_to_sign = "{$request['Action']}:{$request['KeyID']}:".urldecode($request['TimeStamp']);

        $this->debug['stringToSign'] = $string_to_sign;

        try {
            $this->user = Scalr_Account_User::init()->loadByApiAccessKey($request['KeyID']);
        } catch (Exception $e) {}

        if (!$this->user)
            throw new Exception("API Key #{$request['KeyID']} not found in database");

        $auth_key = $this->user->getSetting(Scalr_Account_User::SETTING_API_SECRET_KEY);

        if ($this->user->getAccountId()) {
            if (!$request['EnvID']) {
                $envs = $this->user->getEnvironments();
                if (!$envs[0]['id'])
                    throw new Exception("User has no access to any environemnts");

                $this->Environment = Scalr_Environment::init()->loadById($envs[0]['id']);
            }
            else {
                $this->Environment = Scalr_Environment::init()->loadById($request['EnvID']);
            }

            $this->user->getPermissions()->validate($this->Environment);
        }

        $valid_sign = base64_encode(hash_hmac(self::HASH_ALGO, trim($string_to_sign), $auth_key, 1));

        //You mustn't do this in the next API version!
        $request['Signature'] = str_replace(" ", "+", urldecode($request['Signature']));

        //You mustn't do urldecode here in the next API version!
        $this->debug['reqSignature'] = urldecode($request['Signature']);
        $this->debug['validSignature'] = $valid_sign;
        $this->debug['usedAuthVersion'] = 3;
        $this->debug['md5AccessKey'] = md5($auth_key);

        if ($valid_sign != $request['Signature']) {
            //This is workaround to bugfix SCALRCORE-400.
            //It needn't have made unnecessary urldecode operation with request parameters.
            $sts2 = "{$request['Action']}:{$request['KeyID']}:{$request['TimeStamp']}";
            $vs2 = base64_encode(hash_hmac(self::HASH_ALGO, trim($sts2), $auth_key, 1));

            if ($vs2 != $request['Signature'])
                throw new Exception("Signature doesn't match");
        }
    }

    private function AuthenticateRESTv2($request)
    {
        if (!$request['Signature'])
            throw new Exception("Signature is missing");

        if (!$request['KeyID'])
            throw new Exception("KeyID is missing");

        if (!$request['Timestamp'] && !$request['TimeStamp'])
            throw new Exception("Timestamp is missing");

        foreach ($request as $k=>$v)
        {
            if (is_array($v))
            {
                foreach ($v as $kk => $vv)
                    $request["{$k}[{$kk}]"] = $vv;

                unset($request[$k]);
            }
        }

        uksort($request, array($this, 'insensitiveUksort'));

        $string_to_sign = "";
        foreach ($request as $k=>$v)
        {
            if (!in_array($k, array("Signature", "SysDebug"))) {
                if (is_array($v)) {
                    foreach ($v as $kk => $vv) {
                        $string_to_sign.= "{$k}[{$kk}]{$vv}";
                    }
                }
                else {
                    $string_to_sign.= "{$k}{$v}";
                }
            }
        }

        $this->debug['stringToSign'] = $string_to_sign;

        $this->user = Scalr_Account_User::init()->loadByApiAccessKey($request['KeyID']);

        if (!$this->user)
            throw new Exception("API Key #{$request['KeyID']} not found in database");

        $auth_key = $this->user->getSetting(Scalr_Account_User::SETTING_API_SECRET_KEY);

        if ($this->user->getAccountId()) {
            if (!$request['EnvID']) {
                $envs = $this->user->getEnvironments();
                if (!$envs[0]['id'])
                    throw new Exception("User has no access to any environemnts");

                $this->Environment = Scalr_Environment::init()->loadById($envs[0]['id']);
            }
            else {
                $this->Environment = Scalr_Environment::init()->loadById($request['EnvID']);
            }

            $this->user->getPermissions()->validate($this->Environment);
        }

        $valid_sign = base64_encode(hash_hmac(self::HASH_ALGO, trim($string_to_sign), $auth_key, 1));

        $request['Signature'] = str_replace(" ", "+", $request['Signature']);

        $this->debug['reqSignature'] = $request['Signature'];
        $this->debug['validSignature'] = $valid_sign;

        if ($valid_sign != $request['Signature'])
            throw new Exception("Signature doesn't match");
    }

    private function AuthenticateREST($request)
    {
        if (!$request['Signature'])
            throw new Exception("Signature is missing");

        if (!$request['KeyID'])
            throw new Exception("KeyID is missing");

        if (!$request['Timestamp'] && !$request['TimeStamp'])
            throw new Exception("Timestamp is missing");

        ksort($request);

        $string_to_sign = "";
        foreach ($request as $k=>$v) {
            if (!in_array($k, array("Signature"))) {
                if (is_array($v)) {
                    foreach ($v as $kk => $vv) {
                        $string_to_sign.= "{$k}[{$kk}]{$vv}";
                    }
                }
                else {
                    $string_to_sign.= "{$k}{$v}";
                }
            }
        }

        $this->debug['stringToSign'] = $string_to_sign;

        $this->user = Scalr_Account_User::init()->loadByApiAccessKey($request['KeyID']);

        if (!$this->user)
            throw new Exception("API Key #{$request['KeyID']} not found in database");

        $auth_key = $this->user->getSetting(Scalr_Account_User::SETTING_API_SECRET_KEY);

        if ($this->user->getAccountId()) {
            if (!$request['EnvID']) {
                $envs = $this->user->getEnvironments();
                if (!$envs[0]['id'])
                    throw new Exception("User has no access to any environemnts");

                $this->Environment = Scalr_Environment::init()->loadById($envs[0]['id']);
            }
            else {
                $this->Environment = Scalr_Environment::init()->loadById($request['EnvID']);
            }

            $this->user->getPermissions()->validate($this->Environment);
        }

        $valid_sign = base64_encode(hash_hmac(self::HASH_ALGO, trim($string_to_sign), $auth_key, 1));
        if ($valid_sign != $request['Signature'])
            throw new Exception("Signature doesn't match");
    }

    public function BuildRestServer($request)
    {
        try
        {
            $Reflect = new ReflectionObject($this);
            if ($Reflect->hasMethod($request['Action']))
            {
                //Authenticate
                if ($request['AuthType'] == 'ldap') {
                    $this->AuthenticateLdap($request);
                } else {
                    if ($request['AuthVersion'] == 2)
                        $this->AuthenticateRESTv2($request);
                    elseif ($request['AuthVersion'] == 3)
                        $this->AuthenticateRESTv3($request);
                    else
                        $this->AuthenticateREST($request);

                    if ($this->user->getSetting(Scalr_Account_User::SETTING_API_ENABLED) != 1)
                        throw new Exception(_("API disabled for you. You can enable it at 'Settings -> Environments'"));

                    //Check IP Addresses
                    if ($this->user->getSetting(Scalr_Account_User::SETTING_API_IP_WHITELIST))
                    {
                        $ips = explode(",", $this->user->getSetting(Scalr_Account_User::SETTING_API_IP_WHITELIST));
                        if (!$this->IPAccessCheck($ips))
                            throw new Exception(sprintf(_("Access to the API is not allowed from your IP '%s'"), $_SERVER['REMOTE_ADDR']));
                    }
                }

                //Check limit
                if ($this->Environment->getPlatformConfigValue(ENVIRONMENT_SETTINGS::API_LIMIT_ENABLED, false) == 1) {
                    $hour = $this->Environment->getPlatformConfigValue(ENVIRONMENT_SETTINGS::API_LIMIT_HOUR, false);
                    $limit = $this->Environment->getPlatformConfigValue(ENVIRONMENT_SETTINGS::API_LIMIT_REQPERHOUR, false);
                    $usage = $this->Environment->getPlatformConfigValue(ENVIRONMENT_SETTINGS::API_LIMIT_USAGE, false);
                    if ($usage >= $limit && $hour == date("YmdH")) {
                        $reset = 60 - (int)date("i");

                        header("HTTP/1.0 429 Too Many Requests");
                        exit();

                        //throw new Exception(sprintf("Hourly API requests limit (%s) exceeded. Limit will be reset within %s minutes", $limit, $reset));
                    }

                    if (date("YmdH") > $hour) {
                        $hour = date("YmdH");
                        $usage = 0;
                    }

                    $this->Environment->setPlatformConfig(array(
                        ENVIRONMENT_SETTINGS::API_LIMIT_USAGE => $usage+1,
                        ENVIRONMENT_SETTINGS::API_LIMIT_HOUR => $hour
                    ), false);
                }

                //Execute API call
                $ReflectMethod = $Reflect->getMethod($request['Action']);
                $args = array();
                foreach ($ReflectMethod->getParameters() as $param)
                {
                    if (!$param->isOptional() && !isset($request[$param->getName()]))
                        throw new Exception(sprintf("Missing required parameter '%s'", $param->getName()));
                    else
                        $args[$param->getName()] = $request[$param->getName()];
                }

                $result = $ReflectMethod->invokeArgs($this, $args);

                $this->LastTransactionID = $result->TransactionID;

                // Create response
                $DOMDocument = new DOMDocument('1.0', 'UTF-8');
                $DOMDocument->loadXML("<{$request['Action']}Response></{$request['Action']}Response>");
                $this->ObjectToXML($result, $DOMDocument->documentElement, $DOMDocument);

                $retval = $DOMDocument->saveXML();
            }
            else
                throw new Exception(sprintf("Action '%s' is not defined", $request['Action']));
        }
        catch(Exception $e)
        {

            if (!$this->LastTransactionID)
                $this->LastTransactionID = Scalr::GenerateUID();

            if ($request['SysDebug'])
            {
                $debugInfo = "<StringToSign>{$this->debug['stringToSign']}</StringToSign>";
                $debugInfo .= "<reqSignature>{$this->debug['reqSignature']}</reqSignature>";
                $debugInfo .= "<validSignature>{$this->debug['validSignature']}</validSignature>";
                $debugInfo .= "<md5AccessKey>{$this->debug['md5AccessKey']}</md5AccessKey>";
                $debugInfo .= "<usedAuthVersion>{$this->debug['usedAuthVersion']}</usedAuthVersion>";
            }

            $retval = "<?xml version=\"1.0\"?>\n".
            "<Error>\n".
                "\t<TransactionID>{$this->LastTransactionID}</TransactionID>\n".
                "\t<Message>{$e->getMessage()}</Message>\n".
                $debugInfo.
            "</Error>\n";
        }

        $this->LogRequest(
            $this->LastTransactionID,
            $request['Action'],
            $_SERVER['REMOTE_ADDR'],
            $request,
            $retval
        );

        header("Content-type: text/xml");
        header("Content-length: ".strlen($retval));
        print $retval;
    }

    protected function LogRequest($trans_id, $action, $ipaddr, $request, $response)
    {
        if ($request['debug'] == 1 || $request['Debug'] == 1 || $request['Action'] == 'DNSZoneRecordAdd')
        {
            try
            {
                $this->DB->Execute("INSERT INTO api_log SET
                    transaction_id	= ?,
                    dtadded			= ?,
                    action			= ?,
                    ipaddress		= ?,
                    request			= ?,
                    response		= ?,
                    clientid		= ?,
                    env_id			= ?
                ",array(
                    $trans_id,
                    time(),
                    $action,
                    $ipaddr,
                    http_build_query($request),
                    $response,
                    $this->user->getAccountId(),
                    $this->Environment->id
                ));
            }
            catch(Exception $e) {}
        }
    }

    protected function IPAccessCheck($allowed_ips)
    {
        $current_ip = $_SERVER['REMOTE_ADDR'];
        $current_ip_parts = explode(".", $current_ip);

        foreach ($allowed_ips as $allowed_ip)
        {
            $allowedhost = trim($allowed_ip);
            if ($allowedhost == '')
                continue;

            if (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/si", $allowedhost))
            {
                if (ip2long($allowedhost) == ip2long($current_ip))
                   return true;
            }
            elseif (stristr($allowedhost, "*"))
            {
                $ip_parts = explode(".", trim($allowedhost));
                if (
                    ($ip_parts[0] == "*" || $ip_parts[0] == $current_ip_parts[0]) &&
                    ($ip_parts[1] == "*" || $ip_parts[1] == $current_ip_parts[1]) &&
                    ($ip_parts[2] == "*" || $ip_parts[2] == $current_ip_parts[2]) &&
                    ($ip_parts[3] == "*" || $ip_parts[3] == $current_ip_parts[3])
                   )
                return true;
            }
            else
            {
                $ip = @gethostbyname($allowedhost);
                if ($ip != $allowedhost)
                {
                    if (ip2long($ip) == ip2long($current_ip))
                       return true;
                }
            }
        }

        return false;
    }

    protected function ObjectToXML($obj, $DOMElement, $DOMDocument)
    {
        if (is_object($obj) || is_array($obj))
        {
            foreach ($obj as $k=>$v)
            {
                if (is_object($v))
                    $this->ObjectToXML($v, $DOMElement->appendChild($DOMDocument->createElement($k)), $DOMDocument);
                elseif (is_array($v))
                    foreach ($v as $vv)
                    {
                        $e = $DOMElement->appendChild($DOMDocument->createElement($k));
                        $this->ObjectToXML($vv, $e, $DOMDocument);
                    }
                else
                    $DOMElement->appendChild($DOMDocument->createElement($k, $v));
            }
        }
        else
            $DOMElement->appendChild($DOMDocument->createTextNode($obj));
    }

    protected function CreateInitialResponse()
    {
        $response = new stdClass();
        $response->{"TransactionID"} = Scalr::GenerateUID();

        return $response;
    }
}
