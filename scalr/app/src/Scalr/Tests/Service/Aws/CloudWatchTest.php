<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\Client\QueryClientException;
use Scalr\Service\Aws\CloudWatch\DataType\DatapointList;
use Scalr\Service\Aws\CloudWatch\DataType\DatapointData;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionData;
use Scalr\Service\Aws\CloudWatch\DataType\MetricData;
use Scalr\Service\Aws\CloudWatch;

/**
 * Amazon CloudWatch Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 */
class CloudWatchTest extends CloudWatchTestCase
{
    /**
     * @var CloudWatch
     */
    private $cloudWatch;

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service\Aws.CloudWatchTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isSkipFunctionalTests()) {
            $this->cloudWatch = $this->getContainer()->aws(self::REGION)->cloudWatch;
            $this->cloudWatch->enableEntityManager();
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service\Aws.CloudWatchTestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->cloudWatch = null;
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testCommon()
    {
        if (!class_exists('HttpRequest')) {
            $this->assertTrue(false, 'HttpRequest class does not exist! Please install php dependencies.');
        }
        $cw = $this->getCloudWatchMock();
        $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $cw);
        $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_METRIC_HANDLER, $cw->metric);
    }

    /**
     * @test
     */
    public function testFunctionalMetric()
    {
        $this->skipIfEc2PlatformDisabled();
        $ds = array();
        $metricList = $this->cloudWatch->metric->list(null, 'AWS/EC2');
        $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_METRIC_LIST, $metricList);
        $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $metricList->getCloudWatch());
        /* @var $metric MetricData */
        foreach ($metricList as $metric) {
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_METRIC_DATA, $metric);
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $metric->getCloudWatch());
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_DIMENSION_LIST, $metric->dimensions);
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $metric->dimensions->getCloudWatch());
            $this->assertNotNull($metric->dimensions->getMetricName());
            $this->assertEquals($metric->metricName, $metric->dimensions->getMetricName());
            /* @var $dimension DimensionData */
            foreach ($metric->dimensions as $dimension) {
                if ($metric->metricName == 'NetworkOut' && count($ds) < 2) {
                    $ds[] = $dimension;
                }
                $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_DIMENSION_DATA, $dimension);
                $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $dimension->getCloudWatch());
                $this->assertNotNull($dimension->getMetricName());
                $this->assertEquals($metric->metricName, $dimension->getMetricName());
            }
        }
        try {
            $datapointList = $this->cloudWatch->metric->getStatistics(
                'NetworkOut',
                new \DateTime ('-1 hour', new \DateTimeZone('UTC')),
                new \DateTime (null, new \DateTimeZone('UTC')),
                array('Average'),
                'AWS/EC2',
                300,
                'Bytes',
                $ds
            );
        } catch (QueryClientException $e) {
            $ignoreDatapoint = true;
        }
        if (!isset($ignoreDatapoint)) {
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_DATAPOINT_LIST, $datapointList);
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $datapointList->getCloudWatch());
            foreach ($datapointList as $datapoint) {
                $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_DATAPOINT_DATA, $datapoint);
                $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $datapoint->getCloudWatch());
            }
        }
    }

    /**
     * @test
     */
    public function testMetricStatistic()
    {
        $tstart = new \DateTime('-3600 second', new \DateTimeZone('UTC'));
        $tend = new \DateTime(null, new \DateTimeZone('UTC'));
        $this->assertEquals(3600, $tend->getTimestamp() - $tstart->getTimestamp());
        $cloudWatch = $this->getCloudWatchMock();
        $metricList = $cloudWatch->metric->list(null, 'AWS/EC2');
        $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_METRIC_LIST, $metricList);
        $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $metricList->getCloudWatch());
        $ds = array();
        /* @var $metric MetricData */
        foreach ($metricList as $metric) {
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_METRIC_DATA, $metric);
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $metric->getCloudWatch());
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_DIMENSION_LIST, $metric->dimensions);
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $metric->dimensions->getCloudWatch());
            $this->assertNotNull($metric->dimensions->getMetricName());
            $this->assertEquals($metric->metricName, $metric->dimensions->getMetricName());
            /* @var $dimension DimensionData */
            foreach ($metric->dimensions as $dimension) {
                if ($metric->metricName == 'NetworkOut' && count($ds) < 2) {
                    $ds[] = $dimension;
                }
                $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_DIMENSION_DATA, $dimension);
                $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $dimension->getCloudWatch());
                $this->assertNotNull($dimension->getMetricName());
                $this->assertEquals($metric->metricName, $dimension->getMetricName());
            }
        }
        $datapointList = $cloudWatch->metric->getStatistics(
            'NetworkOut',
            new \DateTime ('-1 hour', new \DateTimeZone('UTC')),
            new \DateTime (null, new \DateTimeZone('UTC')),
            array('Average'),
            'AWS/EC2',
            300,
            'Bytes',
            $ds
        );
        $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_DATAPOINT_LIST, $datapointList);
        $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $datapointList->getCloudWatch());
        foreach ($datapointList as $datapoint) {
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH_DATAPOINT_DATA, $datapoint);
            $this->assertInstanceOf(self::CLASS_CLOUD_WATCH, $datapoint->getCloudWatch());
        }
    }
}