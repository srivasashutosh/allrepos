<?php
namespace Scalr\Tests\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\DataType\InstanceFilterNameType;
use Scalr\Tests\Service\AwsTestCase;

/**
 * InstanceFilterNameTypeTest
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     09.01.2013
 */
class InstanceFilterNameTypeTest extends AwsTestCase
{

    public function providerTestTag()
    {
        return array(
            array('tag', 'tag:Experience', array('Experience')),
            array('tag', 'tag:Foo', array('Foo')),
        );
    }

    /**
     * @test
     * @dataProvider providerTestTag
     */
    public function testTag($name, $value, $args = null)
    {
        if ($args === null) {
            $args = array();
        }
        $type = call_user_func_array($this->getEc2ClassName('DataType\\InstanceFilterNameType') . "::" . $name, $args);
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\InstanceFilterNameType'), $type);
        $this->assertEquals($value, (string) $type);
    }
}