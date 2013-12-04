<?php
namespace Scalr\Service\Aws\S3\Handler;

use Scalr\Service\Aws\S3\DataType\AccessControlPolicyData;
use Scalr\Service\Aws;
use Scalr\Service\Aws\Client\ClientResponseInterface;
use Scalr\Service\Aws\S3\DataType\ObjectList;
use Scalr\Service\Aws\S3\DataType\BucketList;
use Scalr\Service\Aws\S3\DataType\BucketData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\S3\AbstractS3Handler;
use \ArrayObject;

/**
 * BucketHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     12.11.2012
 */
class BucketHandler extends AbstractS3Handler
{

    /**
     * GET Service (ListAllMyBuckets) action
     *
     * This implementation of the GET operation returns a list of all buckets
     * owned by the authenticated sender of the request.
     *
     * @return BucketList  Returns BucketList
     * @throws S3Exception
     * @throws ClientException
     */
    public function getList()
    {
        return $this->getS3()->getApiHandler()->listAllMyBuckets();
    }

    /**
     * GET Bucket (List Objects) action
     *
     * This implementation of the GET operation returns some or all (up to 1000) of the objects in a bucket.
     * You can use the request parameters as selection criteria to return a subset of the objects in a bucket.
     * To use this implementation of the operation, you must have READ access to the bucket.
     *
     * @param   string     $bucketName A bucket Name.
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
    public function listObjects($bucketName, $delimiter = null, $marker = null, $maxKeys = null, $prefix = null)
    {
        return $this->getS3()->getApiHandler()->listObjects($bucketName, $delimiter, $marker, $maxKeys, $prefix);
    }

    /**
     * DELETE Bucket
     *
     * This implementation of the DELETE operation deletes the bucket named in the URI.
     * All objects (including all object versions and Delete Markers)
     * in the bucket must be deleted before the bucket itself can be deleted.
     *
     * @param   string      $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function delete($bucketName)
    {
        return $this->getS3()->getApiHandler()->deleteBucket($bucketName);
    }

    /**
     * Create Bucket action
     *
     * Creates a new bucket belonging to the account of the
     * authenticated request sender.
     *
     * @param   string     $bucketName     A bucket name.
     * @param   string     $bucketRegion   optional AWS Region where bucket have to be located.
     * @param   array      $requestHeaders optional Additional request headers.
     *                                     x-amz-acl|x-amz-grant-read|x-amz-grant-write|x-amz-grant-read-acp|
     *                                     x-amz-grant-write-acp|x-amz-grant-full-control
     * @return  BucketData Returns BucketData object on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function create($bucketName, $bucketRegion = Aws::REGION_US_EAST_1, array $requestHeaders = null)
    {
        return $this->getS3()->getApiHandler()->createBucket($bucketName, $bucketRegion, $requestHeaders);
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
     * @param   string       $bucketName A bucket name
     * @return  AccessControlPolicyData Returns object which describes ACL for the bucket.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getAcl($bucketName)
    {
        return $this->getS3()->getApiHandler()->getBucketAcl($bucketName);
    }

    /**
     * PUT Bucket ACL action.
     *
     * Sets bucket's ACL.
     *
     * @param   string                                $bucketName          A bucket name.
     * @param   AccessControlPolicyData|\DOMDocument|string|array  $aclset AccessControlPolicyData object or
     *                                                                     XML Document or array of x-amz headers
     * @return  bool          Returns True on success of false if failures.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setAcl($bucketName, $aclset)
    {
        if ($aclset instanceof AccessControlPolicyData) {
            $aclset = $aclset->toXml();
        } else if ($aclset instanceof \DOMDocument) {
            $aclset = $aclset->saveXML();
        }
        return $this->getS3()->getApiHandler()->setBucketAcl($bucketName, $aclset);
    }

    /**
     * DELETE Bucket cors action
     *
     * Deletes the cors configuration information set for the bucket.
     *
     * @param   BucketData|string  $bucketName A bucket name or BucketData object
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteCors($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->deleteBucketCors($bucketName);
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
     * @param   BucketData|string $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteLifecycle($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->deleteBucketLifecycle($bucketName);
    }

    /**
     * DELETE Bucket policy action
     *
     * This implementation of the DELETE operation uses the policy subresource to delete the policy on a
     * specified bucket. To use the operation, you must have DeletePolicy permissions on the specified
     * bucket and be the bucket owner.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deletePolicy($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->deleteBucketPolicy($bucketName);
    }

    /**
     * DELETE Bucket tagging action
     *
     * This implementation of the DELETE operation uses the tagging subresource to remove a tag set from
     * the specified bucket.
     * To use this operation, you must have permission to perform the s3:PutBucketTagging action. By
     * default, the bucket owner has this permission and can grant this permission to others.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteTagging($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->deleteBucketTagging($bucketName);
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
     * @param   BucketData|string $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteWebsite($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->deleteBucketWebsite($bucketName);
    }

    /**
     * Returns the cors configuration information set for the bucket.
     *
     * @param   BucketData|string      $bucketName A bucket name.
     * @return  string                 Returns CORSConfiguration XML Document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getCors($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->getBucketCors($bucketName);
    }

    /**
     * GET Bucket lifecycle action
     *
     * Returns the lifecycle configuration information set on the bucket.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @return  string            Returns LifecycleConfiguration XML Document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getLifecycle($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->getBucketLifecycle($bucketName);
    }

    /**
     * GET Bucket policy action
     *
     * This implementation of the GET operation uses the policy subresource to return the policy of a specified
     * bucket. To use this operation, you must have GetPolicy permissions on the specified bucket, and you
     * must be the bucket owner.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @return  string            Returns bucket policy string (json encoded)
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getPolicy($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->getBucketPolicy($bucketName);
    }

    /**
     * GET Bucket location action
     *
     * This implementation of the GET operation uses the location subresource to return a bucket's Region.
     * You set the bucket's Region using the LocationContraint request parameter in a PUT Bucket request.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @return  string      Returns bucket location
     *                      Valid Values: EU | eu-west-1 | us-west-1 | us-west-2 | ap-southeast-1 |
     *                      ap-northeast-1 | sa-east-1 | empty string (for the US Classic Region)
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getLocation($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->getBucketLocation($bucketName);
    }

    /**
     * GET Bucket logging action
     *
     * This implementation of the GET operation uses the logging subresource to return the logging status of
     * a bucket and the permissions users have to view and modify that status. To use GET, you must be the
     * bucket owner.
     *
     * @param   string      $bucketName A bucket name.
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getLogging($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->getBucketLogging($bucketName);
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
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->getBucketNotification($bucketName);
    }

    /**
     * GET Bucket tagging action
     *
     * This implementation of the GET operation uses the tagging subresource to return the tag set associated
     * with the bucket.
     *
     * @param   string      $bucketName A bucket name.
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getTagging($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->getBucketTagging($bucketName);
    }

    /**
     * GET Bucket requestPayment action
     *
     * This implementation of the GET operation uses the requestPayment subresource to return the request
     * payment configuration of a bucket.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getRequestPayment($bucketName)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->getBucketRequestPayment($bucketName);
    }

    /**
     * HEAD Bucket action
     *
     * Cheks whether the bucket exists.
     *
     * @param   string     $bucketName A bucket name.
     * @return  bool       Returns TRUE if bucket does exist or false if not.
     */
    public function isExist($bucketName)
    {
        $r = $this->getS3()->getApiHandler()->headBucket($bucketName);
        return $r == 200 || $r == 403;
    }

    /**
     * HEAD Bucket action
     *
     * Cheks whether the bucket exists and it is allowed for user.
     *
     * @param   string     $bucketName A bucket name.
     * @return  bool       Returns TRUE if bucket does exist and user has permission to access it or false if not.
     */
    public function isAllowed($bucketName)
    {
        return $this->getS3()->getApiHandler()->headBucket($bucketName) == 200;
    }

    /**
     * PUT Bucket cors action
     *
     * Sets the cors configuration for your bucket. If the configuration exists, Amazon S3 replaces it.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @param   string            $cors       A XML document that defines Cors configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setCors($bucketName, $cors)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->setBucketCors($bucketName, $cors);
    }

    /**
     * PUT Bucket lifecycle action
     *
     * Sets the lifecycle configuration for your bucket.
     * If the configuration exists, Amazon S3 replaces it.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @param   string            $lifecycle  A XML document that defines lifecycle configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setLifecycle($bucketName, $lifecycle)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->setBucketLifecycle($bucketName, $lifecycle);
    }

    /**
     * PUT Bucket policy action
     *
     * This implementation of the GET operation uses the policy subresource to return the policy of a specified
     * bucket. To use this operation, you must have GetPolicy permissions on the specified bucket, and you
     * must be the bucket owner.
     *
     * @param   BucketData|string $bucketName A bucket name.
     * @param   string            $policy     A JSON document that defines policy configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setPolicy($bucketName, $policy)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->setBucketPolicy($bucketName, $policy);
    }

    /**
     * PUT Bucket logging action
     *
     * @param   BucketData|string $bucketName          A bucket name.
     * @param   string            $bucketLoggingStatus A XML Document which describes configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setLogging($bucketName, $bucketLoggingStatus)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->setBucketLogging($bucketName, $bucketLoggingStatus);
    }

    /**
     * PUT Bucket notification action
     *
     * This implementation of the PUT operation uses the notification subresource to enable notifications
     * of specified events for a bucket
     *
     * @param   BucketData|string $bucketName   A bucket name.
     * @param   string            $notification A XML Document which describes configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setNotification($bucketName, $notification)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->setBucketNotification($bucketName, $notification);
    }

    /**
     * PUT Bucket tagging action
     *
     * This implementation of the PUT operation uses the tagging subresource to add a set of tags to an
     * existing bucket.
     *
     * @param   BucketData|string $bucketName   A bucket name.
     * @param   string            $tagging      A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setTagging($bucketName, $tagging)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->setBucketTagging($bucketName, $tagging);
    }

    /**
     * PUT Bucket requestPayment action
     *
     * This implementation of the PUT operation uses the requestPayment subresource to set the request
     * payment configuration of a bucket.
     *
     * @param   BucketData|string $bucketName     A bucket name.
     * @param   string     $requestPayment A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setRequestPayment($bucketName, $requestPayment)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->setBucketRequestPayment($bucketName, $requestPayment);
    }

    /**
     * PUT Bucket website action
     *
     * This implementation of the PUT operation uses the website subresource to set the request
     * website configuration of a bucket.
     *
     * @param   BucketData|string $bucketName     A bucket name.
     * @param   string     $website        A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setWebsite($bucketName, $website)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->setBucketWebsite($bucketName, $website);
    }

    /**
     * PUT Object action
     *
     * This implementation of the PUT operation adds an object to a bucket.You must have WRITE permissions
     * on a bucket to add an object to it.
     *
     * @param   BucketData|string   $bucketName     A bucket name.
     * @param   string              $objectName     An object name.
     * @param   string|\SplFileInfo $contentFile    File content of file that should be uploaded.
     *                                              If you provide a path to the file you should pass SplFileInfo object.
     * @param   array               $requestHeaders optional Request headers
     * @return  ClientResponseInterface Returns response on success or throws an exception
     * @throws  S3Exception
     */
    public function addObject($bucketName, $objectName, $contentFile, array $requestHeaders = null)
    {
        if ($bucketName instanceof BucketData) {
            $bucketName = $bucketName->bucketName;
        }
        return $this->getS3()->getApiHandler()->addObject($bucketName, $objectName, $contentFile, $requestHeaders);
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
     * @param   BucketData|string     $bucketName A bucket name.
     * @param   string     $objectName A object name.
     * @param   string     $versionId  optional To remove a specific version of the object it must be used.
     * @param   string     $xAmfMfa    optional The value is the concatenation of the authentication device's
     *                                 serial number, a space, and the value displayed on your authentication device.
     * @return  ClientResponseInterface Returns response on success or throws an exception
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteObject($bucketName, $objectName, $versionId = null, $xAmfMfa = null)
    {
        return $this->getS3()->object->delete($bucketName, $objectName, $versionId, $xAmfMfa);
    }

    /**
     * Gets BucketData from Entity Storage.
     *
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string     $bucketName A bucket name.
     * @return  BucketData Returns BucketData from entity storage if it exists or null otherwise.
     */
    public function get($bucketName)
    {
        return $this->getS3()->getEntityManager()->getRepository('S3:Bucket')->find($bucketName);
    }

    /**
     * Gets Objects list from an Entity Storage that belong to specified bucket
     *
     * @param  string    $bucketName
     * @return ArrayObject Returns list of ObjectData
     */
    public function getObjectsFromStorage($bucketName)
    {
        return $this->getS3()->getEntityManager()->getRepository('S3:Object')->findBy(array('bucketName' => $bucketName));
    }
}