<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * AttachmentSetResponseList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    21.01.2013
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\AttachmentSetResponseData get()
 *           get(int $index)
 *           Returns AttachmentSetResponseData object from the specified position in the list.
 */
class AttachmentSetResponseList extends Ec2ListDataType
{
    /**
     * Constructor
     *
     * @param array|AttachmentSetResponseData  $aListData  AttachmentSetResponseData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('volumeId', 'instanceId', 'device', 'status', 'attachTime', 'deleteOnTermination'),
            __NAMESPACE__ . '\\AttachmentSetResponseData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Attachment', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}