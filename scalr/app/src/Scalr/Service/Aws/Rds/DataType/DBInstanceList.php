<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * DBInstanceList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    20.03.2013
 *
 * @property string    $marker
 *           An optional pagination token provided by a previous request.
 *           If this parameter is specified, the response includes only
 *           records beyond the marker, up to the value specified by MaxRecords
 *
 * @method   string         getMarker() getMarger()     Gets a Marker.
 * @method   DBInstanceList setMarker() setMarker($val) Sets a Marker value.
 */
class DBInstanceList extends RdsListDataType
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
     * @param array|DBInstanceData  $aListData List of DBInstanceData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('dBInstanceIdentifier'), __NAMESPACE__ . '\\DBInstanceData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'DBInstances', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}