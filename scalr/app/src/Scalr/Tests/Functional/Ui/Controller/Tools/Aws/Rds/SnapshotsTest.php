<?php

namespace Scalr\Tests\Functional\Ui\Controller\Tools\Aws\Rds;

use Scalr\Tests\WebTestCase;

/**
 * Functional test for the Scalr_UI_Controller_Tools_Aws_Rds_Snapshots class.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.03.2013
 */
class SnapshotsTest extends WebTestCase
{

    /**
     * {@inheritdoc}
     * @see Scalr\Tests.WebTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $this->skipIfPlatformDisabled(\SERVER_PLATFORMS::EC2);
    }

    /**
     * @test
     */
    public function testXListSnapshotsAction()
    {
        $content = $this->request('/tools/aws/rds/snapshots/xListSnapshots/?cloudLocation=us-east-1');
        $this->assertResponseDataHasKeys(array(
            'dtcreated', 'port', 'status', 'engine', 'avail_zone', 'idtcreated', 'storage', 'name', 'id'
        ), $content);
    }
}