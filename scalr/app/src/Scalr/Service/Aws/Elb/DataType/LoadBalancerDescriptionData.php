<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\Handler\LoadBalancerHandler;
use Scalr\Service\Aws\Elb;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Elb\AbstractElbDataType;
use Scalr\Service\Aws;
use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionList;
use Scalr\Service\Aws\Elb\DataType\InstanceList;
use Scalr\Service\Aws\Elb\DataType\BackendServerDescriptionList;
use Scalr\Service\Aws\Elb\DataType\HealthCheckData;
use Scalr\Service\Aws\Elb\DataType\PoliciesData;
use Scalr\Service\Aws\Elb\DataType\SourceSecurityGroupData;

/**
 * LoadBalancerDescriptionData
 *
 * Contains the result of a successful invocation of DescribeLoadBalancers
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 *
 * @property InstanceList                 $instances                 Provides a list of EC2 instance IDs for the LoadBalancer
 * @property ListenerDescriptionList      $listenerDescriptions      LoadBalancerPort, InstancePort, Protocol, InstanceProtocol, and
 *                                                                   PolicyNames are returned in a list of tuples in the
 *                                                                   ListenerDescriptions element
 * @property BackendServerDescriptionList $backendServerDescriptions Contains a list of back-end server descriptions.
 * @property HealthCheckData              $healthCheck               Specifies information regarding the various health probes
 *                                                                   conducted on the LoadBalancer.
 * @property PoliciesData                 $policies                  Provides a list of policies defined for the LoadBalancer
 * @property SourceSecurityGroupData      $sourceSecurityGroup       The security group that you can use as part of your inbound rules
 *                                                                   for your LoadBalancer's back-end Amazon EC2 application
 *                                                                   instances. To only allow traffic from LoadBalancers, add a security
 *                                                                   group rule to your back end instance that specifies this source
 *                                                                   security group as the inbound source.
 * @method   InstanceList                 getInstances()                    getInstances()
 * @method   LoadBalancerDescriptionData  setInstances()                    setInstances(InstanceList $instanceList)
 * @method   ListenerDescriptionList      getListenerDescriptions()         getListenerDescriptions()
 * @method   LoadBalancerDescriptionData  setListenerDescriptions()         setListenerDescriptions(ListenerDescriptionList $listenerDescriptions)
 * @method   BackendServerDescriptionList getBackendServerDescriptions()    getBackendServerDescriptions()
 * @method   LoadBalancerDescriptionData  setBackendServerDescriptions()    setBackendServerDescriptions(BackendServerDescriptionList $backendServerDescriptions)
 * @method   HealthCheckData              getHealthCheck()                  getHealthCheck()
 * @method   LoadBalancerDescriptionData  setHealthCheck()                  setHealthCheck(HealthCheckData $healthCheck)
 * @method   PoliciesData                 getPolicies()                     getPolicies()
 * @method   LoadBalancerDescriptionData  setPolicies()                     setPolicies(PoliciesData $policies)
 * @method   SourceSecurityGroupData      getSourceSecurityGroup()          getSourceSecurityGroup()
 * @method   LoadBalancerDescriptionData  setSourceSecurityGroup()          setSourceSecurityGroup(SourceSecurityGroupData $sourceSecurityGroup)
 */
class LoadBalancerDescriptionData extends AbstractElbDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var array
     */
    protected $_properties = array(
        'instances',
        'listenerDescriptions',
        'backendServerDescriptions',
        'healthCheck',
        'policies',
        'sourceSecurityGroup'
    );

    /**
     * Specifies a list of Availability Zones.
     *
     * @var array
     */
    public $availabilityZones;

    /**
     * Provides the name of the Amazon Route 53 hosted zone that is
     * associated with the LoadBalancer. For information on how to
     * associate your load balancer with a hosted zone, go to Using
     * Domain Names With Elastic Load Balancing in the
     * Elastic Load Balancing Developer Guide.
     *
     * @var string
     */
    public $canonicalHostedZoneName;

    /**
     * Provides the ID of the Amazon Route 53 hosted zone name that
     * is associated with the LoadBalancer.
     *
     * @var string
     */
    public $canonicalHostedZoneNameId;

    /**
     * Provides the date and time the LoadBalancer was created.
     *
     * @var \DateTime
     */
    public $createdTime;

    /**
     * Specifies the external DNS name associated with the
     * LoadBalancer.
     *
     * @var string
     */
    public $dnsName;

    /**
     * Specifies the name associated with the LoadBalancer
     *
     * @var string
     */
    public $loadBalancerName;

    /**
     * Specifies the type of LoadBalancer.
     * If the Scheme is internet-facing, the LoadBalancer has a
     * publicly resolvable DNS name that resolves to public IP addresses.
     * If the Scheme is internal, the LoadBalancer has a publicly
     * resolvable DNS name that resolves to private IP addresses.
     * This option is only available for LoadBalancers attached to an
     * Amazon VPC.
     *
     * @var string
     */
    public $scheme;

    /**
     * The security groups the LoadBalancer is a member of (VPC only).
     *
     * @var array
     */
    public $securityGroups;

    /**
     * Provides a list of VPC subnet IDs for the LoadBalancer.
     *
     * @var array
     */
    public $subnets;

    /**
     * Provides the ID of the VPC attached to the LoadBalancer.
     *
     * @var string
     */
    public $vpcId;

    /**
     * DeleteLoadBalancer action
     *
     * @return   boolean  Returns TRUE if success
     * @throws   ClientException
     * @throws   ElbException
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->getApiHandler()->deleteLoadBalancer($this->loadBalancerName);
    }

    /**
     * ApplySecurityGroupsToLoadBalancer action.
     *
     * Associates one or more security groups with your LoadBalancer in VPC. The provided security group
     * IDs will override any currently applied security groups.
     *
     * @param  array|string|ListDataType $securityGroupsList The security groups assigned to your
     *                                                       LoadBalancer within your VPC.
     * @return array Returns a SecurityGroups list array.
     * @throws ClientException
     * @throws ElbException
     */
    public function applySecurityGroups($securityGroupsList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->applySecurityGroups($this->loadBalancerName, $securityGroupsList);
    }

    /**
     * AttachLoadBalancerToSubnets action.
     *
     * Adds one or more subnets to the set of configured subnets in the VPC for the LoadBalancer
     *
     * @param    array|string|ListDataType $subnetsList        A list of subnet IDs to add for the LoadBalancer.
     * @return   array                     Returns a Subnets list array.
     * @throws   ClientException
     * @throws   ElbException
     */
    public function attachToSubnets($subnetsList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->attachToSubnets($this->loadBalancerName, $subnetsList);
    }

    /**
     * DetachLoadBalancerFromSubnets action.
     *
     * Removes subnets from the set of configured subnets in the VPC for the LoadBalancer
     *
     * @param    array|string|ListDataType $subnetsList        A list of subnet IDs to remove from the set of configured subnets
     *                                                         for the LoadBalancer.
     * @return   array                     Returns a Subnets list array.
     * @throws   ClientException
     * @throws   ElbException
     */
    public function detachFromSubnets($subnetsList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->detachFromSubnets($this->loadBalancerName, $subnetsList);
    }

    /**
     * CreateLoadBalancerListeners action
     *
     * Creates one or more listeners on a LoadBalancer for the specified port. If a listener with the given port
     * does not already exist, it will be created; otherwise, the properties of the new listener must match the
     * properties of the existing listener.
     *
     * @param    array|ListenerData|ListenerDescriptionData|ListenerList $listenersList optional A list of the Listeners or
     *                            ListenerList object that holds list of LoadBalancerPort,
     *                            InstancePort, Protocol, Yes and SSLCertificateId items.
     *                            If list isn't provided it will use loadBalancer's own listeners list object.
     * @return   boolean          Returns TRUE if listeners are successfully created.
     * @throws   ClientException
     * @throws   ElbException
     */
    public function createListeners($listenersList = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($listenersList === null) {
            if ($this->getListenerDescriptions() === null) {
                throw new ElbException('Empty ListenerDescriptions for the LoadBalancerObject.');
            } else {
                $listenersList = new ListenerList($this->getListenerDescriptions()->getComputed());
            }
        }
        return $this->getElb()->loadBalancer->createListeners($this->loadBalancerName, $listenersList);
    }

    /**
     * DeleteLoadBalancerListeners action
     *
     * Deletes listeners from the LoadBalancer for the specified port.
     *
     * @param   int|array|ListDataType $loadBalancerPortsList  The client port number(s) of the LoadBalancerListener(s)
     *                                                         to be removed.
     * @return  boolean                Returns TRUE if success
     * @throws  ClientException
     * @throws  ElbException
     */
    public function deleteListeners($loadBalancerPortsList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->deleteListeners($this->loadBalancerName, $loadBalancerPortsList);
    }

    /**
     * EnableAvailabilityZonesForLoadBalancer action
     *
     * Adds one or more EC2 Availability Zones to the LoadBalancer.
     * The LoadBalancer evenly distributes requests across all its registered Availability Zones that contain
     * instances. As a result, the client must ensure that its LoadBalancer is appropriately scaled for each
     * registered Availability Zone.
     *
     * @param   string|array|ListDataType $availabilityZonesList optional A list of new Availability Zones for the LoadBalancer.
     *                                    Each Availability Zone must be in the same Region as the LoadBalancer.
     *                                    If list isn't provided it will use loadBalancer's own availabilityZones object.
     * @return  array                     Returns an updated list of Availability Zones for the LoadBalancer.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function enableAvailabilityZones($availabilityZonesList = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($availabilityZonesList === null) {
            if (empty($this->availabilityZones)) {
                throw new ElbException('Empty AvailabilityZones for the LoadBalancerObject.');
            } else {
                $availabilityZonesList = new ListDataType($this->availabilityZones);
            }
        }
        return $this->getElb()->loadBalancer->enableAvailabilityZones($this->loadBalancerName, $availabilityZonesList);
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
     * @param   string|array|ListDataType $availabilityZonesList  A list of Availability Zones to be removed from the LoadBalancer.
     *                                    Each Availability Zone must be in the same Region as the LoadBalancer.
     * @return  array                     Returns an updated list of Availability Zones for the LoadBalancer.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function disableAvailabilityZones($availabilityZonesList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->disableAvailabilityZones($this->loadBalancerName, $availabilityZonesList);
    }

    /**
     * RegisterInstancesWithLoadBalancer action
     *
     * Adds new instances to the LoadBalancer.
     * Once the instance is registered, it starts receiving traffic and requests from the LoadBalancer. Any instance
     * that is not in any of the Availability Zones registered for the LoadBalancer will be moved to the OutOfService
     * state. It will move to the InService state when the Availability Zone is added to the LoadBalancer.
     *
     * IMPORTANT!
     * Completion of this API does not guarantee that operation has completed. Rather, it means that
     * the request has been registered and the changes will happen shortly.
     *
     * @param   string|array|InstanceData|InstanceList $instancesList    A list of instance IDs that should be registered with
     *                                                                   the LoadBalancer.
     * @return  InstanceList Returns an updated list of instances for the LoadBalancer.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function registerInstances($instancesList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->registerInstances($this->loadBalancerName, $instancesList);
    }

    /**
     * DeregisterInstancesFromLoadBalancer action
     *
     * Deregisters instances from the LoadBalancer. Once the instance is deregistered, it will stop receiving
     * traffic from the LoadBalancer.
     * In order to successfully call this API, the same account credentials as those used to create the
     * LoadBalancer must be provided.
     *
     * @param   string|array|InstanceData|InstanceList $instancesList  A list of EC2 instance IDs consisting of all instances
     *                                                                 to be deregistered.
     * @return  InstanceList Returns an updated list of remaining instances registered with the LoadBalancer.
     */
    public function deregisterInstances($instancesList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->deregisterInstances($this->loadBalancerName, $instancesList);
    }

    /**
     * DescribeLoadBalancers action
     *
     * Returns detailed configuration information for the specified LoadBalancers. If no LoadBalancers are
     * specified, the operation returns configuration information for all LoadBalancers created by the caller.
     *
     * @param  string  $marker optional      An optional parameter reserved for future use.
     * @return LoadBalancerDescriptionList   Returns list of detailed configuration for the loadBalancer.
     * @throws QueryClientException
     * @throws ElbException
     */
    public function describe($marker = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->describeLoadBalancers($this->getLoadBalancerName(), $marker);
    }

    /**
     * DescribeInstanceHealth action
     *
     * Returns the current state of the instances of the specified LoadBalancer. If no instances are specified,
     * the state of all the instances for the LoadBalancer is returned.
     *
     * @param   string|array|InstanceData|InstanceList $instancesList optional A list of instance IDs whose states are being queried.
     *                                                                If list isn't provided it will use loadBalancer's own InstanceList.
     * @return  InstanceStateList Returns a list containing health information for the specified instances.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function describeInstanceHealth($instancesList = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($instancesList == null) {
            $instancesList = $this->getInstances();
            if ($instancesList === null || count($instancesList) == 0) {
                throw new ElbException('Empty instances list.');
            }
        }
        return $this->getElb()->loadBalancer->describeInstanceHealth($this->loadBalancerName, $instancesList);
    }

    /**
     * ConfigureHealthCheck action
     *
     * Enables the client to define an application healthcheck for the instances.
     *
     * @param   HealthCheckData $healthCheck optional A structure containing the configuration information
     *                                       for the new healthcheck. If healthCheck isn't provided it will use
     *                                       loadBalancer's own healthCheck object.
     * @return  HealthCheckData The updated healthcheck for the instances.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function configureHealthCheck(HealthCheckData $healthCheck = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($healthCheck === null) {
            if ($this->getHealthCheck() === null) {
                throw new ElbException('Empty HealthCheckData for the LoadBalancerObject.');
            } else {
                $healthCheck = $this->getHealthCheck();
            }
        }
        return $this->getElb()->loadBalancer->configureHealthCheck($this->loadBalancerName, $healthCheck);
    }

    /**
     * SetLoadBalancerListenerSSLCertificate action
     *
     * Sets the certificate that terminates the specified listener's SSL connections.
     * The specified certificate replaces any prior certificate that was used on the
     * same LoadBalancer and port.
     *
     * @param   int|ListenerData|ListenerDescriptionData $loadBalancerPort The port that uses the specified SSL certificate.
     *                                                                     or ListenerData object with set loadbalancerPort property,
     *                                                                     or listenerDescription object with set listener.
     * @param   string                                   $sslCertificateId The ID of the SSL certificate chain to use.
     * @return  boolean Returns boolean TRUE if success or throws exception
     * @throws  ClientException
     * @throws  ElbException
     */
    public function setListenerSslCertificate($loadBalancerPort, $sslCertificateId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->setListenerSslCertificate($this->loadBalancerName, $loadBalancerPort, $sslCertificateId);
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
     * @param   string     $policyName        The name of the policy being created.
     * @param   string     $cookieName        Name of the application cookie used for stickiness.
     * @return  boolean    Returns true if success or throws an exception.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function createAppCookieStickinessPolicy($policyName, $cookieName)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->createAppCookieStickinessPolicy($this->loadBalancerName, $policyName, $cookieName);
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
     * @param   string     $policyName             The name of the policy being created.
     * @param   numeric    $cookieExpirationPeriod The time period in seconds after which the cookie should
     *                                             be considered stale. Not specifying this parameter
     *                                             indicates that the sticky session will last for the duration
     *                                             of the browser session.
     * @return  boolean    Returns true if success or throws an exception.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function createLbCookieStickinessPolicy($policyName, $cookieExpirationPeriod)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->createLbCookieStickinessPolicy(
            $this->loadBalancerName, $policyName, $cookieExpirationPeriod
        );
    }

    /**
     * DeleteLoadBalancerPolicy action
     *
     * Deletes a policy from the LoadBalancer.
     * The specified policy must not be enabled for any listeners.
     *
     * @param   string     $policyName       The mnemonic name for the policy being deleted.
     * @return  boolean    Returns true if success or throws an exception if failure.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function deletePolicy($policyName)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->deletePolicy($this->loadBalancerName, $policyName);
    }

    /**
     * SetLoadBalancerPoliciesOfListener action
     *
     * Associates, updates, or disables a policy with a listener on the LoadBalancer.
     * You can associate multiple policies with a listener.
     *
     * @param   int                       $loadBalancerPort The external port of the LoadBalancer with
     *                                                      which this policy applies to.
     * @param   string|array|ListDataType $PolicyNamesList  optional List of policies to be associated with the listener.
     *                                                      Currently this list can have at most one policy.
     *                                                      If the list is empty, the current policy is removed from the listener.
     * @return  array        Returns updated policies which are associated with the listener if success,
     *                       or throws an exception if failure.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function setPoliciesOfListener($loadBalancerPort, $policyNamesList = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->setPoliciesOfListener($this->loadBalancerName, $loadBalancerPort, $policyNamesList);
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
     * @param   int|BackendServerDescriptionData $instancePort     The port number associated with the back-end server.
     * @param   ListDataType                     $policyNamesList  List of policy names to be set. If the list is empty, then all
     *                                                             current polices are removed from the back-end server.
     * @return  array                            Returns updated list of policy names.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function setPoliciesForBackendServer($instancePort, ListDataType $policyNamesList = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->setPoliciesForBackendServer($this->loadBalancerName, $instancePort, $policyNamesList);
    }
}