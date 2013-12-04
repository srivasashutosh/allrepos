<?php
namespace Scalr\Tests\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Services\Servers\Type\ServerStatus;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;
use \ReflectionClass;

/**
 * ServerStatusTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.12.2012
 */
class ServerStatusTest extends OpenStackTestCase
{
    const TYPE_CLASS_NAME = 'Services\\Servers\\Type\\ServerStatus';

    public function provider()
    {
        $arr = array(
            array('invalid_name', '', true)
        );
        $ref = new ReflectionClass($this->getOpenStackClassName(self::TYPE_CLASS_NAME));
        $len = strlen('STATUS_');
        foreach ($ref->getConstants() as $name => $value) {
            $arr[] = array(lcfirst($this->decamilize(substr($name, $len))), $value, false);
        }
        return $arr;
    }

    /**
     * @test
     * @dataProvider provider
     */
    public function testInit($name, $value, $exeption)
    {
        if ($exeption) {
            try{
                $status = ServerStatus::$name();
                $this->assertTrue(false, 'Exception must be thrown here.');
            } catch (\BadMethodCallException $e) {
                $this->assertTrue(true);
            }
        } else {
            $status = ServerStatus::$name();
            $this->assertInstanceOf($this->getOpenStackClassName(self::TYPE_CLASS_NAME), $status);
            $this->assertEquals($value, (string)$status);
        }
    }
}