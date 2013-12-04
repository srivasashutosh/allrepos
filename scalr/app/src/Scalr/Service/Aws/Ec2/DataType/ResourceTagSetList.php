<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * ResourceTagSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 */
class ResourceTagSetList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|ResourceTagSetData  $aListData List of ResourceTagSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('key', 'value'), __NAMESPACE__ . '\\ResourceTagSetData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Tag', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}