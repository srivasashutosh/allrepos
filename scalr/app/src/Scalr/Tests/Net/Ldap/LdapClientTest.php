<?php
namespace Scalr\Tests\Net\Ldap;

use Scalr\Net\Ldap\LdapClient;
use Scalr\Tests\TestCase;

/**
 * LdapClientTest test
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.06.2013
 */
class LdapClientTest extends TestCase
{

    public function providerRealEscape()
    {
        return array(
            array(' aria ', '\\ aria\\ '),
            array("woo\n\ndoo*", 'woodoo\*'),
            array('<>(),#+;"=', '\\<\\>\\(\\)\\,\\#\\+\\;\\"\\='),
        );
    }

    /**
     * @test
     * @dataProvider providerRealEscape
     * @param   string     $input    Input string
     * @param   string     $expected Expected string
     */
    public function testRealEscape($input, $expected)
    {
        $this->assertEquals($expected, LdapClient::realEscape($input));
    }

    /**
     * @test
     */
    public function testLdapFunctional()
    {
        if (!\Scalr::config('scalr.connections.ldap.user')) {
            $this->markTestSkipped('scalr.connections.ldap section is not defined in the config.');
        }

        $ldap = \Scalr::getContainer()->ldap();
        $config = $ldap->getConfig();

        $valid = $ldap->isValidUser($config->user, $config->password);
        $this->assertTrue($valid);

        $valid = $ldap->isValidUser($config->user, '');
        $this->assertFalse($valid);

        $valid = $ldap->isValidUser($config->user, 'ff');
        $this->assertFalse($valid);

        return;
        //This is for local environment
        $groups = $ldap->getUserGroups('airfull1');
        $this->assertContains('Scalr_Air_Full', $groups);

        $groups = $ldap->getUserGroups('airnest1');
        $this->assertContains('Scalr_Air_Full', $groups);
        $this->assertContains('All_Air_Users', $groups);
    }
}