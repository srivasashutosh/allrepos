<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\CloudFront\DataType as CloudFrontDataType;
use Scalr\Tests\Service\AwsTestCase;
use Scalr\Service\Aws\CloudFront;

/**
 * Amazon CloudFront Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 */
class CloudFrontTest extends AwsTestCase
{

    const CLASS_CLOUD_FRONT = 'Scalr\\Service\\Aws\\CloudFront';

    /**
     * @var \Scalr\Service\Aws
     */
    private $aws;

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isSkipFunctionalTests()) {
            $this->aws = $this->getContainer()->aws(AwsTestCase::REGION);
            $this->aws->cloudFront->enableEntityManager();
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->aws);
        parent::tearDown();
    }

    /**
     * Gets CloudFront Mock
     *
     * @param    callback   $callback
     * @return   CloudFront Returns Ec2 Mock class
     */
    public function getCloudFrontMock($callback = null)
    {
        return $this->getServiceInterfaceMock('CloudFront', $callback);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixturesDirectory()
     */
    public function getFixturesDirectory()
    {
        return parent::getFixturesDirectory() . '/CloudFront';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixtureFilePath()
     */
    public function getFixtureFilePath($filename)
    {
        return $this->getFixturesDirectory() . '/' . CloudFront::API_VERSION_CURRENT . '/' . $filename;
    }

    /**
     * Gets response callback
     *
     * @param   string   $method CloudFront API method
     * @return  \Closure
     */
    public function getResponseCallback($method)
    {
        $responseMock = $this->getQueryClientResponseMock($this->getFixtureFileContent($method . '.xml'));
        return function() use($responseMock) {
            return $responseMock;
        };
    }

    /**
     * @test
     */
    public function testDescribeDistributions()
    {
        $cf = $this->getCloudFrontMock($this->getResponseCallback(substr(__FUNCTION__, 4)));
        $list = $cf->distribution->describe();
        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\DistributionList'), $list);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $list->getCloudFront());
        $this->assertEquals(1, count($list));
        $this->assertEquals(100, $list->maxItems);
        $this->assertEquals('', $list->marker);
        $this->assertEquals(false, $list->isTruncated);

        /* @var $dist CloudFrontDataType\DistributionData */
        $dist = $list->get(0);
        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\DistributionData'), $dist);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $dist->getCloudFront());
        $this->assertEquals('E3F7022YJAMF41', $dist->distributionId);
        $this->assertEquals('Deployed', $dist->status);
        $this->assertEquals('2011-02-22T16:49:06+00:00', $dist->lastModifiedTime->format('c'));
        $this->assertEquals('dvxau28fvexxx.cloudfront.net', $dist->domainName);
        $this->assertEquals(null, $dist->inProgressInvalidationBatches);

        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\TrustedSignerList'), $dist->activeTrustedSigners);
        $this->assertEquals(0, count($dist->activeTrustedSigners));

        /* @var $dc CloudFrontDataType\DistributionConfigData */
        $dc = $dist->distributionConfig;
        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\DistributionConfigData'), $dc);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $dc->getCloudFront());
        $this->assertEquals($dist->distributionId, $dc->getDistributionId());
        $this->assertEquals(null, $dc->callerReference);
        $this->assertEquals('test', $dc->comment);
        $this->assertEquals(null, $dc->defaultRootObject);
        $this->assertEquals(true, $dc->enabled);
        $this->assertEquals('PriceClass_All', $dc->priceClass);

        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\DistributionConfigAliasList'), $dc->aliases);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $dc->aliases->getCloudFront());
        $this->assertEquals($dist->distributionId, $dc->aliases->getDistributionId());
        $this->assertEquals(1, count($dc->aliases));
        $this->assertEquals('test.sdfsdfdsfsdfsd.com', $dc->aliases[0]->cname);

        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\CacheBehaviorList'), $dc->cacheBehaviors);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $dc->cacheBehaviors->getCloudFront());
        $this->assertEquals($dist->distributionId, $dc->cacheBehaviors->getDistributionId());
        $this->assertEquals(0, count($dc->cacheBehaviors));

        /* @var $cb CloudFrontDataType\CacheBehaviorData */
        $cb = $dc->defaultCacheBehavior;
        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\CacheBehaviorData'), $cb);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $cb->getCloudFront());
        $this->assertEquals($dist->distributionId, $cb->getDistributionId());
        $this->assertEquals('MyOrigin', $cb->targetOriginId);
        $this->assertEquals('allow-all', $cb->viewerProtocolPolicy);
        $this->assertEquals(3600, $cb->minTtl);

        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\ForwardedValuesData'), $cb->forwardedValues);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $cb->forwardedValues->getCloudFront());
        $this->assertEquals($dist->distributionId, $cb->forwardedValues->getDistributionId());
        $this->assertEquals(false, $cb->forwardedValues->queryString);

        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\ForwardedValuesCookiesData'), $cb->forwardedValues->cookies);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $cb->forwardedValues->cookies->getCloudFront());
        $this->assertEquals($dist->distributionId, $cb->forwardedValues->cookies->getDistributionId());

        /* @var $cook CloudFrontDataType\ForwardedValuesCookiesData */
        $cook = $cb->forwardedValues->cookies;
        $this->assertEquals('none', $cook->forward);
        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\WhitelistedCookieNamesList'), $cook->whitelistedNames);
        $this->assertInstanceOf(self::CLASS_CLOUD_FRONT, $cook->whitelistedNames->getCloudFront());
        $this->assertEquals($dist->distributionId, $cook->whitelistedNames->getDistributionId());
        $this->assertEquals(0, count($cook->whitelistedNames));
    }

    /**
     * @test
     */
    public function testFunctional()
    {
        $this->skipIfEc2PlatformDisabled();
        $aws = $this->aws;
        $client = $aws->cloudFront->getApiHandler()->getClient();

        $list = $aws->cloudFront->distribution->describe();
        $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\DistributionList'), $list);
        /* @var $dist CloudFrontDataType\DistirbutionData */
        foreach ($list as $dist) {
            $dist->refresh();
            $this->assertNotEmpty($dist->getOriginalXml());
            $this->assertNotNull($dist->distributionConfig->getETag());
            $dist->refreshConfig();
            $this->assertNotNull($dist->getETag());
            $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\DistributionConfigData'), $dist->distributionConfig);
            $this->assertNotEmpty($dist->distributionConfig->getOriginalXml());
            $dom = new \DOMDocument();
            $dom->loadXML($dist->distributionConfig->getOriginalXml());
            $this->assertEqualXMLStructure($dom->firstChild, $dist->distributionConfig->toXml(true)->firstChild);
            unset($dist);
        }
        unset($list);

        //Creates distribution
//         $origin = new CloudFrontDataType\DistributionConfigOriginData();
//         $origin->originId = 'MyOriginId';
//         $origin->domainName = 'test.s3.amazonaws.com';
//         $origin->setS3OriginConfig(new CloudFrontDataType\DistributionS3OriginConfigData(''));

//         $fvd = new CloudFrontDataType\ForwardedValuesData();
//         $fvd->queryString = false;
//         $fvd->setCookies(new CloudFrontDataType\ForwardedValuesCookiesData(
//             CloudFrontDataType\ForwardedValuesCookiesData::FORWARD_NONE
//         ));

//         $ts = new CloudFrontDataType\TrustedSignerList();
//         $ts->setEnabled(false);

//         $dcb = new CloudFrontDataType\CacheBehaviorData();
//         $dcb->minTtl = 3600;
//         $dcb->targetOriginId = $origin->originId;
//         $dcb->viewerProtocolPolicy = 'allow-all';
//         $dcb->setForwardedValues($fvd);
//         $dcb->setTrustedSigners($ts);

//         $dc = new CloudFrontDataType\DistributionConfigData();
//         $dc->comment = 'phpunit test distribution';
//         $dc->enabled = false;
//         $dc->setAliases(array(
//             array('cname' => 'test2.scalr.com')
//         ));
//         $dc->priceClass = 'PriceClass_All';
//         $dc->setOrigins($origin);
//         $dc->setDefaultCacheBehavior($dcb);

//         $dist = $aws->cloudFront->distribution->create($dc);
//         $this->assertInstanceOf($this->getCloudFrontClassName('DataType\\DistributionData'), $dist);

//         $dist->refresh();
        //Too time consuming test
//         $ret = $dist->delete();
//         $this->assertTrue($ret);

        //Releases all memory from storage.
        $aws->getEntityManager()->detachAll();
    }
}