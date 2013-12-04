<?php

namespace Scalr\Tests\Functional\Ui\Controller\Security;

use Scalr\Tests\WebTestCase;

/**
 * Functional test for the Scalr_UI_Controller_Security_Groups class.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    22.02.2013
 */
class GroupsTest extends WebTestCase
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
     * Performs testEditAction as well.
     * @test
     */
    public function testXListGroupsAction()
    {
        $pars = array(
            'platform'      => 'ec2',
            'cloudLocation' => 'us-west-2',
        );
        $uri = '/security/groups/xListGroups/';
        $content = $this->request($uri, $pars);
        $this->assertResponseDataHasKeys(array('id', 'name', 'description'), $content);
        if (!empty($content['data'])) {
            $obj = reset($content['data']);
            //Performs edit action call
            $edit = $this->request('/security/groups/' . $obj['id'] . '/edit', $pars);
            $this->assertInternalType('array', $edit);
            $this->assertArrayHas(true, 'success', $edit);
            $this->assertArrayHasKey('moduleParams', $edit);
            if (!empty($edit['moduleParams']['rules'])) {
                $obj = reset($edit['moduleParams']['rules']);
                $this->assertNotEmpty($obj);
                $this->assertInternalType('array', $obj);
                $keys = array_keys($obj);
                foreach (array('ipProtocol', 'fromPort', 'toPort', 'id', 'comment') as $key) {
                    $this->assertContains($key, $keys);
                }
                if (array_key_exists('cidrIp', $obj)) {
                    $this->assertContains('cidrIp', $keys);
                } else {
                    $this->assertContains('rule', $keys);
                }
            }
        }
    }
}