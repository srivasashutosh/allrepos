<?php
namespace Scalr\Service\Aws\Iam\DataType;

use Scalr\Service\Aws\Iam\AbstractIamListDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AccessKeyMetadataList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    14.11.2012
 *
 * @method   AccessKeyMetadataData get() get($position = null) Gets AccessKeyMetadataData at specified position
 *                                                             in the list.
 */
class AccessKeyMetadataList extends AbstractIamListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('marker', 'isTruncated');

    /**
     * Constructor
     *
     * @param array|AccessKeyMetadataData  $aListData  AccessKeyMetadataData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('accessKeyId'), 'Scalr\\Service\\Aws\\Iam\\DataType\\AccessKeyMetadataData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'AccessKeyId', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}