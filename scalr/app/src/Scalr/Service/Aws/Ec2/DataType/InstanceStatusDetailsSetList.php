<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * InstanceStatusDetailsSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 */
class InstanceStatusDetailsSetList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|InstanceStatusDetailsSetData  $aListData List of InstanceStatusDetailsSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('name', 'status', 'impairedSince'), __NAMESPACE__ . '\\InstanceStatusDetailsSetData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Details', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}