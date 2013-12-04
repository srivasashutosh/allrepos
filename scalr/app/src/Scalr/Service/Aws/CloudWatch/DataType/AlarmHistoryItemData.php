<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchDataType;

/**
 * AlarmHistoryItemData
 *
 * The AlarmHistoryItem data type contains descriptive information about the history of a specific alarm.
 * If you call DescribeAlarmHistory, Amazon CloudWatch returns this data type as part of the
 * DescribeAlarmHistoryResult data type.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 */
class AlarmHistoryItemData extends AbstractCloudWatchDataType
{

    /**
     * The descriptive name for the alarm.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $alarmName;

    /**
     * Machine-readable data about the alarm in JSON format.
     *
     * Length constraints: Minimum length of 1. Maximum length of 4095.
     *
     * @var string
     */
    public $historyData;

    /**
     * The type of alarm history item.
     *
     * Valid Values: ConfigurationUpdate | StateUpdate | Action
     *
     * @var string
     */
    public $historyItemType;

    /**
     * A human-readable summary of the alarm history.
     *
     * Length constraints: Minimum length of 1. Maximum length of 255.
     *
     * @var string
     */
    public $historySummary;

    /**
     * The time stamp for the alarm history item.
     *
     * @var \DateTime
     */
    public $timestamp;
}