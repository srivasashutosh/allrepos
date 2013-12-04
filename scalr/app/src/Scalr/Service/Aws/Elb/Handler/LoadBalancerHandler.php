<?php
namespace Scalr\Service\Aws\Elb\Handler;

use Scalr\Service\Aws\Elb\DataType\BackendServerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionList;
use Scalr\Service\Aws\Elb\DataType\HealthCheckData;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Elb\DataType\InstanceStateList;
use Scalr\Service\Aws\Elb\DataType\InstanceData;
use Scalr\Service\Aws\Elb\DataType\InstanceList;
use Scalr\Service\Aws\Elb\DataType\ListenerList;
use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\AbstractElbHandler;

/**
 * LoadBalancerHandler
 *
 * LoadBalancer API Layer to handle actions which are related to LoadBalancer object.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     05.10.2012
 */
class LoadBalancerHandler extends AbstractElbHandler
{

    /**
     * CreateLoadBalancer action
     *
     * @param  string $loadBalancerName
     *         Load Balancer Name to create
     *
     * @param  array|ListenerData|ListenerDescriptionData|ListenerList $listenersList
     *         The list of the Listeners
     *
     * @param  array|string|ListDataType $availabilityZonesList optional
     *         The list of Availability Zones
     *
     * @param  array|string|ListDataType $subnetsList optional
     *         The list of subnet IDs in your VPC to attach to your LoadBalancer.
     *
     * @param  array|string|ListDataType $securityGroupsList optional
     *         The security groups assigned to your
     *         LoadBalancer within your VPC.
     *
     * @param  string $scheme optional
     *         The type of LoadBalancer
     *
     * @return string Returns the DNS name of the created load balancer.
     * @throws ElbException
     * @throws ClientException
     */
    public function create($loadBalancerName, $listenersList, $availabilityZonesList = null, $subnetsList = null,
                           $securityGroupsList = null, $scheme = null)
    {
        return $this->getElb()->createLoadBalancer(
            $loadBalancerName, $listenersList, $availabilityZonesList,
            $scheme, $securityGroupsList, $subnetsList
        );
    }

    /**
     * DeleteLoadBalancer action
     *
     * @param    string    $loadBalancerName  The name associated with the LoadBalancer
     * @return   boolean   Returns TRUE if success.array
     * @throws   ClientException
     * @throws   ElbException
     */
    public function delete($loadBalancerName)
    {
        return $this->getElb()->getApiHandler()->deleteLoadBalancer($loadBalancerName);
    }

    /**
     * DescribeLoadBalancers action
     *
     * Returns detailed configuration information for the specified LoadBalancers. If no LoadBalancers are
     * specified, the operation returns configuration information for all LoadBalancers created by the caller.
     *
     * @param  array|string|LoadBalancerDescriptionData|LoadBalancerDescriptionList $loadBalancerNamesList optional A list of names associated
     *                                       with the LoadBalancers at creation time.
     * @param  string  $marker optional      An optional parameter reserved for future use.
     * @return LoadBalancerDescriptionList   Returns list of detailed configuration for the specified LoadBalancers.
     * @throws ClientException
     * @throws ElbException
     */
    public function describe($loadBalancerNamesList = null, $marker = null)
    {
        return $this->getElb()->describeLoadBalancers($loadBalancerNamesList, $marker);
    }

    /**
     * ApplySecurityGroupsToLoadBalancer action.
     *
     * Associates one or more security groups with your LoadBalancer in VPC. The provided security group
     * IDs will override any currently applied security groups.
     *
     * @param  string                    $loadBalancerName   The name associated with the LoadBalancer.
     * @param  array|string|ListDataType $securityGroupsList The security groups assigned to your
     *                                                       LoadBalancer within your VPC.
     * @return array                     Returns a Groups list array.
     * @throws ClientException
     * @throws ElbException
     */
    public function applySecurityGroups($loadBalancerName, $securityGroupsList)
    {
        if (!($securityGroupsList instanceof ListDataType)) {
            $securityGroupsList = new ListDataType($securityGroupsList);
        }
        return $this->getElb()->getApiHandler()->applySecurityGroupsToLoadBalancer((string) $loadBalancerName, $securityGroupsList);
    }

    /**
     * AttachLoadBalancerToSubnets action.
     *
     * Adds one or more subnets to the set of configured subnets in the VPC for the LoadBalancer
     *
     * @param    string                    $loadBalancerName   The name associated with the LoadBalancer.
     * @param    array|string|ListDataType $subnetsList        A list of subnet IDs to add for the LoadBalancer.
     * @return   array                     Returns a Subnets list array.
     * @throws   ClientException
     * @throws   ElbException
     */
    public function attachToSubnets($loadBalancerName, $subnetsList)
    {
        if (!($subnetsList instanceof ListDataType)) {
            $subnetsList = new ListDataType($subnetsList);
        }
        return $this->getElb()->getApiHandler()->attachLoadBalancerToSubnets((string) $loadBalancerName, $subnetsList);
    }

    /**
     * DetachLoadBalancerFromSubnets action.
     *
     * Removes subnets from the set of configured subnets in the VPC for the LoadBalancer
     *
     * @param    string                    $loadBalancerName   The name associated with the LoadBalancer.
     * @param    array|string|ListDataType $subnetsList        A list of subnet IDs to remove from the set of configured subnets
     *                                                         for the LoadBalancer.
     * @return   array                     Returns a Subnets list array.
     * @throws   ClientException
     * @throws   ElbException
     */
    public function detachFromSubnets($loadBalancerName, $subnetsList)
    {
        if (!($subnetsList instanceof ListDataType)) {
            $subnetsList = new ListDataType($subnetsList);
        }
        return $this->getElb()->getApiHandler()->detachLoadBalancerFromSubnets((string) $loadBalancerName, $subnetsList);
    }

    /**
     * CreateLoadBalancerListeners action
     *
     * Creates one or more listeners on a LoadBalancer for the specified port. If a listener with the given port
     * does not already exist, it will be created; otherwise, the properties of the new listener must match the
     * properties of the existing listener.
     *
     * @param    string           $loadBalancerName  A load balancer name.
     * @param    array|ListenerData|ListenerDescriptionData|ListenerList $listenersList A list of the Listeners or
     *                            ListenerList object that holds list of LoadBalancerPort,
     *                            InstancePort, Protocol, Yes and SSLCertificateId items.
     * @return   boolean          Returns TRUE if listeners are successfully created.
     * @throws   ClientException
     */
    public function createListeners($loadBalancerName, $listenersList)
    {
        if (!($listenersList instanceof ListenerList)) {
            $listenersList = new ListenerList($listenersList);
        }
        return $this->getElb()->getApiHandler()->createLoadBalancerListeners($loadBalancerName, $listenersList);
    }

    /**
     * DeleteLoadBalancerListeners action
     *
     * Deletes listeners from the LoadBalancer for the specified port.
     *
     * @param   string                 $loadBalancerName       A load balancer name.
     * @param   int|array|ListDataType $loadBalancerPortsList  The client port number(s) of the LoadBalancerListener(s)
     *                                                         to be removed.
     * @return  boolean                Returns TRUE if success
     * @throws  ClientException
     */
    public function deleteListeners($loadBalancerName, $loadBalancerPortsList)
    {
        if (!($loadBalancerPortsList instanceof ListDataType)) {
            $loadBalancerPortsList = new ListDataType($loadBalancerPortsList);
        }
        return $this->getElb()->getApiHandler()->deleteLoadBalancerListeners($loadBalancerName, $loadBalancerPortsList);
    }

    /**
     * EnableAvailabilityZonesForLoadBalancer action
     *
     * Adds one or more EC2 Availability Zones to the LoadBalancer.
     * The LoadBalancer evenly distributes requests across all its registered Availability Zones that contain
     * instances. As a result, the client must ensure that its LoadBalancer is appropriately scaled for each
     * registered Availability Zone.
     *
     * @param   string                    $loadBalancerName       The name associated with the LoadBalancer
     * @param   string|array|ListDataType $availabilityZonesList  A list of new Availability Zones for the LoadBalancer.
     *                                    Each Availability Zone must be in the same Region as the LoadBalancer.
     * @return  array                     Returns an updated list of Availability Zones for the LoadBalancer.
     * @throws  ClientException
     */
    public function enableAvailabilityZones($loadBalancerName, $availabilityZonesList)
    {
        if (!($availabilityZonesList instanceof ListDataType)) {
            $availabilityZonesList = new ListDataType($availabilityZonesList);
        }
        return $this->getElb()->getApiHandler()->enableAvailabilityZonesForLoadBalancer($loadBalancerName, $availabilityZonesList);
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
     * @param   string                    $loadBalancerName       The name associated with the LoadBalancer
     * @param   string|array|ListDataType $availabilityZonesList  A list of Availability Zones to be removed from the LoadBalancer.
     *                                    Each Availability Zone must be in the same Region as the LoadBalancer.
     * @return  array                     Returns an updated list of Availability Zones for the LoadBalancer.
     * @throws  ClientException
     */
    public function disableAvailabilityZones($loadBalancerName, $availabilityZonesList)
    {
        if (!($availabilityZonesList instanceof ListDataType)) {
            $availabilityZonesList = new ListDataType($availabilityZonesList);
        }
        return $this->getElb()->getApiHandler()->disableAvailabilityZonesForLoadBalancer($loadBalancerName, $availabilityZonesList);
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
     * @param   string                                 $loadBalancerName The name associated with the LoadBalancer.
     * @param   string|array|InstanceData|InstanceList $instancesList    A list of instance IDs that should be registered with
     *                                                                   the LoadBalancer.
     * @return  InstanceList Returns an updated list of instances for the LoadBalancer.
     * @throws  ClientException
     */
    public function registerInstances($loadBalancerName, $instancesList)
    {
        if (!($instancesList instanceof InstanceList)) {
            $instancesList = new InstanceList($instancesList);
        }
        return $this->getElb()->getApiHandler()->registerInstancesWithLoadBalancer($loadBalancerName, $instancesList);
    }

    /**
     * DeregisterInstancesFromLoadBalancer action
     *
     * Deregisters instances from the LoadBalancer. Once the instance is deregistered, it will stop receiving
     * traffic from the LoadBalancer.
     * In order to successfully call this API, the same account credentials as those used to create the
     * LoadBalancer must be provided.
     *
     * @param   string                                 $loadBalancerName The name associated with the LoadBalancer.
     * @param   string|array|InstanceData|InstanceList $instancesList    A list of EC2 instance IDs consisting of all instances
     *                                                  to be deregistered.
     * @return  InstanceList Returns an updated list of remaining instances registered with the LoadBalancer.
     * @throws  ClientException
     */
    public function deregisterInstances($loadBalancerName, $instancesList)
    {
        if (!($instancesList instanceof InstanceList)) {
            $instancesList = new InstanceList($instancesList);
        }
        return $this->getElb()->getApiHandler()->deregisterInstancesFromLoadBalancer($loadBalancerName, $instancesList);
    }

    /**
     * DescribeInstanceHealth action
     *
     * Returns the current state of the instances of the specified LoadBalancer. If no instances are specified,
     * the state of all the instances for the LoadBalancer is returned.
     *
     * @param   string                                 $loadBalancerName The name associated with the LoadBalancer.
     * @param   string|array|InstanceData|InstanceList $instancesList    A list of instance IDs whose states are being queried.
     * @return  InstanceStateList Returns a list containing health information for the specified instances.
     * @throws  ClientException
     */
    public function describeInstanceHealth($loadBalancerName, $instancesList)
    {
        if (!($instancesList instanceof InstanceList)) {
            $instancesList = new InstanceList($instancesList);
        }
        return $this->getElb()->getApiHandler()->describeInstanceHealth($loadBalancerName, $instancesList);
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
        return $this->getElb()->getApiHandler()->configureHealthCheck($loadBalancerName, $healthCheck);
    }

    /**
     * SetLoadBalancerListenerSSLCertificate action
     *
     * Sets the certificate that terminates the specified listener's SSL connections.
     * The specified certificate replaces any prior certificate that was used on the
     * same LoadBalancer and port.
     *
     * @param   string                                   $loadBalancerName The name of the the LoadBalancer.
     * @param   int|ListenerData|ListenerDescriptionData $loadBalancerPort The port that uses the specified SSL certificate.
     *                                                                     or ListenerData object with set loadbalancerPort property,
     *                                                                     or listenerDescription object with set listener.
     * @param   string                                   $sslCertificateId The ID of the SSL certificate chain to use.
     * @return  boolean Returns boolean TRUE if success or throws exception
     * @throws  ClientException
     */
    public function setListenerSslCertificate($loadBalancerName, $loadBalancerPort, $sslCertificateId)
    {
        if ($loadBalancerPort instanceof ListenerDescriptionData) {
            $lbport = $loadBalancerPort->listener->loadBalancerPort;
        } else if ($loadBalancerPort instanceof ListenerData) {
            $lbport = $loadBalancerPort->loadBalancerPort;
        }
        return $this->getElb()->getApiHandler()->setLoadBalancerListenerSslCertificate(
            $loadBalancerName, isset($lbport) ? $lbport : (int) $loadBalancerPort, $sslCertificateId
        );
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
        return $this->getElb()->getApiHandler()->createAppCookieStickinessPolicy($loadBalancerName, $policyName, $cookieName);
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
        return $this->getElb()->getApiHandler()->createLbCookieStickinessPolicy(
            $loadBalancerName, $policyName, $cookieExpirationPeriod
        );
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
    public function deletePolicy($loadBalancerName, $policyName)
    {
        return $this->getElb()->getApiHandler()->deleteLoadBalancerPolicy($loadBalancerName, $policyName);
    }

    /**
     * SetLoadBalancerPoliciesOfListener action
     *
     * Associates, updates, or disables a policy with a listener on the LoadBalancer.
     * You can associate multiple policies with a listener.
     *
     * @param   string                                   $loadBalancerName The name associated with the LoadBalancer.
     * @param   int|ListenerDescriptionData|ListenerData $loadBalancerPort The external port of the LoadBalancer with
     *                                                                     which this policy applies to.
     * @param   string|array|ListDataType                $PolicyNamesList  optional List of policies to be associated with the listener.
     *                                                                     Currently this list can have at most one policy.
     *                                                                     If the list is empty, the current policy is removed from the listener.
     * @return  array        Returns updated policies which are associated with the listener if success,
     *                       or throws an exception if failure.
     * @throws  ClientException
     */
    public function setPoliciesOfListener($loadBalancerName, $loadBalancerPort, $policyNamesList = null)
    {
        if ($loadBalancerPort instanceof ListenerDescriptionData) {
            $lbport = $loadBalancerPort->listener->loadBalancerPort;
        } else if ($loadBalancerPort instanceof ListenerData) {
            $lbport = $loadBalancerPort->loadBalancerPort;
        }
        if ($policyNamesList !== null && !($policyNamesList instanceof ListDataType)) {
            $policyNamesList = new ListDataType($policyNamesList);
        }
        return $this->getElb()->getApiHandler()->setLoadBalancerPoliciesOfListener(
            $loadBalancerName, isset($lbport) ? $lbport : (int) $loadBalancerPort, $policyNamesList
        );
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
     * @param   string                           $loadBalancerName The mnemonic name associated with the LoadBalancer.
     * @param   int|BackendServerDescriptionData $instancePort     The port number associated with the back-end server.
     * @param   string|array|ListDataType        $policyNamesList  List of policy names to be set. If the list is empty, then all
     *                                                             current polices are removed from the back-end server.
     * @return  array                            Returns updated list of policy names.
     * @throws  ClientException
     */
    public function setPoliciesForBackendServer($loadBalancerName, $instancePort, $policyNamesList = null)
    {
        if ($instancePort instanceof BackendServerDescriptionData) {
            $iport = $instancePort->instancePort;
        }
        if ($policyNamesList !== null && !($policyNamesList instanceof ListDataType)) {
            $policyNamesList = new ListDataType($policyNamesList);
        }
        return $this->getElb()->getApiHandler()->setLoadBalancerPoliciesForBackendServer(
            $loadBalancerName, isset($iport) ? $iport : (int) $instancePort, $policyNamesList
        );
    }

    /**
     * Gets load balancer from storage.
     *
     * It supposes that load balancer has been previously created or described.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param    string    $loadBalancerName   A load balancer name
     * @return   LoadBalancerDescriptionData|null Returns LoadBalancerDescriptionData object if it has been created or described
     *                                            or NULL if it does not exist.
     * @throws  ClientException
     */
    public function get($loadBalancerName)
    {
        return $this->getElb()->getEntityManager()->getRepository('Elb:LoadBalancerDescription')->find($loadBalancerName);
    }
}