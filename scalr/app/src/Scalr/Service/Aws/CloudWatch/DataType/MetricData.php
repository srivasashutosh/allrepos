<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchDataType;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionList;

/**
 * MetricData
 *
 * The Metric data type contains information about a specific metric. If you call ListMetrics,
 * Amazon CloudWatch returns information contained by this data type.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 * @property  DimensionList             $dimensions           A list of dimensions associated with the metric.
 *                                                            Length constraints: Minimum of 0 item(s) in the list.
 *                                                            Maximum of 10 item(s) in the list.
 */
class MetricData extends AbstractCloudWatchDataType
{
    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('dimensions');

    /**
     * The name of the metric.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $metricName;

    /**
     * The namespace of the metric.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $namespace;

    /**
     * Convenient constructor
     *
     * @param   string        $metricName optional The name of the metric.
     * @param   string        $namespace  optional The namespace of the metric.
     * @param   DimensionList $dimensions optional A list of dimensions associated with the metric.
     */
    public function __construct($metricName = null, $namespace = null, DimensionList $dimensions = null)
    {
        $this->metricName = $metricName;
        $this->namespace = $namespace;
        $this->setDimensions($dimensions);
    }
}