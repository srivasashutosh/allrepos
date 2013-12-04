<?php
namespace Scalr\Tests\Service\OpenStack;

use Scalr\DependencyInjection\Container;
use Scalr\Service\OpenStack\OpenStackConfig;
use Scalr\Service\OpenStack\OpenStack;
use Scalr\Tests\TestCase;

/**
 * OpenStack TestCase
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    05.12.2012
 */
class OpenStackTestCase extends TestCase
{

    /**
     * OpenStack instance
     * @var OpenStack
     */
    protected $rackspace;

    /**
     * Openstack instance
     * @var OpenStack
     */
    protected $openstack;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var \Scalr_Environment
     */
    private $environment;

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $this->container = \Scalr::getContainer();
        $this->environment = new \Scalr_Environment();
        if (!$this->isSkipFunctionalTests()) {
            $this->environment->loadById(\Scalr::config('scalr.phpunit.envid'));
        }
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->environment);
        parent::tearDown();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests.TestCase::getFixturesDirectory()
     */
    public function getFixturesDirectory()
    {
        return parent::getFixturesDirectory() . '/Service/OpenStack';
    }

    /**
     * Gets full class name by its suffix after OpenStack\\
     *
     * @param   string   $classSuffix
     * @return  string
     */
    public function getOpenStackClassName($classSuffix)
    {
        return 'Scalr\\Service\\OpenStack\\' . $classSuffix;
    }

    /**
     * Gets full FIXTURE class name  by its suffix after OpenStack\\
     *
     * @param   string   $classSuffix
     * @return  string
     */
    public function getOpenStackFixtureClassName($classSuffix)
    {
        return 'Scalr\\Tests\\Fixtures\\Service\\OpenStack\\' . $classSuffix;
    }

    /**
     * Gets DI Container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}