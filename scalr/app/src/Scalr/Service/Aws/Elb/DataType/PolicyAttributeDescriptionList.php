<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * PolicyAttributeDescriptionList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    16.10.2012
 * @method   PolicyAttributeDescriptionData get() get($position = null) Gets PolicyAttributeDescriptionData at specified position
 *                                                                   in the list.
 */
class PolicyAttributeDescriptionList extends AbstractElbListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName',
        'policyName'
    );

    /**
     * Constructor
     *
     * @param array|PolicyAttributeDescriptionData  $aListData  PolicyAttributeDescriptionData
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('attributeName'), 'Scalr\\Service\\Aws\\Elb\\DataType\\PolicyAttributeDescriptionData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'AttributeNames', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}