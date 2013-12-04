<?php
namespace Scalr\Tests\Service\Aws\Client\QueryClient;

use Scalr\Tests\Constraint\ArrayHas;
use Scalr\Service\Aws\Client\QueryClient\S3QueryClient;
use Scalr\Tests\Service\AwsTestCase;

/**
 * Test for the S3QueryClient class
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    12.11.2012
 */
class S3QueryClientTest extends AwsTestCase
{

    const CLASS_S3_QUERY_CLIENT = 'Scalr\\Service\\Aws\\Client\\QueryClient\\S3QueryClient';

    const S3TEST_AWSACCESSKEYID  = 'AKIAIOSFODNN7EXAMPLE';

    const S3TEST_SECRETACCESSKEY = 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY';

    const S3TEST_APIVERSION      = '20060301';

    const S3TEST_URL             = 's3.amazonaws.com';

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Data provider for the testCall()
     */
    public function providerCall()
    {
        return array(
            array(
                'GET',
                array(
                    '_subdomain' => 'johnsmith',
                    'Date'       => 'Tue, 27 Mar 2007 19:36:42 +0000',
                ),
                '/photos/puppy.jpg',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . 'bWq2s1WEIj+Ydj0vQ697zp+IXMU='
            ),
            array(
                'PUT',
                array(
                    '_subdomain'     => 'johnsmith',
                    'Content-Type'   => 'image/jpeg',
                    'Content-Length' => 94328,
                    'Date'           => 'Tue, 27 Mar 2007 21:15:45 +0000',
                ),
                '/photos/puppy.jpg',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . 'MyyxeRY7whkBe+bq8fHCL/2kKUg='
            ),
            array(
                'GET',
                array(
                    '_subdomain' => 'johnsmith',
                    'Date'       => 'Tue, 27 Mar 2007 19:42:41 +0000',
                ),
                '/?prefix=photos&max-keys=50&marker=puppy',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . 'htDYFYduRNen8P9ZfE/s9SuKy0U='
            ),
            array(
                'GET',
                array(
                    '_subdomain' => 'johnsmith',
                    'Date'       => 'Tue, 27 Mar 2007 19:44:46 +0000',
                ),
                '/?acl',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . 'c2WLPFtWHVgbEmeEG93a4cG37dM='
            ),
            array(
                'DELETE',
                array(
                    'Date'       => 'Tue, 27 Mar 2007 21:20:27 +0000',
                    'x-amz-date' => 'Tue, 27 Mar 2007 21:20:26 +0000'
                ),
                '/johnsmith/photos/puppy.jpg',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . '9b2sXq0KfxsxHtdZkzx/9Ngqyh8='
            ),
            array(
                'PUT',
                array(
                    '_subdomain'   => 'static.johnsmith.net',
                    'Host'         => 'static.johnsmith.net:8080',
                    'Date'         => 'Tue, 27 Mar 2007 21:06:08 +0000',
                    'x-amz-acl'    => 'public-read',
                    'Content-Type' => 'application/x-download',
                    'Content-MD5'  => '4gJE4saaMU4BqNR0kLY+lw==',
                    'X-Amz-Meta-ReviewedBy'        => 'joe@johnsmith.net,jane@johnsmith.net',
                    'X-Amz-Meta-FileChecksum'      => '0x02661779',
                    'X-Amz-Meta-ChecksumAlgorithm' => 'crc32',
                    'Content-Disposition' => 'attachment; filename=database.dat',
                    'Content-Encoding'    => 'gzip',
                    'Content-Length'      => 5913339,
                ),
                '/db-backup.dat.gz',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . 'ilyl83RwaSoYIEdixDQcA4OnAnc='
            ),
            array(
                'GET',
                array(
                    'Date' => 'Wed, 28 Mar 2007 01:29:59 +0000',
                ),
                '/',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . 'qGdzdERIC03wnaRNKh6OqZehG9s='
            ),
            array(
                'GET',
                array(
                    'Date' => 'Wed, 28 Mar 2007 01:49:49 +0000',
                ),
                '/dictionary/fran%C3%A7ais/pr%c3%a9f%c3%a8re',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . 'DNEZGsoieTZ92F3bUfSPQcbGmlM='
            ),
            array(
                'GET',
                array(
                    'x-amz-date' => 'Sun, 19 Dec 2010 01:53:44 GMT',
                    'Accept'     => '*/*',
                ),
                '/Junk3.txt?response-cache-control=No-cache&response-content-disposition=at'
              . 'tachment%3B%20filename%3Dtesting.txt&response-content-encoding=x-gzip&responsecontent-'
              . 'language=mi%2C%20en&response-expires=Thu%2C%2001%20Dec%201994%2016:00:00%20GMT',
                'AWS ' . S3QueryClientTest::S3TEST_AWSACCESSKEYID . ':' . 'C9cLGSMoJbdULEUoeyStl49paok=' //?
            )
        );
    }

    /**
     * @test
     * @dataProvider providerCall
     */
    public function testCall($action, $options, $path, $authorizationValue)
    {
        $awsAccessKeyId = S3QueryClientTest::S3TEST_AWSACCESSKEYID;
        $secretAccessKey = S3QueryClientTest::S3TEST_SECRETACCESSKEY;
        $apiVersion = S3QueryClientTest::S3TEST_APIVERSION;
        $url = S3QueryClientTest::S3TEST_URL;

        $httpStub = $this->getMock('HttpRequest');
        if ($action == 'PUT') {
            if (isset($options['_putData'])) {
                $httpStub
                    ->expects($this->once())
                    ->method('setPutData')
                    ->with($this->equalTo($options['_putData']))
                ;
            } elseif (isset($options['_putFile'])) {
                $httpStub
                    ->expects($this->once())
                    ->method('setPutFile')
                    ->with($this->equalTo($options['_putFile']))
                ;
            }
        }
        $host = (isset($options['Host']) ? $options['Host'] : (isset($options['_subdomain']) ? $options['_subdomain'] . '.' : '') . $url) . $path;
        $httpStub
            ->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo('https://' . $host))
        ;
        $httpStub
            ->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo(isset($action) ? constant('HTTP_METH_' . $action) : constant('HTTP_METH_GET')))
        ;
        $httpStub
            ->expects($this->once())
            ->method('addHeaders')
            ->with(
                $this->logicalAnd(
                    $this->arrayHasKey('Authorization'),
                    new ArrayHas($this->equalTo($authorizationValue), 'Authorization')
                )
            )
        ;

        $clientStub = $this->getMock(
            self::CLASS_S3_QUERY_CLIENT,
            array('createRequest', 'tryCall'),
            array($awsAccessKeyId, $secretAccessKey, $apiVersion, $url)
        );
        $clientStub
            ->expects($this->once())
            ->method('createRequest')
            ->will($this->returnValue($httpStub))
        ;
        $clientStub
            ->expects($this->once())
            ->method('tryCall')
            ->will($this->returnValue(new \HttpMessage('')))
        ;

        $clientStub->call($action, $options, $path);

        unset($clientStub);
        unset($httpStub);
    }

}