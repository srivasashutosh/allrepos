<?php
namespace Scalr\Service\OpenStack;

use Scalr\Service\OpenStack\Client\AuthToken;
use Scalr\Service\OpenStack\Exception\OpenStackException;

/**
 * OpenStack configuration object
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class OpenStackConfig
{

    /**
     * OpenStack Account Location
     * @var string
     */
    private $identityEndpoint;

    /**
     * OpenStack Region
     * @var string
     */
    private $region;

    /**
     * OpenStack Username
     * @var string
     */
    private $username;

    /**
     * User password
     * @var string|null
     */
    private $password;

    /**
     * User API Key
     * @var string
     */
    private $apiKey;

    /**
     * @var \Closure
     */
    private $updateTokenCallback;

    /**
     * Authentication token.
     * @var AuthToken
     */
    private $authToken;

    /**
     * OpenStack tenant name.
     * @var string
     */
    private $tenantName;

    /**
     * Convenient constructor
     *
     * @param   string                    $username            An user name
     * @param   string                    $identityEndpoint    OpenStack Identity Endpoint
     * @param   string                    $region              OpenStack Region
     * @param   string                    $apiKey              optional An User's API Key
     * @param   \Closure                  $updateTokenCallback optional Update Token Callback
     *                                                         This function must accept one parameter AuthToken object.
     * @param   AuthToken                 $authToken           optional Authentication token for the OpenStack service.
     * @param   string                    $password            optional An User's password
     * @param   string                    $tenantName          optional OpenStack tenant name. This is used for the OpenStack
     */
    public function __construct($username, $identityEndpoint, $region, $apiKey = null, \Closure $updateTokenCallback = null,
                                AuthToken $authToken = null, $password = null, $tenantName = null)
    {
        $this
            ->setUsername($username)
            ->setIdentityEndpoint($identityEndpoint)
            ->setRegion($region)
            ->setPassword($password)
            ->setApiKey($apiKey)
            ->setUpdateTokenCallback($updateTokenCallback)
            ->setAuthToken($authToken)
            ->setTenantName($tenantName)
        ;
    }

    /**
     * Gets OpenStack tenant name
     *
     * @return  string Returns OpenStack tenant name.
     */
    public function getTenantName()
    {
        return $this->tenantName;
    }

    /**
     * Sets OpenStack tenant name
     *
     * @param   string $tenantName OpenStack tenant name
     * @return  OpenStackConfig
     */
    public function setTenantName($tenantName)
    {
        $this->tenantName = $tenantName;
        return $this;
    }

    /**
     * Gets region
     *
     * @return  RegionInterface OpenStack Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Sets OpenStack Region
     *
     * @param   string   $region OpenStack Region
     * @return  OpenStackConfig
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * Gets OpenStack identity endpoint
     *
     * @return  string Returns identity endpoint
     */
    public function getIdentityEndpoint()
    {
        return $this->identityEndpoint;
    }

    /**
     * Gets an username
     *
     * @return  string Returns an username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Gets user's password
     * @return  string Returns user's password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Gets User's API Key
     * @return  string  $apiKey Returns user API key
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Sets a OpenStack identity endpoint
     *
     * @param   string $identityEndpoint OpenStack identity endpoint
     * @return  OpenStackConfig
     */
    public function setIdentityEndpoint($identityEndpoint)
    {
        $this->identityEndpoint = $identityEndpoint;

        return $this;
    }

    /**
     * Sets username
     *
     * @param   string $username An User name.
     * @return  OpenStackConfig
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Sets user's password
     *
     * @param   string $password An User password.
     * @return  OpenStackConfig
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Sets API Key
     *
     * @param   string $apiKey An User API Key
     * @return  OpenStackConfig
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }
    /**
     * Gets update token callback
     *
     * @return  \Closure Returns update token callback
     */
    public function getUpdateTokenCallback()
    {
        return $this->updateTokenCallback;
    }

    /**
     * Sets update token callback
     *
     * @param   \Closure $updateTokenCallback Update token callback must accept one argument - AuthToken
     */
    public function setUpdateTokenCallback(\Closure $updateTokenCallback = null)
    {
        $this->updateTokenCallback = $updateTokenCallback;

        return $this;
    }

    /**
     * Gets an Auth Token
     *
     * @return  AuthToken An authentication token.
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * Checks whether this is OpenStack Endpoint
     *
     * @return  bool Returns TRUE if it is OpenStack Endpoint
     */
    public function isOpenStack()
    {
        return $this->getTenantName() !== null;
    }

    /**
     * Sets an Auth Token
     *
     * @param   AuthToken  $authToken An authentication token.
     * @return  OpenStackConfig
     */
    public function setAuthToken(AuthToken $authToken = null)
    {
        $this->authToken = $authToken;
        return $this;
    }

    /**
     * Gets auth query string
     *
     * @return  array  Returns auth query
     * @throws  OpenStackException
     */
    public function getAuthQueryString()
    {
        if ($this->getApiKey() !== null) {
            $s = array(
                "RAX-KSKEY:apiKeyCredentials" => array(
                    'username' => $this->getUsername(),
                    'apiKey'   => $this->getApiKey(),
                ),
            );
        } else if ($this->getPassword() !== null) {
            $s = array(
                "passwordCredentials" => array(
                    'username' => $this->getUsername(),
                    'password' => $this->getPassword(),
                ),
            );
        } else {
            throw new OpenStackException(
                'Neither api key nor password was provided for the OpenStack config.'
            );
        }
        if ($this->getTenantName() !== null) {
            $s['tenantName'] = $this->getTenantName();
        }
        return $s;
    }
}