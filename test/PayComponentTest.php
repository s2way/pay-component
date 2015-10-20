<?php

require_once "src/PayComponent.php";

class PayComponentTest extends PHPUnit_Framework_TestCase {

    protected $data;
    private $authToken = 'token1';

    public function testConstructor() {
        $pay = new PayComponent($this->authToken, null, null);
        $this->assertEquals($pay->authToken, 'token1');
        $this->assertEquals($pay->payURL, 'http://192.168.100.119:1337');
    }

    /**
      * @expectedException InvalidArgumentException
      * @expectedExceptionMessage some error
    */
    public function testPaymentCardValidationException(){
        $mockPaymentCard = $this->getMockBuilder('PaymentCard')->setMethods(array('validate'))->getMock();
        $mockPaymentCard->expects($this->any())->method('validate')->will(
            $this->returnCallback(
                function(){
                    throw new InvalidArgumentException('some error');
                }
            )
        );
        $payComponent = new PayComponent($this->authToken, $mockPaymentCard, null);
        $payComponent->purchaseByCard();
    }

    /**
      * @expectedException InvalidArgumentException
      * @expectedExceptionMessage some error
    */
    // public function testRequesterCreateError(){
    //     $mockPaymentCard = $this->getMockBuilder('PaymentCard')->setMethods(array('validate'))->getMock();
    //     $mockPaymentCard->expects($this->any())->method('validate')->willReturn(true);

    //     $mockRequester = $this->getMockBuilder('Requester')->setMethods(array('create', 'setURL', 'setPayment'))->getMock();
    //     $mockRequester->expects($this->any())->method('create')->willReturn(false);
    //     $mockRequester->expects($this->any())->method('setURL');
    //     $mockRequester->expects($this->any())->method('setPayment');

    //     $payComponent = new PayComponent($this->authToken, $mockPaymentCard, $mockRequester);
    //     $payComponent->purchaseByCard();
    // }

}