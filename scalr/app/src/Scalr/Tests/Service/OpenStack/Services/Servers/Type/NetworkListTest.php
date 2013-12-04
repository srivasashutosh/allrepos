<?php
namespace Scalr\Tests\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Services\Servers\Type\NetworkList;
use Scalr\Service\OpenStack\Services\Servers\Type\Network;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;

/**
 * NetworkListTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    11.12.2012
 */
class NetworkListTest extends OpenStackTestCase
{

    /**
     * @test
     */
    public function testAppend()
    {
        $p = new Network('uuid-value');
        $list = new NetworkList(array($p, $p));
        $list->append($p);
        foreach ($list as $v) {
            $this->assertSame($p, $v);
        }
        $this->assertEquals('[{"uuid":"uuid-value"},{"uuid":"uuid-value"},{"uuid":"uuid-value"}]', $list->toJson());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testConstructor()
    {
        $list = new NetworkList(array('invalid'));
    }
}