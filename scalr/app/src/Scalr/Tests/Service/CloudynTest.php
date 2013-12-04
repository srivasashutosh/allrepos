<?php
namespace Scalr\Tests\Service;

use Scalr\Tests\TestCase;
use Scalr\Service\Cloudyn;

/**
 * Cloudyn api tests
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.11.2012
 */
class CloudynTest extends TestCase
{

    /**
     * {@inheritdoc}
     * @see SimpleTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     * @see SimpleTestCase::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testFunctionalServiceActions ()
    {
        if ($this->isSkipFunctionalTests()) {
            $this->markTestSkipped();
        }

        $cy = new Cloudyn('', '', \Scalr::config('scalr.cloudyn.environment'));

        $version = $cy->getVersion();
        $this->assertNotEmpty($version);

        $res = $cy->checkStatus();
        $this->assertTrue($res);

        $countries = $cy->countries();
        $this->assertArrayHasKey('US', $countries);
    }
}