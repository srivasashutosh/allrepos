<?php
namespace Scalr\Tests\Service\OpenStack\Type;

use Scalr\Service\OpenStack\Type\Marker;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;

/**
 * MarkerTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.12.2012
 */
class MarkerTest extends OpenStackTestCase
{
    /**
     * @test
     */
    public function testInit()
    {
        $m = Marker::init()->setMarker('10')->setLimit(20);
        $this->assertEquals('10', $m->getMarker());
        $this->assertEquals(20, $m->getLimit());
        unset($m);

        $m = Marker::init(10, '20');
        $this->assertEquals('10', $m->getMarker());
        $this->assertEquals(20, $m->getLimit());
        unset($m);
    }
}