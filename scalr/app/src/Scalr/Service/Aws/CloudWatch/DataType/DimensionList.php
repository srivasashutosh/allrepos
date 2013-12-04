<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * DimensionList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    24.10.2012
 *
 * @method   DimensionData get() get($position = null) Gets DimensionData at specified position
 *                                                     in the list.
 */
class DimensionList extends AbstractCloudWatchListDataType
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
     * @param array|DimensionData  $aListData  DimensionData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('name', 'value'),
            'Scalr\\Service\\Aws\\CloudWatch\\DataType\\DimensionData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Dimensions', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}