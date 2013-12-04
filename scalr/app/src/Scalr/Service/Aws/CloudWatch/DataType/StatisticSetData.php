<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchDataType;

/**
 * StatisticSetData
 *
 * The StatisticSet data type describes the StatisticValues component of MetricDatum,
 * and represents a set of statistics that describes a specific metric.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.10.2012
 */
class StatisticSetData extends AbstractCloudWatchDataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('metricName');

    /**
     * The maximum value of the sample set.
     *
     * @var float
     */
    public $maximum;

    /**
     * The minimum value of the sample set.
     *
     * @var float
     */
    public $minimum;

    /**
     * The number of samples used for the statistic set.
     *
     * @var float
     */
    public $sampleCount;

    /**
     * The sum of values for the sample set.
     *
     * @var float
     */
    public $sum;

}