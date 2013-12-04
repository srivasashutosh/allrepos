<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\S3\AbstractS3DataType;

/**
 * PermissionData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.11.2012
 */
class PermissionData extends AbstractS3DataType
{

    /**
     * For the Bucket it Allows grantee the READ, WRITE, READ_ACP, and WRITE_ACP permissions on the bucket.
     * For the Object it Allows grantee the READ, READ_ACP, and WRITE_ACP permissions on the object
     */
    const PERM_FULL_CONTROL = 'FULL_CONTROL';

    /**
     * For the Bucket it Allows grantee to create, overwrite, and delete any object in the bucket.
     * For the Objects it does not applicable.
     */
    const PERM_WRITE = 'WRITE';

    /**
     * For the Bucket it Allows grantee to write the ACL for the applicable bucket.
     * For the Object it Allows grantee to write the ACL for the applicable object.
     */
    const PERM_WRITE_ACP = 'WRITE_ACP';

    /**
     * For the Bucket it Allows grantee to list the objects in the bucket.
     * For the Object it Allows grantee to read the object data and its metadata.
     */
    const PERM_READ = 'READ';

    /**
     * For the Bucket it Allows grantee to read the bucket ACL.
     * For the Object it Allows grantee to read the object ACL.
     */
    const PERM_READ_ACP = 'READ_ACP';

    /**
     * Permission value
     *
     * @var string
     */
    private $value = null;

    /**
     * Constructor
     *
     * @param   string   $value Permission value.
     * @throws  \InvalidArgumentException
     */
    public function __construct($value)
    {
        $this->set($value);
    }

    /**
     * Gets permission value
     *
     * @return string Returns permission value
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Sets permission value
     *
     * @param   string|PermissionData  $value Permsission value
     * @return  PermissionData
     * @throws  \InvalidArgumentException
     */
    public function set($value)
    {
        if ($value instanceof PermissionData) {
            $value = (string) $value;
        }
        $this->value = $value;
        if (!$this->validate()) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid permission "%s". Valid values are "%s"', $value, join('", "', $this->getValidValues())
            ));
        }
        return $this;
    }

    /**
     * Validates the value.
     *
     * @return bool Returns TRUE on success or FALSE if current value is invalid.
     */
    public function validate()
    {
        $ret = false;
        $ref = new \ReflectionClass(__CLASS__);
        foreach ($ref->getConstants() as $cname => $cvalue) {
            if (strpos($cname, 'PERM_') !== 0) continue;
            if ($cvalue === $this->value) {
                $ret = true;
                break;
            }
        }
        return $ret;
    }

    /**
     * Gets valid values for the permission.
     *
     * @return  array Returns valid values for the permission.
     */
    public static function getValidValues()
    {
        $ref = new \ReflectionClass(__CLASS__);
        $arr = array();
        foreach ($ref->getConstants() as $cname => $cvalue) {
            if (strpos($cname, 'PERM_') !== 0) continue;
            $arr[$cname] = $cvalue;
        }
        return $arr;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toArray()
     */
    public function toArray($ucase = false, &$known = null)
    {
        return (string) $this;
    }

    /**
     * Gets a value
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}