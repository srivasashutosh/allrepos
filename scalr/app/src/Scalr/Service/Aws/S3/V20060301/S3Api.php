<?php
namespace Scalr\Service\Aws\S3\V20060301;

use Scalr\Service\Aws\S3\DataType\CopyObjectResponseData;
use Scalr\Service\Aws\AbstractApi;
use Scalr\Service\Aws\Client\ClientResponseInterface;
use Scalr\Service\Aws\S3\DataType\GranteeData;
use Scalr\Service\Aws\S3\DataType\PermissionData;
use Scalr\Service\Aws\S3\DataType\AccessControlGrantData;
use Scalr\Service\Aws\S3\DataType\AccessControlGrantList;
use Scalr\Service\Aws\S3\DataType\AccessControlPolicyData;
use Scalr\Service\Aws;
use Scalr\Service\Aws\S3\DataType\ObjectData;
use Scalr\Service\Aws\S3\DataType\ObjectList;
use Scalr\Service\Aws\S3\DataType\OwnerData;
use Scalr\Service\Aws\S3\DataType\BucketData;
use Scalr\Service\Aws\S3\DataType\BucketList;
use Scalr\Service\Aws\Client\QueryClient\S3QueryClient;
use Scalr\Service\Aws\Client\QueryClientException;
use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\S3;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\EntityManager;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Client\QueryClientResponse;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientInterface;
use \DateTimeZone;
use \DateTime;

/**
 * S3 Api messaging.
 *
 * Implements S3 Low-Level API Actions.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     12.11.2012
 */
class S3Api extends AbstractApi
{

    /**
     * @var S3
     */
    protected $s3;

    /**
     * @var string
     */
    protected $versiondate;

    /**
     * List allowed x-amz headers for the ACL requests
     *
     * @var array
     */
    protected static $xamzAclAllowedHeaders = array(
        'x-amz-acl',
        'x-amz-grant-read',
        'x-amz-grant-write',
        'x-amz-grant-read-acp',
        'x-amz-grant-write-acp',
        'x-amz-grant-full-control',
    );

    protected static $rangeHeaders = array(
        'Range',
        'If-Modified-Since',
        'If-Unmodified-Since',
        'If-Match',
        'If-None-Match',
    );

    /**
     * Constructor
     *
     * @param   S3                $s3           S3 instance
     * @param   S3QueryClient     $client       Client Interface
     */
    public function __construct(S3 $s3, S3QueryClient $client)
    {
        $this->s3 = $s3;
        $this->client = $client;
        $this->versiondate = preg_replace('#^.+V(\d{4})(\d{2})(\d{2})$#', '\\1-\\2-\\3', __NAMESPACE__);
    }

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
    public function listAllMyBuckets()
    {
        $result = null;
        $options = array();
        $response = $this->client->call('GET', $options, '/');
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new BucketList();
            $result->setS3($this->s3);
            $em = $this->getEntityManager();
            if (isset($sxml->Owner->ID)) {
                $ownerid = (string)$sxml->Owner->ID;
                //Tries to get an owner object from an entity storage.
                $owner = $em->getRepository('S3:Owner')->find($ownerid);
                if ($owner === null) {
                    $owner = new OwnerData();
                    $owner->ownerid = $ownerid;
                    $owner->displayName = (string)$sxml->Owner->DisplayName;
                    $em->attach($owner);
                }
                $result->setOwner($owner);
            }
            if (!empty($sxml->Buckets->Bucket)) {
                foreach ($sxml->Buckets->Bucket as $v) {
                    $bucket = new BucketData();
                    $bucket->bucketName = (string) $v->Name;
                    $bucket->creationDate = new \DateTime((string) $v->CreationDate, new \DateTimeZone('UTC'));
                    $result->append($bucket);
                    $em->attach($bucket);
                    unset($bucket);
                }
            }
        }
        return $result;
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
        $result = null;
        $options = array(
            '_subdomain' => (string) $bucketName,
        );
        $aQueryString = array();
        if ($delimiter !== null) {
            $aQueryString[] = 'delimiter=' . self::escape($delimiter);
        }
        if ($marker !== null) {
            $aQueryString[] = 'marker=' . self::escape($marker);
        }
        if ($maxKeys !== null) {
            $aQueryString[] = 'max-keys=' . self::escape($maxKeys);
        }
        if ($prefix !== null) {
            $aQueryString[] = 'prefix=' . self::escape($prefix);
        }
        $response = $this->client->call('GET', $options, '/' . (!empty($aQueryString) ? '?' . join('&', $aQueryString) : ''));
        if ($response->getError() === false) {
            //Success
            $em = $this->getEntityManager();
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new ObjectList();
            $result->setS3($this->s3);
            $result->setBucketName((string)$sxml->Name);
            if (!empty($sxml->Marker)) {
                $result->setMarker((string)$sxml->Marker);
            }
            if (!empty($sxml->Contents)) {
                foreach ($sxml->Contents as $v) {
                    $objectName = (string) $v->Key;
                    //Tries to find object in storage
                    $object = $this->s3->object->get(array((string)$bucketName, $objectName));
                    if ($object instanceof ObjectData) {
                        //Resets object's properties
                        $object->resetObject();
                    } else {
                        //Creates a new one.
                        $object = new ObjectData();
                        $na = true;
                    }
                    $ownerid = (string) $v->Owner->ID;
                    if (!empty($ownerid)) {
                        $owner = $em->getRepository('S3:Owner')->find($ownerid);
                        if ($owner === null) {
                            $owner = new OwnerData();
                            $owner->ownerid = $ownerid;
                            $owner->displayName = (string) $v->Owner->DisplayName;
                            $em->attach($owner);
                        }
                        $object->setOwner($owner);
                        unset($owner);
                    }
                    $object
                        ->setBucketName((string)$bucketName)
                        ->setObjectName($objectName)
                        ->setETag((string)$v->ETag)
                        ->setSize((string)$v->Size)
                        ->setStorageClass((string)$v->StorageClass)
                        ->setLastModified(new \DateTime((string)$v->LastModified, new \DateTimeZone('UTC')))
                    ;
                    if (isset($na)) {
                        //For the new object we need to attach it
                        $em->attach($object);
                        unset($na);
                    }
                    $result->append($object);
                    unset($object);
                }
            }
        }
        return $result;
    }

    /**
     * Escapes  object name
     *
     * @param  string $objectName
     * @return string Returns objectName without leading slash and url encoded
     */
    protected static function escapeObjectName($objectName)
    {
        $objectName = preg_replace('#^/+#', '', $objectName);
        return self::escape($objectName);
    }

    /**
     * Gets an object
     *
     * This implementation of the GET operation retrieves objects from Amazon S3.
     * To use GET, you must have READ access to the object.
     * If you grant READ access to the anonymous user, you can return the object
     * without using an authorization header.
     *
     * @param   string     $bucketName     An bucket name.
     * @param   string     $objectName     An object key name.
     * @param   array      $requestPars    optional An additional request query parameters. It accepts only allowed params.
     * @param   array      $requestHeaders opitional An optional request headers. It accepts only allowed headers.
     * @return  ClientResponseInterface    Returns response
     * @throws  S3Exception
     * @throws  ClientException
     */
    public function getObject($bucketName, $objectName, array $requestPars = null, array $requestHeaders = null)
    {
        $options = array(
            '_subdomain' => (string) $bucketName,
        );
        $allowedRequestPars = array(
            'versionId',
            'response-content-type',
            'response-content-language',
            'response-expires',
            'response-cache-control',
            'response-content-disposition',
            'response-content-encoding',
        );
        if (!empty($requestPars)) {
            $requestPars = $this->getFilteredArray($allowedRequestPars, $requestPars);
            $aQueryString = array();
            foreach ($requestPars as $k => $v) {
                $aQueryString[] = sprintf('%s=%s', $k, self::escape($v));
            }
        }
        $allowedRequestHeaders = self::$rangeHeaders;
        if (!empty($requestHeaders)) {
            $requestHeaders = $this->getFilteredArray($allowedRequestHeaders, $requestHeaders);
            $options = array_merge($options, $requestHeaders);
        }
        $path = '/' . self::escapeObjectName($objectName)
          . (!empty($aQueryString) ? (strpos($objectName, '?') === false ? '?' : '&') . join('&', $aQueryString) : '');

        $response = $this->client->call('GET', $options, $path);

        return $response->getError() ?: $response;
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
    public function deleteBucket($bucketName)
    {
        $result = false;
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        $response = $this->client->call('DELETE', $options, '/');
        if ($response->getError() === false) {
            $result = true;
            $bucket = $this->s3->bucket->get((string)$bucketName);
            if ($bucket instanceof BucketData) {
                $this->getEntityManager()->detach($bucket);
            }
        }
        return $result;
    }

    /**
     * Deletes the specified subresource for the bucket.
     *
     * @param   string      $bucketName   A bucket name.
     * @param   string      $subresource  A subresource name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteBucketSubresource($bucketName, $subresource)
    {
        $result = false;
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        $allowed = S3QueryClient::getAllowedSubResources();
        if (!in_array($subresource, $allowed)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid subresource "%s" for the bucket. Allowed values are "%s"', $subresource,
                join('", "', $allowed)
            ));
        }
        $response = $this->client->call('DELETE', $options, '/?' . $subresource);
        if ($response->getError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * DELETE Bucket cors action
     *
     * Deletes the cors configuration information set for the bucket.
     *
     * @param   string      $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteBucketCors($bucketName)
    {
        return $this->deleteBucketSubresource($bucketName, 'cors');
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
     * @param   string      $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteBucketLifecycle($bucketName)
    {
        return $this->deleteBucketSubresource($bucketName, 'lifecycle');
    }

    /**
     * DELETE Bucket policy action
     *
     * This implementation of the DELETE operation uses the policy subresource to delete the policy on a
     * specified bucket. To use the operation, you must have DeletePolicy permissions on the specified
     * bucket and be the bucket owner.
     *
     * @param   string      $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteBucketPolicy($bucketName)
    {
        return $this->deleteBucketSubresource($bucketName, 'policy');
    }

    /**
     * DELETE Bucket tagging action
     *
     * This implementation of the DELETE operation uses the tagging subresource to remove a tag set from
     * the specified bucket.
     * To use this operation, you must have permission to perform the s3:PutBucketTagging action. By
     * default, the bucket owner has this permission and can grant this permission to others.
     *
     * @param   string      $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteBucketTagging($bucketName)
    {
        return $this->deleteBucketSubresource($bucketName, 'tagging');
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
     * @param   string      $bucketName A bucket name.
     * @return  bool        Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function deleteBucketWebsite($bucketName)
    {
        return $this->deleteBucketSubresource($bucketName, 'website');
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
    public function createBucket($bucketName, $bucketRegion = Aws::REGION_US_EAST_1, array $requestHeaders = null)
    {
        $result = null;
        $options = array(
            '_subdomain' => (string) $bucketName,
        );
        if (!empty($requestHeaders)) {
            $requestHeaders = $this->getFilteredArray(self::$xamzAclAllowedHeaders, $requestHeaders);
            $options = array_merge($options, $requestHeaders);
        }
        $bucketLocation = $this->_getBucketLocationXml($bucketRegion);
        if ($bucketLocation === null) {
            $options['Content-Length'] = 0;
        } else {
            $options['_putData'] = $bucketLocation;
        }
        $response = $this->client->call('PUT', $options, '/');
        if ($response->getError() === false) {
            $result = new BucketData();
            $result->setS3($this->s3);
            $result->bucketName = (string) $bucketName;
            $result->creationDate = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->getEntityManager()->attach($result);
        }
        return $result;
    }

    /**
     * Gets Location XML string
     *
     * @param   string     $bucketRegion A bucket region
     * @return  string     Returns Location XML
     */
    protected function _getBucketLocationXml($bucketRegion)
    {
        if (strpos($bucketRegion, 'eu-') === 0 && $bucketRegion != Aws::REGION_EU_WEST_1) {
            $bucketRegion = 'EU';
        } elseif ($bucketRegion == Aws::REGION_US_EAST_1) {
            $bucketRegion = null;
        }
        if ($bucketRegion !== null) {
            $putData =
                '<CreateBucketConfiguration xmlns="http://s3.amazonaws.com/doc/' . $this->versiondate . '/">'
              . '<LocationConstraint>' . htmlspecialchars($bucketRegion) . '</LocationConstraint>'
              . '</CreateBucketConfiguration>'
            ;
        } else {
            $putData = null;
        }
        return $putData;
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
    public function getBucketAcl($bucketName)
    {
        $result = null;
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        $response = $this->client->call('GET', $options, '/?acl');
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->Owner)) {
                throw new S3Exception('Unexpected response! ' . $response->getRawContent());
            }
            $list = new AccessControlGrantList();
            $result = new AccessControlPolicyData();
            $result->setS3($this->s3);
            $result->setOriginalXml($response->getRawContent());
            $result->setBucketName((string)$bucketName);
            $result->setOwner(new OwnerData((string)$sxml->Owner->ID, (string)$sxml->Owner->DisplayName));
            $result->setAccessControlList($list);
            if (!empty($sxml->AccessControlList->Grant)) {
                /* @var $sgrant \SimpleXMLElement */
                foreach ($sxml->AccessControlList->Grant as $sgrant) {
                    $attr = $sgrant->Grantee->attributes('xsi', true);
                    $grantee = new GranteeData();
                    $grantee->type = (string) $attr->type;
                    if (isset($sgrant->Grantee->URI)) {
                        $grantee->uri = (string) $sgrant->Grantee->URI;
                    } else {
                        $grantee->granteeId = (string) $sgrant->Grantee->ID;
                        $grantee->displayName = (string) $sgrant->Grantee->DisplayName;
                    }
                    $grant = new AccessControlGrantData();
                    $grant->setPermission(new PermissionData((string)$sgrant->Permission));
                    $grant->setGrantee($grantee);
                    $list->append($grant);
                    unset($grant);
                    unset($grantee);
                }
            }
        }
        return $result;
    }

    /**
     * Gets a object ACL action.
     *
     * This implementation of the GET operation uses the acl subresource to return the access control list (ACL)
     * of an object. To use this operation, you must have READ_ACP access to the object.
     *
     * @param   string       $bucketName A bucket name.
     * @param   string       $objectName An object name.
     * @return  AccessControlPolicyData Returns object which describes ACL for the object.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getObjectAcl($bucketName, $objectName)
    {
        $result = null;
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        $response = $this->client->call('GET', $options, sprintf('/%s?acl', self::escapeObjectName($objectName)));
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->Owner)) {
                throw new S3Exception('Unexpected response! ' . $response->getRawContent());
            }
            $list = new AccessControlGrantList();
            $result = new AccessControlPolicyData();
            $result->setS3($this->s3);
            $result->setOriginalXml($response->getRawContent());
            $result->setBucketName((string)$bucketName);
            $result->setObjectName((string)$objectName);
            $result->setOwner(new OwnerData((string)$sxml->Owner->ID, (string)$sxml->Owner->DisplayName));
            $result->setAccessControlList($list);
            if (!empty($sxml->AccessControlList->Grant)) {
                /* @var $sgrant \SimpleXMLElement */
                foreach ($sxml->AccessControlList->Grant as $sgrant) {
                    $attr = $sgrant->Grantee->attributes('xsi', true);
                    $grantee = new GranteeData();
                    $grantee->type = (string) $attr->type;
                    if (isset($sgrant->Grantee->URI)) {
                        $grantee->uri = (string) $sgrant->Grantee->URI;
                    } else {
                        $grantee->granteeId = (string) $sgrant->Grantee->ID;
                        $grantee->displayName = (string) $sgrant->Grantee->DisplayName;
                    }
                    $grant = new AccessControlGrantData();
                    $grant->setPermission(new PermissionData((string)$sgrant->Permission));
                    $grant->setGrantee($grantee);
                    $list->append($grant);
                    unset($grant);
                    unset($grantee);
                }
            }
        }
        return $result;
    }

    /**
     * Gets a specified bucket subresource
     *
     * @param   string     $bucketName   A bucket name.
     * @param   string     $subresource  A bucket subresource name.
     * @return  ClientResponseInterface Returns response
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getBucketSubresource($bucketName, $subresource)
    {
        $xml = '';
        $options = array(
            '_subdomain' => (string) $bucketName,
        );
        $allowed = S3QueryClient::getAllowedSubResources();
        if (!in_array($subresource, $allowed)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid subresource "%s" for the bucket. Allowed list are "%s"',
                $subresource, join('", "', $allowed)
            ));
        }
        $response = $this->client->call('GET', $options, '/?' . $subresource);
        return $response->getError() === false ? $response : null;
    }

    /**
     * GET Bucket cors action
     *
     * Returns the cors configuration information set for the bucket.
     *
     * @param   string      $bucketName A bucket name.
     * @return  string      Returns CORSConfiguration XML Document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getBucketCors($bucketName)
    {
        $response = $this->getBucketSubresource($bucketName, 'cors');
        return $response->getRawContent();
    }

    /**
     * GET Bucket lifecycle action
     *
     * Returns the lifecycle configuration information set on the bucket.
     *
     * @param   string      $bucketName A bucket name.
     * @return  string      Returns LifecycleConfiguration XML Document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getBucketLifecycle($bucketName)
    {
        $response = $this->getBucketSubresource($bucketName, 'lifecycle');
        return $response->getRawContent();
    }

    /**
     * GET Bucket policy action
     *
     * This implementation of the GET operation uses the policy subresource to return the policy of a specified
     * bucket. To use this operation, you must have GetPolicy permissions on the specified bucket, and you
     * must be the bucket owner.
     *
     * @param   string      $bucketName A bucket name.
     * @return  string      Returns bucket policy string (json encoded)
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getBucketPolicy($bucketName)
    {
        $response = $this->getBucketSubresource($bucketName, 'policy');
        return $response->getRawContent();
    }

    /**
     * GET Bucket location action
     *
     * This implementation of the GET operation uses the location subresource to return a bucket's Region.
     * You set the bucket's Region using the LocationContraint request parameter in a PUT Bucket request.
     *
     * @param   string      $bucketName A bucket name.
     * @return  string      Returns bucket location
     *                      Valid Values: EU | eu-west-1 | us-west-1 | us-west-2 | ap-southeast-1 |
     *                      ap-northeast-1 | sa-east-1 | empty string (for the US Classic Region)
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getBucketLocation($bucketName)
    {
        $response = $this->getBucketSubresource($bucketName, 'location');
        $sxml = simplexml_load_string($response->getRawContent());
        $location = (string) $sxml;
        return $location;
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
    public function getBucketLogging($bucketName)
    {
        $response = $this->getBucketSubresource($bucketName, 'logging');
        return $response->getRawContent();
    }

    /**
     * GET Bucket notification action
     *
     * This implementation of the GET operation uses the notification subresource to return the notification
     * configuration of a bucket.
     *
     * @param   string      $bucketName A bucket name.
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getBucketNotification($bucketName)
    {
        $response = $this->getBucketSubresource($bucketName, 'notification');
        return $response->getRawContent();
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
    public function getBucketTagging($bucketName)
    {
        $response = $this->getBucketSubresource($bucketName, 'tagging');
        return $response->getRawContent();
    }

    /**
     * GET Bucket requestPayment action
     *
     * This implementation of the GET operation uses the requestPayment subresource to return the request
     * payment configuration of a bucket.
     *
     * @param   string      $bucketName A bucket name.
     * @return  string      Returns XML document
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getBucketRequestPayment($bucketName)
    {
        $response = $this->getBucketSubresource($bucketName, 'requestPayment');
        return $response->getRawContent();
    }

    /**
     * HEAD Bucket action.
     *
     * This operation is useful to determine if a bucket exists and you have permission to access it.
     * The operation returns a 200 OK if the bucket exists and you have permission to access it.
     * Otherwise, the operation might return responses such as 404 Not Found and 403 Forbidden.
     *
     * @param   string     $bucketName A bucket name.
     * @return  int        Returns 200, 404 or 403 depends on bucket state.
     */
    public function headBucket($bucketName)
    {
        $options = array(
            '_subdomain' => (string) $bucketName,
        );
        $response = $this->client->call('HEAD', $options, '/');
        return $response->getResponseCode();
    }

    /**
     * PUT Bucket ACL action.
     *
     * @param   string        $bucketName A bucket name.
     * @param   string|array  $aclset     XML Document or array of x-amz headers
     * @return  bool          Returns True on success of false if failures.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketAcl($bucketName, $aclset)
    {
        $result = false;
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        if (is_string($aclset)) {
            $options['_putData'] = $aclset;
        } elseif (is_array($aclset) && !empty($aclset)) {
            $requestHeaders = $this->getFilteredArray(self::$xamzAclAllowedHeaders, $aclset);
            $options = array_merge($options, $requestHeaders);
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid aclset option'));
        }
        $response = $this->client->call('PUT', $options, '/?acl');
        if ($response->getError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * PUT Object ACL action.
     *
     * @param   string        $bucketName A bucket name.
     * @param   string        $objectName A bucket name.
     * @param   string|array  $aclset     XML Document or array of x-amz headers
     * @return  bool          Returns True on success of false if failures.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setObjectAcl($bucketName, $objectName, $aclset)
    {
        $result = false;
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        if (is_string($aclset)) {
            $options['_putData'] = $aclset;
        } elseif (is_array($aclset) && !empty($aclset)) {
            $requestHeaders = $this->getFilteredArray(self::$xamzAclAllowedHeaders, $aclset);
            $options = array_merge($options, $requestHeaders);
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid aclset option'));
        }
        $response = $this->client->call('PUT', $options, sprintf('/%s?acl', self::escapeObjectName($objectName)));
        if ($response->getError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Puts bucket subresource
     *
     * @param   string     $bucketName  A bucket name.
     * @param   string     $subresource A subresource name.
     * @param   string     $putData     Put data. (xml or json formatted string, depends on subresource configuration)
     * @return  bool       Returns true on success or false if failure
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketSubresource($bucketName, $subresource, $putData)
    {
        $result = false;
        $options = array(
            '_subdomain' => (string)$bucketName,
            '_putData'   => (string)$putData,
        );
        $allowed = S3QueryClient::getAllowedSubResources();
        if (!in_array($subresource, $allowed)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid subresource "%s" for the bucket. Allowed list are "%s"',
                $subresource, join('", "', $allowed)
            ));
        }
        $response = $this->client->call('PUT', $options, '/?' . $subresource);
        if ($response->getError() === false) {
            $result = true;
        }
        return $result;
    }

    /**
     * PUT Bucket cors action
     *
     * Sets the cors configuration for your bucket. If the configuration exists, Amazon S3 replaces it.
     *
     * @param   string     $bucketName A bucket name.
     * @param   string     $cors       A XML document that defines Cors configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketCors($bucketName, $cors)
    {
        return $this->setBucketSubresource($bucketName, 'cors', $cors);
    }

    /**
     * PUT Bucket lifecycle action
     *
     * Sets the lifecycle configuration for your bucket.
     * If the configuration exists, Amazon S3 replaces it.
     *
     * @param   string     $bucketName A bucket name.
     * @param   string     $lifecycle  A XML document that defines lifecycle configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketLifecycle($bucketName, $lifecycle)
    {
        return $this->setBucketSubresource($bucketName, 'lifecycle', $lifecycle);
    }

    /**
     * PUT Bucket policy action
     *
     * This implementation of the GET operation uses the policy subresource to return the policy of a specified
     * bucket. To use this operation, you must have GetPolicy permissions on the specified bucket, and you
     * must be the bucket owner.
     *
     * @param   string     $bucketName A bucket name.
     * @param   string     $policy     A JSON document that defines policy configuration.
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketPolicy($bucketName, $policy)
    {
        return $this->setBucketSubresource($bucketName, 'policy', $policy);
    }

    /**
     * PUT Bucket logging action
     *
     * @param   string     $bucketName          A bucket name.
     * @param   string     $bucketLoggingStatus A XML Document which describes configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketLogging($bucketName, $bucketLoggingStatus)
    {
        return $this->setBucketSubresource($bucketName, 'logging', $bucketLoggingStatus);
    }

    /**
     * PUT Bucket notification action
     *
     * This implementation of the PUT operation uses the notification subresource to enable notifications
     * of specified events for a bucket
     *
     * @param   string     $bucketName   A bucket name.
     * @param   string     $notification A XML Document which describes configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketNotification($bucketName, $notification)
    {
        return $this->setBucketSubresource($bucketName, 'notification', $notification);
    }

    /**
     * PUT Bucket tagging action
     *
     * This implementation of the PUT operation uses the tagging subresource to add a set of tags to an
     * existing bucket.
     *
     * @param   string     $bucketName   A bucket name.
     * @param   string     $tagging      A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketTagging($bucketName, $tagging)
    {
        return $this->setBucketSubresource($bucketName, 'tagging', $tagging);
    }

    /**
     * PUT Bucket requestPayment action
     *
     * This implementation of the PUT operation uses the requestPayment subresource to set the request
     * payment configuration of a bucket.
     *
     * @param   string     $bucketName     A bucket name.
     * @param   string     $requestPayment A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketRequestPayment($bucketName, $requestPayment)
    {
        return $this->setBucketSubresource($bucketName, 'requestPayment', $requestPayment);
    }

    /**
     * PUT Bucket website action
     *
     * This implementation of the PUT operation uses the website subresource to set the request
     * website configuration of a bucket.
     *
     * @param   string     $bucketName     A bucket name.
     * @param   string     $website        A XML Document which defines configuration
     * @return  bool       Returns true on succes or false if failure.
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function setBucketWebsite($bucketName, $website)
    {
        return $this->setBucketSubresource($bucketName, 'website', $website);
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
     * @param   string     $bucketName A bucket name.
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
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        if ($xAmfMfa !== null) {
            $options['x-amz-mfa'] = (string) $xAmfMfa;
        }
        $response = $this->client->call('DELETE', $options, '/' . self::escapeObjectName($objectName) . ($versionId !== null ? '?versionId=' . self::escape($versionId) : ''));
        if ($response->getError() === false) {
            //Whether request is not to remove specific version.
            if ($versionId === null) {
                //Whether object exists.
                $object = $this->s3->object->get(array((string)$bucketName, $objectName));
                if ($object !== null) {
                    //If exists we should detach it from the storage
                    $this->getEntityManager()->detach($object);
                }
            }
        }
        return $response;
    }

    /**
     * PUT Object action
     *
     * This implementation of the PUT operation adds an object to a bucket.You must have WRITE permissions
     * on a bucket to add an object to it.
     *
     * @param   string              $bucketName     A bucket name.
     * @param   string              $objectName     An object name.
     * @param   string|\SplFileInfo $contentFile    File content of file that should be uploaded.
     *                                              If you provide a path to the file you should pass SplFileInfo object.
     * @param   array               $requestHeaders optional Request headers
     * @return  ClientResponseInterface Returns response on success or throws an exception
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function addObject($bucketName, $objectName, $contentFile, array $requestHeaders = null)
    {
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        $allowedRequestHeaders = array(
            'Cache-Control',
            'Content-Disposition',
            'Content-Encoding',
            'Content-Length',
            'Content-MD5',
            'Content-Type',
            'Expect',
            'Expires',
            'x-amz-meta-', // this will allow all subpatterns
            'x-amz-server-side-encryption',
            'x-amz-storage-class',
            'x-amz-website-redirect-location'
        );
        if (!empty($requestHeaders)) {
            $requestHeaders = $this->getFilteredArray(array_merge($allowedRequestHeaders, self::$xamzAclAllowedHeaders), $requestHeaders);
            $options = array_merge($options, $requestHeaders);
        }
        if ($contentFile instanceof \SplFileInfo) {
            if (!$contentFile->isFile()) {
                throw new S3Exception(sprintf('File "%s" does not exist', $contentFile->getPathname()));
            }
            $options['_putFile'] = $contentFile->getPathname();
        } else {
            $options['_putData'] = $contentFile;
        }
        $response = $this->client->call(
            'PUT', $options, sprintf('/%s', self::escapeObjectName($objectName))
        );
        return $response->getError() ?: $response;
    }

    /**
     * Copy Object action
     *
     * This implementation of the PUT operation creates a copy of an object that is already stored in Amazon S3.
     * A PUT copy operation is the same as performing a GET and then a PUT
     *
     * @param   string     $scrBucketName  Source bucket name.
     * @param   string     $srcObject      Source object name.
     * @param   string     $destBucketName Destination bucket name.
     * @param   string     $destObject     Destination object name.
     * @param   array      $requestHeaders optional Request headers array looks like array(header => value)
     * @param   string     $versionId      optional Specifies source version id.
     * @return  CopyObjectResponseData     Returns CopyObjectResponseData
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function copyObject($scrBucketName, $srcObject, $destBucketName, $destObject, array $requestHeaders = null, $versionId = null)
    {
        $result = null;
        $options = array(
            '_subdomain'        => (string)$destBucketName,
            'x-amz-copy-source' => sprintf("/%s/%s" . ($versionId !== null ? "?versionId=%s" : ''), $scrBucketName, self::escapeObjectName($srcObject), self::escape($versionId)),
        );
        $allowedHeaders = array(
            'x-amz-metadata-directive',
            'x-amz-copy-source-if-match',
            'x-amz-copy-source-if-none-match',
            'x-amz-copy-source-if-unmodified-since',
            'x-amz-copy-source-if-modified-since',
            'x-amz-server-side-encryption',
            'x-amz-storage-class',
            'x-amz-website-redirect-location'
        );
        $allowedHeaders = array_merge($allowedHeaders, self::$xamzAclAllowedHeaders);
        if (!empty($requestHeaders)) {
            $requestHeaders = $this->getFilteredArray($allowedHeaders, $requestHeaders);
            $options = array_merge($options, $requestHeaders);
        }
        $response = $this->getClient()->call(
            'PUT', $options, sprintf('/%s', self::escapeObjectName($destObject))
        );
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new CopyObjectResponseData();
            $result->setS3($this->s3);
            $result
                ->setBucketName((string)$destBucketName)
                ->setObjectName((string)$destObject)
                ->setHeaders($response->getHeaders())
                ->setETag((string)$sxml->ETag)
                ->setLastModified(new DateTime((string)$sxml->LastModified, new DateTimeZone('UTC')))
            ;
        }
        return $result;
    }

    /**
     * HEAD Object action
     *
     * The HEAD operation retrieves metadata from an object without returning the object itself. This operation
     * is useful if you're only interested in an object's metadata. To use HEAD, you must have READ access to
     * the object.
     * A HEAD request has the same options as a GET operation on an object. The response is identical to the
     * GET response, except that there is no response body.
     *
     * @param   string     $bucketName A bucket name.
     * @param   string     $objectName An object name.
     * @param   array      $requestHeaders optional Range headers looks like array(header => value)
     * @return  ClientResponseInterface Returns response object on success or throws an exception
     * @throws  ClientException
     * @throws  S3Exception
     */
    public function getObjectMetadata($bucketName, $objectName, array $requestHeaders = null)
    {
        $result = null;
        $options = array(
            '_subdomain' => (string)$bucketName,
        );
        $allowedHeaders = self::$rangeHeaders;
        if (!empty($requestHeaders)) {
            $requestHeaders = $this->getFilteredArray($allowedHeaders, $requestHeaders);
            $options = array_merge($options, $requestHeaders);
        }
        $response = $this->getClient()->call(
            'HEAD', $options, sprintf('/%s', self::escapeObjectName($objectName))
        );
        return $response->getError() ?: $response;
    }

    /**
     * Formats output for the given xml document
     *
     * @param   string   $xml XML Document
     * @return  string   Returns formatted xml document
     */
    protected static function getFormattedXml($xml)
    {
        $dom = new \DOMDocument();
        $dom->formatOutput = true;
        $dom->loadXml($xml);
        return $dom->saveXML();
    }

    /**
     * Filter values with allowed keys.
     *
     * @param   array        $allowedKeys     An list of allowed keys.
     * @param   array        $values          An associative array of the values looks like array(key => val).
     * @param   bool         $caseInsensitive optional Case Insencitive comparison
     * @return  array        Returns filtered array.
     */
    protected static function getFilteredArray(array $allowedKeys, array $values, $caseInsensitive = true)
    {
        $arr = array();
        if ($caseInsensitive) {
            $allowedKeys = array_combine(array_map('strtolower', $allowedKeys), $allowedKeys);
        } else {
            $allowedKeys = array_combine($allowedKeys, $allowedKeys);
        }
        $regexp = array();
        foreach ($allowedKeys as $k => $v) {
            if (substr($v, -1) == '-') {
                $regexp[] = '#^' . preg_quote($k, "#") . '.+$#i';
                unset($allowedKeys[$k]);
            }
        }
        foreach ($values as $k => $v) {
            if ($caseInsensitive === true) {
                $k = strtolower($k);
            }
            if (isset($allowedKeys[$k])) {
                $arr[$allowedKeys[$k]] = $v;
            } else if (!empty($regexp)) {
                foreach ($regexp as $reg) {
                    if (preg_match($reg, $k)) {
                        $arr[$k] = $v;
                        break 1;
                    }
                }
            }
        }
        return $arr;
    }

    /**
     * Gets an entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->s3->getEntityManager();
    }
}