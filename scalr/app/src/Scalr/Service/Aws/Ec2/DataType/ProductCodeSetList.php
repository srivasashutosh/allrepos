<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * ProductCodeSetList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 */
class ProductCodeSetList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|ProductCodeSetData  $aListData List of ProductCodeSetData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('productCode', 'type'), __NAMESPACE__ . '\\ProductCodeSetData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'ProductCodes', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}