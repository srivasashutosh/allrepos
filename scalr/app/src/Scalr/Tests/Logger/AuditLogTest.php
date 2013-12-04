<?php
namespace Scalr\Tests\Logger;

use Scalr\Logger\AuditLog\KeyValueRecord;
use Scalr\Logger\AuditLog\Documents\FarmSettingsDocument;
use Scalr\Tests\TestCase;
use Scalr\Logger\AuditLog\AuditLogTags;
use Scalr\Logger\AuditLog;
use Scalr\DependencyInjection\Container;
use Scalr\Logger\AuditLog\Documents\FarmDocument;

/**
 * Audit Log test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.11.2012
 */
class AuditLogTest extends TestCase
{
    const TEST_USER_ID = 7362;

    const CLASS_AUDITLOG_LOG_RECORD = 'Scalr\\Logger\\AuditLog\\LogRecord';

    /**
     * @var AuditLog
     */
    protected $logger;

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $container = \Scalr::getContainer();
        if ($container->user === null) {
            $this->setTestUserToContainer();
        } else {
            try {
                $container->user->getId();
            } catch (\Exception $e) {
                $this->setTestUserToContainer();
            }
        }
        $this->logger = $container->auditLog;
    }

    /**
     * Sets test user to container
     */
    private function setTestUserToContainer()
    {
        $container = \Scalr::getContainer();
        $container->user = new \Scalr_Account_User();
        $container->user->loadById(self::TEST_USER_ID);
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        unset($this->logger);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testDocumentToArray()
    {
        $farm = new FarmDocument();
        $farm['farmid'] = 10;
        $farm['name'] = 'farm name';

        $arr = $farm;
        $this->assertArrayHas(10, 'farmid', $arr);
        $this->assertArrayHas('farm name', 'name', $arr);
        $this->assertArrayHas('Farm', 'datatype', $arr);

        $farm2 = new FarmDocument((array)$farm);
        $this->assertEquals($arr['farmid'], $farm2['farmid']);
        $this->assertEquals($arr['name'], $farm2['name']);

        $dbfarm = new \DBFarm();
        $dbfarm->ID = 10;
        $dbfarm->Name = 'farm name';
        $farm3 = FarmDocument::createFromDbFarm($dbfarm);
        $this->assertEquals($arr['farmid'], $farm3['farmid']);
        $this->assertEquals($arr['name'], $farm3['name']);
    }

    /**
     * @test
     */
    public function testAuditLogTags()
    {
        $tags = new AuditLogTags();
        try {
            $tags->add('unknown-tag--');
            $this->assertTrue(false, 'Exeption must be thrown in this case.');
        } catch(\InvalidArgumentException $e) {}
        $tags->add(AuditLogTags::TAG_STOP, AuditLogTags::TAG_PAUSE);
        $this->assertEquals(AuditLogTags::TAG_STOP . ',' . AuditLogTags::TAG_PAUSE, (string)$tags);
        $tags->remove(AuditLogTags::TAG_STOP);
        $this->assertEquals(AuditLogTags::TAG_PAUSE, (string) $tags);
        $this->assertEquals(isset($tags->pause), true);
        unset($tags->pause);
        $tags->add(AuditLogTags::TAG_CREATE);
        $this->assertEquals(array(AuditLogTags::TAG_CREATE), $tags->get());

        $tags = new AuditLogTags(AuditLogTags::TAG_UPDATE, AuditLogTags::TAG_REMOVE);
        $this->assertEquals(AuditLogTags::TAG_UPDATE . ',' . AuditLogTags::TAG_REMOVE, (string) $tags);
        $tags = new AuditLogTags(array(AuditLogTags::TAG_UPDATE, AuditLogTags::TAG_STOP));
        $this->assertEquals(AuditLogTags::TAG_UPDATE . ',' . AuditLogTags::TAG_STOP, (string) $tags);
    }

    /**
     * @test
     */
    public function testKeyValueRecord()
    {

        $objectBefore = new FarmSettingsDocument(array(
            'farmid'     => 114,
            'crypto.key' => 'was-crypto-key',
        ));

        $object = new FarmSettingsDocument(array(
            'farmid'             => 114,
            'crypto.key'         => 'new-crypto-key',
            'szr.upd.repository' => 'new-szr-upd-repository'
        ));

        //Computes key value record which represents the differences between two states.
        $record = $this->logger->getKeyValueRecord($objectBefore, $object);
        $this->assertContains('FarmSettingsDocument', $record->getObjectDataType());
        $this->assertObjectHasAttribute('crypto.key', $record);
        $this->assertEquals('was-crypto-key', $record->{'crypto.key'}['old_value']);
        $this->assertEquals('new-crypto-key', $record->{'crypto.key'}['new_value']);

        $this->assertObjectHasAttribute('szr.upd.repository', $record);
        $this->assertEquals(null, $record->{'szr.upd.repository'}['old_value']);
        $this->assertEquals('new-szr-upd-repository', $record->{'szr.upd.repository'}['new_value']);
        $this->assertObjectHasAttribute('farmid', $record);
    }

    /**
     * @test
     */
    public function testFunctionalAuditLog()
    {
        $this->markTestSkipped();

        $dbfarm = new \DBFarm();
        $dbfarm->ID = 2332;
        $dbfarm->Name = 'test farm';

        $dbfarm2 = new \DBFarm();
        $dbfarm2->ID = 2332;
        $dbfarm2->Name = 'Changed name';

        $res = $this->logger->log(
            'I have just changed the name of the farm',
            array(AuditLogTags::TAG_UPDATE, AuditLogTags::TAG_TEST),
            $dbfarm2, $dbfarm
        );
        $this->assertTrue($res);

        $records = $this->logger->find(array('tags' => array('$in' => array('update'))), array('time' => -1), 1);
        $this->assertEquals(1, count($records));
        $this->assertInstanceOf(self::CLASS_AUDITLOG_LOG_RECORD, $records[0]);

        $this->logger->getStorage()->cleanup();
    }
}