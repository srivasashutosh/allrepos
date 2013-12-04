<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbDataType;
use Scalr\Service\Aws\Elb\DataType\AppCookieStickinessPolicyList;
use Scalr\Service\Aws\Elb\DataType\LbCookieStickinessPolicyList;

/**
 * PoliciesData
 *
 * Contains the result of a successful invocation of DescribeLoadBalancers
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 * @property AppCookieStickinessPolicyList $appCookieStickinessPolicies A list of the AppCookieStickinessPolicy objects created
 *                                                                      with CreateAppCookieStickinessPolicy
 * @property LbCookieStickinessPolicyList  $lbCookieStickinessPolicies  A list of LBCookieStickinessPolicy objects created with
 *                                                                      CreateAppCookieStickinessPolicy
 */
class PoliciesData extends AbstractElbDataType
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
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var array
     */
    protected $_properties = array(
        'appCookieStickinessPolicies',
        'lbCookieStickinessPolicies'
    );

    /**
     * A list of policy names other than the stickiness policies.
     *
     * @var array
     */
    public $otherPolicies;
}