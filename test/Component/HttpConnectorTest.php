<?php

use PayComponent\Component\HttpConnector;

class HttpConnectorTest extends PHPUnit_Framework_TestCase {

    public function testRequestSuccededStatusCode200(){
        $connector = new HttpConnector();
        $connector->setStatus(200);
        $this->assertTrue($connector->requestSucceded());
    }

    public function testRequestSuccededStatusCode201(){
        $connector = new HttpConnector();
        $connector->setStatus(201);
        $this->assertTrue($connector->requestSucceded());
    }

    public function testRequestSuccededStatusCode202(){
        $connector = new HttpConnector();
        $connector->setStatus(202);
        $this->assertTrue($connector->requestSucceded());
    }

    public function testIsPayValidationError(){
        $connector = new HttpConnector();
        $connector->setStatus(422);
        $this->assertTrue($connector->isPayValidationError());
    }

    public function testSendError(){

        $expectedErrorCode = 3;
        $expectedErrorMessage = '<url> malformed';

        $connector = new HttpConnector();
        $connector->setMethod('GET');
        $connector->setURL('/testes/bad');
        $this->assertFalse($connector->send());

        $this->assertEquals($expectedErrorCode, $connector->getError()['code']);
        $this->assertEquals($expectedErrorMessage, $connector->getError()['message']);
    }

    public function testSendGetSuccess() {

        $connector = new HttpConnector();
        $connector->setMethod('GET');
        $connector->setURL('http://www.google.com.br');

        $expectedStatusCode = 200;

        $this->assertTrue($connector->send());
        $this->assertNull($connector->getError());
        $this->assertEquals($expectedStatusCode, $connector->getStatus());
        $this->assertNotNull($connector->getResponse());
    }

    public function testSendPostSuccess () {
        $connector = new HttpConnector();
        $connector->setMethod('POST');
        $connector->setURL('http://www.google.com.br');
        $connector->setData(array());

        $expectedStatusCode = 405;

        $this->assertTrue($connector->send());
        $this->assertNull($connector->getError());
        $this->assertEquals($expectedStatusCode, $connector->getStatus());
        $this->assertNotNull($connector->getResponse());
    }

    public function testPut() {
        $connector = new HttpConnector();
        $connector->setMethod('PUT');
        $connector->setURL('http://www.google.com.br');
        $connector->setData(array());

        $expectedStatusCode = 405;

        $this->assertTrue($connector->send());
        $this->assertNull($connector->getError());
        $this->assertEquals($expectedStatusCode, $connector->getStatus());
        $this->assertNotNull($connector->getResponse());
    }
}
