<?php
namespace Scalr\Tests;

use Scalr\Tests\Fixtures\DbMock1;

/**
 * Scalr_Environment test
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.12.2012
 */
class EnvironmentTest extends TestCase
{

    const KEY1 = \Modules_Platforms_Ec2::ACCESS_KEY;

    /**
     * Gets storage mock
     * @return DbMock1
     */
    public function getDbMock()
    {
        return new DbMock1();
    }

    /**
     * Gets Scalr Environment mock
     * @return \Scalr_Environment
     */
    public function getEnvironmentMock()
    {
        $env = $this->getMock(
            'Scalr_Environment',
            array('encryptValue', 'decryptValue'),
            array(), '', false
        );
        $env
            ->expects($this->any())
            ->method('encryptValue')
            ->will($this->returnCallback(function($str){
                return 'enc.' . $str;
            }))
        ;
        $env
            ->expects($this->any())
            ->method('decryptValue')
            ->will($this->returnCallback(function($str){
                return substr($str, strlen('enc.'));
            }))
        ;
        return $env;
    }

    /**
     * @test
     */
    public function testGetPlatformConfigValue()
    {
        $db = $this->getDbMock();
        $env = $this->getEnvironmentMock();

        $propDb = new \ReflectionProperty('Scalr_Environment', 'db');
        $propDb->setAccessible(true);
        $propDb->setValue($env, $db);

        $this->assertNull($env->getPlatformConfigValue(self::KEY1));
        $this->assertEquals(1, $db->getCountSelect());

        //This must not cause select to database
        $this->assertNull($env->getPlatformConfigValue(self::KEY1));
        $this->assertEquals(1, $db->getCountSelect());

        //zero must be stored as well as 1
        $env->setPlatformConfig(array(
            self::KEY1 => 0,
        ));
        //Insert should be triggered
        $this->assertEquals(1, $db->getCountInsert());
        //Any delete should be performed
        $this->assertEquals(0, $db->getCountDelete());

        //Cache must be updated
        $this->assertEquals(0, $env->getPlatformConfigValue(self::KEY1));
        //There are no need in exstra select query to database
        $this->assertEquals(1, $db->getCountSelect());

        //Empty string should be stored as well
        $env->setPlatformConfig(array(
            self::KEY1 => '',
        ));
        //Insert should be triggered
        $this->assertEquals(2, $db->getCountInsert());
        //Any delete should be performed
        $this->assertEquals(0, $db->getCountDelete());
        //Cache must be updated
        $this->assertEquals('', $env->getPlatformConfigValue(self::KEY1));
        //There are no need in exstra select query to database
        $this->assertEquals(1, $db->getCountSelect());

        //Empty string should be stored as well
        $env->setPlatformConfig(array(
            self::KEY1 => 1,
        ));
        //Insert should be triggered
        $this->assertEquals(3, $db->getCountInsert());
        //Any delete should be performed
        $this->assertEquals(0, $db->getCountDelete());
        //Cache must be updated
        $this->assertEquals(1, $env->getPlatformConfigValue(self::KEY1));
        //There are no need in exstra select query to database
        $this->assertEquals(1, $db->getCountSelect());

        //False should cause removing
        $env->setPlatformConfig(array(
            self::KEY1 => false,
        ));
        //Insert should not be triggered
        $this->assertEquals(3, $db->getCountInsert());
        //delete should be performed
        $this->assertEquals(1, $db->getCountDelete());
        //Cache must be updated with NULL value
        $this->assertEquals(null, $env->getPlatformConfigValue(self::KEY1));
        //There are no need in exstra select query to database
        $this->assertEquals(1, $db->getCountSelect());

        //False should cause removing
        $env->setPlatformConfig(array(
            self::KEY1 => 'string',
        ));
        //Insert should be triggered
        $this->assertEquals(4, $db->getCountInsert());
        //Any delete should be performed
        $this->assertEquals(1, $db->getCountDelete());
        //Cache must be updated with the value
        $this->assertEquals('string', $env->getPlatformConfigValue(self::KEY1));
        //There are no need in exstra select query to database
        $this->assertEquals(1, $db->getCountSelect());

        //NULL should cause removing
        $env->setPlatformConfig(array(
            self::KEY1 => null,
        ));
        //Insert should not be triggered
        $this->assertEquals(4, $db->getCountInsert());
        //delete should be performed
        $this->assertEquals(2, $db->getCountDelete());
        //Cache must be updated with NULL value
        $this->assertEquals(null, $env->getPlatformConfigValue(self::KEY1));
        //There are no need in exstra select query to database
        $this->assertEquals(1, $db->getCountSelect());
    }
}