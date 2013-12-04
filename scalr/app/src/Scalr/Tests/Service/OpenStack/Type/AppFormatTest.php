<?php
namespace Scalr\Tests\Service\OpenStack\Type;

use Scalr\Service\OpenStack\Type\AppFormat;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;

/**
 * AppFormatTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class AppFormatTest extends OpenStackTestCase
{

    /**
     * Data provider for the testValue() method
     */
    public function provider()
    {
        return array(
            array(AppFormat::APP_JSON, true),
            array(AppFormat::APP_JSON, true),
            array('invalid', false),
        );
    }

    /**
     * @test
     * @dataProvider provider
     */
    public function testValue($format, $bShouldBeValid)
    {
        if (!$bShouldBeValid) {
            try {
                $fmt = new AppFormat($format);
                $this->assertTrue(false, 'Assertion must be thrown here.');
            } catch (\InvalidArgumentException $e) {
                $this->assertTrue(true);
            }
        } else {
            $fmt = new AppFormat($format);
            $this->assertEquals($format, $fmt->get());
            $this->assertEquals($format, (string)$fmt);
            unset($fmt);
        }
    }
}