<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * DatapointList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.10.2012
 * @property string           label                       A label describing the specified metric.
 * @method   DatapointData    get() get($position = null) Gets DatapointData at specified position
 *                                                        in the list.
 */
class DatapointList extends AbstractCloudWatchListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('metricName');

    /**
     * Constructor
     *
     * @param array|DatapointData  $aListData  DatapointData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('average', 'maximum', 'minimum', 'sampleCount', 'sum', 'timestamp', 'unit'),
            'Scalr\\Service\\Aws\\CloudWatch\\DataType\\DatapointData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Datapoints', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}