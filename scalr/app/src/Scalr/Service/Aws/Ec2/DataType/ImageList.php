<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * ImageList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    24.01.2013
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\ImageData get()
 *           get(int $index)
 *           Gets ImageData object from the specified position in the list.
 */
class ImageList extends Ec2ListDataType
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
     * @param array|ImageData  $aListData List of ImageData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'imageId', __NAMESPACE__ . '\\ImageData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'ImageId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}