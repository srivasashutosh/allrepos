<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\CloudWatch;
use Scalr\Tests\Service\AwsTestCase;

/**
 * Amazon CloudWatch TestCase
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 */
class CloudWatchTestCase extends AwsTestCase
{

    const CLASS_CLOUD_WATCH = 'Scalr\\Service\\Aws\\CloudWatch';

    const CLASS_CLOUD_WATCH_METRIC_HANDLER = 'Scalr\\Service\\Aws\\CloudWatch\\Handler\\MetricHandler';

    const CLASS_CLOUD_WATCH_METRIC_LIST = 'Scalr\\Service\\Aws\\CloudWatch\\DataType\\MetricList';

    const CLASS_CLOUD_WATCH_METRIC_DATA = 'Scalr\\Service\\Aws\\CloudWatch\\DataType\\MetricData';

    const CLASS_CLOUD_WATCH_DIMENSION_LIST = 'Scalr\\Service\\Aws\\CloudWatch\\DataType\\DimensionList';

    const CLASS_CLOUD_WATCH_DIMENSION_DATA = 'Scalr\\Service\\Aws\\CloudWatch\\DataType\\DimensionData';

    const CLASS_CLOUD_WATCH_DATAPOINT_LIST = 'Scalr\\Service\\Aws\\CloudWatch\\DataType\\DatapointList';

    const CLASS_CLOUD_WATCH_DATAPOINT_DATA = 'Scalr\\Service\\Aws\\CloudWatch\\DataType\\DatapointData';

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
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixturesDirectory()
     */
    public function getFixturesDirectory()
    {
        return parent::getFixturesDirectory() . '/CloudWatch';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixtureFilePath()
     */
    public function getFixtureFilePath($filename)
    {
        return $this->getFixturesDirectory() . '/' . CloudWatch::API_VERSION_CURRENT . '/' . $filename;
    }

    /**
     * Gets CloudWatch Mock
     *
     * @param    callback $callback
     * @return   CloudWatch      Returns CloudWatch Mock class
     */
    public function getCloudWatchMock($callback = null)
    {
        return $this->getServiceInterfaceMock('CloudWatch');
    }
}