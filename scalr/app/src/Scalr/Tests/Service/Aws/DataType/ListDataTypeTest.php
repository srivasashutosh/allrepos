<?php
namespace Scalr\Tests\Service\Aws\DataType;

use Scalr\Service\Aws\Ec2\DataType\IpPermissionList;
use Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingData;
use Scalr\Service\Aws\Ec2\DataType\ResourceTagSetData;
use Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList;
use Scalr\Service\Aws\Ec2\DataType\GroupList;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionList;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionList;
use Scalr\Service\Aws;
use Scalr\Tests\Service\AwsTestCase;
use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\Elb\DataType\InstanceData;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AWS ListDataType test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.09.2012
 */
class ListDataTypeTest extends AwsTestCase
{

    const CLASS_INSTANCE_DATA = 'Scalr\\Service\\Aws\\Elb\\DataType\\InstanceData';

    const CLASS_LISTENER_DATA = 'Scalr\\Service\\Aws\\Elb\\DataType\\ListenerData';

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        $instanceData = new InstanceData();
        $instanceData->instanceId = 1;

        $instanceData2 = clone $instanceData;
        $instanceData2->instanceId = 2;

        $instanceData3 = clone $instanceData;
        $instanceData3->instanceId = 3;

        $listener = new ListenerData();
        $listener->instancePort = 901;
        $listener->protocol = 'HTTP';
        $listener->loadBalancerPort = 18901;

        $listener2 = new ListenerData();
        $listener2->instancePort = 902;
        $listener2->protocol = 'HTTPS';
        $listener2->loadBalancerPort = 18902;

        return array(
            //Single element is passed as list
            array(
                'loadBalancerOne',
                'loadBalancerNames',
                null,
                array(
                    'LoadBalancerNames.member.1' => 'loadBalancerOne'
                )
            ),
            //Array of the values is passed as list
            array(
                array(
                    'loadBalancerOne',
                    'loadBalancerTwo',
                    'loadBalancerThree'
                ),
                'loadBalancerNames',
                null,
                array(
                    'LoadBalancerNames.member.1' => 'loadBalancerOne',
                    'LoadBalancerNames.member.2' => 'loadBalancerTwo',
                    'LoadBalancerNames.member.3' => 'loadBalancerThree'
                )
            ),
            //Associative array is passed as list
            array(
                array(
                    array(
                        'loadBalancerNames' => 'loadBalancerOne',
                        'anoterprop' => true
                    ),
                    array(
                        'different_prop' => 'different value',
                        'loadBalancerNames' => 'loadBalancerTwo',
                        'anoter_prop' => ''
                    )
                ),
                'loadBalancerNames',
                null,
                array(
                    'LoadBalancerNames.member.1' => 'loadBalancerOne',
                    'LoadBalancerNames.member.2' => 'loadBalancerTwo'
                )
            ),
            //Single DataType object is passed as list
            array(
                $instanceData,
                'instanceId',
                null,
                array(
                    'InstanceId.member.1' => 1
                ),
                'InstanceId.member.1=1'
            ),
            //Array of DataType objects is passed as list
            array(
                array(
                    $instanceData,
                    $instanceData2,
                    $instanceData3
                ),
                'instanceId',
                self::CLASS_INSTANCE_DATA,
                array(
                    'InstanceId.member.1' => $instanceData->instanceId,
                    'InstanceId.member.2' => $instanceData2->instanceId,
                    'InstanceId.member.3' => $instanceData3->instanceId
                )
            ),
            //Array of DataType objects is passed as list with complex propertyName
            array(
                array(
                    $listener,
                    $listener2
                ),
                array(
                    'protocol',
                    'instancePort',
                    'loadBalancerPort'
                ),
                self::CLASS_LISTENER_DATA,
                array(
                    'Undefined.member.1.Protocol' => 'HTTP',
                    'Undefined.member.1.InstancePort' => 901,
                    'Undefined.member.1.LoadBalancerPort' => 18901,
                    'Undefined.member.2.Protocol' => 'HTTPS',
                    'Undefined.member.2.InstancePort' => 902,
                    'Undefined.member.2.LoadBalancerPort' => 18902
                )
            ),
            //Array of Associative arrays is passed as list with complex propertyName
            array(
                array(
                    array(
                        'loadBalancerPort' => 901,
                        'instancePort' => 901,
                        'protocol' => 'HTTP'
                    ),
                    array(
                        'loadBalancerPort' => 902,
                        'instancePort' => 902,
                        'protocol' => 'HTTPS'
                    )
                ),
                array(
                    'protocol',
                    'instancePort',
                    'loadBalancerPort'
                ),
                self::CLASS_LISTENER_DATA,
                array(
                    'Undefined.member.1.Protocol' => 'HTTP',
                    'Undefined.member.1.InstancePort' => 901,
                    'Undefined.member.1.LoadBalancerPort' => 901,
                    'Undefined.member.2.Protocol' => 'HTTPS',
                    'Undefined.member.2.InstancePort' => 902,
                    'Undefined.member.2.LoadBalancerPort' => 902
                )
            )
        );
    }

    /**
     * @test
     * @dataProvider provider
     */
    public function testConstructor($aListData, $propertyName, $dataClassName = null, $opt = array())
    {
        $list = new ListDataType($aListData, $propertyName, $dataClassName);
        $this->assertEquals($opt, $list->getQueryArray(), 'Unexpected options array');
        $this->assertEquals(count($opt), (is_array($propertyName) ? count($propertyName) : 1) * count($list), 'Different number of arguments');
        $this->assertInstanceOf('Iterator', $list, 'List does not implement Iterator interface.');
        $this->assertInstanceOf('Countable', $list, 'List does not implement Countable interface.');
        if (!is_array($propertyName)) {
            $keys = array_keys($opt);
            foreach ($list->getQueryArray() as $key => $value) {
                $this->assertContains($key, $keys, 'Missing key');
                $this->assertContains($value, $opt, 'Missing value');
            }
            $this->assertEquals(array_values($opt), $list->getComputed(), 'Unexpected out of getComputed() method.');
        } else if ($dataClassName != null) {
            foreach ($list as $value) {
                $this->assertInstanceOf($dataClassName, $value);
            }
        }
        unset($list);
    }

    /**
     * @test
     * @expectedException   \InvalidArgumentException
     */
    public function testIllegalObject()
    {
        $instanceData = new InstanceData();
        $instanceData->instanceId = 1;
        $list = new ListDataType($instanceData, 'instanceId', 'stdClass');
        $list->getComputed();
    }

    /**
     * @test
     */
    public function testPropertyInheritance()
    {
        $lbName = 'test-load-balancer';
        $elbStub = $this->getServiceInterfaceMock(Aws::SERVICE_INTERFACE_ELB);
        $elbClassName = get_class($elbStub);
        $lbList = new LoadBalancerDescriptionList();
        $lbList->setElb($elbStub);
        $lbList2 = clone $lbList;
        $listenerDescriptionList = new ListenerDescriptionList();
        for ($i = 0; $i < 3; $i++) {
            $listenerDescription = new ListenerDescriptionData();
            $listenerDescription->listener = new ListenerData();
            $listenerDescription->listener->instancePort = 1024 + $i;
            $listenerDescriptionList->append($listenerDescription);
            unset($listenerDescription);
        }
        $lb = new LoadBalancerDescriptionData();
        $lb->setLoadBalancerName($lbName);
        $lb->listenerDescriptions = $listenerDescriptionList;
        $lb2 = clone $lb;
        //Append test
        $lbList->append($lb);
        $this->assertInstanceOf($elbClassName, $lb->getElb());
        $this->assertEquals($lbName, $lb->getLoadBalancerName());
        $this->assertInstanceOf($elbClassName, $lb->listenerDescriptions->getElb());
        $this->assertEquals($lbName, $lb->listenerDescriptions->getLoadBalancerName());
        /* @var $listenerDescription ListenerDescriptionData */
        foreach ($lb->listenerDescriptions as $listenerDescription) {
            $this->assertInstanceOf($elbClassName, $listenerDescription->getElb());
            $this->assertEquals($lbName, $listenerDescription->getLoadBalancerName());
            $this->assertInstanceOf($elbClassName, $listenerDescription->listener->getElb());
            $this->assertEquals($lbName, $listenerDescription->listener->getLoadBalancerName());
        }
        //Array access set test
        $lbList2[0] = $lb2;
        $this->assertInstanceOf($elbClassName, $lb2->getElb());
        $this->assertInstanceOf($elbClassName, $lb2->listenerDescriptions->getElb());
        /* @var $listenerDescription ListenerDescriptionData */
        foreach ($lb2->listenerDescriptions as $listenerDescription) {
            $this->assertInstanceOf($elbClassName, $listenerDescription->getElb());
        }
    }

    /**
     * @test
     */
    public function testIssetMemberOfTheList()
    {
        $list = new ResourceTagSetList();
        $list->append(new ResourceTagSetData('key-0', 'value-0'));
        $this->assertFalse(isset($list[46]));
        $this->assertFalse(isset($list[1]->key));
        $this->assertTrue(isset($list[0]->value));
        $this->assertTrue(isset($list->get(0)->value));
        $this->assertTrue(empty($list->get(3)->value));

        //non-existent variable test
        $this->assertFalse(isset($foo[0]->missing[12]->none_xistent));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testNonReflectionProperties()
    {
        $data = new BlockDeviceMappingData();
        $data->newOption = 'new-option';
    }

    /**
     * @test
     */
    public function testConstructorFromArrayPartialProperties()
    {
        //We provide there not all the properties from the property array
        $list = new IpPermissionList(array(array(
            'ipProtocol' => 'tcp',
        )));
        $this->assertInstanceOf($this->getEc2ClassName('DataType\\IpPermissionData'), $list[0]);
        $this->assertEquals('tcp', $list[0]->ipProtocol);
    }
}