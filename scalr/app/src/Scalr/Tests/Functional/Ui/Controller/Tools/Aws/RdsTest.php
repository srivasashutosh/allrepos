<?php

namespace Scalr\Tests\Functional\Ui\Controller\Tools\Aws;

use Scalr\Tests\WebTestCase;

/**
 * Functional test for the Scalr_UI_Controller_Tools_Aws_Rds class.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    29.03.2013
 */
class RdsTest extends WebTestCase
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
    public function testXListLogsAction()
    {
        $content = $this->request('/tools/aws/rds/xListLogs?cloudLocation=us-east-1');
        $this->assertResponseDataHasKeys(array('Message', 'Date', 'SourceIdentifier', 'SourceType'), $content);
    }
}