<?php
namespace Scalr\Service\OpenStack\Services;

use Scalr\Service\OpenStack\OpenStack;
use Scalr\Service\OpenStack\Services\Servers\Type\RebootType;
use Scalr\Service\OpenStack\Services\Servers\Handler\ImagesHandler;
use Scalr\Service\OpenStack\Services\Servers\Handler\KeypairsHandler;
use Scalr\Service\OpenStack\Services\Servers\Handler\FloatingIpsHandler;
use Scalr\Service\OpenStack\Services\Servers\Handler\SecurityGroupsHandler;
use Scalr\Service\OpenStack\Services\Servers\Type\ServersExtension;
use Scalr\Service\OpenStack\Services\Servers\Type\DiscConfig;
use Scalr\Service\OpenStack\Services\Servers\Type\NetworkList;
use Scalr\Service\OpenStack\Services\Servers\Type\PersonalityList;
use Scalr\Service\OpenStack\Exception\OpenStackException;
use Scalr\Service\OpenStack\Services\Servers\Type\ListServersFilter;
use Scalr\Service\OpenStack\Client\RestClientResponse;
use Scalr\Service\OpenStack\Services\Servers\V2\ServersApi;

/**
 * OpenStack Next Generation Cloud Servers™ service interface
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    04.12.2012
 *
 * @property \Scalr\Service\OpenStack\Services\Servers\Handler\SecurityGroupsHandler $securityGroups Gets a SecurityGroups service interface handler.
 * @property \Scalr\Service\OpenStack\Services\Servers\Handler\FloatingIpsHandler    $floatingIps    Gets a FloatingIps  service interface handler.
 * @property \Scalr\Service\OpenStack\Services\Servers\Handler\KeypairsHandler       $keypairs       Gets a Keypairs service interface handler.
 * @property \Scalr\Service\OpenStack\Services\Servers\Handler\ImagesHandler         $images         Gets an Images service interface handler.
 *
 * @method   \Scalr\Service\OpenStack\Services\Servers\V2\ServersApi                 getApiHandler() getApiHandler()                 Gets an Service API handler for the specific version
 * @method   array                                                                   list()          list($detail = true, ListServersFilter $filter = null) List Servers action. This operation returns a response
 *                                                                                                   body that lists the servers associated with your account
 */
class ServersService extends AbstractService implements ServiceInterface
{

    const VERSION_V2 = 'V2';

    const VERSION_DEFAULT = self::VERSION_V2;

    /**
     * Miscellaneous cache
     * @var array
     */
    private $cache;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceInterface::getType()
     */
    public static function getType()
    {
        return OpenStack::SERVICE_COMPUTE;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceInterface::getVersion()
     */
    public function getVersion()
    {
        return self::VERSION_DEFAULT;
    }

    public function __call($name, $args)
    {
        if ($name == 'list') {
            return call_user_func_array(array($this->getApiHandler(), 'listServers'), $args);
        }
        throw new \BadFunctionCallException(sprintf(
            'Unknown method "%s" for the class %s', $name, get_class($this)
        ));
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
        return $this->getApiHandler()->createServer(
            $name, $flavorId, $imageId, $diskConfig, $metadata, $personality, $networks, $extProperties
        );
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
        return $this->getApiHandler()->getServerDetails($serverId);
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
        return $this->getApiHandler()->updateServer($serverId, $name, $accessIpv4, $accessIpv6);
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
        return $this->getApiHandler()->createImage($serverId, $name, $metadata);
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
        return $this->getApiHandler()->deleteServer($serverId);
    }

    /**
     * List Images action.
     *
     * This operation lists all images visible by the account.
     *
     * @param   bool             $detailed optional If true it returns detailed description for an every image.
     * @param   ListImagesFilter $filter   optional Filter options.
     * @return  array            Returns list of images
     * @throws  RestClientException
     */
    public function listImages($detailed = true, ListImagesFilter $filter = null)
    {
        return $this->getApiHandler()->listImages($detailed, $filter);
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
        return $this->getApiHandler()->getImage($imageId);
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
        return $this->getApiHandler()->deleteImage($imageId);
    }

    /**
     * List Flavors action
     *
     * This operation lists information for all available flavors.
     *
     * @param   bool             $detailed optional If true it returns detailed description for an every image.
     * @return  array            Returns list of flavors
     * @throws  RestClientException
     */
    public function listFlavors($detailed = true)
    {
        return $this->getApiHandler()->listFlavors($detailed);
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
        return $this->getApiHandler()->resizeServer($serverId, $name, $flavorId, $diskConfig);
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
        return $this->getApiHandler()->confirmResizedServer($serverId);
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
        if (!isset($this->cache['extensions'])) {
            $ret = $this->getApiHandler()->listExtensions();
            $this->cache['extensions'] = array();
            foreach ($ret as $v) {
                $this->cache['extensions'][$v->name] = $v;
            }
        }
        return $this->cache['extensions'];
    }

    /**
     * Checks whether given extension is supported by the service.
     *
     * @param   ServersExtension|string  $extensionName  An extension name
     * @return  bool   Returns true if an extension is supported.
     */
    public function isExtensionSupported($extensionName)
    {
        $list = $this->listExtensions();
        return isset($list[(string)$extensionName]);
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
        return $this->getApiHandler()->listFloatingIpPools();
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
        return $this->getApiHandler()->listFloatingIps();
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
        return $this->getApiHandler()->getFloatingIp($floatingIpAddressId);
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
        return $this->getApiHandler()->createFloatingIp($pool);
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
        return $this->getApiHandler()->deleteFloatingIp($floatingIpAddressId);
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
        return $this->getApiHandler()->addFloatingIp($serverId, $floatingIpAddress);
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
        return $this->getApiHandler()->removeFloatingIp($serverId, $floatingIpAddress);
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
        return $this->getApiHandler()->listKeypairs();
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
        return $this->getApiHandler()->getKeypair($keypairName);
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
        return $this->getApiHandler()->createKeypair($name, $publicKey);
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
        return $this->getApiHandler()->deleteKeypair($name);
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
        return $this->getApiHandler()->listSecurityGroups();
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
        return $this->getApiHandler()->createSecurityGroup($name, $description);
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
        return $this->getApiHandler()->getSecurityGroup($securityGroupId);
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
        return $this->getApiHandler()->deleteSecurityGroup($securityGroupId);
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
        return $this->getApiHandler()->addSecurityGroupRule($rule);
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
        return $this->getApiHandler()->deleteSecurityGroupRule($securityGroupRuleId);
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
        return $this->getApiHandler()->listAddresses($serverId, $networkId);
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
        return $this->getApiHandler()->changeAdminPass($serverId, $adminPass);
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
        return $this->getApiHandler()->rebootServer($serverId, $type);
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
        return $this->getApiHandler()->getConsoleOutput($serverId, $length);
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
        return $this->getApiHandler()->getLimits();
    }
}