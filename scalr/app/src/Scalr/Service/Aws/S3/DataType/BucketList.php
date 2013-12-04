<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3\AbstractS3ListDataType;

/**
 * BucketList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     12.11.2012
 * @method    \Scalr\Service\Aws\S3\DataType\OwnerData       getOwner()     getOwner()                 Returns an bucket owner
 * @method    \Scalr\Service\Aws\S3\DataType\ObjectData      setOwner()     setOwner(OwnerData $owner) Sets an bucket owner
 */
class BucketList extends AbstractS3ListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('owner');

    /**
     * Constructor
     *
     * @param array|BucketData  $aListData  BucketData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            'bucketName',
            'Scalr\\Service\\Aws\\S3\\DataType\\BucketData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'BucketName', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}