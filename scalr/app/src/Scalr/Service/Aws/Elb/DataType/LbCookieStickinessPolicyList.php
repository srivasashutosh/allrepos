<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * LbCookieStickinessPolicyList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.10.2012
 *
 * @method   LbCookieStickinessPolicyData get() get($position = null) Gets LbCookieStickinessPolicyData at specified position
 *                                                                    in the list.
 */
class LbCookieStickinessPolicyList extends AbstractElbListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName'
    );

    /**
     * Constructor
     *
     * @param array|LbCookieStickinessPolicyData  $aListData  Instance List
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array(
                'policyName'
            ),
            'Scalr\\Service\\Aws\\Elb\\DataType\\LbCookieStickinessPolicyData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'LbCookieStickinessPolicies', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}