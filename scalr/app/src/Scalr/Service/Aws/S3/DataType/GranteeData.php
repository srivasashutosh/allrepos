<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\S3\AbstractS3DataType;

/**
 * GranteeData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.11.2012
 */
class GranteeData extends AbstractS3DataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('bucketName');

    /**
     * Grantee type (CanonicalUser|Group)
     *
     * @var string
     */
    public $type;

    /**
     * Grantee ID
     *
     * @var string
     */
    public $granteeId;

    /**
     * Grantee display Name
     *
     * @var string
     */
    public $displayName;

    /**
     * Group URI
     *
     * @var string
     */
    public $uri;

}