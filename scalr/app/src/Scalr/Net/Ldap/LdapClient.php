<?php

namespace Scalr\Net\Ldap;

/**
 * LdapClient (v0.2)
 *
 * @author  Vitaliy Demidov   <vitaliy@scalr.com>
 * @since   06.06.2013
 */
class LdapClient
{
    /**
     * Ldap config object
     *
     * @var LdapConfig
     */
    private $config;

    /**
     * Ldap connection link identifier
     *
     * @var resource
     */
    private $conn;

    /**
     * Constructor
     *
     * @param  LdapConfig  $config LDAP config
     * @throws Exception\LdapException
     */
    public function __construct(LdapConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Gets LdapConfig
     *
     * @return  LdapConfig Returns LdapConfig instance
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Gets LDAP connection link identifier
     *
     * @return  resource Returns LDAP connection link edentifier
     * @throws  Exception\LdapException
     */
    protected function getConnection()
    {
        if (!$this->conn) {
            $this->conn = ldap_connect($this->config->host, $this->config->port);
            if ($this->conn == false) {
                throw new Exception\LdapException(sprintf(
                    "Could not establish LDAP connection to host '%s' port '%d'",
                    $this->config->host, $this->config->port
                ));
            }
            //Sets protocol version
            ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            // We need this for doing an LDAP search.
            ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);
        }

        return $this->conn;
    }

    /**
     * Gets last LDAP error
     *
     * @return  string
     */
    public function getLdapError()
    {
        return isset($this->conn) ? ldap_error($this->conn) : '';
    }

    /**
     * Binds to the LDAP directory with specified RDN and password.
     *
     * @param   string     $username  RDN
     * @param   string     $password  Password
     * @return  bool       Returns TRUE on success or FALSE otherwise
     */
    protected function bindRdn($username = null, $password = null)
    {
        if (!func_num_args()) {
            $res = @ldap_bind($this->conn, $this->config->user, $this->config->password);
        } else {
            $res = @ldap_bind($this->conn, (string)$username, (string)$password);
        }

        return $res;
    }

    /**
     * Checks is this user can be authenticated to LDAP
     *
     * @param   string     $rdn        User's RDN (username@scalr)
     * @param   string     $password   User's password
     * @return  boolean    Returns true on success or false otherwise
     */
    public function isValidUser($rdn, $password)
    {
        if (empty($rdn)) {
            return false;
        }

        $this->getConnection();

        $ret = $this->bindRdn($rdn, $password);

        //It is not enough only successfull bind.
        //It should find the user with the specified credentials.
        if ($ret) {
            $name = strtok($rdn, '@');
            $query = @ldap_search(
                $this->conn, $this->config->baseDn,
                "(&(objectCategory=person)(objectClass=user)(sAMAccountName=" . self::realEscape($name) . "))",
                array('dn'), 0, 1
            );

            if ($query !== false) {
                $results = ldap_get_entries($this->conn, $query);

                if ($results['count'] == 1) {
                    //If it is successful, we should take the DN and bind
                    //again using that DN and the provided password.
                    $dn = $results[0]['dn'];

                    //Now his should either succeed or fail properly
                    $ret = $this->bindRdn($dn, $password);
                } else {
                    $ret = false;
                }
            } else {
                $ret = false;
            }
        }

        return $ret;
    }

    /**
     * Gets the list of the groups in which specified user has memberships.
     *
     * @param   string    $name  User's sAMAccount name
     * @return  array     Returns array of the sAMAccount name of the Groups
     * @throws  Exception\LdapException
     */
    public function getUserGroups($name)
    {
        $groups = array();

        $this->getConnection();

        //Ldap bind
        if ($this->bindRdn() === false) {
            throw new Exception\LdapException(sprintf(
                "Could not bind LDAP. %s",
                $this->getLdapError()
            ));
        }

        $query = @ldap_search(
            $this->conn, $this->config->baseDn,
            "(&(objectCategory=person)(objectClass=user)(sAMAccountName=" . self::realEscape($name) . "))",
            array('dn'), 0, 1
        );
        if ($query === false) {
            throw new Exception\LdapException(sprintf(
                "Could not perform ldap_search. %s",
                $this->getLdapError()
            ));
        }

        $results = ldap_get_entries($this->conn, $query);

        $dn = $results[0]['dn'];

        $filter = "(member:1.2.840.113556.1.4.1941:=" . $dn . ")";

        $search = @ldap_search(
            $this->conn, $this->config->baseDn, $filter, array("sAMAccountName")
        );

        if ($search === false) {
            throw new Exception\LdapException(sprintf(
                "Could not perform ldap_search. %s",
                $this->getLdapError()
            ));
        }

        $results = ldap_get_entries($this->conn, $search);

        for ($item = 0; $item < $results['count']; $item++) {
            $groups[] = $results[$item]['samaccountname'][0];
        }

        return $groups;
    }

    /**
     * Checks whether specified user is member of the Group including all nested groups.
     *
     * @param   string $userDn      User's DN
     * @param   string $groupToFind Group to find
     * @return  bool   Returns true if specified userDN is member of group
     */
    public function isMemberOfGroup($userDn, $groupToFind)
    {
        $this->getConnection();

        //Ldap bind
        if ($this->bindRdn() === false) {
            throw new Exception\LdapException(sprintf(
                "Could not bind LDAP. %s",
                $this->getLdapError()
            ));
        }

        $filter = "(memberof:1.2.840.113556.1.4.1941:=" . $groupToFind . ")";
        $search = ldap_search($this->conn, $userDn, $filter, array("dn"), 1);
        $items = ldap_get_entries($this->conn, $search);

        return !isset($items["count"]) ? false : (bool) $items["count"];
    }

    public function __sleep()
    {
        return array('config');
    }

    public function __wakeup()
    {
        $this->getConnection();
    }

    /**
     * Escapes query string
     *
     * @param   string   $string The query string
     * @return  string
     */
    public static function escape($string)
    {
        return preg_replace(
            array(
                '/[\r\n]+/',
                '/(^ |[\\\\]|[,#+<>;"=]| $)/',
            ),
            array(
                '',
                '\\\\$1',
            ),
            $string
        );
    }

    /**
     * Escapes query string including asterisk and parentheses
     *
     * @param   string   $string The query string
     * @return  string
     */
    public static function realEscape($string)
    {
        return preg_replace(
            array(
                '/[\r\n]+/',
                '/(^ |[\\\\]|[,#+<>;"=*\(\)]| $)/',
            ),
            array(
                '',
                '\\\\$1',
            ),
            $string
        );
    }
}