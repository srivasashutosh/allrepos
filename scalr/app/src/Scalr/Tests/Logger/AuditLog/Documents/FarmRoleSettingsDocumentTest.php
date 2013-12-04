<?php
namespace Scalr\Tests\Logger\AuditLog\Documents;

use Scalr\Logger\AuditLog\Documents\FarmRoleSettingsDocument;
use Scalr\Tests\TestCase;

/**
 * FarmRoleSettingsDocument test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     19.02.2013
 */
class FarmRoleSettingsDocumentTest extends TestCase
{
    public function testDocument()
    {
        $document = new FarmRoleSettingsDocument(array(
            'farmroleid' => 1,
        ));
        $arr = (array) $document;
        $this->assertArrayHas(null, 'aws.instance_type', $arr);
    }
}