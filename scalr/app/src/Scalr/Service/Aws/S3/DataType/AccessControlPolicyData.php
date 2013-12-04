<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\S3\AbstractS3DataType;
use Scalr\Service\Aws\S3\DataType\OwnerData;

/**
 * AccessControlPolicyData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.11.2012
 *
 * @property  OwnerData               $owner              A Bucket or Object owner.
 * @property  AccessControlGrantList  $accessControlList  An Access Control List.
 *
 * @method    string                  getBucketName() getBucketName()            Gets a bucket name which this ACL corresponds to.
 * @method    AccessControlPolicyData setBucketName() setBucketName($bucketName) Sets a bucket name which this ACL corresponds to.
 * @method    string                  getObjectName() getObjectName()            Gets a object name which this ACL corresponds to.
 * @method    AccessControlPolicyData setObjectName() setObjectName($objectName) Sets a object name which this ACL corresponds to.
 * @method    OwnerData               getOwner()      getOwner()                 Gets a Bucket or Object owner.
 * @method    AccessControlPolicyData setOwner()      setOwner(OwnerData $owner) Sets a Bucket or Object owner.
 * @method    AccessControlGrantList  getAccessControlList() getAccessControlList() Gets an Access Control List.
 * @method    AccessControlPolicyData setAccessControlList() setAccessControlList(AccessControlGrantList $accessControlList) Sets an Access Control List.
 */
class AccessControlPolicyData extends AbstractS3DataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('bucketName', 'objectName');

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('owner', 'accessControlList');

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElementNS('http://s3.amazonaws.com/doc/2006-03-01/', 'AccessControlPolicy');
        $xml->appendChild($top);
        $owner = $xml->createElement('Owner');
        $owner->appendChild($xml->createElement('ID', $this->owner->ownerid));
        $owner->appendChild($xml->createElement('DisplayName', $this->owner->displayName));
        $top->appendChild($owner);
        $acl = $xml->createElement('AccessControlList');
        $top->appendChild($acl);
        /* @var $grant AccessControlGrantData */
        foreach ($this->accessControlList as $grant) {
            $g = $xml->createElement('Grant');
            $acl->appendChild($g);
            $gi = $xml->createElement('Grantee');
            $gi->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:type', $grant->getGrantee()->type);
            if ($grant->grantee->uri !== null) {
                $gi->appendChild($xml->createElement('URI', $grant->grantee->uri));
            } else {
                $gi->appendChild($xml->createElement('ID', $grant->grantee->granteeId));
                $gi->appendChild($xml->createElement('DisplayName', $grant->grantee->displayName));
            }
            $g->appendChild($gi);
            $g->appendChild($xml->createElement('Permission', (string)$grant->permission));
            unset($gi);
            unset($g);
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}