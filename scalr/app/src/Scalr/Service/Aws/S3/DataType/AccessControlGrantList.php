<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3\AbstractS3ListDataType;

/**
 * AccessControlGrantList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.11.2012
 */
class AccessControlGrantList extends AbstractS3ListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('bucketName');

    /**
     * Constructor
     *
     * @param array|AccessControlGrantData  $aListData  AccessControlGrantData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('grantee', 'permission'),
            'Scalr\\Service\\Aws\\S3\\DataType\\AccessControlGrantData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'AccessControlList', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}