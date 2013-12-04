<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\Handler\LoadBalancerHandler;
use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * AppCookieStickinessPolicyData
 *
 * The AppCookieStickinessPolicy data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    18.09.2012
 */
class AppCookieStickinessPolicyData extends AbstractElbDataType
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
     * The name of the application cookie used for stickiness.
     *
     * @var string
     */
    public $cookieName;

    /**
     * The mnemonic name for the policy being created. The name must be unique within a
     * set of policies for this LoadBalancer.
     *
     * @var string
     */
    public $policyName;

    /**
     * Constructor
     *
     * @param string $policyName The mnemonic name for the policy being created.
     * @param string $cookieName The name of the application cookie used for stickiness.
     */
    public function __construct($policyName = null, $cookieName = null)
    {
        $this->cookieName = $cookieName;
        $this->policyName = $policyName;
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
     * @param   string     $cookieName optional Name of the application cookie used for stickiness.
     *                                          If it does not provide it will use its property value.
     * @return  boolean    Returns true if success or throws an exception.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function create($cookieName = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($cookieName === null) {
            if ($this->cookieName === null) {
                throw new ElbException('cookieName must be provided!');
            }
            $cookieName = $this->cookieName;
        }
        return $this->getElb()->loadBalancer->createAppCookieStickinessPolicy($this->getLoadBalancerName(), $this->policyName, $cookieName);
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
