<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AppCookieStickinessPolicyList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.10.2012
 *
 * @method   AppCookieStickinessPolicyData get() get($position = null) Gets AppCookieStickinessPolicyData at specified position
 *                                                                     in the list.
 */
class AppCookieStickinessPolicyList extends AbstractElbListDataType
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
     * @param array|AppCookieStickinessPolicyData  $aListData  Instance List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('policyName'), 'Scalr\\Service\\Aws\\Elb\\DataType\\AppCookieStickinessPolicyData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'AppCookieStickinessPolicies', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}