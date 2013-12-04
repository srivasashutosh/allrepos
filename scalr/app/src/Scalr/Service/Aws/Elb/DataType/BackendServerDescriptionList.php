<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * BackendServerDescriptionList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.10.2012
 *
 * @method   BackendServerDescriptionData get() get($position = null) Gets BackendServerDescriptionList at specified position
 *                                                                    in the list.
 */
class BackendServerDescriptionList extends AbstractElbListDataType
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
     * @param array|BackendServerDescriptionData  $aListData  Instance List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('instancePort'), 'Scalr\\Service\\Aws\\Elb\\DataType\\BackendServerDescriptionData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'BackendServerDescriptions', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}