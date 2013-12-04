<?php
namespace Scalr\Tests\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Services\Servers\Type\ImageStatus;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;
use \ReflectionClass;

/**
 * ImageStatusTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    12.12.2012
 */
class ImageStatusTest extends OpenStackTestCase
{
    const TYPE_CLASS_NAME = 'Services\\Servers\\Type\\ImageStatus';

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
                $status = ImageStatus::$name();
                $this->assertTrue(false, 'Exception must be thrown here.');
            } catch (\BadMethodCallException $e) {
                $this->assertTrue(true);
            }
        } else {
            $status = ImageStatus::$name();
            $this->assertInstanceOf($this->getOpenStackClassName(self::TYPE_CLASS_NAME), $status);
            $this->assertEquals($value, (string)$status);
        }
    }
}