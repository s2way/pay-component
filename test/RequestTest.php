<?php

require_once 'src/Requester.php';
require_once 'src/PaymentCard.php';

class RequestTest extends PHPUnit_Framework_TestCase {

    public function setUp() {

        $this->paymentCard = new PaymentCard();
        $this->paymentCard->setData(
            array(
                'description' => 'Description test',
                'amount' => 123456,
                'return_url' => 'http://www.google.com.br',
                'issuer' => 'visa',
                'card_number' => '153241251234',
                'due_date' => '072015',
                'sec_code_status' => 1,
                'security_code' => 619,
                'card_holder' => 'TEST NAME',
                'payment_type' => 'debito',
                'installments' => 1
            )
        );
    }

    public function testSetterPayment() {
        $expectedData = array(
            'data' => array(
                'id' => '0.16624199342913926',
                'auth_token' => 'token_floripa',
                'description' => 'Descrição',
                'amount' => '100',
                'return_url' => 'http://www.google.com',
                'issuer' => 'visa',
                'card_number' => '1031654821043574',
                'due_date' => '122015',
                'sec_code_status' => '1',
                'security_code' => '123',
                'card_holder' => 'Andre_pega_um_pega_geral',
                'payment_type' => 'credito_a_vista',
                'installments' => '1'
            )
        );
        $requester = new Requester();
        $requester->setPayment($expectedData);
        $this->assertEquals($expectedData, $requester->payment);
    }

    public function testMethodCreationError(){
        $expectedError = 'some error';

        $mockedHttpConnector = $this->getMockBuilder('HttpConnector')->setMethods(array('send', 'getError'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(false);
        $mockedHttpConnector->expects($this->any())->method('getError')->willReturn($expectedError);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($this->paymentCard);

        $this->assertFalse($requester->create());
        $this->assertEquals($expectedError, $requester->getError());
    }

    public function testMethodCreationSuccess(){
        $expectedData = '"123456789abcdefg"';

        $mockedHttpConnector = $this->getMockBuilder('HttpConnector')->setMethods(array('send', 'getStatus', 'getResponse'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getStatus')->willReturn(200);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($expectedData);

        $requester = new Requester($mockedHttpConnector);

        $mockedPaymentCard = $this->getMockBuilder('PaymentCard')->setMethods(array('setId'))->getMock();
        $mockedPaymentCard->expects($this->any())->method('setId')->will($this->returnCallback(function($id){
            $this->assertEquals($expectedData, $id);
        }));

        $requester->setPayment($this->paymentCard);

        $this->assertTrue($requester->create());
        $this->assertNull($requester->getError());
    }


}