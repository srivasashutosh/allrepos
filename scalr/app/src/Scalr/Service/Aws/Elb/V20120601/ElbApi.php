<?php
namespace Scalr\Service\Aws\Elb\V20120601;

use Scalr\Service\Aws\AbstractApi;
use Scalr\Service\Aws\Elb\DataType\BackendServerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\LbCookieStickinessPolicyList;
use Scalr\Service\Aws\Elb\DataType\AppCookieStickinessPolicyList;
use Scalr\Service\Aws\Elb\DataType\PoliciesData;
use Scalr\Service\Aws\Elb\DataType\LbCookieStickinessPolicyData;
use Scalr\Service\Aws\Elb\DataType\AppCookieStickinessPolicyData;
use Scalr\Service\Aws\Elb\DataType\InstanceStateData;
use Scalr\Service\Aws\Elb\DataType\InstanceStateList;
use Scalr\Service\Aws\Elb\DataType\InstanceData;
use Scalr\Service\Aws\Elb\DataType\InstanceList;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionList;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\Elb;
use Scalr\Service\Aws\EntityManager;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionList;
use Scalr\Service\Aws\Elb\V20120601\Loader\DescribeLoadBalancersLoader;
use Scalr\Service\Aws\Elb\DataType\ListenerList;
use Scalr\Service\Aws\Client\QueryClientResponse;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientInterface;
use Scalr\Service\Aws\Elb\DataType\HealthCheckData;

/**
 * Elb Api messaging.
 *
 * Implements ELB Low-Level API Actions.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     21.09.2012
 */
class ElbApi extends AbstractApi
{

    /**
     * @var Elb
     */
    protected $elb;

    /**
     * Constructor
     * @param   Elb             $elb    Elb instance
     * @param   ClientInterface $client Client Interface
     */
    public function __construct(Elb $elb, ClientInterface $client)
    {
        $this->elb = $elb;
        $this->client = $client;
    }

    /**
     * DescribeLoadBalancers action
     *
     * Returns detailed configuration information for the specified LoadBalancers. If no LoadBalancers are
     * specified, the operation returns configuration information for all LoadBalancers created by the caller.
     *
     * @param  array|ListDataType $loadBalancerNamesList optional A list of names associated
     *                                  with the LoadBalancers at creation time.
     * @param  string  $marker optional An optional parameter reserved for future use.
     * @return LoadBalancerDescriptionList Returns the list of LoadBalancerDescriptionData.
     * @throws ClientException
     */
    public function describeLoadBalancers(ListDataType $loadBalancerNamesList = null, $marker = null)
    {
        $result = null;
        if ($loadBalancerNamesList !== null) {
            $options = $loadBalancerNamesList->getQueryArray('LoadBalancerNames');
        } else {
            $options = array();
        }
        if ($marker !== null) {
            $options['Marker'] = $marker;
        }
        $response = $this->client->call('DescribeLoadBalancers', $options);
        if ($response->getError() === false) {
            //Success
            $loader = new DescribeLoadBalancersLoader($this->elb);
            $loader->load($response->getRawContent());
            $result = $loader->getResult();
            if ($result instanceof LoadBalancerDescriptionList) {
                $em = $this->getEntityManager();
                foreach ($result as $loadBalancerDescription) {
                    $em->attach($loadBalancerDescription);
                }
            }
        }
        return $result;
    }

    /**
     * CreateLoadBalancer action
     *
     * @param  string       $loadBalancerName      Load Balancer Name.
     * @param  ListenerList $listenersList         A list of the Listeners.
     * @param  ListDataType $availabilityZonesList optional A list of Availability Zones
     * @param  ListDataType $subnetsList           optional A list of subnet IDs in your VPC to attach
     *                                             to your LoadBalancer.
     * @param  ListDataType $securityGroupsList    optional The security groups assigned to your
     *                                             LoadBalancer within your VPC.
     * @param  string       $scheme                optional The type of LoadBalancer
     * @return string       Returns DNSName for the created LoadBalancer.
     * @throws ElbException
     * @throws ClientException
     */
    public function createLoadBalancer($loadBalancerName, ListenerList $listenersList, ListDataType $availabilityZonesList = null,
                                       ListDataType $subnetsList = null, ListDataType $securityGroupsList = null, $scheme = null)
    {
        $result = null;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge(
            $options,
            $listenersList->getQueryArray(),
            $availabilityZonesList !== null ? $availabilityZonesList->getQueryArray('AvailabilityZones') : array(),
            $securityGroupsList !== null ? $securityGroupsList->getQueryArray('SecurityGroups') : array(),
            $subnetsList !== null ? $subnetsList->getQueryArray('Subnets') : array()
        );
        if ($scheme !== null) {
            $options['Scheme'] = (string) $scheme;
        }
        $response = $this->client->call('CreateLoadBalancer', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (isset($sxml->CreateLoadBalancerResult->DNSName)) {
                $result = (string) $sxml->CreateLoadBalancerResult->DNSName;
            } else {
                throw new ElbException(
                    'Unexpected response from server ' . var_export($response->getRawContent(), true)
                );
            }
        }
        return $result;
    }

    /**
     * DeleteLoadBalancer action
     *
     * @param    string    $loadBalancerName  The name associated with the LoadBalancer
     * @return   boolean   Returns TRUE if success or ErrorData if failure
     * @throws   ClientException
     */
    public function deleteLoadBalancer($loadBalancerName)
    {
        $result = false;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $response = $this->client->call('DeleteLoadBalancer', $options);
        if ($response->getError() === false) {
            //Success
            //Removes object from repository if exists
            $em = $this->getEntityManager();
            $object = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($object !== null) {
                $em->detach($object);
            }
            $result = true;
        }
        return $result;
    }

    /**
     * ApplySecurityGroupsToLoadBalancer action.
     *
     * Associates one or more security groups with your LoadBalancer in VPC. The provided security group
     * IDs will override any currently applied security groups.
     *
     * @param    string         $loadBalancerName   The name associated with the LoadBalancer.
     * @param    ListDataType   $securityGroupsList A list of security group IDs to associate
     *                                              with your LoadBalancer in VPC.
     * @return   array          Returns a SecurityGroups list array.
     * @throws   ClientException
     */
    public function applySecurityGroupsToLoadBalancer($loadBalancerName, ListDataType $securityGroupsList)
    {
        $result = array();
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $securityGroupsList->getQueryArray('SecurityGroups'));
        $response = $this->client->call('ApplySecurityGroupsToLoadBalancer', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ApplySecurityGroupsToLoadBalancerResult)) {
                throw new ElbException('Unexpected response! ' . $response->getRawContent());
            }
            if (!empty($sxml->ApplySecurityGroupsToLoadBalancerResult->SecurityGroups->member)) {
                foreach ($sxml->ApplySecurityGroupsToLoadBalancerResult->SecurityGroups->member as $v) {
                    $result[] = (string) $v;
                }
            }
            //Updates object in repository if exists
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null) {
                $loadBalancer->securityGroups = $result;
            }
        }
        return $result;
    }

    /**
     * AttachLoadBalancerToSubnets action.
     *
     * Adds one or more subnets to the set of configured subnets in the VPC for the LoadBalancer
     *
     * @param    string         $loadBalancerName   The name associated with the LoadBalancer.
     * @param    ListDataType   $subnetsList         A list of subnet IDs to add for the LoadBalancer.
     * @return   array          Returns a Subnets list array or ErrorData if failure
     * @throws   ClientException
     */
    public function attachLoadBalancerToSubnets($loadBalancerName, ListDataType $subnetsList)
    {
        $result = array();
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $subnetsList->getQueryArray('Subnets'));
        $response = $this->client->call('AttachLoadBalancerToSubnets', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->AttachLoadBalancerToSubnetsResult)) {
                throw new ElbException('Unexpected response! ' . $response->getRawContent());
            }
            if (!empty($sxml->AttachLoadBalancerToSubnetsResult->Subnets->member)) {
                foreach ($sxml->AttachLoadBalancerToSubnetsResult->Subnets->member as $v) {
                    $result[] = (string) $v;
                }
            }
            //Updates object in repository if exists
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null) {
                $loadBalancer->subnets = $result;
            }
        }
        return $result;
    }

    /**
     * DetachLoadBalancerFromSubnets action.
     *
     * Removes subnets from the set of configured subnets in the VPC for the LoadBalancer
     *
     * @param    string         $loadBalancerName   The name associated with the LoadBalancer.
     * @param    ListDataType   $subnetsList        A list of subnet IDs to remove from the set of configured subnets
     *                                              for the LoadBalancer.
     * @return   array          Returns a Subnets list array.
     * @throws   ClientException
     */
    public function detachLoadBalancerFromSubnets($loadBalancerName, ListDataType $subnetsList)
    {
        $result = array();
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $subnetsList->getQueryArray('Subnets'));
        $response = $this->client->call('DetachLoadBalancerFromSubnets', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->DetachLoadBalancerFromSubnetsResult)) {
                throw new ElbException('Unexpected response! ' . $response->getRawContent());
            }
            if (!empty($sxml->DetachLoadBalancerFromSubnetsResult->Subnets->member)) {
                foreach ($sxml->DetachLoadBalancerFromSubnetsResult->Subnets->member as $v) {
                    $result[] = (string) $v;
                }
            }
            //Updates object in repository if exists
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null) {
                //Removes required subnets from the object
                if (!empty($loadBalancer->subnets) && !empty($result)) {
                    $loadBalancer->subnets = array_values(array_diff($loadBalancer->subnets, $result));
                }
            }
        }
        return $result;
    }

    /**
     * CreateLoadBalancerListeners action
     *
     * Creates one or more listeners on a LoadBalancer for the specified port. If a listener with the given port
     * does not already exist, it will be created; otherwise, the properties of the new listener must match the
     * properties of the existing listener.
     *
     * @param    string           $loadBalancerName  A load balancer name.
     * @param    ListenerList     $listenersList     An ListenerList object that holds list of LoadBalancerPort,
     *                                               InstancePort, Protocol, Yes and SSLCertificateId items.
     * @return   boolean          Returns TRUE if listeners are successfully created.
     * @throws   ClientException
     */
    public function createLoadBalancerListeners($loadBalancerName, ListenerList $listenersList)
    {
        $result = false;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $listenersList->getQueryArray());
        $response = $this->client->call('CreateLoadBalancerListeners', $options);
        if ($response->getError() === false) {
            //Success
            /* @var $loadBalancer LoadBalancerDescriptionData */
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null) {
                //Updates listenerDescriptions for the LoadBalancer object.
                if ($loadBalancer->listenerDescriptions instanceof ListenerDescriptionList ||
                    $loadBalancer->listenerDescriptions !== $listenersList) {
                    $old = array();
                    /* @var $listener ListenerData */
                    foreach ($loadBalancer->listenerDescriptions->getComputed() as $listener) {
                        //Listeners are removed by loadBalancerPort, so we use it as primary key
                        //to compare.
                        $old[$listener->loadBalancerPort] = $listener;
                    }
                    //Append to existing load balancer new listeners which have just been created.
                    foreach ($listenersList as $listener) {
                        if (!array_key_exists($listener->loadBalancerPort, $old)) {
                            $listenerDescription = new ListenerDescriptionData();
                            $listenerDescription->setElb($this->elb);
                            $listenerDescription->listener = $listener;
                            $loadBalancer->listenerDescriptions->append($listenerDescription);
                            unset($listenerDescription);
                        }
                        //It's not allowed to change an existing lisntener properties
                        //in this api method. It will cause DuplicateListener error, therefore
                        //it's no sence to update listener's properties which are received from response.
                    }
                    unset($old);
                }
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DeleteLoadBalancerListeners action
     *
     * Deletes listeners from the LoadBalancer for the specified port.
     *
     * @param   string       $loadBalancerName       A load balancer name.
     * @param   ListDataType $loadBalancerPortsList  The client port number(s) of the LoadBalancerListener(s)
     *                                               to be removed.
     * @return  boolean      Returns TRUE if success
     * @throws  ClientException
     */
    public function deleteLoadBalancerListeners($loadBalancerName, ListDataType $loadBalancerPortsList)
    {
        $result = false;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $loadBalancerPortsList->getQueryArray('LoadBalancerPorts'));
        $response = $this->client->call('DeleteLoadBalancerListeners', $options);
        if ($response->getError() === false) {
            $result = true;
            /* @var $loadBalancer LoadBalancerDescriptionData */
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null) {
                if ($loadBalancer->listenerDescriptions !== null && count($loadBalancer->listenerDescriptions)) {
                    $removed = $loadBalancerPortsList->getComputed();
                    /* @var $listenerDescription ListenerDescriptionData */
                    foreach ($loadBalancer->listenerDescriptions as $k => $listenerDescription) {
                        if (isset($listenerDescription->listener->loadBalancerPort) &&
                            in_array($listenerDescription->listener->loadBalancerPort, $removed)) {
                            unset($loadBalancer->listenerDescriptions[$k]);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * EnableAvailabilityZonesForLoadBalancer action
     *
     * Adds one or more EC2 Availability Zones to the LoadBalancer.
     * The LoadBalancer evenly distributes requests across all its registered Availability Zones that contain
     * instances. As a result, the client must ensure that its LoadBalancer is appropriately scaled for each
     * registered Availability Zone.
     *
     * @param   string       $loadBalancerName       The name associated with the LoadBalancer
     * @param   ListDataType $availabilityZonesList  A list of new Availability Zones for the LoadBalancer.
     *                                               Each Availability Zone must be in the same Region as the LoadBalancer.
     * @return  array        Returns an updated list of Availability Zones for the LoadBalancer.
     * @throws  ClientException
     */
    public function enableAvailabilityZonesForLoadBalancer($loadBalancerName, ListDataType $availabilityZonesList)
    {
        $result = array();
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $availabilityZonesList->getQueryArray('AvailabilityZones'));
        $response = $this->client->call('EnableAvailabilityZonesForLoadBalancer', $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->EnableAvailabilityZonesForLoadBalancerResult)) {
                throw new ElbException('Unexpected response ' . $response->getRawContent());
            }
            if (!empty($sxml->EnableAvailabilityZonesForLoadBalancerResult->AvailabilityZones->member)) {
                foreach ($sxml->EnableAvailabilityZonesForLoadBalancerResult->AvailabilityZones->member as $v) {
                    $result[] = (string) $v;
                }
            }
            /* @var $loadBalancer LoadBalancerDescriptionData */
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null) {
                $loadBalancer->availabilityZones = $result;
            }
        }
        return $result;
    }

    /**
     * DisableAvailabilityZonesForLoadBalancer action
     *
     * Removes the specified EC2 Availability Zones from the set of configured Availability Zones for the
     * LoadBalancer.
     * There must be at least one Availability Zone registered with a LoadBalancer at all times. A client cannot
     * remove all the Availability Zones from a LoadBalancer. Once an Availability Zone is removed, all the
     * instances registered with the LoadBalancer that are in the removed Availability Zone go into the
     * OutOfService state. Upon Availability Zone removal, the LoadBalancer attempts to equally balance the
     * traffic among its remaining usable Availability Zones. Trying to remove an Availability Zone that was not
     * associated with the LoadBalancer does nothing.
     *
     * @param   string       $loadBalancerName       The name associated with the LoadBalancer
     * @param   ListDataType $availabilityZonesList  A list of Availability Zones to be removed from the LoadBalancer.
     *                                               Each Availability Zone must be in the same Region as the LoadBalancer.
     * @return  array        Returns an updated list of Availability Zones for the LoadBalancer.
     * @throws  ClientException
     */
    public function disableAvailabilityZonesForLoadBalancer($loadBalancerName, ListDataType $availabilityZonesList)
    {
        $result = array();
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $availabilityZonesList->getQueryArray('AvailabilityZones'));
        $response = $this->client->call('DisableAvailabilityZonesForLoadBalancer', $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->DisableAvailabilityZonesForLoadBalancerResult)) {
                throw new ElbException('Unexpected response ' . $response->getRawContent());
            }
            if (!empty($sxml->DisableAvailabilityZonesForLoadBalancerResult->AvailabilityZones->member)) {
                foreach ($sxml->DisableAvailabilityZonesForLoadBalancerResult->AvailabilityZones->member as $v) {
                    $result[] = (string) $v;
                }
            }
            /* @var $loadBalancer LoadBalancerDescriptionData */
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null) {
                $loadBalancer->availabilityZones = $result;
            }
        }
        return $result;
    }

    /**
     * RegisterInstancesWithLoadBalancer action
     *
     * Adds new instances to the LoadBalancer.
     * Once the instance is registered, it starts receiving traffic and requests from the LoadBalancer. Any instance
     * that is not in any of the Availability Zones registered for the LoadBalancer will be moved to the OutOfService
     * state. It will move to the InService state when the Availability Zone is added to the LoadBalancer.
     *
     * Important!
     * Completion of this API does not guarantee that operation has completed. Rather, it means that
     * the request has been registered and the changes will happen shortly.
     *
     * @param   string       $loadBalancerName The name associated with the LoadBalancer.
     * @param   InstanceList $instancesList    A list of instance IDs that should be registered with the LoadBalancer.
     * @return  InstanceList Returns an updated list of instances for the LoadBalancer.
     * @throws  ClientException
     */
    public function registerInstancesWithLoadBalancer($loadBalancerName, InstanceList $instancesList)
    {
        $result = null;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $instancesList->getQueryArray());
        $response = $this->client->call('RegisterInstancesWithLoadBalancer', $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->RegisterInstancesWithLoadBalancerResult)) {
                throw new ElbException('Unexpected response ' . $response->getRawContent());
            }
            $result = $this->handleInstanceResultForLoadBalancer(
                $options['LoadBalancerName'], $sxml->RegisterInstancesWithLoadBalancerResult
            );
        }
        return $result;
    }

    /**
     * Handles Instance Result.
     *
     * Loads Instance list from Result and updates load balancer entity if it does exist.
     *
     * @param   string            $loadBalancerName  LoadBalancer name
     * @param   \SimpleXMLElement $instancesResult   Result that contains Instance obejct
     * @return  InstanceList      Returns an updated list of instances for the LoadBalancer.
     * @throws  ClientException
     */
    private function handleInstanceResultForLoadBalancer($loadBalancerName,\SimpleXMLElement $instancesResult)
    {
        $result = new InstanceList();
        $result->setElb($this->elb)->setLoadBalancerName($loadBalancerName);
        if (!empty($instancesResult->Instances->member)) {
            foreach ($instancesResult->Instances->member as $v) {
                $instance = new InstanceData();
                $instance->instanceId = (string) $v->InstanceId;
                $result->append($instance);
                unset($instance);
            }
        }
        /* @var $loadBalancer LoadBalancerDescriptionData */
        $loadBalancer = $this->elb->loadBalancer->get($loadBalancerName);
        if ($loadBalancer !== null) {
            $loadBalancer->instances = $result;
        }
        return $result;
    }

    /**
     * DeregisterInstancesFromLoadBalancer action
     *
     * Deregisters instances from the LoadBalancer. Once the instance is deregistered, it will stop receiving
     * traffic from the LoadBalancer.
     * In order to successfully call this API, the same account credentials as those used to create the
     * LoadBalancer must be provided.
     *
     * @param   string       $loadBalancerName The name associated with the LoadBalancer.
     * @param   InstanceList $instancesList    A list of EC2 instance IDs consisting of all instances
     *                                         to be deregistered.
     * @return  InstanceList Returns an updated list of remaining instances registered with the LoadBalancer.
     * @throws  ClientException
     */
    public function deregisterInstancesFromLoadBalancer($loadBalancerName, InstanceList $instancesList)
    {
        $result = null;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $instancesList->getQueryArray());
        $response = $this->client->call('DeregisterInstancesFromLoadBalancer', $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->DeregisterInstancesFromLoadBalancerResult)) {
                throw new ElbException('Unexpected response ' . $response->getRawContent());
            }
            $result = $this->handleInstanceResultForLoadBalancer(
                $options['LoadBalancerName'], $sxml->DeregisterInstancesFromLoadBalancerResult
            );
        }
        return $result;
    }

    /**
     * DescribeInstanceHealth action
     *
     * Returns the current state of the instances of the specified LoadBalancer. If no instances are specified,
     * the state of all the instances for the LoadBalancer is returned.
     *
     * @param   string       $loadBalancerName The name associated with the LoadBalancer.
     * @param   InstanceList $instancesList    A list of instance IDs whose states are being queried.
     * @return  InstanceStateList Returns a list containing health information for the specified instances.
     * @throws  ClientException
     */
    public function describeInstanceHealth($loadBalancerName, InstanceList $instancesList)
    {
        $result = null;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName
        );
        $options = array_merge($options, $instancesList->getQueryArray());
        $response = $this->client->call('DescribeInstanceHealth', $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->DescribeInstanceHealthResult)) {
                throw new ElbException('Unexpected response ' . $response->getRawContent());
            }
            $result = new InstanceStateList();
            $result->setElb($this->elb)->setLoadBalancerName($options['LoadBalancerName']);
            if (isset($sxml->DescribeInstanceHealthResult->InstanceStates->member)) {
                foreach ($sxml->DescribeInstanceHealthResult->InstanceStates->member as $v) {
                    $instanceState = new InstanceStateData();
                    $instanceState->description = (string) $v->Description;
                    $instanceState->instanceId = (string) $v->InstanceId;
                    $instanceState->reasonCode = (string) $v->ReasonCode;
                    $instanceState->state = (string) $v->State;
                    $result->append($instanceState);
                    unset($instanceState);
                }
            }
            $loadBalancer = $this->elb->loadBalancer->get($loadBalancerName);
            if ($loadBalancer !== null) {
                //I guess we need not update instances for the LoadBalancer here
            }
        }
        return $result;
    }

    /**
     * ConfigureHealthCheck action
     *
     * Enables the client to define an application healthcheck for the instances.
     *
     * @param   string          $loadBalancerName The mnemonic name associated with the LoadBalancer.
     * @param   HealthCheckData $healthCheck      A structure containing the configuration information
     *                                            for the new healthcheck.
     * @return  HealthCheckData The updated healthcheck for the instances.
     * @throws  ClientException
     */
    public function configureHealthCheck($loadBalancerName, HealthCheckData $healthCheck)
    {
        $result = null;
        $options = array(
            'LoadBalancerName'               => (string) $loadBalancerName,
            'HealthCheck.Timeout'            => (string) $healthCheck->timeout,
            'HealthCheck.Target'             => (string) $healthCheck->target,
            'HealthCheck.Interval'           => (string) $healthCheck->interval,
            'HealthCheck.UnhealthyThreshold' => (string) $healthCheck->unhealthyThreshold,
            'HealthCheck.HealthyThreshold'   => (string) $healthCheck->healthyThreshold
        );
        $response = $this->client->call('ConfigureHealthCheck', $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ConfigureHealthCheckResult->HealthCheck)) {
                throw new ElbException('Unexpected response ' . $response->getRawContent());
            }
            $ptr = $sxml->ConfigureHealthCheckResult->HealthCheck;
            //Tries to look loadBalancer instance up in the repository.
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer != null && $loadBalancer->healthCheck instanceof HealthCheckData) {
                $result = $loadBalancer->healthCheck;
            } else {
                $bset = true;
                $result = new HealthCheckData();
                $result->setElb($this->elb)->setLoadBalancerName($options['LoadBalancerName']);
            }
            $result->healthyThreshold = (int) $ptr->HealthyThreshold;
            $result->interval = (int) $ptr->Interval;
            $result->target = (string) $ptr->Target;
            $result->timeout = (int) $ptr->Timeout;
            $result->unhealthyThreshold = (int) $ptr->UnhealthyThreshold;
            unset($ptr);
            if ($loadBalancer != null && isset($bset)) {
                $loadBalancer->healthCheck = $result;
            }
        }
        return $result;
    }

    /**
     * SetLoadBalancerListenerSSLCertificate action
     *
     * Sets the certificate that terminates the specified listener's SSL connections.
     * The specified certificate replaces any prior certificate that was used on the
     * same LoadBalancer and port.
     *
     * @param   string     $loadBalancerName The name of the the LoadBalancer.
     * @param   int        $loadBalancerPort The port that uses the specified SSL certificate.
     * @param   string     $sslCertificateId The ID of the SSL certificate chain to use.
     * @return  boolean    Returns boolean TRUE if success or throws exception
     * @throws  ClientException
     */
    public function setLoadBalancerListenerSslCertificate($loadBalancerName, $loadBalancerPort, $sslCertificateId)
    {
        $result = false;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName,
            'LoadBalancerPort' => (int) $loadBalancerPort,
            'SSLCertificateId' => (string) $sslCertificateId
        );
        $response = $this->client->call('SetLoadBalancerListenerSSLCertificate', $options);
        if ($response->getError() === false) {
            //Tries to look loadBalancer instance up in the repository.
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer != null) {
                //Updates sslCertificateId property for the appropriated loadBalancer and its Listener.
                foreach ($loadBalancer->listenerDescriptions as $listenerDescription) {
                    if ($listenerDescription->listener->loadBalancerPort == $options['LoadBalancerPort']) {
                        $listenerDescription->listener->sslCertificateId = $options['SSLCertificateId'];
                    }
                }
            }
            $result = true;
        }
        return $result;
    }

    /**
     * CreateAppCookieStickinessPolicy action
     *
     * Generates a stickiness policy with sticky session lifetimes that follow that of an application-generated
     * cookie. This policy can be associated only with HTTP/HTTPS listeners.
     *
     * This policy is similar to the policy created by CreateLBCookieStickinessPolicy, except that the lifetime of
     * the special Elastic Load Balancing cookie follows the lifetime of the application-generated cookie specified
     * in the policy configuration. The LoadBalancer only inserts a new stickiness cookie when the application
     * response includes a new application cookie.
     *
     * If the application cookie is explicitly removed or expires, the session stops being sticky until a new
     * application cookie is issued.
     *
     * Note. An application client must receive and send two cookies: the application-generated cookie and
     * the special Elastic Load Balancing cookie named AWSELB. This is the default behavior for many
     * common web browsers.
     *
     * @param   string     $loadBalancerName  The name associated with the LoadBalancer.
     * @param   string     $policyName        The name of the policy being created.
     * @param   string     $cookieName        Name of the application cookie used for stickiness.
     * @return  boolean    Returns true if success or throws an exception.
     * @throws  ClientException
     */
    public function createAppCookieStickinessPolicy($loadBalancerName, $policyName, $cookieName)
    {
        $result = false;
        $options = array(
            'CookieName'       => (string) $cookieName,
            'LoadBalancerName' => (string) $loadBalancerName,
            'PolicyName'       => (string) $policyName
        );
        $response = $this->client->call('CreateAppCookieStickinessPolicy', $options);
        if ($response->getError() === false) {
            $result = true;
            //Tries to look loadBalancer instance up in the repository.
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer != null) {
                if (isset($loadBalancer->policies->appCookieStickinessPolicies)) {
                    /* @var $policy AppCookieStickinessPolicyData */
                    foreach ($loadBalancer->policies->appCookieStickinessPolicies as $policy) {
                        if ($policy->policyName === $options['PolicyName']) {
                            $policy->cookieName = $options['CookieName'];
                            return $result;
                        }
                    }
                }
                if ($loadBalancer->policies === null) {
                    $loadBalancer->policies = new PoliciesData();
                }
                if ($loadBalancer->policies->appCookieStickinessPolicies === null) {
                    $loadBalancer->policies->appCookieStickinessPolicies = new AppCookieStickinessPolicyList();
                }
                $loadBalancer->policies->appCookieStickinessPolicies->append(new AppCookieStickinessPolicyData($options['PolicyName'], $options['CookieName']));
            }
        }
        return $result;
    }

    /**
     * CreateLBCookieStickinessPolicy action
     *
     * Generates a stickiness policy with sticky session lifetimes controlled by the lifetime of the browser
     * (user-agent) or a specified expiration period. This policy can be associated only with HTTP/HTTPS
     * listeners.
     * When a LoadBalancer implements this policy, the LoadBalancer uses a special cookie to track the backend
     * server instance for each request. When the LoadBalancer receives a request, it first checks to see if this
     * cookie is present in the request. If so, the LoadBalancer sends the request to the application server
     * specified in the cookie. If not, the LoadBalancer sends the request to a server that is chosen based on
     * the existing load balancing algorithm.
     * A cookie is inserted into the response for binding subsequent requests from the same user to that server.
     * The validity of the cookie is based on the cookie expiration time, which is specified in the policy
     * configuration.
     *
     * @param   string     $loadBalancerName       The name associated with the LoadBalancer.
     * @param   string     $policyName             The name of the policy being created.
     * @param   numeric    $cookieExpirationPeriod The time period in seconds after which the cookie should
     *                                             be considered stale. Not specifying this parameter
     *                                             indicates that the sticky session will last for the duration
     *                                             of the browser session.
     * @return  boolean    Returns true if success or throws an exception.
     * @throws  ClientException
     */
    public function createLbCookieStickinessPolicy($loadBalancerName, $policyName, $cookieExpirationPeriod)
    {
        $result = false;
        $options = array(
            'CookieExpirationPeriod' => (string) $cookieExpirationPeriod,
            'LoadBalancerName'       => (string) $loadBalancerName,
            'PolicyName'             => (string) $policyName
        );
        $response = $this->client->call('CreateLBCookieStickinessPolicy', $options);
        if ($response->getError() === false) {
            $result = true;
            //Tries to look loadBalancer instance up in the repository.
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer != null) {
                if (isset($loadBalancer->policies->lbCookieStickinessPolicies)) {
                    /* @var $policy LbCookieStickinessPolicyData */
                    foreach ($loadBalancer->policies->lbCookieStickinessPolicies as $policy) {
                        if ($policy->policyName === $options['PolicyName']) {
                            $policy->cookieExpirationPeriod = $options['CookieExpirationPeriod'];
                            return $result;
                        }
                    }
                }
                if ($loadBalancer->policies === null) {
                    $loadBalancer->policies = new PoliciesData();
                }
                if ($loadBalancer->policies->lbCookieStickinessPolicies === null) {
                    $loadBalancer->policies->lbCookieStickinessPolicies = new LbCookieStickinessPolicyList();
                }
                $loadBalancer->policies->lbCookieStickinessPolicies->append(new LbCookieStickinessPolicyData($options['PolicyName'], $options['CookieExpirationPeriod']));
            }
        }
        return $result;
    }

    /**
     * DeleteLoadBalancerPolicy action
     *
     * Deletes a policy from the LoadBalancer.
     * The specified policy must not be enabled for any listeners.
     *
     * @param   string     $loadBalancerName The mnemonic name associated with the LoadBalancer.
     * @param   string     $policyName       The mnemonic name for the policy being deleted.
     * @return  boolean    Returns true if success or throws an exception if failure.
     * @throws  ClientException
     */
    public function deleteLoadBalancerPolicy($loadBalancerName, $policyName)
    {
        $result = false;
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName,
            'PolicyName'       => (string) $policyName
        );
        $response = $this->client->call('DeleteLoadBalancerPolicy', $options);
        if ($response->getError() === false) {
            $result = true;
            //Tries to look loadBalancer instance up in the repository.
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer != null) {
                if (!empty($loadBalancer->policies->otherPolicies) && ($k = array_search($options['PolicyName'], $loadBalancer->policies->otherPolicies)) !== false) {
                    unset($loadBalancer->policies->otherPolicies[$k]);
                    return $result;
                } else {
                    if ($loadBalancer->policies->appCookieStickinessPolicies !== null) {
                        foreach ($loadBalancer->policies->appCookieStickinessPolicies as $k => $policy) {
                            if ($policy->policyName === $options['PolicyName']) {
                                unset($loadBalancer->policies->appCookieStickinessPolicies[$k]);
                                return $result;
                            }
                        }
                    }
                    if ($loadBalancer->policies->lbCookieStickinessPolicies !== null) {
                        foreach ($loadBalancer->policies->lbCookieStickinessPolicies as $k => $policy) {
                            if ($policy->policyName === $options['PolicyName']) {
                                unset($loadBalancer->policies->lbCookieStickinessPolicies[$k]);
                                return $result;
                            }
                        }
                    }
                    if ($loadBalancer->listenerDescriptions !== null) {
                        foreach ($loadBalancer->listenerDescriptions as $k => $listenerDescription) {
                            if (!empty($listenerDescription->policyNames) && ($j = array_search($options['PolicyName'], $listenerDescription->policyNames)) !== false) {
                                unset($listenerDescription->policyNames[$j]);
                                return $result;
                            }
                        }
                    }
                    if ($loadBalancer->backendServerDescriptions !== null) {
                        /* @var $bsDescription BackendServerDescriptionData */
                        foreach ($loadBalancer->backendServerDescriptions as $k => $bsDescription) {
                            if (!empty($bsDescription->policyNames) && ($j = array_search($options['PolicyName'], $bsDescription->policyNames)) !== false) {
                                unset($bsDescription->policyNames[$j]);
                                return $result;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * SetLoadBalancerPoliciesOfListener action
     *
     * Associates, updates, or disables a policy with a listener on the LoadBalancer.
     * You can associate multiple policies with a listener.
     *
     * @param   string       $loadBalancerName The name associated with the LoadBalancer.
     * @param   int          $loadBalancerPort The external port of the LoadBalancer with
     *                                         which this policy applies to.
     * @param   ListDataType $PolicyNamesList  optional List of policies to be associated with the listener.
     *                                         Currently this list can have at most one policy. If the list is empty, the
     *                                         current policy is removed from the listener.
     * @return  array        Returns updated policies which are associated with the listener if success,
     *                       or throws an exception if failure.
     * @throws  ClientException
     */
    public function setLoadBalancerPoliciesOfListener($loadBalancerName, $loadBalancerPort, ListDataType $policyNamesList = null)
    {
        $result = array();
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName,
            'LoadBalancerPort' => (int) $loadBalancerPort
        );
        if ($policyNamesList !== null) {
            $options = array_merge($options, $policyNamesList->getQueryArray('PolicyNames'));
        } else {
            $options['PolicyNames'] = '';
            $policyNamesList = new ListDataType();
        }
        $response = $this->client->call('SetLoadBalancerPoliciesOfListener', $options);
        if ($response->getError() === false) {
            $result = array_values($policyNamesList->getQueryArray('PolicyNames'));
            //Tries to look loadBalancer instance up in the repository.
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null) {
                foreach ($loadBalancer->listenerDescriptions as $k => $listenerDescription) {
                    if ($listenerDescription->listener !== null && $listenerDescription->listener->loadBalancerPort == $options['LoadBalancerPort']) {
                        $loadBalancer->listenerDescriptions[$k]->policyNames = $result;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * SetLoadBalancerPoliciesForBackendServer action
     *
     * Replaces the current set of policies associated with a port on which
     * the back-end server is listening with a new set of policies.
     * After the policies have been created using CreateLoadBalancerPolicy, they
     * can be applied here as a list. At this time, only the back-end server authentication policy type can be
     * applied to the back-end ports; this policy type is composed of multiple public key policies
     *
     * @param   string       $loadBalancerName The mnemonic name associated with the LoadBalancer.
     * @param   int          $instancePort     The port number associated with the back-end server.
     * @param   ListDataType $policyNamesList  List of policy names to be set. If the list is empty, then all
     *                                         current polices are removed from the back-end server.
     * @return  array Returns updated list of policy names.
     * @throws  ClientException
     */
    public function setLoadBalancerPoliciesForBackendServer($loadBalancerName, $instancePort, ListDataType $policyNamesList = null)
    {
        $result = array();
        $options = array(
            'LoadBalancerName' => (string) $loadBalancerName,
            'InstancePort'     => (int) $instancePort
        );
        if ($policyNamesList !== null) {
            $options = array_merge($options, $policyNamesList->getQueryArray('PolicyNames'));
        } else {
            $policyNamesList = new ListDataType();
        }
        $response = $this->client->call('SetLoadBalancerPoliciesForBackendServer', $options);
        if ($response->getError() === false) {
            $result = array_values($policyNamesList->getQueryArray('PolicyNames'));
            //Tries to look loadBalancer instance up in the repository.
            $loadBalancer = $this->elb->loadBalancer->get($options['LoadBalancerName']);
            if ($loadBalancer !== null && $loadBalancer->backendServerDescriptions !== null) {
                /* @var $bsDescription BackendServerDescriptionData */
                foreach ($loadBalancer->backendServerDescriptions as $k => $bsDescription) {
                    if ($bsDescription->instancePort == $options['InstancePort']) {
                        $loadBalancer->backendServerDescriptions[$k]->policyNames = $result;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Gets an EntityManager
     *
     * @return \Scalr\Service\Aws\EntityManager
     */
    public function getEntityManager()
    {
        return $this->elb->getEntityManager();
    }
}