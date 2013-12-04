<?php
namespace Scalr\Service\Aws;

use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Elb\Handler\LoadBalancerHandler;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionList;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\ListenerList;
use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\QueryClient;
use Scalr\Service\Aws;
use Scalr\Service\Aws\Elb\V20120601\ElbApi;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;

/**
 * Amazon web servise Elastic load balancer service interface
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     21.09.2012
 *
 * @property  \Scalr\Service\Aws\Elb\Handler\LoadBalancerHandler $loadBalancer
 *            A LoadBalancer handler that is the layer for the related api calls.
 *
 * @method    \Scalr\Service\Aws\Elb\V20120601\ElbApi getApiHandler()
 *            getApiHandler()
 *            Gets the API low-level handler
 */
class Elb extends AbstractService implements ServiceInterface
{

    /**
     * AWS ELB API Version 20120601
     */
    const API_VERSION_20120601 = '20120601';

    /**
     * Current version of the API
     */
    const API_VERSION_CURRENT = self::API_VERSION_20120601;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractService::getCurrentApiVersion()
     */
    public function getCurrentApiVersion()
    {
        return self::API_VERSION_CURRENT;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractService::getAvailableApiVersions()
     */
    public function getAvailableApiVersions()
    {
        return array(
            self::API_VERSION_20120601
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractService::getUrl()
     */
    public function getUrl()
    {
        return 'elasticloadbalancing.' . $this->getAws()->getRegion() . '.amazonaws.com';
    }

    /**
     * DescribeLoadBalancers action
     *
     * Returns detailed configuration information for the specified LoadBalancers.
     * If no LoadBalancers are specified, the operation returns configuration information for all
     * LoadBalancers created by the caller.
     *
     * @param  $loadBalancerNamesList      array|string|LoadBalancerDescriptionData|LoadBalancerDescriptionList optional
     *         A list of names associated with the LoadBalancers at creation time.
     *
     * @param  $marker                     string optional
     *         An optional parameter reserved for future use.
     *
     * @return LoadBalancerDescriptionList Returns list of detailed configuration for the specified LoadBalancers
     * @throws ClientException
     */
    public function describeLoadBalancers($loadBalancerNamesList = null, $marker = null)
    {
        if ($loadBalancerNamesList !== null && !($loadBalancerNamesList instanceof ListDataType)) {
            $list = new ListDataType($loadBalancerNamesList, 'loadBalancerName');
        }
        $ret = $this->getApiHandler()->describeLoadBalancers(isset($list) ? $list : null, $marker);
        return $ret;
    }

    /**
     * CreateLoadBalancer action
     *
     * @param  string $loadBalancerName
     *         Load Balancer Name
     *
     * @param  array|ListenerData|ListenerDescriptionData|ListenerList $listenersList
     *         A list of the Listeners
     *
     * @param  array|string|ListDataType $availabilityZonesList optional
     *         A list of Availability Zones
     *
     * @param  array|string|ListDataType $subnetsList optional
     *         A list of subnet IDs in your VPC to attach to your LoadBalancer.
     *
     * @param  array|string|ListDataType $securityGroupsList optional
     *         The security groups assigned to your LoadBalancer within your VPC.
     *
     * @param  string $scheme optional
     *         The type of LoadBalancer
     *
     * @return string Returns the DNS name of the created load balancer
     * @throws ElbException
     * @throws ClientException
     */
    public function createLoadBalancer($loadBalancerName, $listenersList, $availabilityZonesList = null,
                                       $subnetsList = null, $securityGroupsList = null, $scheme = null)
    {
        if (!is_string($loadBalancerName)) {
            throw new \InvalidArgumentException('Invalid loadBalancerName argument. It must be string.');
        }
        if (!($listenersList instanceof ListenerList)) {
            $listenersList = new ListenerList($listenersList);
        }
        if ($scheme !== null && !is_string($scheme)) {
            throw new \InvalidArgumentException('Invalid scheme argument. It must be string.');
        }
        if ($availabilityZonesList !== null && !($availabilityZonesList instanceof ListDataType)) {
            $availabilityZonesList = new ListDataType($availabilityZonesList);
        }
        if ($securityGroupsList !== null && !($securityGroupsList instanceof ListDataType)) {
            $securityGroupsList = new ListDataType($securityGroupsList);
        }
        if ($subnetsList !== null && !($subnetsList instanceof ListDataType)) {
            $subnetsList = new ListDataType($subnetsList);
        }
        return $this->getApiHandler()->createLoadBalancer(
            $loadBalancerName, $listenersList, $availabilityZonesList,
            $subnetsList, $securityGroupsList, $scheme
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractService::getAllowedEntities()
     */
    public function getAllowedEntities()
    {
        return array(
            'loadBalancer'
        );
    }
}
