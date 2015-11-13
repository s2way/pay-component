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

    // public function testIsPayValidationError(){
    //     $connector = new HttpConnector();
    //     $connector->setStatus(422);
    //     $this->assertTrue($connector->isPayValidationError());
    // }
}