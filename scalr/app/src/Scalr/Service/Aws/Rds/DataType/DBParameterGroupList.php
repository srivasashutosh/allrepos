<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * DBParameterGroupList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.03.2013
 *
 * @property string    $marker
 *           An optional pagination token provided by a previous request.
 *           If this parameter is specified, the response includes only
 *           records beyond the marker, up to the value specified by MaxRecords
 *
 * @method   string               getMarker() getMarger()     Gets a Marker.
 * @method   DBParameterGroupList setMarker() setMarker($val) Sets a Marker value.
 */
class DBParameterGroupList extends RdsListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('marker');

    /**
     * Constructor
     *
     * @param array|DBParameterGroupData  $aListData List of DBParameterGroupData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('dBParameterGroupName', 'dBParameterGroupFamily', 'description'), __NAMESPACE__ . '\\DBParameterGroupData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'DBParameterGroups', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}