<?php

namespace Scalr\Tests\Functional\Ui\Controller\Tools\Aws\Rds;

use Scalr\Tests\WebTestCase;

/**
 * Functional test for the Scalr_UI_Controller_Tools_Aws_Rds_Sg class.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    29.03.2013
 */
class SgTest extends WebTestCase
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
        $content = $this->request('/tools/aws/rds/sg/xList/?cloudLocation=us-east-1');
        $this->assertResponseDataHasKeys(array(
            'EC2SecurityGroups' => $this->isType('array'),
            'DBSecurityGroupDescription',
            'IPRanges' => $this->isType('array'),
            'OwnerId' => $this->logicalNot($this->isEmpty()),
            'DBSecurityGroupName' => $this->logicalNot($this->isEmpty())
        ), $content);
    }
}
