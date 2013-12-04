<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * AddressList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    29.01.2013
 */
class AddressList extends Ec2ListDataType
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
     * @param array|AddressData  $aListData AddressData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array(
                'publicIp', 'allocationId', 'associationId', 'domain', 'networkInterfaceId', 'networkInterfaceOwnerId'
            ),
            __NAMESPACE__ . '\\AddressData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Addresses', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}