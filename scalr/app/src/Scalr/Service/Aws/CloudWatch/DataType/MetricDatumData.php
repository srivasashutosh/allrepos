<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchDataType;

/**
 * MetricDatumData
 *
 * The MetricDatum data type encapsulates the information sent with PutMetricData to either create
 * a new metric or add new values to be aggregated into an existing metric.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.10.2012
 * @property  DimensionList             $dimensions           A list of dimensions associated with the metric.
 *                                                            Length constraints: Minimum of 0 item(s) in the list.
 *                                                            Maximum of 10 item(s) in the list.
 * @property  StatisticSetData          $statisticValues      A set of statistical values describing the metric.
 */
class MetricDatumData extends AbstractCloudWatchDataType
{
    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('dimensions', 'statisticValues');

    /**
     * The name of the metric.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $metricName;

    /**
     * The time stamp used for the metric. If not specified, the default value is set to
     * the time the metric data was received.
     *
     * @var \DateTime
     */
    public $timestamp;

}