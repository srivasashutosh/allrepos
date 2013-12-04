<?php
namespace Scalr\Service\Aws\CloudWatch\DataType;

use Scalr\Service\Aws\CloudWatch\AbstractCloudWatchListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * DimensionList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.10.2012
 * @method   DimensionFilterData get() get($position = null) Gets DimensionFilterData at specified position
 *                                                           in the list.
 */
class DimensionFilterList extends AbstractCloudWatchListDataType
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
     * @param array|DimensionFilterData  $aListData  DimensionFilterData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('name', 'value'),
            'Scalr\\Service\\Aws\\CloudWatch\\DataType\\DimensionFilterData'
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