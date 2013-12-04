<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * VpcList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.03.2013
 */
class VpcList extends Ec2ListDataType
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
     * @param array|VpcData  $aListData List of VpcData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'vpcId', __NAMESPACE__ . '\\VpcData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Vpcs', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}