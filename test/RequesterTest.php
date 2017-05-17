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

    public function testMethodCreationRequestError(){

        $expectedError = 'some request error';

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod','setUrl','setData','send','getError'))->getMock();
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

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod','setUrl','setData','send','getResponse', 'requestSucceded', 'isPayValidationError'))->getMock();
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

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod','setUrl','setData','send','getResponse', 'requestSucceded', 'isPayValidationError'))->getMock();
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

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('send', 'requestSucceded', 'getResponse', 'setMethod', 'setUrl', 'setData'))->getMock();
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

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('send', 'getError', 'setMethod', 'setUrl', 'setData'))->getMock();
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
        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'setData','send', 'requestSucceded', 'isPayValidationError','getResponse'))->getMock();
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

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'setData','send', 'requestSucceded', 'isPayValidationError','getResponse'))->getMock();
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

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('send', 'getResponse', 'requestSucceded', 'setMethod', 'setUrl', 'setData'))->getMock();
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
        $expectedResponse = '{"token" : "client_token"}';

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('send', 'getResponse', 'requestSucceded', 'setMethod', 'setUrl', 'setData'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn($expectedResponse);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($this->paymentCard);

        $this->assertTrue($requester->process());
        $this->assertNull($requester->getError());
        $this->assertEquals(null, $this->paymentCard->getReturnURL());
        $this->assertEquals('client_token', $this->paymentCard->getToken());
    }

    public function testMethodProcessSuccessWithPurchaseByToken() {
        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('send', 'getResponse', 'requestSucceded', 'setMethod', 'setUrl', 'setData'))->getMock();
        $mockedHttpConnector->expects($this->any())->method('send')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('requestSucceded')->willReturn(true);
        $mockedHttpConnector->expects($this->any())->method('getResponse')->willReturn(null);

        $payment = new PaymentToken();
        $this->paymentData['token'] = 'weijwr0329esofk';
        $payment->setData($this->paymentData);

        $requester = new Requester($mockedHttpConnector);
        $requester->setPayment($payment);

        $this->assertTrue($requester->process());
        $this->assertNull($requester->getError());
        $this->assertEquals(null, $payment->getReturnURL());
    }

    public function testGetOrderByReferenceGetFail() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(null)->getMock();
        $selfClass->setBaseURL('BASE_URL');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'send', 'getError'))->getMock();
        $mockedHttpConnector->expects($this->once())
            ->method('setMethod')
            ->with('GET');
        $mockedHttpConnector->expects($this->once())
            ->method('setUrl')
            ->with('BASE_URL/orders?reference=REFERENCE');
        $mockedHttpConnector->expects($this->once())
            ->method('send')
            ->with('AUTH_TOKEN')
            ->will($this->returnValue(false));
        $mockedHttpConnector->expects($this->once())
            ->method('getError')
            ->will($this->returnValue('ERROR'));
        $selfClass->httpConnector = $mockedHttpConnector;

        $result = $selfClass->getOrderByReference('REFERENCE', 'AUTH_TOKEN');

        $this->assertFalse($result);
        $this->assertEquals('GET', $selfClass->method);
        $this->assertEquals('ERROR', $selfClass->getError());

    }

    public function testGetOrderByReferenceGetResponseError() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(null)->getMock();
        $selfClass->setBaseURL('BASE_URL');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'send', 'getResponse', 'requestSucceded'))->getMock();
        $mockedHttpConnector->expects($this->once())
            ->method('setMethod');
        $mockedHttpConnector->expects($this->once())
            ->method('setUrl');
        $mockedHttpConnector->expects($this->once())
            ->method('send')
            ->will($this->returnValue(true));
        $mockedHttpConnector->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue('{"response": "RESPONSE"}'));
        $mockedHttpConnector->expects($this->once())
            ->method('requestSucceded')
            ->will($this->returnValue(false));
        $selfClass->httpConnector = $mockedHttpConnector;

        $result = $selfClass->getOrderByReference('REFERENCE', 'AUTH_TOKEN');

        $this->assertFalse($result);
        $this->assertEquals(array('response' => 'RESPONSE'), $selfClass->getError());

    }

    public function testGetOrderByReferenceGetResponseEmpty() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(null)->getMock();
        $selfClass->setBaseURL('BASE_URL');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'send', 'getResponse', 'requestSucceded'))->getMock();
        $mockedHttpConnector->expects($this->once())
            ->method('setMethod');
        $mockedHttpConnector->expects($this->once())
            ->method('setUrl');
        $mockedHttpConnector->expects($this->once())
            ->method('send')
            ->will($this->returnValue(true));
        $mockedHttpConnector->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue(''));
        $mockedHttpConnector->expects($this->once())
            ->method('requestSucceded')
            ->will($this->returnValue(true));
        $selfClass->httpConnector = $mockedHttpConnector;

        $result = $selfClass->getOrderByReference('REFERENCE', 'AUTH_TOKEN');

        $this->assertFalse($result);
        $this->assertEquals('', $selfClass->getError());

    }

    public function testGetOrderByReference() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(null)->getMock();
        $selfClass->setBaseURL('BASE_URL');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'send', 'getResponse', 'requestSucceded'))->getMock();
        $mockedHttpConnector->expects($this->once())
            ->method('setMethod');
        $mockedHttpConnector->expects($this->once())
            ->method('setUrl');
        $mockedHttpConnector->expects($this->once())
            ->method('send')
            ->will($this->returnValue(true));
        $mockedHttpConnector->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue('{"response": "RESPONSE"}'));
        $mockedHttpConnector->expects($this->once())
            ->method('requestSucceded')
            ->will($this->returnValue(true));
        $selfClass->httpConnector = $mockedHttpConnector;

        $result = $selfClass->getOrderByReference('REFERENCE', 'AUTH_TOKEN');

        $this->assertEquals(array('response' => 'RESPONSE'), $result);

    }

    public function testCancelOrderNotFound() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('getOrderByReference'))->getMock();
        $selfClass->expects($this->once())
            ->method('getOrderByReference')
            ->with('REFERENCE', 'AUTH_TOKEN')
            ->will($this->returnValue(false));

        $result = $selfClass->cancel('REFERENCE', 'AUTH_TOKEN');

        $this->assertFalse($result);

    }

    public function testCancelPutFail(){

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('getOrderByReference'))->getMock();
        $selfClass->expects($this->once())
            ->method('getOrderByReference')
            ->with('REFERENCE', 'AUTH_TOKEN')
            ->will($this->returnValue(array('id' => 'ORDER_ID')));
        $selfClass->setBaseURL('BASE_URL');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'send', 'getError'))->getMock();
        $mockedHttpConnector->expects($this->once())
            ->method('setMethod')
            ->with('PUT');
        $mockedHttpConnector->expects($this->once())
            ->method('setUrl')
            ->with('BASE_URL/orders/ORDER_ID');
        $mockedHttpConnector->expects($this->once())
            ->method('send')
            ->with('AUTH_TOKEN')
            ->will($this->returnValue(false));
        $mockedHttpConnector->expects($this->once())
            ->method('getError')
            ->will($this->returnValue('ERROR'));
        $selfClass->httpConnector = $mockedHttpConnector;

        $result = $selfClass->cancel('REFERENCE', 'AUTH_TOKEN');

        $this->assertFalse($result);
        $this->assertEquals('PUT', $selfClass->method);
        $this->assertEquals('ERROR', $selfClass->getError());

    }

    public function testCancelPutGetResponseError() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('getOrderByReference'))->getMock();
        $selfClass->expects($this->once())
            ->method('getOrderByReference')
            ->with('REFERENCE', 'AUTH_TOKEN')
            ->will($this->returnValue(array('id' => 'ORDER_ID')));
        $selfClass->setBaseURL('BASE_URL');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'send', 'getResponse','requestSucceded'))->getMock();
        $mockedHttpConnector->expects($this->once())
            ->method('setMethod');
        $mockedHttpConnector->expects($this->once())
            ->method('setUrl');
        $mockedHttpConnector->expects($this->once())
            ->method('send')
            ->will($this->returnValue(true));
        $mockedHttpConnector->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue('{"response": "RESPONSE"}'));
        $mockedHttpConnector->expects($this->once())
            ->method('requestSucceded')
            ->will($this->returnValue(false));
        $selfClass->httpConnector = $mockedHttpConnector;

        $result = $selfClass->cancel('REFERENCE', 'AUTH_TOKEN');

        $this->assertFalse($result);

    }

    public function testCancel() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('getOrderByReference'))->getMock();
        $selfClass->expects($this->once())
            ->method('getOrderByReference')
            ->with('REFERENCE', 'AUTH_TOKEN')
            ->will($this->returnValue(array('id' => 'ORDER_ID')));
        $selfClass->setBaseURL('BASE_URL');

        $mockedHttpConnector = $this->getMockBuilder('PayComponent\Component\HttpConnector')->setMethods(array('setMethod', 'setUrl', 'send', 'getResponse','requestSucceded'))->getMock();
        $mockedHttpConnector->expects($this->once())
            ->method('setMethod');
        $mockedHttpConnector->expects($this->once())
            ->method('setUrl');
        $mockedHttpConnector->expects($this->once())
            ->method('send')
            ->will($this->returnValue(true));
        $mockedHttpConnector->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue(''));
        $mockedHttpConnector->expects($this->once())
            ->method('requestSucceded')
            ->will($this->returnValue(true));
        $selfClass->httpConnector = $mockedHttpConnector;

        $result = $selfClass->cancel('REFERENCE', 'AUTH_TOKEN');

        $this->assertTrue($result);

    }

    public function testGetStatusOrderNotFound() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('getOrderByReference'))->getMock();
        $selfClass->expects($this->once())
            ->method('getOrderByReference')
            ->with('REFERENCE', 'AUTH_TOKEN')
            ->will($this->returnValue(false));

        $result = $selfClass->getStatus('REFERENCE', 'AUTH_TOKEN');

        $this->assertFalse($result);

    }

    public function testGetStatusNotSet() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('getOrderByReference'))->getMock();
        $selfClass->expects($this->once())
            ->method('getOrderByReference')
            ->will($this->returnValue(array('id' => 'ORDER_ID')));

        $result = $selfClass->getStatus('REFERENCE', 'AUTH_TOKEN');

        $this->assertFalse($result);

    }

    public function testGetStatusApproved() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('getOrderByReference'))->getMock();
        $selfClass->expects($this->once())
            ->method('getOrderByReference')
            ->will($this->returnValue(array('id' => 'ORDER_ID', 'status' => 'APPROVED')));

        $result = $selfClass->getStatus('REFERENCE', 'AUTH_TOKEN');

        $this->assertEquals(array('status' => 'APPROVED'), $result);

    }

    public function testGetStatusRejected() {

        $selfClass = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('getOrderByReference'))->getMock();
        $selfClass->expects($this->once())
            ->method('getOrderByReference')
            ->will($this->returnValue(array(
                'id' => 'ORDER_ID',
                'status' => 'REJECTED',
                'acquirer_message' => 'MESSAGE', 
                'acquirer_action' => 'ACTION', 
                'acquirer_code' => 'CODE'
            )));

        $result = $selfClass->getStatus('REFERENCE', 'AUTH_TOKEN');

        $this->assertEquals(array(
            'status' => 'REJECTED',
            'reason' => 'MESSAGE',
            'action' => 'ACTION',
            'code' => 'CODE'
            ), $result);

    }
}
