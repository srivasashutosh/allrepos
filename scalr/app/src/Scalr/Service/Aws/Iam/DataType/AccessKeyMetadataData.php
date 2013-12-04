<?php
namespace Scalr\Service\Aws\Iam\DataType;

use Scalr\Service\Aws\IamException;
use Scalr\Service\Aws\Iam\AbstractIamDataType;

/**
 * AccessKeyMetadataData
 *
 * The AccessKey data type contains information about an AWS access key, without its secret key.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     14.11.2012
 */
class AccessKeyMetadataData extends AbstractIamDataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array();

    /**
     * @var string
     */
    public $userName;

    /**
     * @var string
     */
    public $accessKeyId;

    /**
     * @var \DateTime
     */
    public $createDate;

    /**
     * Active | Inactive
     * @var string
     */
    public $status;
}