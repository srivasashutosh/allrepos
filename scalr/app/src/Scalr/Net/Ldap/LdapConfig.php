<?php

namespace Scalr\Net\Ldap;

/**
 * LdapConfig
 *
 * @author  Vitaliy Demidov   <vitaliy@scalr.com>
 * @since   06.06.2013
 */
class LdapConfig
{
    /**
     * LDAP connection host
     *
     * @var string
     */
    private $host;

    /**
     * LDAP connection port
     *
     * @var int
     */
    private $port;

    /**
     * Username
     *
     * @var string
     */
    private $user;

    /**
     * Password
     *
     * @var string
     */
    private $password;

    /**
     * Base DN
     *
     * @var string
     */
    private $baseDn;

    /**
     * Contstructor
     *
     * @param   string     $host     A connection host
     * @param   int        $port     A connection port
     * @param   string     $user     The username
     * @param   string     $password The user's password
     * @param   string     $baseDn   The base DN
     */
    public function __construct($host, $port, $user, $password, $baseDn)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->baseDn = $baseDn;
    }

    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            throw new Exception\LdapException(sprintf(
                "Property '%s' does not exist for '%s' class.",
                $name, get_class($this)
            ));
        }
        $this->$name = $value;
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function __invoke($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }
}