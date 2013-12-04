<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\Rds\RdsListDataType;
use Scalr\Service\Aws\RdsException;

/**
 * ParameterList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.03.2013
 *
 * @property string    $marker
 *           An optional pagination token provided by a previous request.
 *           If this parameter is specified, the response includes only
 *           records beyond the marker, up to the value specified by MaxRecords
 *
 * @method   string         getMarker() getMarger()     Gets a Marker.
 * @method   DBInstanceList setMarker() setMarker($val) Sets a Marker value.
 */
class ParameterList extends RdsListDataType
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
     * @param array|ParameterData  $aListData List of ParameterData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array(
                'parameterName', 'parameterValue', 'allowedValues', 'applyMethod',
                'applyType', 'dataType', 'description', 'isModifiable',
                'minimumEngineVersion', 'source',
            ),
            __NAMESPACE__ . '\\ParameterData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Parameters', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}