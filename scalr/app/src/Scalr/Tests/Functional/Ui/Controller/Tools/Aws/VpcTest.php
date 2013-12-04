<?php

namespace Scalr\Tests\Functional\Ui\Controller\Tools\Aws;

use Scalr\Tests\WebTestCase;

/**
 * Functional test for the Scalr_UI_Controller_Tools_Aws_Vpc class.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.02.2013
 */
class VpcTest extends WebTestCase
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
    public function testXListViewSubnetsAction()
    {
        $content = $this->request('/tools/aws/vpc/xListViewSubnets?cloudLocation=us-east-1');
        $this->assertResponseDataHasKeys(array('id', 'description'), $content);
    }
}