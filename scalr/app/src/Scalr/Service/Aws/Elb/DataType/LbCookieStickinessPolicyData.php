<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * LbCookieStickinessPolicyData
 *
 * The LBCookieStickinessPolicy data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class LbCookieStickinessPolicyData extends AbstractElbDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName'
    );

    /**
     * The time period in seconds after which the cookie should be
     * considered stale. Not specifying this parameter indicates that the
     * stickiness session will last for the duration of the browser session.
     *
     * @var numeric
     */
    public $cookieExpirationPeriod;

    /**
     * The name for the policy being created. The name must be unique
     * within the set of policies for this LoadBalancer.
     *
     * @var string
     */
    public $policyName;

    /**
     * Constructor
     *
     * @param   string     $policyName             optional The name for the policy being created.
     * @param   numeric    $cookieExpirationPeriod optional The time period in seconds after which the cookie should be
     *                                                      considered stale.
     */
    public function __construct($policyName = null, $cookieExpirationPeriod = null)
    {
        $this->policyName = $policyName;
        $this->cookieExpirationPeriod = $cookieExpirationPeriod;
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
     * @param   numeric    $cookieExpirationPeriod The time period in seconds after which the cookie should
     *                                             be considered stale. Not specifying this parameter
     *                                             indicates that the sticky session will last for the duration
     *                                             of the browser session.
     * @return  boolean    Returns true if success or throws an exception.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function create($cookieExpirationPeriod = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($cookieExpirationPeriod === null) {
            if ($this->cookieExpirationPeriod === null) {
                throw new ElbException('cookieName must be provided!');
            }
            $cookieExpirationPeriod = $this->cookieExpirationPeriod;
        }
        return $this->getElb()->loadBalancer->createLbCookieStickinessPolicy($this->getLoadBalancerName(), $this->policyName, $cookieExpirationPeriod);
    }

    /**
     * DeleteLoadBalancerPolicy action
     *
     * Deletes a policy from the LoadBalancer.
     * The specified policy must not be enabled for any listeners.
     *
     * @return  boolean    Returns true if success or throws an exception if failure.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->deletePolicy($this->getLoadBalancerName(), $this->policyName);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Elb.AbstractElbDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->policyName === null) {
            throw new ElbException('policyName has not been set for the ' . get_class($this));
        }
    }
}