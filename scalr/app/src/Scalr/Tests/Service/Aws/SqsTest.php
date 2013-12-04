<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\Sqs\DataType\QueueAttributeData;
use Scalr\Service\Aws;
use Scalr\Tests\Service\AwsTestCase;
use Scalr\Service\Aws\Client\QueryClientException;
use Scalr\Service\Aws\Sqs;

/**
 * Amazon Sqs Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     07.11.2012
 */
class SqsTest extends SqsTestCase
{
    /**
     * @var Sqs
     */
    private $sqs;

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service\Aws.SqsTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isSkipFunctionalTests()) {
            $this->sqs = $this->getContainer()->aws(self::REGION)->sqs;
            $this->sqs->enableEntityManager();
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service\Aws.SqsTestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->sqs);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testFunctionalSqs()
    {
        $this->skipIfEc2PlatformDisabled();

        $client = $this->sqs->getApiHandler()->getClient();

        //This test should pass only when we start it before 60 second timeout.
        $testQueueName = self::getTestName('queue-name');
        $queue = $this->sqs->queue->create($testQueueName, new QueueAttributeData('DelaySeconds', 0));
        $this->assertInstanceOf(SqsTestCase::CLASS_SQS_QUEUE_DATA, $queue);
        $this->assertEquals($testQueueName, $queue->queueName);
        $this->assertEquals(
            $this->sqs->getUrl() . '/' . $this->getContainer()->awsAccountNumber . '/' . $testQueueName,
            preg_replace('#^https?\://#', '', $queue->queueUrl)
        );

        $queue2 = $this->sqs->queue->getUrl($testQueueName);
        $this->assertInstanceOf(SqsTestCase::CLASS_SQS_QUEUE_DATA, $queue2);
        $this->assertEquals(spl_object_hash($queue), spl_object_hash($queue2));
        unset($queue2);

        $queueList = $this->sqs->queue->getList();
        $this->assertInstanceOf(SqsTestCase::CLASS_SQS_QUEUE_LIST, $queueList);

        $queueFromStorage = $this->sqs->queue->get($testQueueName);
        $this->assertInstanceOf(SqsTestCase::CLASS_SQS_QUEUE_DATA, $queueFromStorage);
        $this->assertEquals(spl_object_hash($queue), spl_object_hash($queueFromStorage));
        unset($queueFromStorage);

        $attrs = $queue->fetchAttributes();
        $this->assertInstanceOf(SqsTestCase::CLASS_SQS_QUEUE_ATTRIBUTE_LIST, $attrs);
        $this->assertNotNull($queue->getDelaySeconds());
        unset($attrs);

        $res = $queue->delete();
        $this->assertTrue($res);
        $this->assertNull($this->sqs->queue->get($testQueueName));
    }
}