<?php
namespace Scalr\Service\Aws\Elb\V20120601\Loader;

use Scalr\Service\Aws\Elb;
use Scalr\Service\Aws\LoaderException;
use Scalr\Service\Aws\LoaderInterface;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionList;
use Scalr\Service\Aws\Elb\DataType\LbCookieStickinessPolicyList;
use Scalr\Service\Aws\Elb\DataType\AppCookieStickinessPolicyList;
use Scalr\Service\Aws\Elb\DataType\BackendServerDescriptionList;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionList;
use Scalr\Service\Aws\Elb\DataType\InstanceList;
use Scalr\Service\Aws\Elb\DataType\BackendServerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\SourceSecurityGroupData;
use Scalr\Service\Aws\Elb\DataType\PoliciesData;
use Scalr\Service\Aws\Elb\DataType\InstanceData;
use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\HealthCheckData;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\LbCookieStickinessPolicyData;
use Scalr\Service\Aws\Elb\DataType\AppCookieStickinessPolicyData;

/**
 * DescribeLoadBalancers Loader
 *
 * Loads DescribeLoadBalancersResult
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     29.09.2012
 */
class DescribeLoadBalancersLoader implements LoaderInterface
{

    /**
     * @var ListDataType
     */
    private $result;

    private $elb;

    public function __construct(Elb $elb)
    {
        $this->elb = $elb;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.LoaderInterface::getResult()
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.LoaderInterface::load()
     */
    public function load($xml)
    {
        if (isset($this->result)) {
            unset($this->result);
        }
        $this->result = new LoadBalancerDescriptionList();
        $this->result->setElb($this->elb);
        /* @var $simpleXmlElement \SimpleXmlElement */
        $simpleXmlElement = simplexml_load_string($xml);
        if (!isset($simpleXmlElement->DescribeLoadBalancersResult)) {
            throw new LoaderException('An attempt has been made to load inappropriate XML.');
        }
        if (!empty($simpleXmlElement->DescribeLoadBalancersResult->LoadBalancerDescriptions->member)) {
            /* @var $lb \SimpleXmlElement */
            foreach ($simpleXmlElement->DescribeLoadBalancersResult->LoadBalancerDescriptions->member as $lb) {
                $loadBalancerName = (string) $lb->LoadBalancerName;
                //Tries to look loadBalancer in the repository
                /* @var $loadBalancer LoadBalancerDescriptionData */
                $loadBalancer = $this->elb->loadBalancer->get($loadBalancerName);
                if ($loadBalancer !== null) {
                    //We load information directly into the same object, so we need to reset it before.
                    $loadBalancer->resetObject();
                } else {
                    //Object couldn't be found in the repository and it's to be created.
                    $loadBalancer = new LoadBalancerDescriptionData();
                }
                $loadBalancer->setElb($this->elb);
                $loadBalancer->securityGroups = array();
                if (!empty($lb->SecurityGroups->member)) {
                    foreach ($lb->SecurityGroups->member as $v) {
                        $loadBalancer->securityGroups[] = (string) $v;
                    }
                }
                $loadBalancer->loadBalancerName = $loadBalancerName;
                $loadBalancer->createdTime = new \DateTime((string) $lb->CreatedTime);
                $loadBalancer->canonicalHostedZoneName = (string) $lb->CanonicalHostedZoneName;
                $loadBalancer->canonicalHostedZoneNameId = (string) $lb->CanonicalHostedZoneNameID;
                $loadBalancer->scheme = (string) $lb->Scheme;
                $loadBalancer->dnsName = (string) $lb->DNSName;
                if (isset($lb->HealthCheck->Interval)) {
                    $loadBalancer->healthCheck = new HealthCheckData();
                    $loadBalancer->healthCheck->setElb($this->elb);
                    $loadBalancer->healthCheck->setLoadBalancerName($loadBalancerName);
                    $loadBalancer->healthCheck->interval = (int) $lb->HealthCheck->Interval;
                    $loadBalancer->healthCheck->target = (string) $lb->HealthCheck->Target;
                    $loadBalancer->healthCheck->healthyThreshold = (int) $lb->HealthCheck->HealthyThreshold;
                    $loadBalancer->healthCheck->timeout = (int) $lb->HealthCheck->Timeout;
                    $loadBalancer->healthCheck->unhealthyThreshold = (int) $lb->HealthCheck->UnhealthyThreshold;
                }
                $loadBalancer->listenerDescriptions = new ListenerDescriptionList();
                $loadBalancer->listenerDescriptions->setElb($this->elb);
                $loadBalancer->listenerDescriptions->setLoadBalancerName($loadBalancerName);
                if (!empty($lb->ListenerDescriptions->member)) {
                    /* @var $ld \SimpleXmlElement */
                    foreach ($lb->ListenerDescriptions->member as $ld) {
                        $listenerDescription = new ListenerDescriptionData();
                        $listenerDescription->setElb($this->elb);
                        $listenerDescription->setLoadBalancerName($loadBalancerName);
                        $listenerDescription->listener = new ListenerData();
                        $listenerDescription->listener->setElb($this->elb);
                        $listenerDescription->listener->setLoadBalancerName($loadBalancerName);
                        if (isset($ld->Listener->InstancePort)) {
                            $listenerDescription->listener->instancePort = (int) $ld->Listener->InstancePort;
                            $listenerDescription->listener->instanceProtocol = (string) $ld->Listener->InstanceProtocol;
                            $listenerDescription->listener->loadBalancerPort = (int) $ld->Listener->LoadBalancerPort;
                            $listenerDescription->listener->protocol = (string) $ld->Listener->Protocol;
                            $listenerDescription->listener->sslCertificateId = (string) $ld->Listener->SslCertificateId;
                        }
                        $listenerDescription->policyNames = array();
                        if (!empty($ld->PolicyNames->member)) {
                            foreach ($ld->PolicyNames->member as $v) {
                                $listenerDescription->policyNames[] = (string) $v;
                            }
                        }
                        $loadBalancer->listenerDescriptions->append($listenerDescription);
                        unset($listenerDescription);
                    }
                }
                $loadBalancer->instances = new InstanceList();
                $loadBalancer->instances->setElb($this->elb);
                if (!empty($lb->Instances->member)) {
                    foreach ($lb->Instances->member as $v) {
                        $instance = new InstanceData();
                        $instance->setElb($this->elb);
                        $instance->instanceId = (string) $v->InstanceId;
                        $loadBalancer->instances->append($instance);
                        unset($instance);
                    }
                }
                $loadBalancer->availabilityZones = array();
                if (!empty($lb->AvailabilityZones->member)) {
                    foreach ($lb->AvailabilityZones->member as $v) {
                        $loadBalancer->availabilityZones[] = (string) $v;
                    }
                }
                if (!empty($lb->SourceSecurityGroup)) {
                    $loadBalancer->sourceSecurityGroup = new SourceSecurityGroupData();
                    $loadBalancer->sourceSecurityGroup->setElb($this->elb);
                    $loadBalancer->sourceSecurityGroup->groupName = (string) $lb->SourceSecurityGroup->GroupName;
                    $loadBalancer->sourceSecurityGroup->ownerAlias = (string) $lb->SourceSecurityGroup->OwnerAlias;
                }
                $loadBalancer->backendServerDescriptions = new BackendServerDescriptionList();
                $loadBalancer->backendServerDescriptions->setElb($this->elb);
                if (!empty($lb->BackendServerDescriptions->member)) {
                    foreach ($lb->BackendServerDescriptions->member as $v) {
                        $backendServerDescription = new BackendServerDescriptionData();
                        $backendServerDescription->setElb($this->elb);
                        $backendServerDescription->instancePort = (int) $v->InstancePort;
                        $backendServerDescription->policyNames = array();
                        if (!empty($v->PolicyNames->member)) {
                            foreach ($v->PolicyNames->member as $t) {
                                $backendServerDescription->policyNames[] = (string) $t;
                            }
                        }
                        $loadBalancer->backendServerDescriptions->append($backendServerDescription);
                        unset($backendServerDescription);
                    }
                }
                $loadBalancer->subnets = array();
                if (!empty($lb->Subnets->member)) {
                    foreach ($lb->Subnets->member as $v) {
                        $loadBalancer->subnets[] = (string) $v;
                    }
                }
                //Fixme ID or Id ?
                if (isset($lb->VpcId)) {
                    $loadBalancer->vpcId = (string) $lb->VpcId;
                }
                $loadBalancer->policies = new PoliciesData();
                $loadBalancer->policies->setElb($this->elb);
                $loadBalancer->policies->setLoadBalancerName($loadBalancerName);
                $loadBalancer->policies->appCookieStickinessPolicies = new AppCookieStickinessPolicyList();
                $loadBalancer->policies->appCookieStickinessPolicies->setElb($this->elb);
                $loadBalancer->policies->appCookieStickinessPolicies->setLoadBalancerName($loadBalancerName);
                $loadBalancer->policies->lbCookieStickinessPolicies = new LbCookieStickinessPolicyList();
                $loadBalancer->policies->lbCookieStickinessPolicies->setElb($this->elb);
                $loadBalancer->policies->lbCookieStickinessPolicies->setLoadBalancerName($loadBalancerName);
                $loadBalancer->policies->otherPolicies = array();
                if (!empty($lb->Policies->AppCookieStickinessPolicies->member)) {
                    foreach ($lb->Policies->AppCookieStickinessPolicies->member as $v) {
                        $object = new AppCookieStickinessPolicyData();
                        $object->setElb($this->elb);
                        $object->setLoadBalancerName($loadBalancerName);
                        $object->cookieName = (string) $v->CookieName;
                        $object->policyName = (string) $v->PolicyName;
                        $loadBalancer->policies->appCookieStickinessPolicies->append($object);
                        unset($object);
                    }
                }
                if (!empty($lb->Policies->LBCookieStickinessPolicies->member)) {
                    foreach ($lb->Policies->LBCookieStickinessPolicies->member as $v) {
                        $object = new LbCookieStickinessPolicyData();
                        $object->setElb($this->elb);
                        $object->setLoadBalancerName($loadBalancerName);
                        $object->policyName = (string) $v->PolicyName;
                        //Long int
                        $object->cookieExpirationPeriod = ((string) $v->CookieExpirationPeriod) - 0;
                        $loadBalancer->policies->lbCookieStickinessPolicies->append($object);
                        unset($object);
                    }
                }
                if (!empty($lb->Policies->OtherPolicies)) {
                    if (!empty($lb->Policies->OtherPolicies->member)) {
                        foreach ($lb->Policies->OtherPolicies->member as $v) {
                            $loadBalancer->policies->otherPolicies[] = (string) $v;
                        }
                    }
                }
                $this->result->append($loadBalancer);
                unset($loadBalancer);
            }
        }
        return $this->result;
    }
}