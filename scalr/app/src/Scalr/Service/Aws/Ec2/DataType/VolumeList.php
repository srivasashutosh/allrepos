<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * VolumeList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    21.01.2013
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\VolumeData get()
 *           get(int $index)
 *           Returns VolumeData object from the specified position in the list.
 */
class VolumeList extends Ec2ListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('requestId');

    /**
     * Constructor
     *
     * @param array|VolumeData  $aListData List of VolumeData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'volumeId', __NAMESPACE__ . '\\VolumeData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'VolumeId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}