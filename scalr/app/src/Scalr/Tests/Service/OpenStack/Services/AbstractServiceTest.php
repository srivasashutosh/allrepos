<?php
namespace Scalr\Tests\Service\OpenStack\Services;

use Scalr\Tests\Service\OpenStack\OpenStackTestCase;

/**
 * AbstractServiceTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    05.12.2012
 */
class AbstractServiceTest extends OpenStackTestCase
{

    /**
     * Data provider
     */
    public function provider()
    {
        return array(
            array($this->getOpenStackFixtureClassName('Services\\FooService'), 'foo'),
            array($this->getOpenStackFixtureClassName('Services\\SomeService'), 'some'),
            array($this->getOpenStackFixtureClassName('Services\\FakeService'), 'redefined'),
        );
    }

    /**
     * @test
     * @dataProvider provider
     *
     * @param   string  $class A service interface class
     * @param   string  $name  An expected service name
     */
    public function testGetName($class, $name)
    {
        $this->assertNotNull($class::getName());
        $this->assertEquals($name, $class::getName());
    }
}