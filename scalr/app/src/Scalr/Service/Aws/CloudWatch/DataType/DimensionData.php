<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchDataType;

/**
 * DimensionData
 *
 * The Dimension data type further expands on the identity of a metric using a Name, Value pair.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 * @method string        getMetricName() getMetricName()                   Gets a metric name which is associated with dimension.
 * @method DimensionData setMetricName() setMetricName(string $metricName) Sets the metric name which is associated with dimension.
 */
class DimensionData extends AbstractCloudWatchDataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('metricName');

    /**
     * The name of the dimension
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $name;

    /**
     * The value representing the dimension measurement
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $value;

    /**
     * Convenient constructor
     *
     * @param   string     $name  optional The name of the dimension
     * @param   string     $value optional The value representing the dimension measurement
     */
    public function __construct($name = null, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }
}