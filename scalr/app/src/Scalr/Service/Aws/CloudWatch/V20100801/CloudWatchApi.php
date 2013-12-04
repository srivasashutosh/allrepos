<?php
namespace Scalr\Service\Aws\CloudWatch\V20100801;

use Scalr\Service\Aws\AbstractApi;
use Scalr\Service\Aws\CloudWatch\DataType\DatapointData;
use Scalr\Service\Aws\CloudWatch\DataType\DatapointList;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionData;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionList;
use Scalr\Service\Aws\CloudWatch\DataType\MetricList;
use Scalr\Service\Aws\CloudWatch\DataType\MetricData;
use Scalr\Service\Aws\CloudWatchException;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionFilterList;
use Scalr\Service\Aws\CloudWatch;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\EntityManager;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Client\QueryClientResponse;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientInterface;

/**
 * CloudWatch Api messaging.
 *
 * Implements CloudWatch Low-Level API Actions.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 */
class CloudWatchApi extends AbstractApi
{

    /**
     * @var CloudWatch
     */
    protected $cloudWatch;

    /**
     * Constructor
     * @param   CloudWatch      $cloudWatch    CloudWatch instance
     * @param   ClientInterface $client        Client Interface
     */
    public function __construct(CloudWatch $cloudWatch, ClientInterface $client)
    {
        $this->cloudWatch = $cloudWatch;
        $this->client = $client;
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
     */
    public function listMetrics (DimensionFilterList $dimensionFilterList = null, $namespace = null,
                                  $metricName = null, $nextToken = null)
    {
        $result = null;
        if ($dimensionFilterList !== null) {
            $options = $dimensionFilterList->getQueryArray();
        } else {
            $options = array();
        }
        if ($namespace !== null && $namespace != '') {
            $options['Namespace'] = (string) $namespace;
        }
        if ($metricName !== null && $metricName != '') {
            $options['MetricName'] = (string) $metricName;
        }
        if ($nextToken !== null) {
            $options['NextToken'] = (string) $nextToken;
        }
        $response = $this->client->call('ListMetrics', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ListMetricsResult)) {
                throw new CloudWatchException('Unexpected response! ' . $response->getRawContent());
            }
            $result = new MetricList();
            $result->setCloudWatch($this->cloudWatch);
            $result->setNextToken(
                !empty($sxml->ListMetricsResult->NextToken) ?
                (string)$sxml->ListMetricsResult->NextToken : null
            );
            if (!empty($sxml->ListMetricsResult->Metrics->member)) {
                foreach ($sxml->ListMetricsResult->Metrics->member as $v) {
                    $metric = new MetricData(
                        (string) $v->MetricName,
                        (string) $v->Namespace,
                        new DimensionList()
                    );
                    if (!empty($v->Dimensions->member)) {
                        foreach ($v->Dimensions->member as $d) {
                            $dimension = new DimensionData((string) $d->Name, (string) $d->Value);
                            $metric->dimensions->append($dimension);
                            unset($dimension);
                        }
                    }
                    $result->append($metric);
                    unset($metric);
                }
            }
        }
        return $result;
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
     * @param   ListDataType  $statistics The metric statistics to return.
     *                                    Valid Values: Average | Sum | SampleCount | Maximum | Minimum
     * @param   string        $namespace  The namespace of the metric, with or without spaces.
     * @param   int           $period     optional The granularity, in seconds, of the returned datapoints.
     *                                    Period must be at least 60 seconds and must be a multiple of 60.
     *                                    The default value is 60.
     * @param   string        $unit       optional The unit for the metric.
     * @param   DimensionList $dimensions optional A list of dimensions describing qualities of the metric.
     * @return  DatapointList Returns the datapoints for the specified metric.
     * @throws  ClientException
     * @throws  CloudWatchException
     */
    public function getMetricStatistics($metricName, \DateTime $startTime, \DateTime $endTime,
                                        ListDataType $statistics, $namespace, $period = 60, $unit = null,
                                        DimensionList $dimensions = null)
    {
        $result = null;
        $options = array(
            'MetricName'   => (string) $metricName,
            'StartTime'    => $startTime->format('c'),
            'EndTime'      => $endTime->format('c'),
            'Period'       => (int) $period,
            'Namespace'    => (string) $namespace,
        );
        $options = array_merge($options, $statistics->getQueryArray('Statistics'));
        if ($dimensions != null) {
            $options = array_merge($options, $dimensions->getQueryArray());
        }
        if ($unit !== null) {
            $options['Unit'] = (string) $unit;
        }
        $response = $this->client->call('GetMetricStatistics', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->GetMetricStatisticsResult)) {
                throw new CloudWatchException('Unexpected response! ' . $response->getRawContent());
            }
            $ptr =& $sxml->GetMetricStatisticsResult;
            $result = new DatapointList();
            $result->setCloudWatch($this->cloudWatch);
            $result->setMetricName(!empty($ptr->Label) ? (string) $ptr->Label : null);
            if (!empty($ptr->Datapoints->member)) {
                foreach ($ptr->Datapoints->member as $v) {
                    $datapoint = new DatapointData();
                    $datapoint->average = (double) $v->Average;
                    $datapoint->maximum = (double) $v->Maximum;
                    $datapoint->minimum = (double) $v->Minimum;
                    $datapoint->sampleCount = (double) $v->SampleCount;
                    $datapoint->sum = (double) $v->Sum;
                    $datapoint->timestamp = new \DateTime((string)$v->Timestamp);
                    $datapoint->unit = (string) $v->Unit;
                    $result->append($datapoint);
                    unset($datapoint);
                }
            }
            unset($ptr);
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
        return $this->cloudWatch->getEntityManager();
    }
}