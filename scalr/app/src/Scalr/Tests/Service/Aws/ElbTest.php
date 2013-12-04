<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\Ec2\DataType\InstanceFilterNameType;
use Scalr\Service\Aws\Ec2\DataType\InstanceFilterData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStateData;
use Scalr\Service\Aws\Ec2\DataType\ResourceTagSetData;
use Scalr\Service\Aws\Ec2\DataType\PlacementResponseData;
use Scalr\Service\Aws\Ec2\DataType\RunInstancesRequestData;
use Scalr\Tests\Service\AwsTestCase;
use Scalr\Service\Aws\Elb\DataType\InstanceData;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Elb\DataType\LbCookieStickinessPolicyData;
use Scalr\Service\Aws\Elb\DataType\AppCookieStickinessPolicyData;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionData;
use Scalr\Tests\Service\Aws\ElbTestCase;
use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\Client\QueryClientException;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Elb;
use Scalr\Service\Aws;

/**
 * AWS Elb Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.09.2012
 */
class ElbTest extends ElbTestCase
{

    /**
     * LoadBalancer name from test xml
     */
    const LB_NAME_TEST = 'phpunit-test-load-balancer';

    const LB_NAME_FUNC_TEST = 'lb';

    const LB_NAME_FUNC_TEST_1 = 'lb1';

    const LB_NAME_FUNC_TEST_2 = 'lb2';

    /**
     * @var Elb
     */
    protected $elb;

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service\Aws.ElbTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isSkipFunctionalTests()) {
            $this->elb = $this->getContainer()->aws(self::REGION)->elb;
            $this->elb->enableEntityManager();
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service\Aws.ElbTestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->elb = null;
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testGetApiVersion()
    {
        $this->skipIfEc2PlatformDisabled();
        $this->assertEquals(Elb::API_VERSION_CURRENT, $this->elb->getApiVersion(), 'Invalid API Version');
    }

    /**
     * @test
     * @expectedException   Scalr\Service\AwsException
     */
    public function testSetApiVersionInvalid()
    {
        $this->skipIfEc2PlatformDisabled();
        $this->elb->setApiVersion('invalid-api-version');
    }

    /**
     * Creates load balancer gently for functional test.
     *
     * If loadbalancer does exist this function deletes it at first turn, and then
     * recreates with the required options.
     *
     * @param  string                                                  $loadBalancerName      Load Balancer Name
     * @param  array|ListenerData|ListenerDescriptionData|ListenerList $listenersList         A list of the Listeners
     * @param  array|string|ListDataType                               $availabilityZonesList optional A list of Availability Zones
     * @param  array|string|ListDataType                               $subnetsList           optional A list of subnet IDs in your
     *                                                                                        VPC to attach to your LoadBalancer.
     * @param  array|string|ListDataType                               $securityGroupsList    optional The security groups assigned to your
     *                                                                                        LoadBalancer within your VPC.
     * @param  string                                                  $scheme                optional The type of LoadBalancer
     * @return LoadBalancerDescriptionData                             Returns LoadBalancerDescriptionData.
     * @throws ElbException
     * @throws ClientException
     */
    public function createLoadBalancerGently($loadBalancerName, $listenersList, $availabilityZonesList = null, $subnetsList = null, $securityGroupsList = null, $scheme = null)
    {
        $tries = 0;
        do {
            try {
                $dnsName = $this->elb->loadBalancer->create(
                    $loadBalancerName, $listenersList, $availabilityZonesList, $subnetsList, $securityGroupsList, $scheme
                );
                $loadBalancer = $this->elb->loadBalancer->describe($loadBalancerName)->get(0);
                break;
            } catch (QueryClientException $e) {
                if ($e->getErrorData()->getCode() != ErrorData::ERR_DUPLICATE_LOAD_BALANCER_NAME) {
                    throw new QueryClientException($e->getErrorData());
                }
                $this->elb->loadBalancer->delete($loadBalancerName);
                $tries++;
            }
        } while ($tries < 2);
        return isset($loadBalancer) ? $loadBalancer : null;
    }

    /**
     * @test
     */
    public function testGetApiHandler()
    {
        $this->skipIfEc2PlatformDisabled();
        $apiHandler = $this->elb->getApiHandler();
        $apiVersion = $this->elb->getApiVersion();
        $apiHandlerClass = 'Scalr\\Service\\Aws\\Elb\\V' . $apiVersion . '\\ElbApi';
        $this->assertInstanceOf($apiHandlerClass, $apiHandler, 'Unexpected api handler class name.');
        $this->assertEquals($apiHandler, $this->elb->getApiHandler(), 'Different instances of ELB API handlers detected.');
        $lbHandler = $this->elb->loadBalancer;
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\Handler\\LoadBalancerHandler', $this->elb->loadBalancer);
        $this->assertEquals($lbHandler, $this->elb->loadBalancer);
    }

    /**
     * @test
     */
    public function testGetRegion()
    {
        $this->skipIfEc2PlatformDisabled();
        $region = $this->elb->getAws()->getRegion();
        $this->assertEquals(self::REGION, $region, 'Unexpected Region detected.');
    }

    /**
     * @test
     */
    public function testGetUrl()
    {
        $this->skipIfEc2PlatformDisabled();
        $url = $this->elb->getUrl();
        $this->assertEquals('elasticloadbalancing.' . self::REGION . '.amazonaws.com', $url, 'Unexpected url detected.');
    }

    /**
     * @test
     */
    public function testCreateLoadBalancers()
    {
        $this->skipIfEc2PlatformDisabled();
        $loadBalancer1 = $this->createLoadBalancerGently(self::getTestName(self::LB_NAME_FUNC_TEST_1), array(
            array(
                'loadBalancerPort' => 80,
                'instancePort' => 1024,
                'protocol' => 'HTTP',
                'sslCertificateId' => null
            )
        ), array(
            AwsTestCase::AVAILABILITY_ZONE_A
        ));
        $loadBalancer2 = $this->createLoadBalancerGently(self::getTestName(self::LB_NAME_FUNC_TEST_2), array(
            array(
                'loadBalancerPort' => 443,
                'instancePort' => 1025,
                'protocol' => 'HTTP',
                'sslCertificateId' => null
            )
        ), array(
            AwsTestCase::AVAILABILITY_ZONE_A,
            AwsTestCase::AVAILABILITY_ZONE_D
        ));
    }

    /**
     * @test
     * @depends testCreateLoadBalancers
     */
    public function testCreateLoadBalancerTheSameName()
    {
        $this->skipIfEc2PlatformDisabled();
        $exeption = null;
        $repo = $this->elb->getEntityManager()->getRepository('Elb:LoadBalancerDescription');
        $lb2 = $repo->find(self::getTestName(self::LB_NAME_FUNC_TEST_2));
        $this->assertInstanceOf(self::CLASS_LOAD_BALANCER_DESCRIPTION_DATA, $lb2);
        try {
            $dnsName = $this->elb->loadBalancer->create(self::getTestName(self::LB_NAME_FUNC_TEST_2), array(
                array(
                    'loadBalancerPort' => 1026,
                    'instancePort'     => 1026,
                    'protocol'         => 'HTTP',
                    'sslCertificateId' => null
                )
            ), array(
                AwsTestCase::AVAILABILITY_ZONE_D
            ));
            $this->assertTrue(false, 'QueryClientException must be thrown here.');
        } catch (QueryClientException $exeption) {
            $this->assertInstanceOf(self::CLASS_ERROR_DATA, $exeption->getErrorData());
            $this->assertEquals(ErrorData::ERR_DUPLICATE_LOAD_BALANCER_NAME, $exeption->getErrorData()->getCode());
        }
    }

    /**
     * @test
     * @depends  testCreateLoadBalancerTheSameName
     */
    public function testDescribeLoadBalancers()
    {
        $this->skipIfEc2PlatformDisabled();
        $list = $this->elb->loadBalancer->describe(array(self::getTestName(self::LB_NAME_FUNC_TEST_1), self::getTestName(self::LB_NAME_FUNC_TEST_2)));
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\LoadBalancerDescriptionList', $list, 'describeLoadBalancers() result must be instance of LoadBalancerDescriptionList');
        $this->assertEquals(2, count($list));
        try {
            $this->elb->loadBalancer->describe('unknown-load-balancer-name');
            $this->assertTrue(false, 'QueryClientException must be thrown here!');
        } catch (QueryClientException $e) {
            $this->assertInstanceOf(self::CLASS_ERROR_DATA, $e->getErrorData());
            $this->assertEquals(ErrorData::ERR_LOAD_BALANCER_NOT_FOUND, $e->getErrorData()->getCode());
        }
        $em = $this->elb->getEntityManager();
        $repository = $em->getRepository('Elb:LoadBalancerDescription');
        /* @var $one LoadBalancerDescriptionData */
        $one = $repository->find(self::getTestName(self::LB_NAME_FUNC_TEST_1));
        $this->assertInstanceOf(self::CLASS_LOAD_BALANCER_DESCRIPTION_DATA, $one);
        $this->assertEquals(self::getTestName(self::LB_NAME_FUNC_TEST_1), $one->loadBalancerName, 'Load Balancer name does not match.');
        try {
            $one->applySecurityGroups(array(
                'sg-1'
            ));
            $this->assertTrue(false, 'QueryClientException must be thrown here.');
        } catch (QueryClientException $e) {
            $this->assertEquals(ErrorData::ERR_INVALID_CONFIGURATION_REQUEST, $e->getErrorData()->getCode());
        }
        $two = $repository->find(self::getTestName(self::LB_NAME_FUNC_TEST_2));
        $this->assertInstanceOf(self::CLASS_LOAD_BALANCER_DESCRIPTION_DATA, $two);
        $this->assertEquals(self::getTestName(self::LB_NAME_FUNC_TEST_2), $two->loadBalancerName, 'Load Balancer name does not match.');
    }

    /**
     * @test
     * @depends  testCreateLoadBalancerTheSameName
     */
    public function testDeleteLoadBalancer()
    {
        $this->skipIfEc2PlatformDisabled();
        $repository = $this->elb->getEntityManager()->getRepository('Elb:LoadBalancerDescription');
        $ret = $this->elb->loadBalancer->delete(self::getTestName(self::LB_NAME_FUNC_TEST_1));
        $one = $repository->find(self::getTestName(self::LB_NAME_FUNC_TEST_1));
        $this->assertNull($one);
        $two = $repository->find(self::getTestName(self::LB_NAME_FUNC_TEST_2));
        $this->assertNotNull($two);
        $two->delete();
        //Object $two does not unset in this case.
        $this->assertNotNull($two);
        $two = $repository->find(self::getTestName(self::LB_NAME_FUNC_TEST_2));
        $this->assertNull($two);
    }

    /**
     * @test
     * @depends  testDeleteLoadBalancer
     */
    public function testDescribeLoadBalancerAfterDelete()
    {
        $this->skipIfEc2PlatformDisabled();
        $list = $this->elb->loadBalancer->describe();
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\LoadBalancerDescriptionList', $list, 'describeLoadBalancers() result must be instance of LoadBalancerDescriptionList');
        $em = $this->elb->getEntityManager();
        $repository = $em->getRepository('Elb:LoadBalancerDescription');
        $one = $repository->find(self::getTestName(self::LB_NAME_FUNC_TEST_1));
        $this->assertNull($one);
        $two = $repository->find(self::getTestName(self::LB_NAME_FUNC_TEST_2));
        $this->assertNull($two);
    }

    /**
     * @test
     */
    public function testAttachLoadBalancerToSubnets()
    {
        $elb = $this->getElbMock();
        $result = $elb->loadBalancer->attachToSubnets('name', array(
            'subnet-73e19518',
            'subnet-73e19519',
            'subnet-73e19520'
        ));
        $this->assertEquals(array(
            'subnet-73e19518',
            'subnet-73e19519',
            'subnet-73e19520'
        ), $result);
    }

    /**
     * @test
     */
    public function testDetachLoadBalancerFromSubnets()
    {
        $subnetsToRemove = array(
            'subnet-1'
        );
        $elb = $this->getElbMock();
        $repo = $elb->getEntityManager()->getRepository('Elb:LoadBalancerDescription');
        $elb->loadBalancer->describe(self::LB_NAME_TEST);
        $result = $elb->loadBalancer->detachFromSubnets(self::LB_NAME_TEST, $subnetsToRemove);
        $this->assertEquals($subnetsToRemove, $result);
        $subnets = $repo->find(self::LB_NAME_TEST)->subnets;
        $this->assertEquals(array(
            'subnet-2'
        ), $subnets);
    }

    /**
     * @test
     */
    public function testApplySecurityGroupsToLoadBalancer()
    {
        $elb = $this->getElbMock();
        $result = $elb->loadBalancer->applySecurityGroups('name', array(
            'sg-4519f42a',
            'sg-4519f42b'
        ));
        $this->assertEquals(array(
            'sg-4519f42a',
            'sg-4519f42b'
        ), $result);
    }

    /**
     * @test
     * @depends testCreateLoadBalancers
     */
    public function testFunctionalLoadBalancerComplex()
    {
        $this->skipIfEc2PlatformDisabled();
        $aws = $this->elb->getAws();
        $aws->ec2->enableEntityManager();
        $aws->getEntityManager()->detachAll();
        $nameTag = new ResourceTagSetData('Name', self::getTestName('elb'));
        $loadBalancerName = self::getTestName(self::LB_NAME_FUNC_TEST);
        $loadBalancer = $this->createLoadBalancerGently(
            $loadBalancerName,
            array(
                array(
                    'loadBalancerPort' => 80,
                    'instancePort' => 1024,
                    'protocol' => 'HTTP',
                    'sslCertificateId' => null
                )
            ),
            $aws->ec2->availabilityZone->describe(AwsTestCase::AVAILABILITY_ZONE_A)
        );

        $this->assertInstanceOf(self::CLASS_LOAD_BALANCER_DESCRIPTION_DATA, $loadBalancer,
            'Could not create load balancer');

        //We should clean up the instances that were created by this test before but had not been terminated.
        $reservationsList = $aws->ec2->instance->describe(
            null, new InstanceFilterData(InstanceFilterNameType::tag('Name'), self::getTestName('elb'))
        );
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservationList'), $reservationsList);
        foreach ($reservationsList as $r) {
            /* @var $i \Scalr\Service\Aws\Ec2\DataType\InstanceData */
            foreach ($r->instancesSet as $i) {
                $ds = $i->describeStatus();
                if ($ds->instanceState->name == InstanceStateData::NAME_RUNNING) {
                    $i->terminate();
                }
            }
        }
        unset($reservationsList);

        try {
            $listener1 = new ListenerData(1025, 1025, 'HTTP');
            $ret = $loadBalancer->createListeners(array(
                $listener1,
                new ListenerData(1026, 1026, 'HTTP')
            ));
            $this->assertTrue($ret, 'Could not create listeners');
            $this->assertEquals(
                spl_object_hash($listener1),
                spl_object_hash($loadBalancer->listenerDescriptions->get(1)->listener)
            );
            $a = array();
            foreach ($loadBalancer->listenerDescriptions as $listenerDescription) {
                if (!isset($a[(string)$listenerDescription->listener])) {
                    $a[(string)$listenerDescription->listener] = '';
                }
                $a[(string)$listenerDescription->listener] .= (string)$listenerDescription->listener;
            }
            $mapping = array();
            $newListenerDescriptions = $this->elb->loadBalancer
                ->describe($loadBalancerName)->get(0)
                ->listenerDescriptions
            ;
            /* @var $listenerDescription ListenerDescriptionData */
            foreach ($newListenerDescriptions as $k => $listenerDescription) {
                $mapping[$listenerDescription->listener->loadBalancerPort] = $k;
                $this->assertArrayHasKey((string) $listenerDescription->listener, $a,
                    'LoadBalancerDescription->listener object has not been updated properly.');
            }
            $ret = $loadBalancer->createListeners();
            $this->assertTrue($ret, 'Cannot create listeners from loadBalancer instance itself.');
            try {
                $ret = $listener1->setPolicies('policy-of-listener-1');
                $this->assertTrue(false, 'Exception must be thrown here.');
            } catch (ClientException $e) {
                $this->assertEquals(ErrorData::ERR_POLICY_NOT_FOUND, $e->getErrorData()->getCode());
            }

            $ret = $loadBalancer->listenerDescriptions->get($mapping[1026])->delete();
            $this->assertTrue($ret, 'Cannot remove listener');
            foreach ($loadBalancer->listenerDescriptions as $listenerDescription) {
                $this->assertNotEquals(1026, $listenerDescription->listener->loadBalancerPort);
            }

            $ret = $this->elb->loadBalancer
                ->enableAvailabilityZones($loadBalancerName, AwsTestCase::AVAILABILITY_ZONE_D)
            ;
            $this->assertContains(AwsTestCase::AVAILABILITY_ZONE_D, $ret);
            $this->assertEquals($ret, $loadBalancer->availabilityZones);

            $ret = $loadBalancer->disableAvailabilityZones(AwsTestCase::AVAILABILITY_ZONE_A);
            $this->assertNotContains(AwsTestCase::AVAILABILITY_ZONE_A, $ret);
            $this->assertEquals($ret, $loadBalancer->availabilityZones);

            //It depends from the instance which should be created before this test.
            //RunInstance test
            $request = new RunInstancesRequestData('ami-82fa58eb', 1, 1);
            $request->instanceType = 'm1.small';
            $placement = new PlacementResponseData();
            $placement->availabilityZone = AwsTestCase::AVAILABILITY_ZONE_A;
            $request->setPlacement($placement);
            $rd = $aws->ec2->instance->run($request);
            $this->assertInstanceOf($this->getEc2ClassName('DataType\\ReservationData'), $rd);
            /* @var $ind \Scalr\Service\Aws\Ec2\DataType\InstanceData */
            $ind = $rd->instancesSet[0];
            unset($request);
            //Sometimes it can't find recently created instance.
            sleep(8);
            //Creates the tag for the instance
            $res = $ind->createTags(array($nameTag, array('key' => 'Extratag', 'value' => 'extravalue')));
            $this->assertTrue($res);

            //Instance state must be in the running state
            $maxTimeout = 300;
            $interval = 2;
            while ($ind->instanceState->name !== InstanceStateData::NAME_RUNNING && $maxTimeout > 0) {
                sleep($interval);
                $maxTimeout -= $interval;
                $interval *= 2;
                $ind = $ind->refresh();
            }
            $this->assertEquals(InstanceStateData::NAME_RUNNING, $ind->instanceState->name);

            $instanceList = $loadBalancer->registerInstances($ind->instanceId);
            $this->assertContains($ind->instanceId, $instanceList->getQueryArray());
            $this->assertEquals($instanceList, $this->elb->loadBalancer->get($loadBalancerName)->instances);
            $this->assertEquals($instanceList, $loadBalancer->instances);
            $instanceStateList = $loadBalancer->describeInstanceHealth();
            $this->assertInstanceOf(self::CLASS_INSTANCE_STATE_LIST, $instanceStateList);
            $this->assertContains($ind->instanceId, $instanceStateList->getQueryArray());
            /* @var $instanceData InstanceData */
            foreach ($loadBalancer->instances as $instanceData) {
                $h = $instanceData->describeHealth();
                $this->assertInstanceOf(self::CLASS_INSTANCE_STATE_LIST, $h);
                $this->assertEquals(1, count($h));
                $this->assertContains($instanceData->instanceId, $h->getQueryArray());
            }
            $dInstanceList = $loadBalancer->deregisterInstances($ind->instanceId);
            $this->assertEmpty($dInstanceList->getQueryArray());
            $this->assertEmpty($loadBalancer->instances->getQueryArray());

            $ind->terminate();

            $loadBalancer->healthCheck->setInterval(29)->setTimeout(4)->setHealthyThreshold(9);

            $ret = $loadBalancer->healthCheck->configure();
            $this->assertEquals(spl_object_hash($ret), spl_object_hash($loadBalancer->healthCheck));
            $this->assertEquals($loadBalancer->healthCheck->interval, 29);
            $this->assertEquals($loadBalancer->healthCheck->timeout, 4);
            $this->assertEquals($loadBalancer->healthCheck->healthyThreshold, 9);
            try {
                foreach ($loadBalancer->listenerDescriptions as $listenerDescription) {
                    if ($listenerDescription->listener->loadBalancerPort == 80) {
                        $listenerDescription->updateSslCertificate('invalid-sertificate-id-test');
                    }
                }
                $this->assertTrue(false, 'ClientException must be thrown here.');
            } catch (ClientException $e) {
                $this->assertEquals(ErrorData::ERR_INVALID_CONFIGURATION_REQUEST, $e->getErrorData()->getCode());
            }

            $ret = $loadBalancer->createAppCookieStickinessPolicy('test-policy-1', 'test_cookie_1');
            $this->assertTrue($ret);
            $this->assertInstanceOf(self::CLASS_APP_COOKIE_STICKINESS_POLICY_LIST, $loadBalancer->policies->appCookieStickinessPolicies);
            $this->assertEquals(1, count($loadBalancer->policies->appCookieStickinessPolicies));
            $this->assertEquals('test-policy-1', $loadBalancer->policies->appCookieStickinessPolicies->get(0)->policyName);
            $this->assertEquals('test_cookie_1', $loadBalancer->policies->appCookieStickinessPolicies->get(0)->cookieName);

            $ret = $loadBalancer->createLbCookieStickinessPolicy('test-lb-policy-1', 1111111111);
            $this->assertTrue($ret);
            $this->assertInstanceOf(self::CLASS_LB_COOKIE_STICKINESS_POLICY_LIST, $loadBalancer->policies->lbCookieStickinessPolicies);
            $this->assertEquals(1, count($loadBalancer->policies->lbCookieStickinessPolicies));
            $this->assertEquals('test-lb-policy-1', $loadBalancer->policies->lbCookieStickinessPolicies->get(0)->policyName);
            $this->assertEquals(1111111111, $loadBalancer->policies->lbCookieStickinessPolicies->get(0)->cookieExpirationPeriod);

            $policy = new AppCookieStickinessPolicyData('app-policy-2');
            $loadBalancer->policies->appCookieStickinessPolicies->append($policy);
            $ret = $policy->create('app_cookie_2');
            $this->assertTrue($ret);
            $this->assertEquals(spl_object_hash($policy), spl_object_hash($loadBalancer->policies->appCookieStickinessPolicies->get(1)));
            unset($policy);

            $ret = $listener1->setPolicies('app-policy-2');
            $this->assertEquals(array('app-policy-2'), $ret);
            $this->assertEquals($ret, $loadBalancer->listenerDescriptions[$mapping[1025]]->policyNames);

            $ret = $listener1->setPolicies();
            $this->assertEmpty($ret);
            $this->assertEmpty($loadBalancer->listenerDescriptions[$mapping[1025]]->policyNames);

            $ret = $loadBalancer->listenerDescriptions[$mapping[1025]]->listener->delete();
            $this->assertTrue($ret, 'Cannot remove listener');
            foreach ($loadBalancer->listenerDescriptions as $listenerDescription) {
                $this->assertNotEquals(1025, $listenerDescription->listener->loadBalancerPort);
            }
            $this->assertContains('app-policy-2', $loadBalancer->policies->appCookieStickinessPolicies->getQueryArray());

            $ret = $loadBalancer->policies->appCookieStickinessPolicies->get(1)->delete();
            $this->assertTrue($ret);
            $this->assertEquals(1, count($loadBalancer->policies->appCookieStickinessPolicies));
            $this->assertNotContains('app-policy-2', $loadBalancer->policies->appCookieStickinessPolicies->getQueryArray());

            $ret = $loadBalancer->policies->lbCookieStickinessPolicies->get(0)->delete();
            $this->assertTrue($ret);
            $this->assertEquals(0, count($loadBalancer->policies->lbCookieStickinessPolicies));
        } catch (\Exception $e) {
            $loadBalancer->delete();
            throw $e;
        }
        $ret = $loadBalancer->delete();
        $this->assertTrue($ret, 'Could not delete loadbalancer');
        unset($loadBalancer);
    }

    /**
     * @test
     */
    public function testLoadBalancerListeners()
    {
        $listenersResult = array(
            'Listeners.member.1.Protocol' => 'HTTP',
            'Listeners.member.1.InstancePort' => 1024,
            'Listeners.member.1.LoadBalancerPort' => 80,
            'Listeners.member.1.SSLCertificateId' => '',
            'Listeners.member.2.Protocol' => 'HTTP',
            'Listeners.member.2.InstancePort' => 1025,
            'Listeners.member.2.LoadBalancerPort' => 1025,
            'Listeners.member.2.SSLCertificateId' => null,
            'Listeners.member.3.Protocol' => 'HTTP',
            'Listeners.member.3.InstancePort' => 1026,
            'Listeners.member.3.LoadBalancerPort' => 1026,
            'Listeners.member.3.SSLCertificateId' => null
        );
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $repo = $elb->getEntityManager()->getRepository('Elb:LoadBalancerDescription');
        $loadBalancer = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $ret = $loadBalancer->createListeners(array(
            new ListenerData(1025, 1025, 'HTTP'),
            new ListenerData(1026, 1026, 'HTTP')
        ));
        $this->assertTrue($ret, 'Could not create listeners');
        foreach ($loadBalancer->listenerDescriptions as $v) {
            $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb', $v->getElb());
            $this->assertEquals($loadBalancerName, $v->getLoadBalancerName());
            $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb', $v->listener->getElb());
            $this->assertEquals($loadBalancerName, $v->listener->getLoadBalancerName());
        }
        $this->assertEquals($listenersResult, $loadBalancer->listenerDescriptions->getQueryArray());
        $ret = $loadBalancer->createListeners();
        $this->assertTrue($ret, 'Can not create listeners from loadBalancer instance itself.');
        $this->assertEquals($listenersResult, $loadBalancer->listenerDescriptions->getQueryArray());
        $loadBalancer->deleteListeners(80);
        $this->assertEquals(array(
            'Listeners.member.1.Protocol' => 'HTTP',
            'Listeners.member.1.InstancePort' => 1025,
            'Listeners.member.1.LoadBalancerPort' => 1025,
            'Listeners.member.1.SSLCertificateId' => null,
            'Listeners.member.2.Protocol' => 'HTTP',
            'Listeners.member.2.InstancePort' => 1026,
            'Listeners.member.2.LoadBalancerPort' => 1026,
            'Listeners.member.2.SSLCertificateId' => null
        ), $loadBalancer->listenerDescriptions->getQueryArray());
        $elb->loadBalancer->deleteListeners($loadBalancerName, array(
            1025,
            1026
        ));
        $this->assertEquals(array(), $loadBalancer->listenerDescriptions->getQueryArray());
        $loadBalancer->delete();
    }

    /**
     * @test
     */
    public function testAvailabilityZones()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $loadBalancer = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $this->assertEquals(array(
            AwsTestCase::AVAILABILITY_ZONE_A
        ), $loadBalancer->availabilityZones);
        $loadBalancer->enableAvailabilityZones(array(
            AwsTestCase::AVAILABILITY_ZONE_D,
            AwsTestCase::AVAILABILITY_ZONE_C
        ));
        $this->assertEquals(array(
            AwsTestCase::AVAILABILITY_ZONE_A,
            AwsTestCase::AVAILABILITY_ZONE_D,
            AwsTestCase::AVAILABILITY_ZONE_C
        ), $loadBalancer->availabilityZones);
        $loadBalancer->disableAvailabilityZones(AwsTestCase::AVAILABILITY_ZONE_D);
        $this->assertEquals(array(
            AwsTestCase::AVAILABILITY_ZONE_A,
            AwsTestCase::AVAILABILITY_ZONE_C
        ), $loadBalancer->availabilityZones);
        $loadBalancer->delete();
    }

    /**
     * @test
     */
    public function testInstances()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $loadBalancer = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $this->assertEquals(array(
            'instance-id-1',
            'instance-id-2'
        ), array_values($loadBalancer->instances->getQueryArray()));
        $loadBalancer->registerInstances(array(
            'instance-id-3',
            'instance-id-4'
        ));
        $this->assertEquals(array(
            'instance-id-1',
            'instance-id-2',
            'instance-id-3',
            'instance-id-4'
        ), array_values($loadBalancer->instances->getQueryArray()));
        $res = $loadBalancer->deregisterInstances(array(
            'instance-id-2',
            'instance-id-3'
        ));
        $this->assertEquals(array(
            'instance-id-1',
            'instance-id-4'
        ), array_values($loadBalancer->instances->getQueryArray()));
        $this->assertEquals($res, $loadBalancer->instances);
        $instanceHealthResult = $loadBalancer->describeInstanceHealth(array(
            'instance-id-1',
            'instance-id-4'
        ));
        $this->assertEquals(2, count($instanceHealthResult));
        for ($i = 0; $i < 1; $i++) {
            foreach (array(
                'description',
                'instance-id',
                'reason-code',
                'state'
            ) as $j) {
                $name = preg_replace('/(-([a-z]))/e', "strtoupper('\\2')", $j);
                $this->assertEquals($j . '-' . ($i * 3 + 1), $instanceHealthResult[$i]->{$name});
            }
        }
        $loadBalancer->delete();
    }

    /**
     * @test
     */
    public function testConfigureHealthCheck()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $loadBalancer = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        //Saves the original value of healthCheck
        $original = clone $loadBalancer->healthCheck;
        //Modify own loadBalancer's healthCheck data
        $loadBalancer->healthCheck->interval = 10;
        $loadBalancer->healthCheck->target = 'TCP:1025';
        $loadBalancer->healthCheck->healthyThreshold = 15;
        $loadBalancer->healthCheck->timeout = 2;
        $loadBalancer->healthCheck->unhealthyThreshold = 1;
        //Saves the expected result
        $expected = clone $loadBalancer->healthCheck;
        //Configure healthCheck using loadBalancer's own dataset
        $result = $loadBalancer->configureHealthCheck();
        $this->assertEquals($expected, $result);
        //Reset loadBalancer
        $elb->loadBalancer->describe($loadBalancerName);
        //Checks if it's ok
        $this->assertEquals($original->toArray(), $loadBalancer->healthCheck->toArray());
        //Configures healthCheck
        $result = $loadBalancer->configureHealthCheck($expected);
        //Checks if the result matches expected
        $this->assertEquals($expected->toArray(), $result->toArray());
        //Checks whether loadBalancer's own healthCheck dataset is updated with the result.
        $this->assertEquals($expected->toArray(), $loadBalancer->healthCheck->toArray());
        unset($expected);
        unset($result);
        $loadBalancer->delete();
    }

    /**
     * @test
     */
    public function testExternalIdentifiersInheritance()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $elbClass = get_class($elb);
        /* @var $lb LoadBalancerDescriptionData */
        $lb = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $this->assertInstanceOf($elbClass, $lb->listenerDescriptions->getElb());
        $this->assertEquals($loadBalancerName, $lb->listenerDescriptions->getLoadBalancerName());
        foreach ($lb->listenerDescriptions as $listenerDescription) {
            $this->assertInstanceOf($elbClass, $listenerDescription->getElb());
            $this->assertEquals($loadBalancerName, $listenerDescription->getLoadBalancerName());
        }
        $this->assertInstanceOf($elbClass, $lb->backendServerDescriptions->getElb());
        $this->assertEquals($loadBalancerName, $lb->backendServerDescriptions->getLoadBalancerName());
        foreach ($lb->backendServerDescriptions as $backendServerDescription) {
            $this->assertInstanceOf($elbClass, $backendServerDescription->getElb());
            $this->assertEquals($loadBalancerName, $backendServerDescription->getLoadBalancerName());
        }
        $this->assertInstanceOf($elbClass, $lb->healthCheck->getElb());
        $this->assertEquals($loadBalancerName, $lb->healthCheck->getLoadBalancerName());
        $this->assertInstanceOf($elbClass, $lb->instances->getElb());
        $this->assertEquals($loadBalancerName, $lb->instances->getLoadBalancerName());
        foreach ($lb->instances as $instance) {
            $this->assertInstanceOf($elbClass, $instance->getElb());
            $this->assertEquals($loadBalancerName, $instance->getLoadBalancerName());
        }
        $this->assertInstanceOf($elbClass, $lb->policies->getElb());
        $this->assertEquals($loadBalancerName, $lb->instances->getLoadBalancerName());
        $this->assertInstanceOf($elbClass, $lb->policies->appCookieStickinessPolicies->getElb());
        $this->assertEquals($loadBalancerName, $lb->policies->appCookieStickinessPolicies->getLoadBalancerName());
        foreach ($lb->policies->appCookieStickinessPolicies as $appCookieStickinessPolicy) {
            $this->assertInstanceOf($elbClass, $appCookieStickinessPolicy->getElb());
            $this->assertEquals($loadBalancerName, $appCookieStickinessPolicy->getLoadBalancerName());
        }
        $this->assertInstanceOf($elbClass, $lb->policies->lbCookieStickinessPolicies->getElb());
        $this->assertEquals($loadBalancerName, $lb->policies->lbCookieStickinessPolicies->getLoadBalancerName());
        foreach ($lb->policies->lbCookieStickinessPolicies as $lbCookieStickinessPolicy) {
            $this->assertInstanceOf($elbClass, $lbCookieStickinessPolicy->getElb());
            $this->assertEquals($loadBalancerName, $lbCookieStickinessPolicy->getLoadBalancerName());
        }
        $this->assertInstanceOf($elbClass, $lb->sourceSecurityGroup->getElb());
        $this->assertEquals($loadBalancerName, $lb->sourceSecurityGroup->getLoadBalancerName());
    }

    /**
     * @test
     */
    public function testSetLoadBalancerListenerSSLCertificate()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $lb = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        /* @var $listenerDescription ListenerDescriptionData */
        $listenerDescription = $lb->listenerDescriptions->get(0);
        $listenerDescription->updateSslCertificate('ssl-cert-1');
        $this->assertEquals($listenerDescription->listener->sslCertificateId, 'ssl-cert-1');
        $listener1 = new ListenerData(1025, 1025, 'HTTP');
        $listener2 = new ListenerData(1026, 1026, 'HTTP');
        $lb->createListeners(array(
            $listener1,
            $listener2
        ));
        $listener1->updateSslCertificate('ssl-cert-11');
        $this->assertEquals($listener1->sslCertificateId, 'ssl-cert-11');
        $this->assertEquals($lb->listenerDescriptions->get(1)->listener->sslCertificateId, 'ssl-cert-11');
        $elb->loadBalancer->setListenerSslCertificate($loadBalancerName, $listener2->loadBalancerPort, 'ssl-cert-12');
        $this->assertEquals($listener2->getSslCertificateId(), 'ssl-cert-12');
        $this->assertEquals($listener2->sslCertificateId, $lb->listenerDescriptions->get(2)->listener->sslCertificateId);
    }

    /**
     * @test
     */
    public function testCreateAppCookieStickinessPolicy()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $lb = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $this->assertEquals(2, count($lb->policies->appCookieStickinessPolicies));
        $policy = new AppCookieStickinessPolicyData('app-csp-policyname-11', 'app-csp-cookiename-11');
        $lb->policies->appCookieStickinessPolicies->append($policy);
        $policy->create();
        $this->assertEquals($policy, $lb->policies->appCookieStickinessPolicies->get(2));
        $this->assertEquals($policy->cookieName, $lb->policies->appCookieStickinessPolicies->get(2)->cookieName);
        $policy->create('app-csp-cookiename-12');
        $this->assertEquals($policy->cookieName, 'app-csp-cookiename-12');
        $this->assertEquals($lb->policies->appCookieStickinessPolicies->get(2)->cookieName, 'app-csp-cookiename-12');
        $lb->createAppCookieStickinessPolicy('app-csp-policyname-22', 'app-csp-cookiename-22');
        $this->assertEquals($lb->policies->appCookieStickinessPolicies->get(3)->cookieName, 'app-csp-cookiename-22');
        $this->assertEquals($lb->policies->appCookieStickinessPolicies->get(3)->policyName, 'app-csp-policyname-22');
    }

    /**
     * @test
     */
    public function testCreateLbCookieStickinessPolicy()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $lb = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $this->assertEquals(2, count($lb->policies->lbCookieStickinessPolicies));
        $policy = new LbCookieStickinessPolicyData('app-lb-policyname-11', 'app-lb-cookie-expiration-period-11');
        $lb->policies->lbCookieStickinessPolicies->append($policy);
        $policy->create();
        $this->assertEquals($policy, $lb->policies->lbCookieStickinessPolicies->get(2));
        $this->assertEquals($policy->cookieExpirationPeriod, $lb->policies->lbCookieStickinessPolicies->get(2)->cookieExpirationPeriod);
        $policy->create('app-lb-cookie-expiration-period-12');
        $this->assertEquals($policy->cookieExpirationPeriod, 'app-lb-cookie-expiration-period-12');
        $this->assertEquals($lb->policies->lbCookieStickinessPolicies->get(2)->cookieExpirationPeriod, 'app-lb-cookie-expiration-period-12');
        $lb->createLbCookieStickinessPolicy('app-lb-policyname-22', 'app-lb-cookie-expiration-period-22');
        $this->assertEquals($lb->policies->lbCookieStickinessPolicies->get(3)->cookieExpirationPeriod, 'app-lb-cookie-expiration-period-22');
        $this->assertEquals($lb->policies->lbCookieStickinessPolicies->get(3)->policyName, 'app-lb-policyname-22');
    }

    /**
     * @test
     */
    public function testSetLoadBalancerPoliciesOfListener()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $lb = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $res = $lb->listenerDescriptions->get(0)->setPolicies(array(
            'new-policy-1',
            'new-policy-2'
        ));
        $this->assertEquals(array(
            'new-policy-1',
            'new-policy-2'
        ), $res);
        $res = $elb->loadBalancer->setPoliciesOfListener($loadBalancerName, 80, 'p-80');
        $this->assertEquals(array(
            'p-80'
        ), $res);
        $this->assertEquals($lb->listenerDescriptions->get(0)->policyNames, $res);
        $ret = $lb->listenerDescriptions->get(0)->listener->setPolicies();
        $this->assertEmpty($ret);
        $this->assertEquals(array(), $lb->listenerDescriptions->get(0)->policyNames);
    }

    /**
     * @test
     */
    public function testDeleteLoadBalancerPolicy()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $lb = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $this->assertEquals(2, count($lb->policies->lbCookieStickinessPolicies));
        $this->assertEquals(2, count($lb->policies->appCookieStickinessPolicies));
        $ret = $lb->deletePolicy('app-csp-policyname-1');
        $this->assertTrue($ret);
        $this->assertEquals(1, count($lb->policies->appCookieStickinessPolicies));
        $ret = $lb->policies->appCookieStickinessPolicies->get(1)->delete();
        $this->assertTrue($ret);
        $this->assertEquals(0, count($lb->policies->appCookieStickinessPolicies));
        $ret = $lb->policies->lbCookieStickinessPolicies->get(0)->delete();
        $this->assertTrue($ret);
        $this->assertEquals(1, count($lb->policies->lbCookieStickinessPolicies));
    }

    /**
     * @test
     */
    public function testSetLoadBalancerPoliciesForBackendServer()
    {
        $loadBalancerName = self::LB_NAME_TEST;
        $elb = $this->getElbMock();
        $lb = $elb->loadBalancer->describe($loadBalancerName)->get(0);
        $this->assertEquals(2, count($lb->backendServerDescriptions));
        $this->assertEquals(array(
            'bs-policy-1',
            'bs-policy-2'
        ), $lb->backendServerDescriptions->get(0)->policyNames);
        $ret = $lb->backendServerDescriptions->get(0)->setPolicies(array(
            'new-bs-policy-1'
        ));
        $this->assertEquals(array(
            'new-bs-policy-1'
        ), $ret);
        $this->assertEquals($ret, $lb->backendServerDescriptions->get(0)->policyNames);
        $ret = $elb->loadBalancer->setPoliciesForBackendServer($loadBalancerName, $lb->backendServerDescriptions->get(1), 'policy-22');
        $this->assertEquals(array(
            'policy-22'
        ), $ret);
        $this->assertEquals($ret, $lb->backendServerDescriptions->get(1)->policyNames);
    }
}