<?php

use PayComponent\Requester;
use PayComponent\PaymentCard;
use PayComponent\PaymentToken;
use PayComponent\Component\HttpConnector;

class RequesterTest extends PHPUnit_Framework_TestCase {

    public function setUp() {

        date_default_timezone_set('America/Sao_Paulo');

        $this->paymentCard = new PaymentCard();
        $this->paymentData = array(
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
        );
        $this->paymentCard->setData($this->paymentData);
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

    public function testMethodCreationRequestError(){

        $expectedError = 'some request error';

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('setMethod','setUrl','setData','send','getError'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(false);
        $mockedHttpConnector->expects($this->any())->method('getError')->willReturn($expectedError);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($this->paymentCard);

        $this->assertFalse($requester->create());
        $this->assertEquals($expectedError, $requester->getError());
    }

    public function testMethodCreationPayValidationError(){

        $throwError = '{"error": "some pay error"}';
        $expectedError = array('error' => 'some pay error');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('setMethod','setUrl','setData','send','getResponse', 'requestSucceded', 'isPayValidationError'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($throwError);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(false);
        $mockedHttpConnector->expects($this->any())->method('isPayValidationError')->willReturn(true);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($this->paymentCard);

        $this->assertFalse($requester->create());
        $this->assertEquals($expectedError, $requester->getError());
    }

    public function testMethodCreationOtherError(){

        $throwError = '{"error": "other error"}';
        $expectedError = array('error' => 'other error');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('setMethod','setUrl','setData','send','getResponse', 'requestSucceded', 'isPayValidationError'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($throwError);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(false);
        $mockedHttpConnector->expects($this->any())->method('isPayValidationError')->willReturn(false);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($this->paymentCard);

        $this->assertFalse($requester->create());
        $this->assertEquals($expectedError, $requester->getError());
    }

    public function testMethodCreationSuccess(){
        $expectedData = '{"order_id": "123456789abcdefg"}';

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('send', 'requestSucceded', 'getResponse', 'setMethod', 'setUrl', 'setData'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(200);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($expectedData);

        $requester = new Requester($mockedHttpConnector);

        $mockedPaymentCard = $this->getMockBuilder('PayComponent\PaymentCard')->setMethods(array('setId'))->getMock();
        $mockedPaymentCard->expects($this->any())->method('setId')->will($this->returnCallback(function($id){
            $this->assertEquals($expectedData, $id);
        }));

        $requester->setPayment($this->paymentCard);

        $this->assertTrue($requester->create());
        $this->assertNull($requester->getError());
    }

    public function testMethodProcessRequestError() {
        $orderId = 'wepoijfasldkfjwope';
        $baseURL = 'http://base.url';
        $expectedError = 'some error';
        $expectedURL = "{$baseURL}/orders/{$orderId}";

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('send', 'getError', 'setMethod', 'setUrl', 'setData'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(false);
        $mockedHttpConnector->expects($this->any())->method('getError')->willReturn($expectedError);
        $mockedHttpConnector->expects($this->any())->method('setUrl')->will($this->returnCallback(function($url) use ($expectedURL) {
            $this->assertEquals($expectedURL, $url);
        }));

        $requester = new Requester($mockedHttpConnector);
        $requester->setBaseURL($baseURL);
        $this->paymentCard->setId($orderId);
        $requester->setPayment($this->paymentCard);

        $this->assertFalse($requester->process());
        $this->assertEquals($expectedError, $requester->getError());
    }

    public function testMethodProcessRequestPayValidationError() {

        $throwError = '{
          "code": "UnprocessableEntityError",
          "message": "[{\"code\":602,\"message\":\"Unknown auth_token\"},{\"code\":628,\"message\":\"Empty payment_type\"},{\"code\":631,\"message\":\"Empty installments\"},{\"code\":613,\"message\":\"Order not found\"}]"
        }';
        $expectedError = array(
            'code' => 'UnprocessableEntityError',
            'message' => '[{"code":602,"message":"Unknown auth_token"},{"code":628,"message":"Empty payment_type"},{"code":631,"message":"Empty installments"},{"code":613,"message":"Order not found"}]'
        );
        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'setData','send', 'requestSucceded', 'isPayValidationError','getResponse'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($throwError);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(false);


        $requester = new Requester($mockedHttpConnector);
        $requester->setBaseURL('any url');
        $this->paymentCard->setId('any id');
        $requester->setPayment($this->paymentCard);

        $this->assertFalse($requester->process());
        $this->assertEquals($expectedError, $requester->getError());
    }

      public function testMethodProcessRequestOtherError() {

        $throwError = '{"error": "other error"}';
        $expectedError = array('error' => 'other error');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'setData','send', 'requestSucceded', 'isPayValidationError','getResponse'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($throwError);
        $mockedHttpConnector->expects($this->any())->method('isPayValidationError')->willReturn(false);

        $requester = new Requester($mockedHttpConnector);
        $requester->setBaseURL('any url');
        $this->paymentCard->setId('any id');
        $requester->setPayment($this->paymentCard);

        $this->assertFalse($requester->process());
        $this->assertEquals($expectedError, $requester->getError());
    }

    public function testMethodProcessSuccess() {
        $expectedResponse = '{"authentication_url": "http://somerul.com", "token" : "client_token"}';

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('send', 'getResponse', 'requestSucceded', 'setMethod', 'setUrl', 'setData'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($expectedResponse);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($this->paymentCard);

        $this->assertTrue($requester->process());
        $this->assertNull($requester->getError());
        $this->assertEquals('http://somerul.com', $this->paymentCard->getReturnURL());
        $this->assertEquals('client_token', $this->paymentCard->getToken());
    }

    public function testMethodProcessSuccessWithoutAuthURL() {
        $expectedResponse = '{"return_url": "http://somerul.com", "token" : "client_token"}';

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('send', 'getResponse', 'requestSucceded', 'setMethod', 'setUrl', 'setData'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($expectedResponse);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($this->paymentCard);

        $this->assertTrue($requester->process());
        $this->assertNull($requester->getError());
        $this->assertEquals('http://somerul.com', $this->paymentCard->getReturnURL());
        $this->assertEquals('client_token', $this->paymentCard->getToken());
    }

    public function testMethodProcessSuccessWithPurchaseByToken() {
        $expectedResponse = '{"return_url": "http://somerul.com"}';

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\HttpConnector')->setMethods(array('send', 'getResponse', 'requestSucceded', 'setMethod', 'setUrl', 'setData'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($expectedResponse);

        $payment = new PaymentToken();
        $this->paymentData['token'] = 'weijwr0329esofk';
        $payment->setData($this->paymentData);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($payment);

        $this->assertTrue($requester->process());
        $this->assertNull($requester->getError());
        $this->assertEquals('http://somerul.com', $payment->getReturnURL());
    }
}
