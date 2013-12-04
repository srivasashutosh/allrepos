<?php

namespace Scalr\Tests\Functional\Ui\Controller\Platforms;

use Scalr\Tests\WebTestCase;

/**
 * Functional test for the Scalr_UI_Controller_Platforms_Ec2 class.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.02.2013
 */
class Ec2Test extends WebTestCase
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
    public function testXGetAvailZonesAction()
    {
        $content = $this->request('/platforms/ec2/xGetAvailZones?cloudLocation=us-east-1');
        $this->assertResponseDataHasKeys(array('id', 'name', 'state'), $content);
    }

    /**
     * @test
     */
    public function testXGetSnapshotsAction()
    {
        $content = $this->request('/platforms/ec2/xGetSnapshots?cloudLocation=us-east-1');
        $this->assertResponseDataHasKeys(array(
            'snapid'      => $this->matchesRegularExpression('/^snap\-[0-9a-f]+$/'),
            'createdat'   => $this->matchesRegularExpression('/^[\w]{3} [\d]{1,2}, [\d]{4} [\d]{2}\:[\d]{2}\:[\d]{2}$/'),
            'size'        => $this->matchesRegularExpression('/^\d+$/'),
            'snapshotId'  => $this->matchesRegularExpression('/^snap\-[0-9a-f]+$/'),
            'createdDate' => $this->matchesRegularExpression('/^[\w]{3} [\d]{1,2}, [\d]{4} [\d]{2}\:[\d]{2}\:[\d]{2}$/'),
            'volumeId'    => $this->matchesRegularExpression('/^vol\-[0-9a-f]+$/'),
            'description'
        ), $content, true);
    }
}