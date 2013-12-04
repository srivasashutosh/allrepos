<?php
namespace Scalr\Service\OpenStack\Services;

use Scalr\Service\OpenStack\Services\Network\Type\ListPortsFilter;
use Scalr\Service\OpenStack\Services\Network\Type\CreatePort;
use Scalr\Service\OpenStack\Services\Network\Type\CreateSubnet;
use Scalr\Service\OpenStack\Services\Network\Type\ListSubnetsFilter;
use Scalr\Service\OpenStack\Services\Network\Type\ListNetworksFilter;
use Scalr\Service\OpenStack\OpenStack;
use Scalr\Service\OpenStack\Exception\OpenStackException;
use Scalr\Service\OpenStack\Client\RestClientResponse;
use Scalr\Service\OpenStack\Services\Network\V2\NetworkApi;

/**
 * OpenStack Network (OpenStack Quantum API)
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.05.2013
 *
 * @property \Scalr\Service\OpenStack\Services\Network\Handler\NetworksHandler $networks
 *           Gets a Networks service interface handler.
 *
 * @property \Scalr\Service\OpenStack\Services\Network\Handler\SubnetsHandler $subnets
 *           Gets a Subnets service interface handler.
 *
 * @property \Scalr\Service\OpenStack\Services\Network\Handler\PortsHandler $ports
 *           Gets a Ports service interface handler.
 *
 * @method   \Scalr\Service\OpenStack\Services\Network\V2\NetworkApi getApiHandler()
 *           getApiHandler()
 *           Gets an Network API handler for the specific version
 */
class NetworkService extends AbstractService implements ServiceInterface
{

    const VERSION_V2 = 'V2';

    //If you change this version, please be aware of getEndpointUrl() method of this class
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
        return OpenStack::SERVICE_NETWORK;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceInterface::getVersion()
     */
    public function getVersion()
    {
        return self::VERSION_DEFAULT;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.AbstractService::getEndpointUrl()
     */
    public function getEndpointUrl()
    {
        //Endpoint url in the service catalog does not include version
        $cfg = $this->getOpenStack()->getConfig();
        return $cfg->getAuthToken() === null ?
            $cfg->getIdentityEndpoint() :
            parent::getEndpointUrl() . '/v2.0';
    }

    /**
     * List Networks action (GET /networks[/network-id])
     *
     * Lists a summary of all networks defined in Quantum that are accessible
     * to the tenant who submits the request.
     *
     * @param   string                   $networkId optional The ID of the network to show detailed info
     * @param   ListNetworksFilter|array $filter    optional The query filter.
     * @return  array|object Returns the list of the networks or one network
     * @throws  RestClientException
     */
    public function listNetworks($networkId = null, $filter = null)
    {
        if (!empty($filter) && !($filter instanceof ListNetworksFilter)) {
            $filter = ListNetworksFilter::initArray($filter);
        }
        return $this->getApiHandler()->listNetworks($networkId, $filter);
    }

    /**
     * ListSubnets action (GET /subnets[/subnet-id])
     *
     * Lists all subnets that are accessible to the tenant who submits the
     * request.
     *
     * @param   string                  $subnetId optional The ID of the subnet to show detailed info
     * @param   ListSubnetsFilter|array $filter   optional The filter.
     * @return  array|object Returns the list of the subnets or one subnet
     * @throws  RestClientException
     */
    public function listSubnets($subnetId = null, $filter = null)
    {
        if (!empty($filter) && !($filter instanceof ListSubnetsFilter)) {
            $filter = ListSubnetsFilter::initArray($filter);
        }
        return $this->getApiHandler()->listSubnets($subnetId, $filter);
    }

    /**
     * ListPorts action (GET /ports[/port-id])
     *
     * Lists all ports to which the tenant has access.
     *
     * @param   string                $portId optional The ID of the port to show detailed info
     * @param   ListPortsFilter|array $filter The filter options
     * @return  array|object Returns the list of the ports or the information about one port
     * @throws  RestClientException
     */
    public function listPorts($portId = null, $filter = null)
    {
        if (!empty($filter) && !($filter instanceof ListPortsFilter)) {
            $filter = ListPortsFilter::initArray($filter);
        }
        return $this->getApiHandler()->listPorts($portId, $filter);
    }

    /**
     * Create Network action (POST /networks)
     *
     * Creates a new Quantum network.
     *
     * @param   string     $name         optional A string specifying a symbolic name for the network,
     *                                   which is not required to be unique
     * @param   bool       $adminStateUp optional The administrative status of the network
     * @param   bool       $shared       optional Whether this network should be shared across all
     *                                   tenants or not. Note that the default policy setting restrict
     *                                   usage of this attribute to administrative users only
     * @param   string     $tenantId     optional The tenant which will own the network. Only administrative
     *                                   users can set the tenant identifier. This cannot be changed using
     *                                   authorization policies
     * @return  object Returns detailed information for the created network
     * @throws  RestClientException
     */
    public function createNetwork($name = null, $adminStateUp = null, $shared = null, $tenantId = null)
    {
        return $this->getApiHandler()->createNetwork($name, $adminStateUp, $shared, $tenantId);
    }

    /**
     * Update Network action (PUT /networks/network-id)
     *
     * Updates the specified network.
     * Either name or admin_state_up must be provided for this action.
     *
     * @param   string     $networkId    The ID of the network to update.
     * @param   string     $name         optional A string specifying a symbolic name for the network,
     *                                   which is not required to be unique
     * @param   bool       $adminStateUp optional The administrative status of the network
     * @return  object Returns detailed information for the updated network
     * @throws  RestClientException
     * @throws  \BadFunctionCallException
     */
    public function updateNetwork($networkId, $name = null, $adminStateUp = null)
    {
        return $this->getApiHandler()->updateNetwork($networkId, $name, $adminStateUp);
    }

    /**
     * Delete Network action (DELETE /networks/network-id)
     *
     * This operation deletes a Quantum network and its associated subnets provided that no
     * port is currently configured on the network.
     *
     * If ports are still configured on the network that you want to delete, a 409 Network In Use
     * error is returned.
     *
     * @param   string     $networkId    The ID of the network to update.
     * @return  bool       Returns true on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function deleteNetwork($networkId)
    {
        return $this->getApiHandler()->deleteNetwork($networkId);
    }

    /**
     * Creates Subnet
     *
     * This operation creates a new subnet on the specified network. The network ID,
     * network_id, is required. You must also specify the cidr attribute for the subnet.
     *
     * The remaining attributes are optional.
     * By default, Quantum creates IP v4 subnets. To create an IP v6 subnet, you must specify the
     * value 6 for the ip_version attribute in the request body. Quantum does not try to derive
     * the correct IP version from the provided CIDR. If the parameter for the gateway address,
     * gateway_ip, is not specified, Quantum allocates an address from the cidr for the gateway
     * for the subnet.
     *
     * To specify a subnet without a gateway, specify the value null for the gateway_ip
     * attribute in the request body. If allocation pools attribute, allocation_pools, is not
     * specified, Quantum automatically allocates pools for covering all IP addresses in the CIDR,
     * excluding the address reserved for the subnet gateway. Otherwise, you can explicitly
     * specify allocation pools as shown in the following example.
     *
     * When allocation_pools and gateway_ip are both specified, it is up to the user
     * ensuring the gateway ip does not overlap with the specified allocation pools; otherwise a
     * 409 Conflict error will be returned.
     *
     * @param   CreateSubnet|array $request Create subnet request object
     * @return  object       Returns subnet object on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function createSubnet($request)
    {
        if (!($request instanceof CreateSubnet)) {
            $request = CreateSubnet::initArray($request);
        }
        return $this->getApiHandler()->createSubnet($request);
    }

    /**
     * Update Subnet action (PUT /subnets/subnet-id)
     *
     * This operation updates the specified subnet. Some attributes, such as IP version
     * (ip_version), CIDR (cidr), and IP allocation pools (allocation_pools) cannot be
     * updated. Attempting to update these attributes results in a 400 Bad Request error.
     *
     * @param   string       $subnetId The Id of the subnet
     * @param   array|object $options  Raw options object (It will be json_encoded and passed as is.)
     * @return  object       Returns subnet object on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function updateSubnet($subnetId, $options)
    {
        return $this->getApiHandler()->updateSubnet($subnetId, $options);
    }

    /**
     * Delete Subnet action (DELETE /subnets/subnet-id)
     *
     * This operation removes a subnet from a Quantum network. The operation fails if IP
     * addresses from the subnet that you want to delete are still allocated.
     *
     * @param   string     $subnetId    The ID of the subnet to remove.
     * @return  bool       Returns true on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function deleteSubnet($subnetId)
    {
        return $this->getApiHandler()->deleteSubnet($subnetId);
    }

    /**
     * Create Port action (POST /ports)
     *
     * This operation creates a new Quantum port. The network where the port is created must
     * be specified in the network_id attribute in the request body. You can also specify the
     * following optional attributes:
     *
     * • A symbolic name for the port
     *
     * • MAC address. If an invalid address is specified a 400 Bad Request error will be
     * returned, whereas a 409 Conflict error will be returned if the specified MAC address
     * is already in use.
     *
     * When the MAC address is not specified, Quantum will try to allocate one for the port
     * being created. If there is a failure while generating the address, a 503 Service
     * Unavailable error will be returned.
     *
     * • Administrative state. Set to true for up, and false for down.
     *
     * • Fixed IPs
     *
     * • If you specify just a subnet ID, Quantum allocates an available IP from that subnet to
     * the port.
     *
     * • If you specify both a subnet ID and an IP address, Quantum tries to allocate the
     * specified address to the port.
     *
     * • Host routes for the port, in addition to the host routes defined for the subnets that the
     * port is associated with.
     *
     * @param   CreatePort|array $request Create port request object
     * @return  object           Returns port object on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function createPort($request)
    {
        if (!($request instanceof CreatePort)) {
            $request = CreatePort::initArray($request);
        }
        return $this->getApiHandler()->createPort($request);
    }

    /**
     * Update port action (PUT /ports/port-id)
     *
     * You can use this operation to update information for a port, such as its symbolic name and
     * associated IPs. When you update IPs for a port, the previously associated IPs are removed,
     * returned to the respective subnets allocation pools, and replaced by the IPs specified in the
     * body for the update request. Therefore, this operation replaces the fixed_ip attribute
     * when it is specified in the request body. If the new IP addresses are not valid, for example,
     * they are already in use, the operation fails and the existing IP addresses are not removed
     * from the port.
     *
     * @param   string       $portId  The ID of the port
     * @param   array|object $options The list of the options to change
     * @return  object       Returns port object on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function updatePort($portId, $options)
    {
        return $this->getApiHandler()->updatePort($portId, $options);
    }

    /**
     * Delete Port action (DELETE /ports/port-id)
     *
     * @param   string     $portId    The ID of the port to remove.
     * @return  bool       Returns true on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function deletePort($portId)
    {
        return $this->getApiHandler()->deletePort($portId);
    }
}