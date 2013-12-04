<?php
namespace Scalr\Tests\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Services\Servers\Type\ServerStatus;
use Scalr\Service\OpenStack\Services\Servers\Type\ListServersFilter;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;

/**
 * ListServersFilterTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.12.2012
 */
class ListServersFilterTest extends OpenStackTestCase
{
    /**
     * @test
     */
    public function testInit()
    {
        $m = ListServersFilter::init('name', 'flavorid', null, ServerStatus::active(), null, '10', 20)->setLimit(30);
        $this->assertEquals(30, $m->getLimit());
        $this->assertEquals('10', $m->getMarker());
        $this->assertEquals('name', $m->getName());
        $this->assertEquals('flavorid', $m->getFlavorId());
        $this->assertEquals(ServerStatus::STATUS_ACTIVE, (string)$m->getStatus());
        unset($m);
    }
}