<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchDataType;

/**
 * DimensionFilterData
 *
 * The DimensionFilter data type is used to filter ListMetrics results.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 */
class DimensionFilterData extends AbstractCloudWatchDataType
{
    /**
     * The dimension name to be matched.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $name;

    /**
     * The value of the dimension to be matched.
     *
     * Note! Specifying a Name without specifying a Value
     * returns all values associated with that Name.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $value;

    /**
     * Convenient constructor
     *
     * @param   string     $name  optional The dimension name to be matched.
     * @param   string     $value optional The value of the dimension to be matched.
     */
    public function __construct($name = null, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }
}