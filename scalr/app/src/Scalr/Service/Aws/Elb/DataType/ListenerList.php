<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * ListenerList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.09.2012
 *
 * @method   ListenerData get() get($position = null) Gets ListenerData at specified position
 *                                                    in the list.
 */
class ListenerList extends AbstractElbListDataType
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
     * @param array|ListenerData[]|ListenerDescriptionData[] $aListData  Listener list
     */
    public function __construct($aListData = null)
    {
        if ($aListData !== null) {
            //Makes it possible to pass ListenerDescriptionData list
            if (!is_array($aListData)) {
                $aListData = array($aListData);
            }
            foreach ($aListData as $k => $v) {
                if ($v instanceof ListenerDescriptionData) {
                    /* @var $v ListenerDescriptionData */
                    $aListData[$k] = $v->listener;
                }
            }
        }
        parent::__construct(
            $aListData,
            array(
                'loadBalancerPort',
                'instancePort',
                'protocol',
                'sslCertificateId'
            ),
            'Scalr\\Service\\Aws\\Elb\\DataType\\ListenerData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Listeners', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}