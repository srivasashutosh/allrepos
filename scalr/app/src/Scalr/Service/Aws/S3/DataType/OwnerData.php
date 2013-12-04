<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\S3\AbstractS3DataType;

/**
 * OwnerData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     20.11.2012
 */
class OwnerData extends AbstractS3DataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('bucketName', 'objectName');

    /**
     * Object owner's ID
     *
     * @var string
     */
    public $ownerid;

    /**
     * Object owner's name
     *
     * @var string
     */
    public $displayName;

    /**
     * Convenient constructor
     *
     * @param   string     $ownerid     optional An object owners's ID.
     * @param   string     $displayName optional An owner display name.
     */
    public function __construct($ownerid = null, $displayName = null)
    {
        $this->ownerid = $ownerid;
        $this->displayName = $displayName;
    }
}