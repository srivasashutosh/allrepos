<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\Handler\LoadBalancerHandler;
use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * HealthCheckData
 *
 * The HealthCheck data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class HealthCheckData extends AbstractElbDataType
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
     * Specifies the number of consecutive health probe successes required
     * before moving the instance to the Healthy state.
     *
     * @var int
     */
    public $healthyThreshold;

    /**
     * Specifies the approximate interval, in seconds, between health checks of
     * an individual instance.
     *
     * @var int
     */
    public $interval;

    /**
     * Specifies the instance being checked. The protocol is either TCP, HTTP,
     * HTTPS, or SSL. The range of valid ports is one (1) through 65535.
     *
     * TCP is the default, specified as a TCP: port pair, for example
     * "TCP:5000". In this case a healthcheck simply attempts to open a
     * TCP connection to the instance on the specified port. Failure to
     * connect within the configured timeout is considered unhealthy.
     * SSL is also specified as SSL: port pair, for example, SSL:5000.
     * For HTTP or HTTPS protocol, the situation is different.You have
     * to include a ping path in the string. HTTP is specified as a
     * HTTP:port;/;PathToPing; grouping, for example
     * "HTTP:80/weather/us/wa/seattle". In this case, a HTTP GET
     * request is issued to the instance on the given port and path. Any
     * answer other than "200 OK" within the timeout period is considered
     * unhealthy.
     * The total length of the HTTP ping target needs to be 1024 16-bit
     * Unicode characters or less.
     *
     * @var string
     */
    public $target;

    /**
     * Specifies the amount of time, in seconds, during which no response means
     * a failed health probe.
     *
     * Note. This value must be less than the Interval value
     *
     * @var int
     */
    public $timeout;

    /**
     * Specifies the number of consecutive health probe failures required before
     * moving the instance to the Unhealthy state.
     *
     * @var int
     */
    public $unhealthyThreshold;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Elb.AbstractElbDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        if ($this->timeout === null || $this->healthyThreshold === null || $this->interval === null ||
            $this->target === null || $this->unhealthyThreshold === null) {
            throw new ElbException(get_class($this) . ' has not been initialized with properties values yet.');
        }
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
    public function configure(HealthCheckData $healthCheck = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($healthCheck === null) {
            $healthCheck = $this;
        }
        return $this->getElb()->loadBalancer->configureHealthCheck($this->getLoadBalancerName(), $healthCheck);
    }
}