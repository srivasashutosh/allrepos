<?php
namespace Scalr\Tests\Service\OpenStack\Client;

use Scalr\Service\OpenStack\Exception\RestClientException;
use Scalr\Service\OpenStack\Type\AppFormat;
use Scalr\Service\OpenStack\Client\RestClientResponse;
use Scalr\Tests\Service\OpenStack\OpenStackTestCase;

/**
 * RestClientResponseTest
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class RestClientResponseTest extends OpenStackTestCase
{
    public function providerHasError()
    {
        $json = '{"computeFault":{"code":500,"message":"Fault!","details":"Error Details..."}}';
        $xml = '<?xml version="1.0" encoding="UTF-8"?><computeFault xmlns="http://docs.openstack.org/compute/api/v1.1" code="500">'
             . '<message>Fault!</message><details>Error Details...</details></computeFault>';
        return array(
            array(AppFormat::json(), $json),
            array(AppFormat::xml(), $xml),
        );
    }

    /**
     * @test
     * @dataProvider providerHasError
     */
    public function testHasError($format, $content)
    {
        $message = $this->getMock('HttpMessage', array('getBody', 'getResponseCode'), array(''));
        //It returns 500 error
        $message->expects($this->any())->method('getResponseCode')->will($this->returnValue(500));
        //Content should have error message in appropriated application format
        $message->expects($this->once())->method('getBody')->will($this->returnValue($content));

        $response = new RestClientResponse($message, $format);

        try {
            $response->hasError();
            $this->assertTrue(false, 'Exception are expected to be thrown here.');
        } catch (RestClientException $e) {
            $this->assertTrue(true);
            $this->assertInstanceOf($this->getOpenStackClassName('Client\\ErrorData'), $e->error);
            $this->assertEquals(500, $e->error->code);
            $this->assertEquals('Fault!', $e->error->message);
            $this->assertEquals('Error Details...', $e->error->details);
        }
    }
}