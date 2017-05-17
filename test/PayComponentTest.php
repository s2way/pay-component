<?php

use PayComponent\PayComponent;

class PayComponentTest extends PHPUnit_Framework_TestCase {

    public function testPaymentCardValidationError() {
        $mockedPaymentCard = $this->getMockBuilder('PayComponent\PaymentCard')->setMethods(array('validate'))->getMock();
        $mockedPaymentCard->expects($this->any())->method('validate')->willReturn(false);
        $payComponent = new PayComponent($mockedPaymentCard);
        $this->assertFalse($payComponent->purchaseByCard(array()));
    }

    public function testPurchaseByCardCreationError() {
        $expectedError = 'some error';

        $mockedPaymentCard = $this->getMockBuilder('PayComponent\PaymentCard')->setMethods(array('validate'))->getMock();
        $mockedPaymentCard->expects($this->any())->method('validate')->willReturn(true);

        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('create', 'getError'))->getMock();
        $mockedRequester->expects($this->any())->method('create')->willReturn(false);
        $mockedRequester->expects($this->any())->method('getError')->willReturn($expectedError);

        $payComponent = new PayComponent($mockedPaymentCard, $mockedRequester);

        $this->assertFalse($payComponent->purchaseByCard(array('auth_token'=>'token1')));
        $this->assertEquals($expectedError, $payComponent->getError());
    }

    public function testPurchaseByCardProcessError() {
        $expectedError = 'some error';

        $mockedPaymentCard = $this->getMockBuilder('PayComponent\PaymentCard')->setMethods(array('validate'))->getMock();
        $mockedPaymentCard->expects($this->any())->method('validate')->willReturn(true);

        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('create', 'getError', 'process'))->getMock();
        $mockedRequester->expects($this->any())->method('create')->willReturn(true);
        $mockedRequester->expects($this->any())->method('getError')->willReturn($expectedError);
        $mockedRequester->expects($this->any())->method('process')->willReturn(false);

        $payComponent = new PayComponent($mockedPaymentCard, $mockedRequester);

        $this->assertFalse($payComponent->purchaseByCard(array('auth_token'=>'token1')));
        $this->assertEquals($expectedError, $payComponent->getError());
    }

    public function testPurchaseByCardSuccess() {

        $mockedPaymentCard = $this->getMockBuilder('PayComponent\PaymentCard')->setMethods(array('validate'))->getMock();
        $mockedPaymentCard->expects($this->any())->method('validate')->willReturn(true);

        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('create', 'process'))->getMock();
        $mockedRequester->expects($this->any())->method('create')->willReturn(true);
        $mockedRequester->expects($this->any())->method('process')->willReturn(true);

        $payComponent = new PayComponent($mockedPaymentCard, $mockedRequester);

        $this->assertTrue($payComponent->purchaseByCard(array('auth_token'=>'token1')));
        $this->assertNull($payComponent->getError());
    }

    public function testPurchaseByTokenCreationError() {
        $expectedError = 'some error';

        $mockedPaymentToken = $this->getMockBuilder('PayComponent\PaymentToken')->setMethods(array('validate'))->getMock();
        $mockedPaymentToken->expects($this->any())->method('validate')->willReturn(true);

        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('create', 'getError'))->getMock();
        $mockedRequester->expects($this->any())->method('create')->willReturn(false);
        $mockedRequester->expects($this->any())->method('getError')->willReturn($expectedError);

        $payComponent = new PayComponent($mockedPaymentToken, $mockedRequester);

        $this->assertFalse($payComponent->purchaseByToken(array('auth_token'=>'token1')));
        $this->assertEquals($expectedError, $payComponent->getError());
    }

    public function testPurchaseByTokenProcessError() {
        $expectedError = 'some error';

        $mockedPaymentToken = $this->getMockBuilder('PayComponent\PaymentToken')->setMethods(array('validate'))->getMock();
        $mockedPaymentToken->expects($this->any())->method('validate')->willReturn(true);

        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('create', 'getError', 'process'))->getMock();
        $mockedRequester->expects($this->any())->method('create')->willReturn(true);
        $mockedRequester->expects($this->any())->method('getError')->willReturn($expectedError);
        $mockedRequester->expects($this->any())->method('process')->willReturn(false);

        $payComponent = new PayComponent($mockedPaymentToken, $mockedRequester);

        $this->assertFalse($payComponent->purchaseByToken(array('auth_token'=>'token1')));
        $this->assertEquals($expectedError, $payComponent->getError());
    }

    public function testPurchaseByTokenSuccess() {

        $mockedPaymentToken = $this->getMockBuilder('PayComponent\PaymentToken')->setMethods(array('validate'))->getMock();
        $mockedPaymentToken->expects($this->any())->method('validate')->willReturn(true);

        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('create', 'process'))->getMock();
        $mockedRequester->expects($this->any())->method('create')->willReturn(true);
        $mockedRequester->expects($this->any())->method('process')->willReturn(true);

        $payComponent = new PayComponent($mockedPaymentToken, $mockedRequester);

        $this->assertTrue($payComponent->purchaseByToken(array('auth_token'=>'token1')));
        $this->assertNull($payComponent->getError());
    }

    public function testSetPayUrl() {
        $expectedPayBaseUrl = 'http://192.168.122.1:1337';
        $payComponent = new PayComponent();
        $payComponent->setPayURL($expectedPayBaseUrl);
        $this->assertEquals($expectedPayBaseUrl, $payComponent->getPayURL());
    }

    public function testGetToken(){
        $expectedToken = 'EXPECTED_TOKEN';
        $mockedPaymentCard = $this->getMockBuilder('PayComponent\PaymentCard')->setMethods(array('validate'))->getMock();
        $mockedPaymentCard->expects($this->any())->method('validate')->willReturn(true);
        $mockedPaymentCard->setToken($expectedToken); // Token que foi retornado pela Cielo

        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('create', 'process'))->getMock();
        $mockedRequester->expects($this->atLeastOnce())->method('create')->willReturn(true);
        $mockedRequester->expects($this->atLeastOnce())->method('process')->willReturn(true);

        $payComponent = new PayComponent($mockedPaymentCard, $mockedRequester);
        $payComponent->setAuthToken('token_teste'); // Token utilizado pela operação no Pay
        $payComponent->purchaseByCard(null);

        $this->assertEquals($expectedToken, $payComponent->getToken());
    }

    public function testGetRedirectURL(){

        $expectedRedirectURL = 'https://pay.com.br';

        $mockedPaymentCard = $this->getMockBuilder('PayComponent\PaymentCard')->setMethods(array('validate', 'getReturnURL'))->getMock();
        $mockedPaymentCard->expects($this->any())->method('validate')->willReturn(true);
        $mockedPaymentCard->expects($this->any())->method('getReturnURL')->will($this->returnCallback(function(){
            return 'https://pay.com.br';
        }));

        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('create', 'process'))->getMock();
        $mockedRequester->expects($this->any())->method('create')->willReturn(true);
        $mockedRequester->expects($this->any())->method('process')->willReturn(true);

        $payComponent = new PayComponent($mockedPaymentCard, $mockedRequester);
        $payComponent->setAuthToken('any_token');
        $payComponent->purchaseByCard(null);

        $this->assertEquals($expectedRedirectURL, $payComponent->getRedirectURL());
    }

    public function testCancel(){
        $mockedRequester = $this->getMockBuilder('PayComponent\Requester')->setMethods(array('setBaseURL', 'cancel'))->getMock();
        $mockedRequester->expects($this->once())
            ->method('setBaseURL')
            ->with('URL');
        $mockedRequester->expects($this->once())
            ->method('cancel')
            ->with('ID', 'AUTH_TOKEN')
            ->will($this->returnValue('CANCEL_RETURN'));

        $payComponent = new PayComponent(null, $mockedRequester);
        $payComponent->setAuthToken('AUTH_TOKEN');
        $payComponent->setPayURL('URL');

        $result = $payComponent->cancel('ID');

        $this->assertEquals('CANCEL_RETURN', $result);
    }
}
