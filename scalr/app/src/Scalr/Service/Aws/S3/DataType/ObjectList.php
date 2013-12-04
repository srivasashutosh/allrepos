<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3\AbstractS3ListDataType;

/**
 * ObjectList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     20.11.2012
 * @property  string  $bucketName   An bucket name which object corresponds to.
 * @method    string                                    getMarker() getMarker()        Gets an marker which indicates
 *                                                                                     where in the bucket to begin listing.
 * @method    \Scalr\Service\Aws\S3\DataType\ObjectList setMarker() setMarker($marker) Sets an marker
 */
class ObjectList extends AbstractS3ListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('bucketName');

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('marker');

    /**
     * Constructor
     *
     * @param array|BucketData  $aListData  BucketData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            'objectName',
            'Scalr\\Service\\Aws\\S3\\DataType\\ObjectData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'ObjectName', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}