<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * RecurringChargesSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    14.01.2013
 */
class RecurringChargesSetList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|RecurringChargesSetData  $aListData List of RecurringChargesSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('frequency', 'amount'), __NAMESPACE__ . '\\RecurringChargesSetData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'RecurringCharges', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}