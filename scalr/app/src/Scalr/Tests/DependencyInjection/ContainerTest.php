<?php
namespace Scalr\Tests\DependencyInjection;

use Scalr\Tests\Fixtures\DiObject1;
use Scalr\DependencyInjection\Container;
use Scalr\Tests\TestCase;

/**
 * ContainerTest test
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    11.04.2013
 */
class ContainerTest extends TestCase
{

    /**
     * DI Container
     *
     * @var Container
     */
    private $container;

    /**
     * {@inheritdoc}
     * @see Scalr\Tests.TestCase::setUp()
     */
    protected function setUp()
    {
        $this->container = \Scalr::getContainer();
        //Usual service
        $this->container->test1 = function ($cont) {
            return new DiObject1();
        };
        //Shared service
        $this->container->setShared('test2', function ($cont) {
            return new DiObject1();
        });
        //Service which is using singletone behaviour
        //but delegated internally by additional parameters.
        $this->container->test3 = function ($cont, $args) {
            $params = array();
            $params['region'] = isset($args[0]) ? $args[0] : 'default';
            $serviceid = 'test3.' . md5($params['region']);
            if (!$cont->initialized($serviceid)) {
                $cont->setShared($serviceid, function($cont) use ($params) {
                    return new DiObject1($params['region']);
                });
            }
            return $cont->get($serviceid);
        };
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests.TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->container->set('test1', null);
        $this->container->set('test2', null);
        $this->container->set('test3', null);
        unset($this->container);
    }

    protected function echoUsage()
    {
        printf("%0.4f\n", memory_get_usage() / 1024 / 1024);
    }

    /**
     * @test
     */
    public function testReleaseMemory1()
    {
        $usage = memory_get_usage();
        for ($i = 0; $i < 1000; ++$i) {
            $obj = $this->container->test1;
            unset($obj);
        }
        $diff = memory_get_usage() - $usage;
        $this->assertLessThan(8000, $diff);
    }

    /**
     * @test
     */
    public function testReleaseMemory2()
    {
        $usage = memory_get_usage();
        $prev = null;
        for ($i = 0; $i < 1000; ++$i) {
            $obj = $this->container->test2;
            if ($prev) {
                $this->assertSame($obj, $prev);
            }
            $prev = $obj;
            unset($obj);
        }
        unset($prev);
        $this->assertGreaterThan(1024000, memory_get_usage() - $usage);
        $this->container->release('test2');
        $this->assertLessThan(50000, memory_get_usage() - $usage);
    }

    /**
     * @test
     */
    public function testReleaseMemory3()
    {
        $regions = array('region1', 'region2', 'region3');
        $obj = array();
        $usage = memory_get_usage();
        $prev = array();
        for ($i = 0; $i < 1000; ++$i) {
            foreach ($regions as $region) {
                $obj[$region] = $this->container->test3($region);
                if (isset($prev[$region])) {
                    $this->assertSame($obj[$region], $prev[$region]);
                }
                $prev[$region] = $obj[$region];
                unset($obj[$region]);
            }
        }

        foreach ($regions as $r)
            unset($prev[$r]);

        $this->container->release('test3');
        $this->assertLessThan(15000, memory_get_usage() - $usage);
    }

    /**
     * @test
     */
    public function testInvokedService()
    {
        $this->assertSame(
            $this->container->config->get('scalr.phpunit'),
            $this->container->config('scalr.phpunit')
        );
    }

    /**
     * @test
     */
    public function testFunctionalLdap()
    {
        if ($this->isSkipFunctionalTests())
            $this->markTestSkipped();

        $this->assertInstanceOf('Scalr\\Net\\Ldap\\LdapClient', $this->container->ldap);
    }

    /**
     * @test
     */
    public function testFunctionalDnsdb()
    {
        // It can be unavailable from the local net.
        $this->markTestSkipped();

        $this->assertInstanceOf('Scalr\\Db\\ConnectionPool', $this->container->dnsdb);
        $data = $this->container->dnsdb->getOne('SELECT * FROM `domains` LIMIT 1');
    }
}