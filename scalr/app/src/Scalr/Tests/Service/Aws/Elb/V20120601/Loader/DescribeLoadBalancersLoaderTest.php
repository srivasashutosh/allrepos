<?php
namespace Scalr\Tests\Service\Aws\Elb\V20120601\Loader;

use Scalr\Tests\Service\Aws\ElbTestCase;
use Scalr\Service\Aws\Elb\DataType\PoliciesData;
use Scalr\Service\Aws\Elb\DataType\SourceSecurityGroupData;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\Elb\V20120601\Loader\DescribeLoadBalancersLoader;

/**
 * DescribeLoadBalancersLoader Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     27.09.2012
 */
class DescribeLoadBalancersLoaderTest extends ElbTestCase
{

    public function testLoader()
    {
        $loader = new DescribeLoadBalancersLoader($this->getElbMock(null));
        /* @var $LoadBalancerList \Scalr\Service\Aws\DataType\LoadBalancerDescriptionList */
        $LoadBalancerList = $loader->load($this->getFixtureFileContent('DescribeLoadBalancers.xml'));
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\LoadBalancerDescriptionList', $LoadBalancerList);
        $data = $LoadBalancerList->getOriginal();
        /* @var $first LoadBalancerDescriptionData */
        $first = $data[0];
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\LoadBalancerDescriptionData', $first);
        $this->assertEquals('phpunit-test-load-balancer', $first->loadBalancerName, 'Invalid loadBalancerName value');
        $this->assertEquals('2012-09-27T09:01:31+00:00', $first->createdTime->format('c'), 'Invalid createdTime value');
        $this->assertEquals(array(
            'security-group-1',
            'security-group-2'
        ), $first->securityGroups, 'Invalid securityGroups value');
        $this->assertEquals('phpunit-test-load-balancer-1651037026.us-east-1.elb.amazonaws.com', $first->canonicalHostedZoneName, 'Invalid canonicalHostedZoneName value');
        $this->assertEquals('Z3DZXE0Q79N41H', $first->canonicalHostedZoneNameId, 'Invalid canonicalHostedZoneNameId value');
        $this->assertEquals('internet-facing', $first->scheme, 'Invalid scheme value');
        $this->assertEquals('phpunit-test-load-balancer-1651037026.us-east-1.elb.amazonaws.com', $first->dnsName, 'Invalid dnsName value');
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\HealthCheckData', $first->healthCheck, 'Invalid healthCheck object');
        $this->assertEquals(30, $first->healthCheck->interval, 'Invalid healthCheck interval value');
        $this->assertEquals('TCP:1024', $first->healthCheck->target, 'Invalid healthCheck target value');
        $this->assertEquals(10, $first->healthCheck->healthyThreshold, 'Invalid healthCheck healthyThreshold value');
        $this->assertEquals(5, $first->healthCheck->timeout, 'Invalid healthCheck timeout value');
        $this->assertEquals(2, $first->healthCheck->unhealthyThreshold, 'Invalid healthCheck unhealthyThreshold value');
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\ListenerDescriptionList', $first->listenerDescriptions);
        $listenerDescriptions = $first->listenerDescriptions->getOriginal();
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\ListenerDescriptionData', $listenerDescriptions[0]);
        $this->assertEquals(array(
            'policy-1',
            'policy-2'
        ), $listenerDescriptions[0]->policyNames);
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\ListenerData', $listenerDescriptions[0]->listener);
        $this->assertEquals('HTTP', $listenerDescriptions[0]->listener->protocol);
        $this->assertEquals(80, $listenerDescriptions[0]->listener->loadBalancerPort);
        $this->assertEquals('HTTP', $listenerDescriptions[0]->listener->instanceProtocol);
        $this->assertEquals(1024, $listenerDescriptions[0]->listener->instancePort);
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\InstanceList', $first->instances);
        $instances = $first->instances->getOriginal();
        $this->assertEquals('instance-id-1', $instances[0]->instanceId);
        $this->assertEquals('instance-id-2', $instances[1]->instanceId);
        $this->assertEquals(array(
            'us-east-1a'
        ), $first->availabilityZones);
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\SourceSecurityGroupData', $first->sourceSecurityGroup);
        $this->assertEquals('amazon-elb-sg', $first->sourceSecurityGroup->groupName);
        $this->assertEquals('amazon-elb', $first->sourceSecurityGroup->ownerAlias);
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\BackendServerDescriptionList', $first->backendServerDescriptions);
        $backendServerDescriptions = $first->backendServerDescriptions->getOriginal();
        $this->assertEquals(10001, $backendServerDescriptions[0]->instancePort);
        $this->assertEquals(array(
            'bs-policy-1',
            'bs-policy-2'
        ), $backendServerDescriptions[0]->policyNames);
        $this->assertEquals(10002, $backendServerDescriptions[1]->instancePort);
        $this->assertEquals(array(
            'bs-policy-3',
            'bs-policy-4'
        ), $backendServerDescriptions[1]->policyNames);
        unset($backendServerDescriptions);
        $this->assertEquals(array(
            'subnet-1',
            'subnet-2'
        ), $first->subnets);
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\PoliciesData', $first->policies);
        $this->assertEquals(array(
            'other-policy-1',
            'other-policy-2'
        ), $first->policies->otherPolicies);
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\AppCookieStickinessPolicyList', $first->policies->appCookieStickinessPolicies);
        $appCookieSticknessPolicies = $first->policies->appCookieStickinessPolicies->getOriginal();
        $this->assertEquals('app-csp-policyname-1', $appCookieSticknessPolicies[0]->policyName);
        $this->assertEquals('app-csp-cookiename-1', $appCookieSticknessPolicies[0]->cookieName);
        $this->assertEquals('app-csp-policyname-2', $appCookieSticknessPolicies[1]->policyName);
        $this->assertEquals('app-csp-cookiename-2', $appCookieSticknessPolicies[1]->cookieName);
        unset($appCookieSticknessPolicies);
        $this->assertInstanceOf('Scalr\\Service\\Aws\\Elb\\DataType\\LbCookieStickinessPolicyList', $first->policies->lbCookieStickinessPolicies);
        $lbCookieSticknessPolicies = $first->policies->lbCookieStickinessPolicies->getOriginal();
        $this->assertEquals('lb-csp-policyname-1', $lbCookieSticknessPolicies[0]->policyName);
        $this->assertEquals(21, $lbCookieSticknessPolicies[0]->cookieExpirationPeriod);
        $this->assertEquals('lb-csp-policyname-2', $lbCookieSticknessPolicies[1]->policyName);
        $this->assertEquals(22, $lbCookieSticknessPolicies[1]->cookieExpirationPeriod);
        unset($lbCookieSticknessPolicies);
        $arr = $first->toArray();
        $this->assertEquals(array(
            array(
                'policyNames' => array(
                    'policy-1',
                    'policy-2'
                ),
                'listener' => array(
                    'instancePort' => 1024,
                    'instanceProtocol' => 'HTTP',
                    'loadBalancerPort' => 80,
                    'protocol' => 'HTTP',
                    'sslCertificateId' => ''
                )
            )
        ), $arr['listenerDescriptions']);
    }
}