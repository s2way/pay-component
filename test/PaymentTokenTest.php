<?php

use PayComponent\PaymentToken;
use PayComponent\Component\Validator;
use PHPUnit\Framework\TestCase;

class PaymentTokenTest extends TestCase {

    private $field = null;

    public function setUp() {
        $this->data = array(
            'id'           => 'order_id',
            'description'  => 'Description Teste',
            'amount'       => 2500,
            'issuer'       => 'visa',
            'payment_type' => 'credit',
            'installments' => 1,
            'token'        => '32165843216543213546514',
            'street'     => 'ANY STREET',
            'number'     => 15
        );
    }

    /**
     ********************************
     ***** TESTS PARENT METHODS *****
     ********************************
    */

    public function testSetAuthToken() {
        $expectedData = 'someToken';
        $pay = new PaymentToken();
        $pay->setAuthToken($expectedData);
        $this->assertEquals($expectedData, $pay->getAuthToken());
    }

    public function testSetId() {
        $expectedData = 123;
        $pay = new PaymentToken();
        $pay->setId($expectedData);
        $this->assertEquals($expectedData, $pay->getId());
    }

    /**
     *******************************
     ***** TESTS CLASS METHODS *****
     *******************************
     */

    public function testCreationData() {
        $this->data['auth_token'] = 'any';

        $expectedData = array(
            'id'          => $this->data['id'],
            'auth_token'  => $this->data['auth_token'],
            'description' => $this->data['description'],
            'amount'      => $this->data['amount'],
        );

        $pay = new PaymentToken();
        $pay->setAuthToken($this->data['auth_token']);
        $pay->setData($this->data);
        $this->assertEquals($expectedData, $pay->getCreationData());
    }

    public function testProcessData() {
        $this->data['auth_token'] = 'any';

        $expectedData = array(
            'issuer'       => $this->data['issuer'],
            'payment_type' => $this->data['payment_type'],
            'installments' => $this->data['installments'],
            'auth_token'   => $this->data['auth_token'],
            'token'        => $this->data['token']
        );
        $pay = new PaymentToken();
        $pay->setAuthToken($this->data['auth_token']);
        $pay->setData($this->data);
        $this->assertEquals($expectedData, $pay->getProcessData());
    }

}
