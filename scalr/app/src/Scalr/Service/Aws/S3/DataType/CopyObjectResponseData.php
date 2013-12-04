<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\S3\AbstractS3DataType;
use \DateTime;

/**
 * CopyObjectResponseData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     21.12.2012
 */
class CopyObjectResponseData extends AbstractS3DataType
{
    /**
     * Destination Bucket Name
     * @var string
     */
    public $bucketName;

    /**
     * Destination Object Name
     * @var string
     */
    public $objectName;

    /**
     * Array of response headers
     * @var array
     */
    public $headers;

    /**
     * The ETag only reflects changes to the contents of an object, not its metadata
     * @var string
     */
    public $eTag;

    /**
     * Last modified
     * @var DateTime
     */
    public $lastModified;
}