<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * MetricList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.10.2012
 * @method   MetricData get() get($position = null) Gets MetricData at specified position
 *                                                  in the list.
 */
class MetricList extends AbstractCloudWatchListDataType
{

    /**
     * Constructor
     *
     * @param array|MetricData  $aListData  MetricData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('metricName', 'namespace'),
            'Scalr\\Service\\Aws\\CloudWatch\\DataType\\MetricData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Metrics', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}