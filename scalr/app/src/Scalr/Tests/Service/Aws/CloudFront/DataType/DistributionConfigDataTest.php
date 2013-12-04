<?php
namespace Scalr\Tests\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFront\DataType\ForwardedValuesCookiesData;
use Scalr\Service\Aws\CloudFront\DataType\ForwardedValuesData;
use Scalr\Service\Aws\CloudFront\DataType\CacheBehaviorData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigAliasData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigData;
use Scalr\Tests\Service\AwsTestCase;

/**
 * DistributionConfigDataTest
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     05.02.2013
 */
class DistributionConfigDataTest extends AwsTestCase
{
    /**
     * @test
     */
    public function testToXml()
    {
        $cfg = new DistributionConfigData();
        $cfg->setAliases(array(array('cname' => 'cname-1'), new DistributionConfigAliasData('cname-2')));
        $cfg->callerReference = 'caller-reference';
        $cfg->comment = 'the-comment';
        $cfg->enabled = true;
        $cfg->priceClass = 'price-class';
        $cfg->defaultRootObject = 'default-root-object';

        $cbd = new CacheBehaviorData();
        $cbd->minTtl = 3600;
        $cbd->pathPattern = 'path-pattern';
        $cbd->targetOriginId = 'target-origin-id';
        $cbd->viewerProtocolPolicy = 'pp-1';
        $cbd->setTrustedSigners(array(
            array('awsAccountNumber' => 'awsAccountNumber-1'),
            array('awsAccountNumber' => 'awsAccountNumber-2')
        ));
        $fc = new ForwardedValuesCookiesData();
        $fc->forward = 'whitelist';
        $fc->setWhitelistedNames(array(
            array('name' => 'cookie-name-1'),
            array('name' => 'cookie-name-2'),
        ));
        $fwd = new ForwardedValuesData();
        $fwd->queryString = true;
        $fwd->setCookies($fc);

        $cbd->setForwardedValues($fwd);
        $cfg->setCacheBehaviors(array($cbd, $cbd));
        $cfg->setDefaultCacheBehavior($cbd);

        $this->assertEquals($this->getFixtureFileContent('CloudFront/20120701/DistributionConfig1.xml'), $cfg->toXml());
        unset($cfg);
    }
}