<?php
namespace Scalr\Service\OpenStack\Services\Network\V2;

use Scalr\Service\OpenStack\Services\Network\Type\ListPortsFilter;
use Scalr\Service\OpenStack\Services\Network\Type\CreatePort;
use Scalr\Service\OpenStack\Services\Network\Type\CreateSubnet;
use Scalr\Service\OpenStack\Services\Network\Type\ListSubnetsFilter;
use Scalr\Service\OpenStack\Type\BooleanType;
use Scalr\Service\OpenStack\Services\Network\Type\ListNetworksFilter;
use Scalr\Service\OpenStack\Exception\RestClientException;
use Scalr\Service\OpenStack\Type\AppFormat;
use Scalr\Service\OpenStack\Client\RestClientResponse;
use Scalr\Service\OpenStack\Client\ClientInterface;
use Scalr\Service\OpenStack\Services\NetworkService;

/**
 * OpenStack Quantum API v2.0 (May 7, 2013)
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.05.2013
 */
class NetworkApi
{

    /**
     * @var NetworkService
     */
    protected $service;

    /**
     * Constructor
     *
     * @param   NetworkService $network
     */
    public function __construct(NetworkService $network)
    {
        $this->service = $network;
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
     * Escapes string
     *
     * @param   string    $string A string needs to be escapted
     * @return  string    Returns url encoded string
     */
    public function escape($string)
    {
        return rawurlencode($string);
    }

    /**
     * List Networks action (GET /networks[/network-id])
     *
     * Lists a summary of all networks defined in Quantum that are accessible
     * to the tenant who submits the request.
     *
     * @param   string             $networkId optional The ID of the network to show detailed info
     * @param   ListNetworksFilter $filter    optional The query filter.
     * @return  array|object Returns the list of the networks or one network
     * @throws  RestClientException
     */
    public function listNetworks($networkId = null, ListNetworksFilter $filter = null)
    {
        $result = null;
        $detailed = ($networkId !== null ? sprintf("/%s", $this->escape($networkId)) : '');
        $response = $this->getClient()->call(
            $this->service,
            '/networks' . $detailed . ($filter !== null ? '?' . $filter->getQueryString() : ''),
            null, 'GET'
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = empty($detailed) ? $result->networks : $result->network;
        }
        return $result;
    }

    /**
     * ListSubnets action (GET /subnets[/subnet-id])
     *
     * Lists all subnets that are accessible to the tenant who submits the
     * request.
     *
     * @param   string            $subnetId optional The ID of the subnet to show detailed info
     * @param   ListSubnetsFilter $filter   optional The filter.
     * @return  array|object Returns the list of the subnets or one subnet
     * @throws  RestClientException
     */
    public function listSubnets($subnetId = null, ListSubnetsFilter $filter = null)
    {
        $result = null;
        $detailed = ($subnetId !== null ? sprintf("/%s", $this->escape($subnetId)) : '');
        $response = $this->getClient()->call(
            $this->service,
            '/subnets' . $detailed . ($filter !== null ? '?' . $filter->getQueryString() : '')
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = empty($detailed) ? $result->subnets : $result->subnet;
        }
        return $result;
    }

    /**
     * ListPorts action (GET /ports[/port-id])
     *
     * Lists all ports to which the tenant has access.
     *
     * @param   string          $portId optional The ID of the port to show detailed info
     * @param   ListPortsFilter $filter The filter options
     * @return  array|object Returns the list of the ports or the information about one port
     * @throws  RestClientException
     */
    public function listPorts($portId = null, ListPortsFilter $filter = null)
    {
        $result = null;
        $detailed = ($portId !== null ? sprintf("/%s", $this->escape($portId)) : '');
        $response = $this->getClient()->call(
            $this->service,
            '/ports' . $detailed . ($filter !== null ? '?' . $filter->getQueryString() : '')
        );
        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = empty($detailed) ? $result->ports : $result->port;
        }
        return $result;
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
        $result = null;

        $network = array();
        if ($name !== null) {
            $network['name'] = (string) $name;
        }
        if ($adminStateUp !== null) {
            $network['admin_state_up'] = (string)BooleanType::init($adminStateUp);
        }
        if ($shared !== null) {
            $network['shared'] = (string)BooleanType::init($shared);
        }
        if ($tenantId !== null) {
            $network['tenantId'] = (string) $tenantId;
        }

        $response = $this->getClient()->call(
            $this->service, '/networks',
            (!empty($network) ? array('network' => $network) : null), 'POST'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->network;
        }

        return $result;
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
        $result = null;

        $network = array();
        if ($name !== null) {
            $network['name'] = (string) $name;
        }
        if ($adminStateUp !== null) {
            $network['admin_state_up'] = (string)BooleanType::init($adminStateUp);
        }
        if (empty($network)) {
            throw new \BadFunctionCallException(sprintf(
                "Bad request. Either name or admin_state_up must have been provided for %s action."
            ), __FUNCTION__);
        }

        $response = $this->getClient()->call(
            $this->service, sprintf('/networks/%s', $this->escape($networkId)),
            array('_putData' => json_encode(array('network' => $network))), 'PUT'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->network;
        }

        return $result;
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
     * @param   string     $networkId    The ID of the network to remove.
     * @return  bool       Returns true on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function deleteNetwork($networkId)
    {
        $result = false;

        $response = $this->getClient()->call(
            $this->service, sprintf('/networks/%s', $this->escape($networkId)),
            null, 'DELETE'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = true;
        }

        return $result;
    }

    /**
     * Creates Subnet (POST /subnets)
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
     * @param   CreateSubnet $request Create subnet request object
     * @return  object       Returns subnet object on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function createSubnet(CreateSubnet $request)
    {
        $result = null;

        $options = array('subnet' => array_filter(
            (array)$request,
            create_function('$v', 'return $v !== null;')
        ));

        $response = $this->getClient()->call(
            $this->service, '/subnets',
            $options, 'POST'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->subnet;
        }

        return $result;
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
        $result = null;

        $response = $this->getClient()->call(
            $this->service, sprintf('/subnets/%s', $this->escape($subnetId)),
            array('_putData' => json_encode(array('subnet' => $options))), 'PUT'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->subnet;
        }

        return $result;
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
        $result = false;

        $response = $this->getClient()->call(
            $this->service, sprintf('/subnets/%s', $this->escape($subnetId)),
            null, 'DELETE'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = true;
        }

        return $result;
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
     * @param   CreatePort $request Create port request object
     * @return  object       Returns port object on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function createPort(CreatePort $request)
    {
        $result = null;

        $options = array('port' => array_filter(
            (array)$request,
            create_function('$v', 'return $v !== null;')
        ));

        $response = $this->getClient()->call(
            $this->service, '/ports',
            $options, 'POST'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->port;
        }

        return $result;
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
        $result = null;

        $response = $this->getClient()->call(
            $this->service, sprintf('/ports/%s', $this->escape($portId)),
            array('_putData' => json_encode(array('port' => $options))), 'PUT'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = $result->port;
        }

        return $result;
    }

    /**
     * Delete Port action (DELETE /ports/port-id)
     *
     * This operation removes a port from a Quantum network. If IP addresses are associated with
     * the port, they are returned to the respective subnets allocation pools.
     *
     * @param   string     $portId    The ID of the port to remove.
     * @return  bool       Returns true on success or throws an exception otherwise
     * @throws  RestClientException
     */
    public function deletePort($portId)
    {
        $result = false;

        $response = $this->getClient()->call(
            $this->service, sprintf('/ports/%s', $this->escape($portId)),
            null, 'DELETE'
        );

        if ($response->hasError() === false) {
            $result = json_decode($response->getContent());
            $result = true;
        }

        return $result;
    }
}