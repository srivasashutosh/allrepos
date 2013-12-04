<?php
namespace Scalr\Service\Aws;

use Scalr\Service\Aws\CloudWatch\Handler\MetricHandler;
use Scalr\Service\Aws\CloudWatch\V20100801\CloudWatchApi;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\QueryClient;
use Scalr\Service\Aws;

/**
 * Amazon CloudWatch web service interface
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 * @property-read  \Scalr\Service\Aws\CloudWatch\Handler\MetricHandler $metric A Metric handler that is the layer for the related api calls.
 * @method \Scalr\Service\Aws\CloudWatch\V20100801\CloudWatchApi getApiHandler() getApiHandler()
 */
class CloudWatch extends AbstractService implements ServiceInterface
{

    /**
     * API Version 20100801
     */
    const API_VERSION_20100801 = '20100801';

    /**
     * Current version of the API
     */
    const API_VERSION_CURRENT = self::API_VERSION_20100801;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractService::getCurrentApiVersion()
     */
    public function getCurrentApiVersion()
    {
        return self::API_VERSION_CURRENT;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractService::getAvailableApiVersions()
     */
    public function getAvailableApiVersions()
    {
        return array(
            self::API_VERSION_20100801
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractService::getUrl()
     */
    public function getUrl()
    {
        return 'monitoring.' . $this->getAws()->getRegion() . '.amazonaws.com';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractService::getAllowedEntities()
     */
    public function getAllowedEntities()
    {
        return array('metric', 'alarm');
    }
}

/* TODO [postponed] Implement folowing API methods:
    - DeleteAlarms
    - DescribeAlarmHistory
    - DescribeAlarms
    - DescribeAlarmsForMetric
    - DisableAlarmActions
    - EnableAlarmActions
    - PutMetricAlarm
    - PutMetricData
    - SetAlarmState
*/