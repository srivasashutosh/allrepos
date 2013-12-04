<?php
namespace Scalr\Service\Aws\CloudWatch\Handler;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionData;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionList;
use Scalr\Service\Aws\CloudWatchException;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\CloudWatch\DataType\MetricList;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionFilterData;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionFilterList;
use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchHandler;

/**
 * MetricHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.10.2012
 * @method    \Scalr\Service\Aws\CloudWatch\DataType\MetricList list() list(array|DimensionFilterData|DimensionFilterList $dimensionFilterList = null, $namespace = null, $metricName = null, $nextToken = null)   ListMetrics action
 */
class MetricHandler extends AbstractCloudWatchHandler
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractServiceRelatedType::__call()
     */
    public function __call($name, $arguments)
    {
        if ($name == 'list') {
            return call_user_func_array(array($this, '_list'), $arguments);
        } else {
            return parent::__call($name, $arguments);
        }
    }

    /**
     * ListMetrics action
     *
     * Returns a list of valid metrics stored for the AWS account owner.
     * Returned metrics can be used with GetMetricStatistics
     * to obtain statistical data for a given metric.
     *
     * @param   array|DimensionFilterData|DimensionFilterList $dimensionFilterList optional A list of dimensions to filter against.
     *                                      Minimum of 0 item(s) in the list.
     *                                      Maximum of 10 item(s) in the list.
     * @param   string $namespace           optional The namespace to filter against.
     * @param   string $metricName          optional The name of the metric to filter against.
     * @param   string $nextToken           optional The token returned by a previous call
     *                                      to indicate that there is more data available.
     * @return  MetricList Returns A list of metrics used to generate statistics for an AWS account.
     * @throws  ClientException
     * @throws  CloudWatchException
     */
    private function _list ($dimensionFilterList = null, $namespace = null, $metricName = null, $nextToken = null)
    {

        if ($dimensionFilterList !== null && !($dimensionFilterList instanceof DimensionFilterList)) {
            $dimensionFilterList = new DimensionFilterList($dimensionFilterList);
        }
        return $this->getCloudWatch()->getApiHandler()->listMetrics(
            $dimensionFilterList, $namespace, $metricName, $nextToken
        );
    }

    /**
     * Gets statistics for the specified metric.
     *
     * Note!
     * The maximum number of data points returned from a single GetMetricStatistics request
     * is 1,440. If a request is made that generates more than 1,440 data points, Amazon CloudWatch
     * returns an error. In such a case, alter the request by narrowing the specified time range or
     * increasing the specified period. Alternatively, make multiple requests across adjacent time ranges.
     *
     * Amazon CloudWatch aggregates data points based on the length of the period that you specify. For
     * example, if you request statistics with a one-minute granularity, Amazon CloudWatch aggregates data
     * points with time stamps that fall within the same one-minute period. In such a case, the data points queried
     * can greatly outnumber the data points returned.
     *
     * Note!
     * The maximum number of data points that can be queried is 50,850; whereas the maximum
     * number of data points returned is 1,440.
     *
     * The following examples show various statistics allowed by the data point query maximum of 50,850 when
     * you call GetMetricStatistics on Amazon EC2 instances with detailed (one-minute) monitoring
     * enabled:
     *  -  Statistics for up to 400 instances for a span of one hour
     *  -  Statistics for up to 35 instances over a span of 24 hours
     *  -  Statistics for up to 2 instances over a span of 2 weeks
     *
     * @param   string        $metricName The name of the metric, with or without spaces.
     * @param   \DateTime     $startTime  The time stamp to use for determining the first datapoint to return.
     *                                    The value specified is inclusive; results include datapoints with the
     *                                    time stamp specified.
     * @param   \DateTime     $endTime    The time stamp to use for determining the last datapoint to return.
     *                                    The value specified is exclusive; results will include datapoints up
     *                                    to the time stamp specified.
     * @param   string|array|ListDataType $statistics The metric statistics to return.
     *                                    Valid Values: Average | Sum | SampleCount | Maximum | Minimum
     * @param   string        $namespace  The namespace of the metric, with or without spaces.
     * @param   int           $period     optional The granularity, in seconds, of the returned datapoints.
     *                                    Period must be at least 60 seconds and must be a multiple of 60.
     *                                    The default value is 60.
     * @param   string        $unit       optional The unit for the metric.
     * @param   array|DimensionData|DimensionList $dimensions optional A list of dimensions describing qualities of the metric.
     * @return  DatapointList Returns the datapoints for the specified metric.
     * @throws  ClientException
     * @throws  CloudWatchException
     */
    public function getStatistics($metricName, \DateTime $startTime, \DateTime $endTime,
                                  $statistics, $namespace, $period = 60, $unit = null, $dimensions = null)
    {
        if (!($statistics instanceof ListDataType)) {
            $statistics = new ListDataType($statistics);
        }
        if ($dimensions !== null && !($dimensions instanceof DimensionList)) {
            $dimensions = new DimensionList($dimensions);
        }
        return $this->getCloudWatch()->getApiHandler()->getMetricStatistics(
            $metricName, $startTime, $endTime, $statistics, $namespace, $period, $unit, $dimensions
        );
    }
}