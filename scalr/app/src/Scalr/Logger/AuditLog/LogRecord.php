<?php

namespace Scalr\Logger\AuditLog;

/**
 * Log record
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.11.2012
 */
class LogRecord
{

    /**
     * Auto-generated session identifier.
     * This identifier is unique within one script lifetime, nevertheless it might be regenerated
     * by the special request.
     *
     * @var string
     */
    private static $sid;


    /**
     * Unique identifier of the session.
     *
     * @var string
     */
    private $sessionid;

    /**
     * Unique identifier of the transaction.
     *
     * @var string
     */
    private $uuid;

    /**
     * @var \DateTime
     */
    private $time;

    /**
     * @var int
     */
    private $accountid;

    /**
     * @var int
     */
    private $envid;

    /**
     * @var int
     */
    private $userid;

    /**
     * @var string
     */
    private $email;

    /**
     * @var int
     */
    private $ip;

    /**
     * @var string
     */
    private $message;

    /**
     * @var AuditLogTags
     */
    private $tags;

    /**
     * @var KeyValueRecord
     */
    private $data;

    /**
     * @var string
     */
    private $datatype;

    public function __sleep()
    {
        return array(
            'sessionid', 'accountid', 'uuid', 'time', 'envid', 'userid',
            'email', 'ip', 'message', 'tags', 'data', 'datatype'
        );
    }

    /**
     * Constructor
     *
     * @param   string    $uuid optional unique identifier
     */
    public function __construct($uuid = null)
    {
        if ($uuid === null) {
            $uuid = self::generateUuid();
        }
        if (self::$sid === null) {
            $this->sessionid = self::generateSessionId();
        } else {
            $this->sessionid = self::$sid;
        }
        $this->uuid = $uuid;
    }

    /**
     * Gets uuid
     *
     * @return string Returns uuid
     */
    public static function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * Gets an uuid
     *
     * @return string Returns uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Gets a timestamp
     *
     * @return \DateTime Returns timestamp
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Gets an accountid
     *
     * @return int Returns an accountid
     */
    public function getAccountid()
    {
        return $this->accountid;
    }

    /**
     * Gets an environment id
     *
     * @return int Returns environment id
     */
    public function getEnvid()
    {
        return $this->envid;
    }

    /**
     * Gets an user id
     *
     * @return int Returns an user id
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Gets an user email
     *
     * @return string Returns an user email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Gets an user ip address
     *
     * @return int Gets an user ip address
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Gets an user ip address in IPV4
     *
     * @return string Returns user's IPv4 address
     */
    public function getIpv4()
    {
        return $this->ip !== null? long2ip($this->ip) : null;
    }

    /**
     * Gets an message
     *
     * @return string Returns a message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Gets the tags
     *
     * @return array Returns the tags that are associated with the record
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Gets the data
     * @return KeyValueRecord Returns the data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Gets the ID of the session
     *
     * @return string Returns the ID of the session
     */
    public function getSessionid()
    {
        return $this->sessionid;
    }

    /**
     * Sets an timestamp
     *
     * @param   \DateTime $time
     * @return  LogRecord
     */
    public function setTime(\DateTime $time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Sets an environment id
     *
     * @param   int      $envid Environment id
     * @return  LogRecord
     */
    public function setEnvid($envid)
    {
        $this->envid = $envid;

        return $this;
    }

    /**
     * Sets an userid
     *
     * @param   int  $userid  An user id;
     * @return  LogRecord
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Sets an email
     *
     * @param string $email An user email
     * @return  LogRecord
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Sets an user ip
     *
     * @param   int $ip An user ip
     * @return  LogRecord
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Sets an message
     *
     * @param   string    $message An message
     * @return  LogRecord
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Sets tags
     *
     * @param   AuditLogTags     $tags An tags that associated with the record.
     * @return  LogRecord
     */
    public function setTags(AuditLogTags $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Sets an accountid
     *
     * @param   int      $accountid An accountid
     * @return  LogRecord
     */
    public function setAccountid($accountid)
    {
        $this->accountid = $accountid;

        return $this;
    }

    /**
     * Sets an sessionid
     *
     * @param   int      $sessionid The ID of the session.
     * @return  LogRecord
     */
    public function setSessionid($sessionid)
    {
        $this->sessionid = $sessionid;

        return $this;
    }

    /**
     * Sets an document
     *
     * @param  object $data optional An Document
     */
    public function setData(KeyValueRecord $data = null)
    {
        $this->data = $data;
        $this->datatype = $data !== null ? $data->getObjectDataType() : null;
        return $this;
    }

    /**
     * Gets datatype
     *
     * @return   string    $datatype
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * Sets datatype
     *
     * @param   string     $datatype A datatype
     * @return  LogRecord
     */
    public function setDatatype($datatype)
    {
        $this->datatype = $datatype;
        return $this;
    }

    /**
     * Forces generation the sessionid
     *
     * @return  string new sessionid
     */
    public static function generateSessionId()
    {
        self::$sid = self::generateUuid();

        return self::$sid;
    }
}