<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Client\ClientResponseInterface;
use Scalr\Service\Aws\S3\DataType\AccessControlPolicyData;
use Scalr\Service\Aws;
use Scalr\Service\Aws\S3\DataType\ObjectData;
use Scalr\Service\Aws\S3;
use Scalr\Tests\Service\AwsTestCase;
use \SplFileInfo;

/**
 * Amazon A3 Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     12.11.2012
 */
class S3Test extends AwsTestCase
{

    const CLASS_S3 = 'Scalr\\Service\\Aws\\S3';

    const CLASS_S3_BUCKET_DATA = "Scalr\\Service\\Aws\\S3\\DataType\\BucketData";

    const CLASS_S3_BUCKET_LIST = "Scalr\\Service\\Aws\\S3\\DataType\\BucketList";

    const CLASS_S3_OBJECT_DATA = "Scalr\\Service\\Aws\\S3\\DataType\\ObjectData";

    const CLASS_S3_OBJECT_LIST = "Scalr\\Service\\Aws\\S3\\DataType\\ObjectList";

    const CLASS_S3_OWNER_DATA = "Scalr\\Service\\Aws\\S3\\DataType\\OwnerData";

    const CLASS_S3_ACCESS_CONTROL_POLICY_DATA = "Scalr\\Service\\Aws\\S3\\DataType\\AccessControlPolicyData";

    const OBJECT_NAME_DUMMY = 'dummy/юникод';

    /**
     * @var S3
     */
    private $s3;

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isSkipFunctionalTests()) {
            $this->s3 = $this->getContainer()->aws->s3;
            $this->s3->enableEntityManager();
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->s3);
        parent::tearDown();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixturesDirectory()
     */
    public function getFixturesDirectory()
    {
        return parent::getFixturesDirectory() . '/S3';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixtureFilePath()
     */
    public function getFixtureFilePath($filename)
    {
        return $this->getFixturesDirectory() . '/' . S3::API_VERSION_CURRENT . '/' . $filename;
    }

    /**
     * Gets S3 class name
     *
     * @param   string   $suffix Suffix
     * @return  string
     */
    public function getS3ClassName($suffix)
    {
        return 'Scalr\\Service\\Aws\\S3\\' . $suffix;
    }

    /**
     * Gets S3 Mock
     *
     * @param    callback $callback
     * @return   S3       Returns S3 Mock class
     */
    public function getS3Mock($callback = null)
    {
        return $this->getServiceInterfaceMock('S3');
    }

    /**
     * @test
     */
    public function testFunctionalErrorMessageShouldContainAction()
    {
        $this->skipIfEc2PlatformDisabled();

        try {
            $object = $this->s3->object->create('unexistent-bucket', '- illegal name -', 'content');
            $this->assertTrue(false, 'ClientException must be thrown here.');
        } catch (ClientException $e) {
            $this->assertContains('Request AddObject failed.', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function testFunctionalS3 ()
    {
        $this->skipIfEc2PlatformDisabled();
        $client = $this->s3->getApiHandler()->getClient();

        $bucketList = $this->s3->bucket->getList();
        $this->assertInstanceOf(self::CLASS_S3_BUCKET_LIST, $bucketList);
        $this->assertInstanceOf(self::CLASS_S3, $bucketList->getS3());
        $this->assertInstanceOf(self::CLASS_S3_OWNER_DATA, $bucketList->getOwner());

        $bucket = $this->s3->bucket->get(self::getTestName('bucket'));
        if ($bucket !== null) {
            $list = $bucket->listObjects();
            /* @var $object ObjectData */
            foreach ($list as $object) {
                if ($object->objectName == self::getTestName(self::OBJECT_NAME_DUMMY)) {
                    $object->delete();
                }
            }
            unset($list);
            $bucket->delete();
            unset($bucket);
        }

        $bucket = $this->s3->bucket->get(self::getTestName('bucket-copy'));
        if ($bucket !== null) {
            $list = $bucket->listObjects();
            /* @var $object ObjectData */
            foreach ($list as $object) {
                if ($object->objectName == self::getTestName(self::OBJECT_NAME_DUMMY)) {
                    $object->delete();
                }
            }
            unset($list);
            $bucket->delete();
            unset($bucket);
        }

        //Tests creation of the bucket
        $bucket = $this->s3->bucket->create(self::getTestName('bucket'), Aws::REGION_AP_SOUTHEAST_1);
        $this->assertInstanceOf(self::CLASS_S3_BUCKET_DATA, $bucket);
        $this->assertInstanceOf(self::CLASS_S3, $bucket->getS3());
        $this->assertEquals(spl_object_hash($bucket), spl_object_hash($this->s3->bucket->get(self::getTestName('bucket'))));
        $this->assertEquals(self::getTestName('bucket'), $bucket->bucketName);
        $this->assertNotEmpty($bucket->creationDate);

        $bucketCopy = $this->s3->bucket->create(self::getTestName('bucket-copy'), Aws::REGION_AP_SOUTHEAST_1);
        $this->assertInstanceOf(self::CLASS_S3_BUCKET_DATA, $bucketCopy);
        $this->assertInstanceOf(self::CLASS_S3, $bucketCopy->getS3());

        //Checks location
        $this->assertEquals(Aws::REGION_AP_SOUTHEAST_1, $bucket->getLocation());

        $acl = $bucket->getAcl();
        $this->assertInstanceOf(self::CLASS_S3_ACCESS_CONTROL_POLICY_DATA, $acl);
        $this->assertNotEmpty($acl->toXml());
        //Checks that generated document is properly constructed.
        $dom = new \DOMDocument();
        $dom->loadXML($acl->getOriginalXml());
        $this->assertEqualXMLStructure($acl->toXml(true)->firstChild, $dom->firstChild);
        //Applies canned ACL
        $ret = $bucket->setAcl(array('x-amz-acl' => 'authenticated-read'));
        $this->assertTrue($ret);
        $acl2 = $bucket->getAcl();
        $this->assertInstanceOf(self::CLASS_S3_ACCESS_CONTROL_POLICY_DATA, $acl2);
        //Restores acl to previous state
        $ret = $bucket->setAcl($acl);
        $this->assertTrue($ret);
        //Compare restored with its stored value
        $this->assertEqualXMLStructure($bucket->getAcl()->toXml(true)->firstChild, $dom->firstChild);

        //Create object test
        $fileInfo = new SplFileInfo($this->getFixturesDirectory() . '/dummy');
        /* @var $response ClientResponseInterface */
        $response = $bucket->addObject(self::getTestName(self::OBJECT_NAME_DUMMY), $fileInfo, array('Content-Type' => 'text/plain; charset:UTF-8'));
        $this->assertInstanceOf($this->getAwsClassName('Client\\ClientResponseInterface'), $response);
        $this->assertNotEmpty($response->getHeader('ETag'));
        /* @var $dresponse ClientResponseInterface */
        $dresponse = $bucket->getObject(self::getTestName(self::OBJECT_NAME_DUMMY));
        $this->assertInstanceOf($this->getAwsClassName('Client\\ClientResponseInterface'), $dresponse);
        $objectContent = $dresponse->getRawContent();
        $this->assertContains('This is a dummy file', $objectContent);
        unset($dresponse);

        $objList = $bucket->listObjects();
        $this->assertInstanceOf($this->getS3ClassName('DataType\\ObjectList'), $objList);
        $object = $this->s3->object->get(array(self::getTestName('bucket'), self::getTestName(self::OBJECT_NAME_DUMMY)));
        $this->assertInstanceOf($this->getS3ClassName('DataType\\ObjectData'), $object);
        $this->assertSame($object, $objList[0]);
        $arr = $this->s3->bucket->getObjectsFromStorage(self::getTestName('bucket'));
        $this->assertSame($object, $arr[0]);
        unset($arr);

        $objectAcl = $object->getAcl();
        $this->assertInstanceOf(self::CLASS_S3_ACCESS_CONTROL_POLICY_DATA, $objectAcl);
        $ret = $object->setAcl(array('x-amz-acl' => 'public-read'));
        $this->assertTrue($ret);
        $objectAclChanged = $object->getAcl();
        $this->assertInstanceOf(self::CLASS_S3_ACCESS_CONTROL_POLICY_DATA, $objectAclChanged);
        $this->assertContains('xsi:type="CanonicalUser"', $objectAclChanged->toXml());
        unset($objectAclChanged);
        unset($objectAcl);

        $rmetadata = $object->getMetadata();
        $this->assertInstanceOf($this->getAwsClassName('Client\\ClientResponseInterface'), $rmetadata);
        $this->assertNotEmpty($rmetadata->getHeader('ETag'));
        $this->assertNotEmpty($rmetadata->getHeader('Last-Modified'));
        unset($rmetadata);

        $copyResponse = $object->copy($bucketCopy, self::getTestName(self::OBJECT_NAME_DUMMY));
        $this->assertInstanceOf($this->getS3ClassName('DataType\\CopyObjectResponseData'), $copyResponse);
        $this->assertInstanceOf(self::CLASS_S3, $copyResponse->getS3());
        $this->assertEquals(self::getTestName('bucket-copy'), $copyResponse->bucketName);
        $this->assertEquals(self::getTestName(self::OBJECT_NAME_DUMMY), $copyResponse->objectName);
        $this->assertNotEmpty($copyResponse->eTag);
        $this->assertInstanceOf('DateTime', $copyResponse->lastModified);
        $this->assertInternalType('array', $copyResponse->headers);
        unset($copyResponse);

        $dresponse = $bucket->deleteObject(self::getTestName(self::OBJECT_NAME_DUMMY));
        $this->assertInstanceOf($this->getAwsClassName('Client\\ClientResponseInterface'), $dresponse);
        $this->assertEquals(204, $dresponse->getResponseCode());
        unset($dresponse);
        unset($objList);
        unset($object);
        //Object must be detached from entity storage after deletion.
        $object = $this->s3->object->get(array(self::getTestName('bucket'), self::getTestName(self::OBJECT_NAME_DUMMY)));
        $this->assertNull($object);

        $dresponse = $bucketCopy->deleteObject(self::getTestName(self::OBJECT_NAME_DUMMY));
        $this->assertInstanceOf($this->getAwsClassName('Client\\ClientResponseInterface'), $dresponse);
        $this->assertEquals(204, $dresponse->getResponseCode());
        unset($dresponse);

        $ret = $bucket->delete();
        $this->assertTrue($ret);
        $this->assertNull($this->s3->bucket->get(self::getTestName('bucket')));
        unset($bucket);

        $ret = $bucketCopy->delete();
        $this->assertTrue($ret);
        $this->assertNull($this->s3->bucket->get(self::getTestName('bucket-copy')));
        unset($bucketCopy);
    }
}
