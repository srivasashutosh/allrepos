<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchDataType;

/**
 * MetricAlarmData
 *
 * The MetricAlarm data type represents an alarm.
 * You can use PutMetricAlarm to create or update an alarm.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 * @property  DimensionList             $dimensions           A list of dimensions associated with the metric.
 *                                                            Length constraints: Minimum of 0 item(s) in the list.
 *                                                            Maximum of 10 item(s) in the list.
 */
class MetricAlarmData extends AbstractCloudWatchDataType
{
    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('dimensions');

    /**
     * Indicates whether actions should be executed during
     * any changes to the alarm's state.
     *
     * @var bool
     */
    public $actionsEnabled;

    /**
     * The list of actions to execute when this alarm
     * transitions into an ALARM state from any other state.
     * Each action is specified as an Amazon Resource
     * Number (ARN). Currently the only actions supported
     * are publishing to an Amazon SNS topic and
     * triggering an Auto Scaling policy.
     *
     * Type: String list
     * Length constraints:
     * Minimum of 0 item(s) in the list.
     * Maximum of 5 item(s) in the list.
     *
     * @var array
     */
    public $alarmActions;

    /**
     * The Amazon Resource Name (ARN) of the alarm.
     *
     * Length constraints: Minimum length of 1. Maximum length of 1600.
     *
     * @var string
     */
    public $alarmArn;

    /**
     * The time stamp of the last update to the alarm configuration.
     *
     * @var \DateTime
     */
    public $alarmConfigurationUpdatedTimestamp;

    /**
     * The description for the alarm.
     *
     * Length constraints: Minimum length of 0. Maximum length of 255.
     *
     * @var string
     */
    public $alarmDescription;

    /**
     * The name of the alarm.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $alarmName;

    /**
     * The arithmetic operation to use when comparing the
     * specified Statistic and Threshold.The specified
     * Statistic value is used as the first operand.
     *
     * Valid Values: GreaterThanOrEqualToThreshold | GreaterThanThreshold |
     * LessThanThreshold | LessThanOrEqualToThreshold
     *
     * @var string
     */
    public $comparisonOperator;

    /**
     * The number of periods over which data is compared
     * to the specified threshold.
     *
     * @var int
     */
    public $evaluationPeriods;

    /**
     * The list of actions to execute when this alarm transitions into an INSUFFICIENT_DATA state from
     * any other state. Each action is specified as an Amazon Resource Number (ARN). Currently the
     * only actions supported are publishing to an Amazon SNS topic or triggering an Auto Scaling policy.
     * Important! The current WSDL lists this attribute as UnknownActions.
     *
     * Type: String list
     * Length constraints: Minimum of 0 item(s) in the list. Maximum of 5 item(s) in the list.
     *
     * @var array
     */
    public $insufficientDataActions;

    /**
     * The name of the alarm's metric.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $metricName;

    /**
     * The namespace of alarm's associated metric.
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $namespace;

    /**
     * The list of actions to execute when this alarm transitions into an OK state from any other state.
     * Each action is specified as an Amazon Resource Number (ARN). Currently the only actions supported
     * are publishing to an Amazon SNS topic and triggering an Auto Scaling policy.
     *
     * Type: String list
     * Length constraints: Minimum of 0 item(s) in the list. Maximum of 5 item(s) in the list.
     * UriParam: OKActions
     *
     * @var array
     */
    public $okActions;

    /**
     * The period in seconds over which the statistic is applied.
     *
     * @var int
     */
    public $period;

    /**
     * A human-readable explanation for the alarm's state.
     *
     * Length constraints: Minimum length of 0. Maximum length of 1023.
     *
     * @var string
     */
    public $stateReason;

    /**
     * An explanation for the alarm's state in machine-readable JSON format
     *
     * Length constraints: Minimum length of 0. Maximum length of 4000.
     *
     * @var string
     */
    public $stateReasonData;

    /**
     * The time stamp of the last update to the alarm's
     *
     * @var \DateTime
     */
    public $stateUpdatedTimestamp;

    /**
     * The state value for the alarm.
     *
     * Valid Values: OK | ALARM | INSUFFICIENT_DATA
     *
     * @var string
     */
    public $stateValue;

    /**
     * The statistic to apply to the alarm's associated metric.
     *
     * Valid Values: SampleCount | Average | Sum | Minimum | Maximum
     *
     * @var string
     */
    public $statistic;

    /**
     * The value against which the specified statistic is compared.
     *
     * @var float
     */
    public $threshold;

    /**
     * The unit of the alarm's associated metric.
     *
     * Valid Values: Seconds | Microseconds | Milliseconds | Bytes | Kilobytes |
     * Megabytes | Gigabytes | Terabytes | Bits | Kilobits | Megabits | Gigabits |
     * Terabits | Percent | Count | Bytes/Second | Kilobytes/Second |
     * Megabytes/Second | Gigabytes/Second | Terabytes/Second | Bits/Second |
     * Kilobits/Second | Megabits/Second | Gigabits/Second | Terabits/Second | Count/Second | None
     *
     * @var string
     */
    public $unit;

}