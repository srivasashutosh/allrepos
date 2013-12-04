<?php
namespace Scalr\Tests\Logger\AuditLog\Documents;

use Scalr\Logger\AuditLog\Documents\FarmSettingsDocument;
use Scalr\Tests\TestCase;

/**
 * FarmSettingsDocument
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     19.02.2013
 */
class FarmSettingsDocumentTest extends TestCase
{
    public function testDocument()
    {
        $document = new FarmSettingsDocument(array(
            'farmid'             => 1788,
            'crypto.key'         => 'crypto-key',
            'szr.upd.repository' => 'szr-upd-repository',
        ));
        $arr = (array)$document;
        $this->assertArrayHas(1788, 'farmid', $arr);
        $this->assertArrayHas('crypto-key', 'crypto.key', $arr);
        $this->assertArrayHas('szr-upd-repository', 'szr.upd.repository', $arr);
        $this->assertArrayHas('FarmSettings', 'datatype', $arr);
        $this->assertArrayHas(null, 'szr.upd.schedule', $arr);

        $this->assertEquals('crypto-key', $document["crypto.key"]);
        $this->assertEquals(1788, $document['farmid']);
    }
}