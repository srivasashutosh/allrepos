<?php
namespace Scalr\Tests\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Services\Servers\Type\Personality;
use Scalr\Service\OpenStack\Services\Servers\Type\PersonalityList;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;

/**
 * PersonalityListTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    11.12.2012
 */
class PersonalityListTest extends OpenStackTestCase
{

    /**
     * @test
     */
    public function testAppend()
    {
        $p = new Personality('/path', 'contents');
        $list = new PersonalityList(array($p, $p));
        $list->append($p);
        foreach ($list as $v) {
            $this->assertSame($p, $v);
        }
        $this->assertEquals('[{"path":"\\/path","contents":"contents"},{"path":"\\/path","contents":"contents"},{"path":"\\/path","contents":"contents"}]', $list->toJson());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testConstructor()
    {
        $list = new PersonalityList(array('invalid'));
    }
}