<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * ReservationList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\ReservationData get()
 *           get($index)
 *           Gets ReservationData object from the spicified position from the list
 */
class ReservationList extends Ec2ListDataType
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
     * @param array|ReservationData  $aListData List of ReservationData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('reservationId'), __NAMESPACE__ . '\\ReservationData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Reservations', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}