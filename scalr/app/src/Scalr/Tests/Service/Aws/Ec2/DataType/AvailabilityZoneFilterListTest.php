<?php
namespace Scalr\Tests\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneFilterList;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneFilterData;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneFilterNameType;
use Scalr\Tests\Service\AwsTestCase;

/**
 * AvailabilityZoneFilterListTest
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     29.01.2013
 */
class AvailabilityZoneFilterListTest extends AwsTestCase
{

    public function provider()
    {
        return array(
            array(
                //data
                array(
                    array(
                        'name'  => AvailabilityZoneFilterNameType::state(),
                        'value' => 'us',
                    ),
                    new AvailabilityZoneFilterData(AvailabilityZoneFilterNameType::zoneName(), 'us-east-1')
                ),
                //result
                array (
                    'Filter.1.Name'    => (string)AvailabilityZoneFilterNameType::state(),
                    'Filter.1.Value.1' => 'us',
                    'Filter.2.Name'    => (string)AvailabilityZoneFilterNameType::zoneName(),
                    'Filter.2.Value.1' => 'us-east-1',
                ),
            ),
        );
    }

    /**
     * @test
     * @dataProvider provider
     */
    public function testConstructor($data, $result)
    {
        $list = new AvailabilityZoneFilterList($data);
        $this->assertEquals($result, $list->getQueryArrayBare('Filter'));
    }

    /**
     * @test
     * @dataProvider provider
     */
    public function testAppend($data, $result)
    {
        $list = new AvailabilityZoneFilterList();
        foreach ($data as $d) {
            $list->append($d);
        }
        $this->assertEquals($result, $list->getQueryArrayBare('Filter'));
    }
}