<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * ReservedInstanceList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    14.01.2013
 */
class ReservedInstanceList extends Ec2ListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('requestId');

    /**
     * Constructor
     *
     * @param array|ReservedInstanceData  $aListData List of ReservedInstanceData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('reservedInstancesId'), __NAMESPACE__ . '\\ReservedInstanceData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'ReservedInstances', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}