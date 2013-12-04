<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InternetGatewayAttachmentList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    03.04.2013
 *
 * @method   string getInternetGatewayId() getInternetGatewayId()
 *           Gets the ID of the associated Internet Gateway
 *
 * @method   InternetGatewayAttachmentList setInternetGatewayId() setInternetGatewayId($id)
 *           Sets the ID of the associated Internet Gateway
 */
class InternetGatewayAttachmentList extends Ec2ListDataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('internetGatewayId');

    /**
     * Constructor
     *
     * @param array|InternetGatewayAttachmentData  $aListData InternetGatewayAttachmentData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('vpcId', 'state'), __NAMESPACE__ . '\\InternetGatewayAttachmentData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Attachments', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}