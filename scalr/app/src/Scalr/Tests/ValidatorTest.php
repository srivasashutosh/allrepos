<?php
namespace Scalr\Tests;

use \Scalr_Validator;

/**
 * Scalr_Validator test
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    20.05.2013
 */
class ValidatorTest extends TestCase
{

    public function providerValidateIp()
    {
        return array(
            array('', false),
            array('-1', false),
            array(array(), false),
            array(new \stdClass(), false),
            array('10.10.0.1', true),
            array('10.10.0.1', true, 4),
            array('10..0.1', false, 4),
            array('10.1.0.1', false, 6),
            array('10.1.0.1', true, 'all'),
            //Version by default is 4
            array('2001:db8:85a3::8a2e:370:7334', false),
            array('2001:db8:85a3::8a2e:370:7334', false, 4),
            array('2001:db8:85a3::8a2e:370:7334', true, 6),
            array('2001:db8:85a3::8a2e:370:7334', true, 'all'),
        );
    }

    /**
     * @test
     * @dataProvider providerValidateIp
     */
    public function testValidateIp($ip, $valid, $version = null)
    {
        $validator = new Scalr_Validator();

        $ret = $validator->validateIp($ip, null, (isset($version) ? array('version' => $version) : null));
        if ($valid) {
            $this->assertTrue($ret);
        } else {
            $this->assertNotEquals(true, $ret);
        }
    }

    public function providerValidateDomain()
    {
        return array(
            array('', false),
            array('in\\valid.domain', false),
            array('valid.domain.ru', true),
        );
    }

    /**
     * @test
     * @dataProvider providerValidateDomain
     */
    public function testValidateDomain($value, $valid, $options = null)
    {
        $validator = new Scalr_Validator();

        $ret = $validator->validateDomain($value, null, $options);
        if ($valid) {
            $this->assertTrue($ret);
        } else {
            $this->assertNotEquals(true, $ret);
        }
    }
}