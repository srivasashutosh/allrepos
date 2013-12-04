<?php

/**
 * Scalr Session class
 *
 * @method  string          getCloudynToken() getCloudynToken()       Gets a Cloudyn Token from session
 * @method  Scalr_Session   setCloudynToken() setCloudynToken($token) Sets a Cloudyn Token into session
 * @method  array           getLdapGroups()   getLdapGroups()         Gets LDAP groups from session
 * @method  Scalr_Session   setLdapGroups()   setLdapGroups($groups)  Sets LDAP group into session
 * 
 */
class Scalr_Session
{

    private $userId;

    private $envId;

    private $sault;

    private $hash;

    private $hashpwd;

    private $virtual;

    private $cloudynToken;

    private $ldapGroups;

    private $restored = false;

    private static $_session = null;

    /**
     * @var ReflectionClass
     */
    private static $refClass;

    const SESSION_USER_ID = 'userId';

    const SESSION_ENV_ID  = 'envId';

    const SESSION_HASH    = 'hash';

    const SESSION_SAULT   = 'sault';

    const SESSION_VIRTUAL = 'virtual';

    const SESSION_CLOUDYN_TOKEN = 'cloudynToken';

    const SESSION_LDAP_GROUPS = 'ldapGroups';

    /**
     * @return Scalr_Session
     */
    public static function getInstance()
    {
        if (self::$_session === null) {
            self::$_session = new Scalr_Session();
            self::$_session->hashpwd = Scalr_Util_CryptoTool::hash(@file_get_contents(APPPATH."/etc/.cryptokey"));
            ini_set('session.cookie_httponly', true);
        }

        if (! self::$_session->restored) {
            self::$_session->restored = true;
            Scalr_Session::restore();
        }

        return self::$_session;
    }

    /**
     * @param $userId
     * @param bool $virtual Session created by admin
     */
    public static function create($userId, $virtual = false)
    {
        @session_start();
        $_SESSION[__CLASS__][self::SESSION_USER_ID] = $userId;
        $_SESSION[__CLASS__][self::SESSION_VIRTUAL] = $virtual;

        $sault = Scalr_Util_CryptoTool::sault();
        $_SESSION[__CLASS__][self::SESSION_SAULT] = $sault;
        $_SESSION[__CLASS__][self::SESSION_HASH] = self::createHash($userId, $sault);
        @session_write_close();

        self::restore(false);
    }

    protected static function getUserPassword($userId)
    {
        $db = \Scalr::getDb();
        return $db->GetOne('SELECT `password` FROM `account_users` WHERE id = ?', array($userId));
    }

    protected static function getAccountHash($userId)
    {
        $db = \Scalr::getDb();
        $hash = $db->GetOne('SELECT `value` FROM client_settings JOIN account_users ON account_users.account_id = client_settings.clientid
            WHERE `key` = ? AND account_users.id = ?', array(Scalr_Account::SETTING_AUTH_HASH, $userId));

        if (!$hash) {
            $accountId = $db->GetOne('SELECT account_id FROM account_users WHERE id = ?', array($userId));
            if ($accountId) {
                $hash = Scalr_Util_CryptoTool::sault();
                $acc = new Scalr_Account();
                $acc->loadById($accountId);
                $acc->setSetting(Scalr_Account::SETTING_AUTH_HASH, $hash);
            }
        }

        return $hash;
    }

    protected static function createHash($userId, $sault)
    {
        $pass = self::getUserPassword($userId);
        return Scalr_Util_CryptoTool::hash("{$userId}:{$pass}:" . self::getInstance()->hashpwd . ":{$sault}");
    }

    protected static function createCookieHash($userId, $sault, $hash)
    {
        $pass = self::getUserPassword($userId);
        $userHash = self::getAccountHash($userId);
        return Scalr_Util_CryptoTool::hash("{$sault}:{$hash}:{$userId}:{$userHash}:{$pass}:" . self::getInstance()->hashpwd);
    }

    protected static function restore($checkKeepSessionCookie = true)
    {
        $session = self::getInstance();
        @session_start();
        $refClass = self::getReflectionClass();
        foreach ($refClass->getConstants() as $constname => $constvalue) {
            if (substr($constname, 0, 8) !== 'SESSION_') continue;
            $session->{$constvalue} = isset($_SESSION[__CLASS__][$constvalue]) ?
                $_SESSION[__CLASS__][$constvalue] : null;
        }

        $newhash = self::createHash($session->userId, $session->sault);
        if (! ($newhash == $session->hash && !empty($session->hash))) {
            // reset session (invalid)
            $session->userId = 0;
            $session->hash = '';

            if ($checkKeepSessionCookie && self::isCookieKeepSession())
                self::restore(false);
        }

        @session_write_close();
    }

    public static function isCookieKeepSession()
    {
        // check for session restore
        if (isset($_COOKIE['scalr_user_id']) &&
            isset($_COOKIE['scalr_sault']) &&
            isset($_COOKIE['scalr_hash']) &&
            isset($_COOKIE['scalr_signature'])
        ) {
            $signature = self::createCookieHash($_COOKIE['scalr_user_id'], $_COOKIE['scalr_sault'], $_COOKIE['scalr_hash']);
            $hash = self::createHash($_COOKIE['scalr_user_id'], $_COOKIE['scalr_sault']);

            if ($signature == $_COOKIE['scalr_signature'] && $hash == $_COOKIE['scalr_hash']) {
                $_SESSION[__CLASS__][self::SESSION_USER_ID] = $_COOKIE['scalr_user_id'];
                $_SESSION[__CLASS__][self::SESSION_SAULT] = $_COOKIE['scalr_sault'];
                $_SESSION[__CLASS__][self::SESSION_HASH] = $_COOKIE['scalr_hash'];

                return true;
            }
        }

        return false;
    }

    public static function destroy()
    {
        @session_start();
        @session_destroy();

        @setcookie("tender_email", "0", time()-86400, "/");
        @setcookie("tender_expires", "0", time()-86400, "/");
        @setcookie("tender_hash", "0", time()-86400, "/");
        @setcookie("tender_name", "0", time()-86400, "/");
        @setcookie("_tender_session", "0", time()-86400, "/");
        @setcookie("anon_token", "0", time()-86400, "/");

        $clearKeepSession = true;

        if (isset($_COOKIE['scalr_user_id']) &&
            isset($_COOKIE['scalr_sault']) &&
            isset($_COOKIE['scalr_hash']) &&
            isset($_COOKIE['scalr_signature'])
        ) {
            $signature = self::createCookieHash($_COOKIE['scalr_user_id'], $_COOKIE['scalr_sault'], $_COOKIE['scalr_hash']);
            $hash = self::createHash($_COOKIE['scalr_user_id'], $_COOKIE['scalr_sault']);

            if ($signature == $_COOKIE['scalr_signature'] && $hash == $_COOKIE['scalr_hash'] && self::getInstance()->getUserId() != $_COOKIE['scalr_user_id']) {
                $clearKeepSession = false;
            }
        }

        if ($clearKeepSession) {
            @setcookie("scalr_user_id", "0", time() - 86400, "/", null, false, true);
            @setcookie("scalr_hash", "0", time() - 86400, "/", null, false, true);
            @setcookie("scalr_sault", "0", time() - 86400, "/", null, false, true);
            @setcookie("scalr_signature", "0", time() - 86400, "/", null, false, true);
        }

        @session_write_close();
    }

    public static function keepSession()
    {
        $session = self::getInstance();
        $db = \Scalr::getDb();

        $tm = time() + 86400 * 30;

        setcookie('scalr_user_id', $session->userId, $tm, "/", null, false, true);
        setcookie('scalr_sault', $session->sault, $tm, "/", null, false, true);
        setcookie('scalr_hash', $session->hash, $tm, "/", null, false, true);
        setcookie("scalr_signature", self::createCookieHash($session->userId, $session->sault, $session->hash), $tm, "/");
    }

    public function isAuthenticated()
    {
        return $this->userId ? true : false;
    }

    public function isVirtual()
    {
        return $this->virtual;
    }

    public function setEnvironmentId($envId)
    {
        @session_start();
        $_SESSION[__CLASS__][self::SESSION_ENV_ID] = $this->envId = $envId;
        @session_write_close();
    }

    /**
     * This method is used to provide getters and setters for the session vars
     *
     * @param   string     $name
     * @param   array      $params
     * @throws  \BadMethodCallException
     */
    public function __call($name, $params)
    {
        if (preg_match('#^(get|set)(.+)$#', $name, $m)) {
            $ref = self::getReflectionClass();
            $property = lcfirst($m[2]);
            $constant = 'SESSION_' . strtoupper(preg_replace('/(?!^)[[:upper:]]+/', '_' . '$0', $property));
            if ($ref->hasConstant($constant)) {
                if ($m[1] == 'get') {
                    return $this->{$property};
                } elseif ($m[1] == 'set') {
                    //set are expected to be here
                    @session_start();
                    $this->{$property} = $params[0];
                    $_SESSION[__CLASS__][$property] = $this->{$property};
                    @session_write_close();
                    return $this;
                }
            }
        }
        throw new \BadMethodCallException(sprintf(
            'Method "%s" does not exist for the class %s', $name, get_class($this)
        ));
    }

    /**
     * Gets an User ID
     *
     * @return  int Returns an User ID
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Gets an Environment ID
     *
     * @return  int Returns an Environment ID
     */
    public function getEnvironmentId()
    {
        return $this->envId;
    }

    /**
     * Gets a reflection class
     *
     * @return ReflectionClass Returns a reflection  class
     */
    private static function getReflectionClass()
    {
        if (self::$refClass === null) {
            self::$refClass = new ReflectionClass(__CLASS__);
        }
        return self::$refClass;
    }
}
