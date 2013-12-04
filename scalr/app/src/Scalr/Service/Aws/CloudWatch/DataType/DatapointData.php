<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchDataType;

/**
 * DatapointData
 *
 * The Datapoint data type encapsulates the statistical data that Amazon CloudWatch computes from
 * metric data.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 */
class DatapointData extends AbstractCloudWatchDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('metricName');

    /**
     * The average of metric values that correspond to the datapoint.
     *
     * @var float
     */
    public $average;

    /**
     * The maximum of the metric value used for the datapoint.
     *
     * @var float
     */
    public $maximum;

    /**
     * The minimum metric value used for the datapoint.
     *
     * @var float
     */
    public $minimum;

    /**
     * The number of metric values that contributed to the aggregate value of this datapoint.
     *
     * @var float
     */
    public $sampleCount;

    /**
     * The sum of metric values used for the datapoint.
     *
     * @var float
     */
    public $sum;

    /**
     * The time stamp used for the datapoint.
     *
     * @var \DateTime
     */
    public $timestamp;

    /**
     * The standard unit used for the datapoint.
     *
     * Valid Values: Seconds | Microseconds | Milliseconds | Bytes |
     * Kilobytes | Megabytes | Gigabytes | Terabytes | Bits | Kilobits |
     * Megabits | Gigabits | Terabits | Percent | Count | Bytes/Second |
     * Kilobytes/Second | Megabytes/Second | Gigabytes/Second |
     * Terabytes/Second | Bits/Second | Kilobits/Second | Megabits/Second |
     * Gigabits/Second | Terabits/Second | Count/Second | None
     *
     * @var string
     */
    public $unit;
}