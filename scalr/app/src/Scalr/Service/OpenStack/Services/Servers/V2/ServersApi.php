<?php
namespace Scalr\Service\OpenStack\Services\Servers\V2;

use Scalr\Service\OpenStack\Services\Servers\Type\RebootType;
use Scalr\Service\OpenStack\Services\Servers\Type\ListImagesFilter;
use Scalr\Service\OpenStack\Services\Servers\Type\NetworkList;
use Scalr\Service\OpenStack\Services\Servers\Type\PersonalityList;
use Scalr\Service\OpenStack\Services\Servers\Type\DiscConfig;
use Scalr\Service\OpenStack\Services\Servers\Type\ListServersFilter;
use Scalr\Service\OpenStack\Exception\RestClientException;
use Scalr\Service\OpenStack\Type\AppFormat;
use Scalr\Service\OpenStack\Client\RestClientResponse;
use Scalr\Service\OpenStack\Client\ClientInterface;
use Scalr\Service\OpenStack\Services\ServersService;

/**
 * OpenStack Next Generation Cloud Servers™ API API v2 (Nov 7, 2012)
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    04.12.2012
 */
class ServersApi
{

    /**
     * @var ServersService
     */
    protected $service;

    /**
     * Constructor
     *
     * @param   ServersService $servers
     */
    public function __construct(ServersService $servers)
    {
        $this->service = $servers;
    }

    /**
     * Gets HTTP Client
     *
     * @return  ClientInterface Returns HTTP Client
     */
    public function getClient()
    {
        return $this->service->getOpenStack()->getClient();
    }

    /**
     * List Servers action
     *
     * @param   bool              $detail optional Should it return detailed info?
     * @param   ListServersFilter $filter optional Filter options.
     * @return  array             Returns servers list array
     * @throws  RestClientException
     */
    public function listServers($detail = true, ListServersFilter $filter = null)
    {
        $result = null;
        if ($filter !== null) {
            $options = $filter->getQueryData();
        } else {
            $options = array();
        }
        $response = $this->getClient()->call(
            $this->service,
            '/servers' . ($detail ? '/detail' : ''),
            $options
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->servers;
        }
        return $result;
    }

    /**
     * Create Server action
     *
     * @param   string          $name        A server name to create.
     * @param   string          $flavorId    A flavorId.
     * @param   string          $imageId     An imageId.
     * @param   DiscConfig      $diskConfig  optional The disk configuration value.
     * @param   array           $metadata    optional Metadata key and value pairs.
     * @param   PersonalityList $personality optional File path and contents.
     * @param   NetworkList     $networks    optional The networks to which you want to attach the server.
     * @param   array           $extProperties optional extensions properties
     * @return  object          Returns server object
     * @throws  RestClientException
     */
    public function createServer($name, $flavorId, $imageId, DiscConfig $diskConfig = null,
                                 array $metadata = null, PersonalityList $personality = null,
                                 NetworkList $networks = null, array $extProperties = null)
    {
        $result = null;

        $server = array(
            'name'      => $name,
            'imageRef'  => $imageId,
            'flavorRef' => $flavorId,
        );
        if ($diskConfig !== null) {
            $server["OS-DCF:diskConfig"] = (string) $diskConfig;
        }
        if ($metadata !== null) {
            $server["metadata"] = $metadata;
        }
        if ($personality !== null && count($personality) > 0) {
            $server["personality"] = $personality->toArray();
        }
        if ($networks !== null && count($networks) > 0) {
            $server["networks"] = $networks->toArray();
        }

        if (!empty($extProperties))
            $server = array_merge($server, $extProperties);

        $options = array(
            'server' => $server,
        );
        $response = $this->getClient()->call(
            $this->service,
            '/servers', $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->server;
        }
        return $result;
    }

    /**
     * Get Server Details action
     *
     * Lists details for the specified server.
     *
     * @param   string     $serverId  A server ID.
     * @return  object     Returns server details
     * @throws  RestClientException
     */
    public function getServerDetails($serverId)
    {
        $result = null;
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s', $serverId), null, 'GET'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->server;
        }
        return $result;
    }

    /**
     * Update Server action
     *
     * @param   string     $serverId   A server ID.
     * @param   string     $name       optional A new Server Name.
     * @param   string     $accessIpv4 optional Server IPv4
     * @param   string     $accessIpv6 optional Server IPv6
     * @return  object     Returns updated server object
     * @throws  RestClientException
     * @throws  \InvalidArgumentException
     */
    public function updateServer($serverId, $name = null, $accessIpv4 = null, $accessIpv6 = null)
    {
        $result = null;
        $server = array();
        if ($name !== null) {
            $server['name'] = (string) $name;
        }
        if ($accessIpv4 !== null) {
            $server['accessIPv4'] = (string) $accessIpv4;
        }
        if ($accessIpv6 !== null) {
            $server['accessIPv6'] = (string) $accessIpv6;
        }
        if (empty($server)) {
            throw new \InvalidArgumentException(
                'At least one optional parameter (name, accessIPv4 or accessIPv6) must be provided to update.'
            );
        }
        $options['_putData'] = json_encode(array(
            'server' => $server,
        ));
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s', $serverId), $options, 'PUT'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
        }
        return $result;
    }

    /**
     * Delete Server action
     *
     * This operation deletes a specified server instance from the system
     *
     * @param   string     $serverId  A server ID.
     * @return  bool       Returns true on success or throws an exception
     * @throws  RestClientException
     */
    public function deleteServer($serverId)
    {
        $result = false;
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s', $serverId), null, 'DELETE'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Create Image action.
     *
     * Currently, image creation is an asynchronous operation, so coordinating the
     * creation with data quiescence, and so on, is currently not possible.
     *
     * @param   string     $serverId A server ID
     * @param   string     $name     The name for the new image.
     * @param   array      $metadata Key and value pairs for metadata. The maximum size of the
     *                               metadata key and value is 255 bytes each
     * @return  string      Returns the ID to the newly created image.
     * @throws  RestClientException
     */
    public function createImage($serverId, $name, array $metadata = null)
    {
        $result = null;
        $ci = array(
            'name' => (string) $name,
        );
        if ($metadata !== null) {
            $ci['metadata'] = $metadata;
        }
        $options = array(
            'createImage' => $ci,
        );
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s/action', $serverId), $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = $response->getHeader('Location');
            if ($result !== null) {
                $result = preg_replace('#^.+/([^/]+)$#', '\\1', $result);
            }
        }
        return $result;
    }

    /**
     * List Images action
     *
     * This operation lists all images visible by the account.
     *
     * @param   bool             $detailed optional If true it returns detailed description for an every image.
     * @param   ListImagesFilter $filter   optional Filter options.
     * @return  array            Returns list of images array
     * @throws  RestClientException
     */
    public function listImages($detailed = true, ListImagesFilter $filter = null)
    {
        $result = null;
        if ($filter !== null) {
            $options = $filter->getQueryData();
        } else {
            $options = array();
        }
        $response = $this->getClient()->call(
            $this->service,
            '/images' . ($detailed ? '/detail' : ''),
            $options
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->images;
        }
        return $result;
    }

    /**
     * Get Image Details action
     *
     * @param   string   $imageId  Image ID. An UUID for the image.
     * @return  object   Returns detailed info about an image
     * @throws  RestClientException
     */
    public function getImage($imageId)
    {
        $result = null;
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/images/%s', $imageId)
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->image;
        }
        return $result;
    }

    /**
     * Delete Image action.
     *
     * @param   string   $imageId  Image ID. An UUID for the image.
     * @return  bool     Returns true on succes or throws an exception if failure
     * @throws  RestClientException
     */
    public function deleteImage($imageId)
    {
        $result = false;
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/images/%s', $imageId), null, 'DELETE'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * List Flavors action
     *
     * This operation lists information for all available flavors.
     *
     * @param   bool             $detailed optional If true it returns detailed description for an every image.
     * @return  array            Returns list of flavors array
     * @throws  RestClientException
     */
    public function listFlavors($detailed = true)
    {
        $result = null;
        $options = array();
        $response = $this->getClient()->call(
            $this->service,
            '/flavors' . ($detailed ? '/detail' : ''),
            $options
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->flavors;
        }
        return $result;
    }

    /**
     * Resize server action
     *
     * @param   string     $serverId   The server ID to resize.
     * @param   string     $name       The name for the resized server.
     * @param   string     $flavorId   The flavor ID.
     * @param   DiscConfig $diskConfig optional Disc config.
     * @return  bool       Returns true on success or throws an exception on failure
     * @throws  RestClientException
     */
    public function resizeServer($serverId, $name, $flavorId, DiscConfig $diskConfig = null)
    {
        $result = false;
        $resize = array(
            'name'      => (string) $name,
            'flavorRef' => (string) $flavorId,
        );
        if ($diskConfig !== null) {
            $resize['OS-DCF:diskConfig'] = (string) $diskConfig;
        }
        $options = array(
            'resize' => $resize,
        );
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s/action', $serverId),
            $options,
            'POST'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Change Administrator password action.
     *
     * @param   string     $serverId  The server Id.
     * @param   string     $adminPass The administrator password.
     * @return  bool       Returns TRUE on success
     * @throws  RestClientException
     */
    public function changeAdminPass($serverId, $adminPass)
    {
        $result = false;
        $action = array(
            'adminPass' => (string) $adminPass,
        );
        $options = array(
            'changePassword' => $action,
        );
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s/action', $serverId),
            $options,
            'POST'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Reboot server action.
     *
     * @param   string     $serverId  The server Id.
     * @param   RebootType $type      optional Reboot type. (RebootType::soft() by default)
     * @return  bool       Returns TRUE on success
     * @throws  RestClientException
     */
    public function rebootServer($serverId, RebootType $type = null)
    {
        $result = false;
        $action = array(
            'type' => ($type === null ? RebootType::TYPE_SOFT : (string) $type),
        );
        $options = array(
            'reboot' => $action,
        );
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s/action', $serverId),
            $options,
            'POST'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Confirm Resized Server action
     *
     * During a resize operation, the original server is saved for a period of time to allow roll back
     * if a problem occurs. After you verify that the newly resized server works properly, use this
     * operation to confirm the resize. After you confirm the resize, the original server is removed
     * and you cannot roll back to that server. All resizes are automatically confirmed after 24
     * hours if you do not explicitly confirm or revert the resize.
     *
     * @param   string    $serverId  A server id.
     * @return  bool      Returns true on success or throws an exception on failure
     * @throws  RestClientException
     */
    public function confirmResizedServer($serverId)
    {
        $result = false;
        $options = array(
            "confirmResize" => null,
        );
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s/action', $serverId),
            $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * List Extensions action
     *
     * This operation returns a response body. In the response body, each extension is identified
     * by two unique identifiers, a namespace and an alias. Additionally an extension contains
     * documentation links in various formats
     *
     * @return  array      Returns list of available extensions
     * @throws  RestClientException
     */
    public function listExtensions()
    {
        $result = null;
        $response = $this->getClient()->call($this->service, '/extensions');
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->extensions;
        }
        return $result;
    }

    /**
     * List keypairs method
     *
     * View a lists of keypairs associated with the account.
     *
     * @return  array Returns the list of keypairs
     * @throws  RestClientException
     */
    public function listKeypairs()
    {
        $result = null;
        $response = $this->getClient()->call($this->service, '/os-keypairs');
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->keypairs;
        }
        return $result;
    }

    /**
     * Gets keypair
     *
     * Show a keypair associated with the account.
     *
     * @param   string  $keypairName Keypair name
     * @return  object  Returns the keypair object
     * @throws  RestClientException
     */
    public function getKeypair($keypairName)
    {
        $result = null;
        $response = $this->getClient()->call($this->service, sprintf('/os-keypairs/%s', $keypairName));
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->keypair;
        }
        return $result;
    }

    /**
     * Generates or imports keypair.
     *
     * @param   string     $name      A keypair name.
     * @param   string     $publicKey optional The public ssh key to import. If not provided, one will be generated.
     * @return  object     Returns generated keypair object
     * @throws  RestClientException
     */
    public function createKeypair($name, $publicKey = null)
    {
        $result = null;
        $keypair = array(
            'name'  => (string)$name,
        );
        if (isset($publicKey)) {
            $keypair['public_key'] = (string) $publicKey;
        }
        $options = array(
            'keypair' => $keypair,
        );
        $response = $this->getClient()->call(
            $this->service, '/os-keypairs', $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->keypair;
        }
        return $result;
    }

    /**
     * Removes a keypair by its name.
     *
     * @param   string    $name  A keypair name.
     * @return  bool      Returns true on success or throws an exception if failure.
     * @throws  RestClientException
     */
    public function deleteKeypair($name)
    {
        $result = false;
        $response = $this->getClient()->call($this->service, sprintf('/os-keypairs/%s', $name), null, 'DELETE');
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * List Floating Ips action
     *
     * Lists floating IP addresses associated with the tenant or account.
     *
     * @return  array Returns the list floating IP addresses associated with the tenant or account.
     * @throws  RestClientException
     */
    public function listFloatingIps()
    {
        $result = null;
        $response = $this->getClient()->call($this->service, '/os-floating-ips');
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->floating_ips;
        }
        return $result;
    }

    /**
     * Gets floating Ip details
     *
     * Lists details of the floating IP address associated with floating_IP_address_ID.
     *
     * @param   int   $floatingIpAddressId  The unique identifier associated with allocated floating IP address.
     * @return  object Returns details of the floating IP address.
     * @throws  RestClientException
     */
    public function getFloatingIp($floatingIpAddressId)
    {
        $result = null;
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/os-floating-ips/%s', $floatingIpAddressId)
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->floating_ip;
        }
        return $result;
    }

    /**
     * List Floating Ip Pools action
     *
     * View a list of Floating IP Pools.
     *
     * @return  array Returns the list of floating ip pools.
     * @throws  RestClientException
     */
    public function listFloatingIpPools()
    {
        $result = null;
        $response = $this->getClient()->call($this->service, '/os-floating-ip-pools');
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->floating_ip_pools;
        }
        return $result;
    }

    /**
     * Allocates a new floating IP address to a tenant or account.
     *
     * @param   string   $pool optiional Pool to allocate IP address from. Will use default pool if not specified.
     * @return  object   Returns allocated floating ip details
     * @throws  RestClientException
     */
    public function createFloatingIp($pool = null)
    {
        $result = null;
        $options = array();
        if (isset($pool)) {
            $options['pool'] = (string) $pool;
        }
        $response = $this->getClient()->call(
            $this->service,
            '/os-floating-ips',
            $options,
            'POST'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->floating_ip;
        }
        return $result;
    }

    /**
     * Deallocates the floating IP address associated with floating_IP_address_ID.
     *
     * @param   int $floatingIpAddressId Floating IP address ID
     * @return  bool Returns true on success or throws an exception
     * @throws  RestClientException
     */
    public function deleteFloatingIp($floatingIpAddressId)
    {
        $result = false;
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/os-floating-ips/%s', $floatingIpAddressId),
            null, 'DELETE'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Add floating IP to an instance.
     *
     * @param   string     $serverId            The Server ID.
     * @param   string     $floatingIpAddress Floating IP Address.
     * @return  bool       Returns true on success or throws an exception
     * @throws  RestClientException
     */
    public function addFloatingIp($serverId, $floatingIpAddress)
    {
        $result = false;
        $options = array(
            'addFloatingIp' => array(
                'address' => (string) $floatingIpAddress,
            ),
        );
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s/action', $serverId),
            $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Remove floating IP from an instance.
     *
     * @param   string     $serverId            The Server ID.
     * @param   string     $floatingIpAddress Floating IP Address.
     * @return  bool       Returns true on success or throws an exception
     * @throws  RestClientException
     */
    public function removeFloatingIp($serverId, $floatingIpAddress)
    {
        $result = false;
        $options = array(
            'removeFloatingIp' => array(
                'address' => (string) $floatingIpAddress,
            ),
        );
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s/action', $serverId),
            $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * List security groups.
     *
     * @param   string     $serverId   optional The server ID (UUID) of interest to you.
     * @return  array Returns the list of the security groups
     * @throws  RestClientException
     */
    public function listSecurityGroups($serverId = null)
    {
        $result = null;
        $response = $this->getClient()->call(
            $this->service,
            ($serverId === null ? '/os-security-groups' : sprintf('/servers/%s/os-security-groups', (string)$serverId))
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->security_groups;
        }
        return $result;
    }

    /**
     * Create Security Group action
     *
     * @param   string     $name        A security group name.
     * @param   string     $description A description.
     * @return  object     Returns created secrurity group.
     * @throws  RestClientException
     */
    public function createSecurityGroup($name, $description)
    {
        $result = null;
        $options = array(
            'security_group' => array(
                'name'        => (string) $name,
                'description' => (string) $description,
            ),
        );
        $response = $this->getClient()->call(
            $this->service, '/os-security-groups', $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->security_group;
        }
        return $result;
    }

    /**
     * Gets a specific security group
     *
     * @param   int      $securityGroupId  Security group unique identifier.
     * @return  object   Returns security group Object
     * @throws  RestClientException
     */
    public function getSecurityGroup($securityGroupId)
    {
        $result = null;
        $response = $this->getClient()->call($this->service, sprintf('/os-security-groups/%s', $securityGroupId));
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->security_group;
        }
        return $result;
    }

    /**
     * Removes a specific security group
     *
     * @param   int      $securityGroupId  Security group unique identifier.
     * @return  bool     Returns true on success or throws an exception
     * @throws  RestClientException
     */
    public function deleteSecurityGroup($securityGroupId)
    {
        $result = false;
        $response = $this->getClient()->call($this->service, sprintf('/os-security-groups/%s', $securityGroupId), null, 'DELETE');
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Creates security group rule.
     *
     * @param   object|array   $rule  Security Group rule to create
     * @return  object   Returns created security group rule
     * @throws  RestClientException
     */
    public function addSecurityGroupRule($rule)
    {
        $result = null;
        $options = array(
            'security_group_rule' => $rule,
        );
        $response = $this->getClient()->call(
            $this->service, '/os-security-group-rules', $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->security_group_rule;
        }
        return $result;
    }

    /**
     * Get Limits action
     *
     * Applications can programmatically determine current account limits by using this API operation.
     *
     * @return  object  Returns limits object
     * @throws  RestClientException
     */
    public function getLimits()
    {
        $result = null;
        $response = $this->getClient()->call(
            $this->service, '/limits'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->limits;
        }
        return $result;
    }

    /**
     * Removes a specific security group rule
     *
     * @param   int      $securityGroupRuleId  Security group rule ID.
     * @return  bool     Returns true on success or throws an exception
     * @throws  RestClientException
     */
    public function deleteSecurityGroupRule($securityGroupRuleId)
    {
        $result = false;
        $response = $this->getClient()->call($this->service, sprintf('/os-security-group-rules/%s', $securityGroupRuleId), null, 'DELETE');
        if ($response->hasError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * List Addresses action
     *
     * @param   string    $serverId  A server ID.
     * @param   string    $networkId A network ID.
     * @return  object    Returns all networks and addresses associated with a specific server.
     * @throws  RestClientException
     */
    public function listAddresses($serverId, $networkId = null)
    {
        $result = null;
        $response = $this->getClient()->call(
            $this->service, sprintf('/servers/%s/ips' . ($networkId !== null ? '/%s' : ''), $serverId, $networkId)
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $networkId === null ? $result->addresses : $result->network;
        }
        return $result;
    }

    /**
     * Gets Server Console Output
     *
     * Gets the output from the console log for a server.
     *
     * @param   string     $serverId A server ID
     * @param   int        $length   Number of lines to fetch from end of console log.
     * @return  string     Returns the console output for server instance.
     * @throws  RestClientException
     */
    public function getConsoleOutput($serverId, $length = null)
    {
        $result = null;
        if ($length == null) {
            $ci = new \stdClass();
        } else {
            $ci = array(
                'length' => (int)$length,
            );
        }
        $options = array(
            'os-getConsoleOutput' => $ci,
        );
        $response = $this->getClient()->call(
            $this->service,
            sprintf('/servers/%s/action', $serverId), $options, 'POST'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->output;
        }
        return $result;
    }
}