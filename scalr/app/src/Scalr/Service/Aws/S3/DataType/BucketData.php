<?php
namespace Scalr\Service\Aws\S3\DataType;

use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\S3\AbstractS3DataType;

/**
 * BucketData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     12.11.2012
 */
class BucketData extends AbstractS3DataType
{
    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array();

    /**
     * The bucket name
     *
     * @var string
     */
    public $bucketName;

    /**
     * Creation date
     *
     * @var \DateTime
     */
    public $creationDate;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\S3.AbstractS3DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->bucketName === null) {
            throw new S3Exception(sprintf(
                'bucketName property has not been initialized for the %s yet.', get_class($this)
            ));
        }
    }

    /**
     * GET Bucket (List Objects) action
     *
     * This implementation of the GET operation returns some or all (up to 1000) of the objects in a bucket.
     * You can use the request parameters as selection criteria to return a subset of the objects in a bucket.
     * To use this implementation of the operation, you must have READ access to the bucket.
     *
     * @param   string     $delimiter  optional A delimiter is a character you use to group keys.
     *                                 All keys that contain the same string between the prefix, if specified,
     *                                 and the first occurrence of the delimiter after the prefix are grouped
     *                                 under a single result element, CommonPrefixes. If you don't specify
     *                                 the prefix parameter, then the substring starts at the beginning of the
     *                                 key. The keys that are grouped under CommonPrefixes result element
     *                                 are not returned elsewhere in the response.
     * @param   string     $marker     optional Specifies the key to start with when listing objects in a bucket.
     *                                 Amazon S3 lists objects in alphabetical order.
     * @param   string     $maxKeys    optional Sets the maximum number of keys returned in the response body.
     *                                 The response might contain fewer keys but will never contain more.
     *                                 If there are additional keys that satisfy the search criteria
     *                                 but were not returned because max-keys was exceeded, the response contains
     *                                 <IsTruncated>true</IsTruncated>.To return the additional keys.
     * @param   string     $prefix     optional Limits the response to keys that begin with the specified prefix.
     *                                 You can use prefixes to separate a bucket into different groupings of keys.
     *                                 (You can think of using prefix to make groups in the same way you'd use
     *                                 a folder in a file system.)
     * @return  ObjectList  Returns list of Objects
     * @throws  S3Exception
     * @throws  ClientException
     */
    public function listObjects ($delimiter = null, $marker = null, $maxKeys = null, $prefix = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->listObjects($this->getBucketName(), $delimiter, $marker, $maxKeys, $prefix);
    }

    /**
     * DELETE Bucket
     *
     * This implementation of the DELETE operation deletes the bucket named in the URI.
     * All objects (including all object versions and Delete Markers)
     * in the bucket must be deleted before the bucket itself can be deleted.
     *
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function delete ()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->delete($this->bucketName);
    }

    /**
     * Gets an object
     *
     * This implementation of the GET operation retrieves objects from Amazon S3.
     * To use GET, you must have READ access to the object.
     * If you grant READ access to the anonymous user, you can return the object
     * without using an authorization header.
     *
     * @param   string     $objectName     An object key name.
     * @param   array      $requestPars    optional An additional request query parameters. It accepts only allowed params.
     * @param   array      $requestHeaders opitional An optional request headers. It accepts only allowed headers.
     * @return  ClientResponseInterface    Returns response
     * @throws  S3Exception
     */
    public function getObject ($objectName, array $requestPars = null, array $requestHeaders = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->object->download($this->bucketName, $objectName, $requestPars, $requestHeaders);
    }

    /**
     * Gets a bucket ACL action.
     *
     * This implementation of the GET operation uses the acl subresource
     * to return the access control list (ACL) of a bucket.
     *
     * To use GET to return the ACL of the bucket, you must have READ_ACP access to the bucket.
     * If READ_ACP permission is granted to the anonymous user, you can return the
     * ACL of the bucket without using an authorization header.
     *
     * @return  AccessControlPolicyData Returns object which describes ACL for the bucket.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getAcl()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getAcl($this->bucketName);
    }

    /**
     * PUT Bucket ACL action.
     *
     * Sets bucket's ACL.
     *
     * @param   AccessControlPolicyData|\DOMDocument|string|array  $aclset AccessControlPolicyData object or
     *                                                                     XML Document or array of x-amz headers
     * @return  bool          Returns True on success of false if failures.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setAcl($aclset)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setAcl($this->bucketName, $aclset);
    }

    /**
     * DELETE Bucket cors action
     *
     * Deletes the cors configuration information set for the bucket.
     *
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteCors()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->deleteCors($this->bucketName);
    }

    /**
     * DELETE Bucket lifecycle action
     *
     * Deletes the lifecycle configuration from the specified bucket.
     * Amazon S3 removes all the lifecycle configuration rules in the
     * lifecycle subresource associated with the bucket.
     * Your objects never expire, and Amazon S3 no longer automatically
     * deletes any objects on the basis of rules contained in the deleted
     * lifecycle configuration.
     *
     * To use this operation, you must have permission to perform the s3:PutLifecycleConfiguration action.
     * By default, the bucket owner has this permission and the bucket owner can grant this permission
     * to others.
     *
     * There is usually some time lag before lifecycle configuration deletion is fully propagated
     * to all the Amazon S3 systems.
     *
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteLifecycle()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->deleteLifecycle($this->bucketName);
    }

    /**
     * DELETE Bucket policy action
     *
     * This implementation of the DELETE operation uses the policy subresource to delete the policy on a
     * specified bucket. To use the operation, you must have DeletePolicy permissions on the specified
     * bucket and be the bucket owner.
     *
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deletePolicy()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->deletePolicy($this->bucketName);
    }

    /**
     * DELETE Bucket tagging action
     *
     * This implementation of the DELETE operation uses the tagging subresource to remove a tag set from
     * the specified bucket.
     * To use this operation, you must have permission to perform the s3:PutBucketTagging action. By
     * default, the bucket owner has this permission and can grant this permission to others.
     *
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteTagging()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->deleteTagging($this->bucketName);
    }

    /**
     * DELETE Bucket website action
     *
     * This operation removes the website configuration for a bucket.
     * Amazon S3 returns a 200 OK response upon successfully deleting a website configuration
     * on the specified bucket.
     * You will get a 200 OK response if the website configuration you are trying to delete
     * does not exist on the bucket.
     * Amazon S3 returns a 404 response if the bucket specified in the request does not exist.
     *
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteWebsite()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->deleteWebsite($this->bucketName);
    }

    /**
     * Returns the cors configuration information set for the bucket.
     *
     * @return  string                 Returns CORSConfiguration XML Document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getCors()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getCors($this->bucketName);
    }

    /**
     * GET Bucket lifecycle action
     *
     * Returns the lifecycle configuration information set on the bucket.
     *
     * @return  string            Returns LifecycleConfiguration XML Document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getLifecycle()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getLifecycle($this->bucketName);
    }

    /**
     * GET Bucket policy action
     *
     * This implementation of the GET operation uses the policy subresource to return the policy of a specified
     * bucket. To use this operation, you must have GetPolicy permissions on the specified bucket, and you
     * must be the bucket owner.
     *
     * @return  string            Returns bucket policy string (json encoded)
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getPolicy()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getPolicy($this->bucketName);
    }

    /**
     * GET Bucket location action
     *
     * This implementation of the GET operation uses the location subresource to return a bucket's Region.
     * You set the bucket's Region using the LocationContraint request parameter in a PUT Bucket request.
     *
     * @return  string      Returns bucket location
     *                      Valid Values: EU | eu-west-1 | us-west-1 | us-west-2 | ap-southeast-1 |
     *                      ap-northeast-1 | sa-east-1 | empty string (for the US Classic Region)
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getLocation()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getLocation($this->bucketName);
    }

    /**
     * GET Bucket logging action
     *
     * This implementation of the GET operation uses the logging subresource to return the logging status of
     * a bucket and the permissions users have to view and modify that status. To use GET, you must be the
     * bucket owner.
     *
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getLogging()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getLogging($this->bucketName);
    }

    /**
     * GET Bucket notification action
     *
     * This implementation of the GET operation uses the notification subresource to return the notification
     * configuration of a bucket.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getNotification($bucketName)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getNotification($this->bucketName);
    }

    /**
     * GET Bucket tagging action
     *
     * This implementation of the GET operation uses the tagging subresource to return the tag set associated
     * with the bucket.
     *
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getTagging()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getTagging($this->bucketName);
    }

    /**
     * GET Bucket requestPayment action
     *
     * This implementation of the GET operation uses the requestPayment subresource to return the request
     * payment configuration of a bucket.
     *
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getRequestPayment()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->getRequestPayment($this->bucketName);
    }

    /**
     * PUT Bucket lifecycle action
     *
     * Sets the lifecycle configuration for your bucket.
     * If the configuration exists, Amazon S3 replaces it.
     *
     * @param   string     $lifecycle  A XML document that defines lifecycle configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setLifecycle($lifecycle)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setLifecycle($this->bucketName);
    }

    /**
     * PUT Bucket cors action
     *
     * Sets the cors configuration for your bucket. If the configuration exists, Amazon S3 replaces it.
     *
     * @param   string     $cors       A XML document that defines Cors configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setCors($cors)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setCors($this->bucketName, $cors);
    }

    /**
     * PUT Bucket policy action
     *
     * This implementation of the GET operation uses the policy subresource to return the policy of a specified
     * bucket. To use this operation, you must have GetPolicy permissions on the specified bucket, and you
     * must be the bucket owner.
     *
     * @param   string            $policy     A JSON document that defines policy configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setPolicy($policy)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setPolicy($this->bucketName, $policy);
    }

    /**
     * PUT Bucket logging action
     *
     * @param   string            $bucketLoggingStatus A XML Document which describes configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setLogging($bucketLoggingStatus)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setLogging($this->bucketName, $bucketLoggingStatus);
    }

    /**
     * PUT Bucket notification action
     *
     * This implementation of the PUT operation uses the notification subresource to enable notifications
     * of specified events for a bucket
     *
     * @param   string            $notification A XML Document which describes configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setNotification($notification)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setNotification($this->bucketName, $notification);
    }

    /**
     * PUT Bucket tagging action
     *
     * This implementation of the PUT operation uses the tagging subresource to add a set of tags to an
     * existing bucket.
     *
     * @param   string            $tagging      A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setTagging($tagging)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setTagging($this->bucketName, $tagging);
    }

    /**
     * PUT Bucket requestPayment action
     *
     * This implementation of the PUT operation uses the requestPayment subresource to set the request
     * payment configuration of a bucket.
     *
     * @param   string     $requestPayment A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setRequestPayment($requestPayment)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setRequestPayment($this->bucketName, $requestPayment);
    }

    /**
     * PUT Bucket website action
     *
     * This implementation of the PUT operation uses the website subresource to set the request
     * website configuration of a bucket.
     *
     * @param   string     $website        A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setWebsite($website)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->setWebsite($this->bucketName, $website);
    }

    /**
     * PUT Object action
     *
     * This implementation of the PUT operation adds an object to a bucket.You must have WRITE permissions
     * on a bucket to add an object to it.
     *
     * @param   string              $objectName     An object name.
     * @param   string|\SplFileInfo $contentFile    File content of file that should be uploaded.
     *                                              If you provide a path to the file you should pass SplFileInfo object.
     * @param   array               $requestHeaders optional Request headers
     * @return  ClientResponseInterface Returns response on success or throws an exception
     * @throws  S3Exception
     */
    public function addObject($objectName, $contentFile, array $requestHeaders = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->bucket->addObject($this->bucketName, $objectName, $contentFile, $requestHeaders);
    }

    /**
     * DELETE Object action.
     *
     * The DELETE operation removes the null version (if there is one) of an object and inserts a delete marker,
     * which becomes the latest version of the object. If there isn't a null version, Amazon S3 does not remove
     * any objects
     *
     * To remove a specific version, you must be the bucket owner and you must use the versionId
     * subresource. Using this subresource permanently deletes the version. If the object deleted is a Delete
     * Marker, Amazon S3 sets the response header, x-amz-delete-marker, to true.
     * If the object you want to delete is in a bucket where the bucket versioning configuration is MFA Delete
     * enabled, you must include the x-amz-mfa request header in the DELETE versionId request. Requests
     * that include x-amz-mfa must use HTTPS.
     *
     * @param   string     $objectName A object name.
     * @param   string     $versionId  optional To remove a specific version of the object it must be used.
     * @param   string     $xAmfMfa    optional The value is the concatenation of the authentication device's
     *                                 serial number, a space, and the value displayed on your authentication device.
     * @return  ClientResponseInterface Returns response on success or throws an exception
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteObject($objectName, $versionId = null, $xAmfMfa = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getS3()->object->delete($this->bucketName, $objectName, $versionId, $xAmfMfa);
    }
}