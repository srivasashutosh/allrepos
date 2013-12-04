<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * IPRangeList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    22.03.2013
 */
class IPRangeList extends RdsListDataType
{

    /**
     * Constructor
     *
     * @param array|IPRangeData  $aListData List of IPRangeData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('cIDRIP', 'status'),
            __NAMESPACE__ . '\\IPRangeData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'IPRanges', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}