<?php

namespace Scalr\Tests\System\Config;

use Scalr\System\Config\Yaml;
use Scalr\Tests\TestCase;

/**
 * Yaml parser test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     29.05.2013
 */
class YamlTest extends TestCase
{

    const CONFIG_CLASS = 'Scalr\\System\\Config\\Yaml';

    /**
     * {@inheritdoc}
     * @see Scalr\Tests.TestCase::getFixturesDirectory()
     */
    public function getFixturesDirectory()
    {
        return parent::getFixturesDirectory() . '/System/Config';
    }

    /**
     * Gets fixtures file path
     *
     * @param   string    $path
     * @return  string    Returns full path to the fixture file
     */
    public function getFixture($path)
    {
        return $this->getFixturesDirectory() . '/' . $path;
    }

    /**
     * @test
     */
    public function testLoadIni()
    {
        $config = Yaml::load($this->getFixture('test-parameters.ini'));
        $this->assertInstanceOf(self::CONFIG_CLASS, $config);
        $this->assertInternalType('array', $config->toArray());
        $this->assertEquals($config->toArray(), json_decode($config->toJson(), true));
        $this->assertNotEmpty($config->getModified());
        $this->assertNotEmpty($config->getPath());
        $this->assertEmpty($config->getImports());
    }

    /**
     * @test
     */
    public function testLoad()
    {
        $config = Yaml::load($this->getFixture('test-config.yml'));
        $this->assertInstanceOf(self::CONFIG_CLASS, $config);
        $this->assertInternalType('array', $config->toArray());
        $this->assertEquals($config->toArray(), json_decode($config->toJson(), true));
        $this->assertNotEmpty($config->getModified());
        $this->assertNotEmpty($config->getPath());
        $this->assertNotEmpty($config->getImports());

        $serialized = serialize($config);
        $unserialized = unserialize($serialized);
        $this->assertEquals($config, $unserialized);
        $this->assertEquals($config->getModified(), $unserialized->getModified());
        $this->assertEquals($config->getPath(), $unserialized->getPath());
        unset($serialized);
        unset($unserialized);

        $this->assertInternalType('array', $config->get('scalr'));
        $this->assertInternalType('array', $config['scalr']);
        $this->assertEquals($config['scalr'], $config->get('scalr'));

        $imports = $config->getImports();
        $this->assertInternalType('array', $imports);
        $this->assertNotEmpty($imports);

        foreach(array('test-parameters.ini', 'Subpath/child.yml', 'Subpath/to-override.yml') as $fxt) {
            $path = realpath($this->getFixture($fxt));
            $this->assertContains($path, array_keys($imports));
            //Checks modification time of the imported document
            $this->assertNotEmpty($imports[$path]);
        }

        $this->assertEquals(3, count($imports));
        $this->assertEquals(false, $config('scalr.auditlog.enabled'));
        $this->assertEquals('scalr', $config('scalr.auditlog.mysql.name'));
        $this->assertEquals('localhost', $config->get('scalr.auditlog.mysql.host'));
        $this->assertEquals('', $config->get('scalr.auditlog.mysql.port'));
        $this->assertEquals('mysql', $config->get('scalr.auditlog.mysql.driver'));
        $this->assertEquals('scalruser', $config->get('scalr.auditlog.mysql.user'));
        $this->assertEquals('scalrpassword', $config->get('scalr.auditlog.mysql.pass'));

        $this->assertEquals('scalr', $config->get('scalr.connections.mysql.name'));
        $this->assertEquals('localhost', $config->get('scalr.connections.mysql.host'));
        $this->assertEquals('', $config->get('scalr.connections.mysql.port'));
        $this->assertEquals('mysqli', $config->get('scalr.connections.mysql.driver'));
        $this->assertEquals('scalruser', $config->get('scalr.connections.mysql.user'));
        $this->assertEquals('scalrpassword', $config->get('scalr.connections.mysql.pass'));

        $this->assertEquals(true, $config->get('scalr.phpunit.skip_functional_tests'));
        $this->assertEquals(100001, $config->get('scalr.phpunit.test_userid'));
        $this->assertEquals(200002, $config->get('scalr.phpunit.test_envid'));

        $this->assertEquals("this is\nmultiline\nstring\n", $config->get('scalr.inherit.multiline'));
        $this->assertEquals("foo-value", $config->get('scalr.inherit.foo'));
        $this->assertEquals("genius", $config->get('scalr.inherit.ado'));
        $this->assertEquals("f7eba2160a5f8ab30e8299502d04d38a", md5($config->get('scalr.inherit.longstring')));

        $this->assertEquals("foo-value", $config->get('parameters.foo'));
        $this->assertEquals("en_US", $config->get('parameters.locale'));
    }
}