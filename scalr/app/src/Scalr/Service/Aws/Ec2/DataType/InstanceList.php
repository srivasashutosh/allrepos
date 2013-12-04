<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 *
 * @method   string getReservationId()
 *           getReservationId()
 *           Gets an associated Reservattion ID.
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\InstanceList setReservationId()
 *           setReservationId($id)
 *           Sets an associated Reservattion ID.
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\InstanceData get()
 *           get($index)
 *           Gets InstanceData object from the spicified position from the list
 */
class InstanceList extends Ec2ListDataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('reservationId');

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array();

    /**
     * Constructor
     *
     * @param array|InstanceData  $aListData List of InstanceData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('instanceId'), __NAMESPACE__ . '\\InstanceData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Instances', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}