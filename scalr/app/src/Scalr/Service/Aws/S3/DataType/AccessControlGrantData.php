<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\S3\AbstractS3DataType;

/**
 * AccessControlGrantData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.11.2012
 *
 * @property  \Scalr\Service\Aws\S3\DataType\PermissionData         $permission     A permission type.
 * @property  \Scalr\Service\Aws\S3\DataType\GranteeData            $grantee        A grantee.
 *
 * @method    GranteeData            getGrantee()    getGrantee()                              Gets grantee.
 * @method    AccessControlGrantData setGrantee()    setGrantee(GranteeData $grantee)          Sets grantee.
 * @method    PermissionData         getPermission() getPermission()                           Gets a permission type.
 * @method    AccessControlGrantData setPermission() setPermission(PermissionData $permission) Sets a permission type.
 */
class AccessControlGrantData extends AbstractS3DataType
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
    protected $_properties = array('grantee', 'permission');
}