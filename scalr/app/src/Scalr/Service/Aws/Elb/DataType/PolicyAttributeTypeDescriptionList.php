<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * PolicyAttributeTypeDescriptionList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    16.10.2012
 * @method   PolicyAttributeTypeDescriptionData get() get($position = null) Gets PolicyAttributeTypeDescriptionData at specified position
 *                                                                          in the list.
 */
class PolicyAttributeTypeDescriptionList extends AbstractElbListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName',
        'policyTypeName'
    );

    /**
     * Constructor
     *
     * @param array|PolicyAttributeTypeDescriptionData  $aListData  PolicyAttributeTypeDescriptionData
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('attributeTypeName'),
            'Scalr\\Service\\Aws\\Elb\\DataType\\PolicyAttributeTypeDescriptionData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'AttributeTypeNames', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}