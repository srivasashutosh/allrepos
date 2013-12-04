<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * IpRangeList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\IpRangeData get()
 *           get(int $index)
 *           Gets IpRangeData object for the specified position in the list.
 */
class IpRangeList extends Ec2ListDataType
{

    /**
     * Constructor
     *
     * @param array|IpRangeData  $aListData List of IpRangeData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('cidrIp'), __NAMESPACE__ . '\\IpRangeData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'IpRanges', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}