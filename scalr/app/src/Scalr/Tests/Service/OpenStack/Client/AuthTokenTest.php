<?php
namespace Scalr\Tests\Service\OpenStack\Client;

use Scalr\Service\OpenStack\Client\AuthToken;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;

/**
 * AuthTokenTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class AuthTokenTest extends OpenStackTestCase
{
    /**
     * @test
     */
    public function testConstruction()
    {
        $authToken = AuthToken::loadJson('{"access":{"token":{"expires": "2012-09-14T15:11:57.585-05:00","id": "858fb4c2-bf15-4dac-917d-8ec750ae9baa","tenant": {"id": "010101","name": "0101012"}}}}');
        $this->checkToken($authToken);
        $d = $authToken->getAuthDocument();

        $authToken = new AuthToken();
        $authToken
            ->setExpires(new \DateTime('2012-09-14T15:11:57.585-05:00'))
            ->setId('858fb4c2-bf15-4dac-917d-8ec750ae9baa')
            ->setTenantId('010101')
            ->setTenantName('0101012')
            ->setAuthDocument($d)
            ->setRegionEndpoints(array())
            ->setZones(array())
        ;
        $this->checkToken($authToken);
        unset($authToken);


        unset($authToken);
    }

    public function checkToken ($authToken)
    {
        $expected = '["2012-09-14T15:11:57-05:00","858fb4c2-bf15-4dac-917d-8ec750ae9baa","010101","0101012","{\"access\":{\"token\":{\"expires\":\"2012-09-14T15:11:57.585-05:00\",\"id\":\"858fb4c2-bf15-4dac-917d-8ec750ae9baa\",\"tenant\":{\"id\":\"010101\",\"name\":\"0101012\"}}}}",[],[]]';
        $this->assertEquals($expected, (string)$authToken);
        $ser = serialize($authToken);
        unset($authToken);
        /* @var $authToken AuthToken */
        $authToken = unserialize($ser);

        $this->assertEquals($expected, (string)$authToken);
        $this->assertEquals('2012-09-14T15:11:57-05:00', $authToken->getExpires()->format('c'));
        $this->assertEquals('858fb4c2-bf15-4dac-917d-8ec750ae9baa', $authToken->getId());
        $this->assertEquals('010101', $authToken->getTenantId());
        $this->assertEquals('0101012', $authToken->getTenantName());

        $this->assertTrue($authToken->isExpired());
    }

}