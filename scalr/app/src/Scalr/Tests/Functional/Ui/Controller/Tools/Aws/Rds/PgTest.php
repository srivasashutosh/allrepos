<?php

namespace Scalr\Tests\Functional\Ui\Controller\Tools\Aws\Rds;

use Scalr\Tests\WebTestCase;

/**
 * Functional test for the Scalr_UI_Controller_Tools_Aws_Rds_Pg class.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    29.03.2013
 */
class PgTest extends WebTestCase
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
    public function testXListAction()
    {
        $content = $this->request('/tools/aws/rds/pg/xList?cloudLocation=us-east-1');
        $this->assertResponseDataHasKeys(array(
            'Engine', 'Description', 'DBParameterGroupName'
        ), $content);
    }
}
