<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\Sqs\DataType\QueueList;
use Scalr\Service\Aws\Sqs;
use Scalr\Tests\Service\AwsTestCase;

/**
 * Amazon Sqs TestCase
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     07.11.2012
 */
class SqsTestCase extends AwsTestCase
{

    const CLASS_SQS = 'Scalr\\Service\\Aws\\Sqs';

    const CLASS_SQS_QUEUE_LIST = 'Scalr\\Service\\Aws\\Sqs\\DataType\\QueueList';

    const CLASS_SQS_QUEUE_DATA = 'Scalr\\Service\\Aws\\Sqs\\DataType\\QueueData';

    const CLASS_SQS_QUEUE_ATTRIBUTE_LIST = 'Scalr\\Service\\Aws\\Sqs\\DataType\\QueueAttributeList';

    const CLASS_SQS_MESSAGE_DATA = 'Scalr\\Service\\Aws\\Sqs\\DataType\\MessageData';

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
        return parent::getFixturesDirectory() . '/Sqs';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixtureFilePath()
     */
    public function getFixtureFilePath($filename)
    {
        return $this->getFixturesDirectory() . '/' . Sqs::API_VERSION_CURRENT . '/' . $filename;
    }

    /**
     * Gets Sqs Mock
     *
     * @param    callback $callback
     * @return   Sqs      Returns Sqs Mock class
     */
    public function getCloudWatchMock($callback = null)
    {
        return $this->getServiceInterfaceMock('Sqs');
    }
}