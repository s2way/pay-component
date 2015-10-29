<?php

/*
    TOOD: Tentar testar o payment passado para o objeto requester.
*/
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

}