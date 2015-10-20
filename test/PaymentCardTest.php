<?php

require_once 'src/PaymentCard.php';
require_once 'src/Component/Validator.php';

class PaymentCardTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->data = array(
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
    }

    /**
     ********************************
     ***** TESTS PARENT METHODS *****
     ********************************
     */

    public function testSetData() {
        $expectedData = array(
            'some' => 'data'
        );
        $pay = new PaymentCard();
        $pay->setData($expectedData);
        $this->assertEquals($expectedData, $pay->getData());
    }

    public function testSetAuthToken() {
        $expectedData = 'someToken';
        $pay = new PaymentCard();
        $pay->setAuthToken($expectedData);
        $this->assertEquals($expectedData, $pay->getAuthToken());
    }

    /**
      * @expectedException InvalidArgumentException
      * @expectedExceptionMessage some error
    */
    public function testValidateException() {
        $validator = $this->getMockBuilder('Validator')->setMethods(array('validate', 'getError'))->getMock();
        $validator->expects($this->any())->method('validate')->willReturn(false);
        $validator->expects($this->any())->method('getError')->willReturn('some error');
        $pay = new PaymentCard($validator);
        $pay->validate();
    }

    public function testValidatePassed() {
        $validator = $this->getMockBuilder('Validator')->setMethods(array('validate', 'getError'))->getMock();
        $validator->expects($this->any())->method('validate')->willReturn(true);
        $pay = new PaymentCard($validator);
        $pay->validate();
        $this->assertEmpty($validator->getError());
    }

    /**
     *******************************
     ***** TESTS CLASS METHODS *****
     *******************************
     */

    /**
      * @expectedException InvalidArgumentException
      * @expectedExceptionMessage Invalid auth_token.
    */
    public function testAuthTokenNotEmpty() {
        $pay = new PaymentCard();
        
        $pay->setData($this->data);
        $pay->validate();
    }

    public function testDescriptionNotEmpty() {$this->validateNotEmpty('description');}

    public function testAmountNotEmpty() {$this->validateNotEmpty('amount');}

    public function testReturnURLNotEmpty() {$this->validateNotEmpty('return_url');}

    public function testIssuerNotEmpty() {$this->validateNotEmpty('issuer');}

    public function testCardNumberNotEmpty() {$this->validateNotEmpty('card_number');}

    public function testDueDateNotEmpty() {$this->validateNotEmpty('due_date');}

    public function testSecCodeStatusNotEmpty() {$this->validateNotEmpty('sec_code_status');}

    public function testCardHolderNotEmpty() {$this->validateNotEmpty('card_holder');}

    public function testPaymentTypeNotEmpty() {$this->validateNotEmpty('payment_type');}

    public function testInstallmentsNotEmpty() {$this->validateNotEmpty('installments');}


    /**
     * @expectedExceptionMessage Description is too long.
     * @expectedException InvalidArgumentException
     */
    public function testDescriptionMaxLength() {
        $pay = new PaymentCard();

        $this->data['description'] = implode(array_fill(0, 1025, 'm'));

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The amount should be in cents.
     */
    public function testAmoutNaturalNumberWithDot() {
        $pay = new PaymentCard();

        $this->data['amount'] = 2.33;

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The amount should be in cents.
     */
    public function testAmoutNaturalNumberWithComma() {
        $pay = new PaymentCard();

        $this->data['amount'] = '2,33';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Amount must be positive.
     */
    public function testAmoutNaturalNumberNegative() {
        $pay = new PaymentCard();

        $this->data['amount'] = -2.33;

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Amount is too long.
     */
    public function testAmoutTooLong() {
        $pay = new PaymentCard();

        $this->data['amount'] = implode(array_fill(0, 13, '1'));;

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Return URL is too long.
     */
    public function testReturnURLTooLong() {
        $pay = new PaymentCard();

        $this->data['return_url'] = implode(array_fill(0, 2049, 'm'));

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid return_url.
     */
    public function testReturnURLInvalid() {
        $pay = new PaymentCard();

        $this->data['return_url'] = 'https://www.google.';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown issuer.
     */
    public function testUnknownIssuer() {
        $pay = new PaymentCard();

        $this->data['issuer'] = 'other issuer';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage card_number is too long.
     */
    public function testCardNumberTooLong() {
        $pay = new PaymentCard();

        $this->data['card_number'] = implode(array_fill(0, 20, 'm'));

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid card_number.
     */
    public function testInvalidCardNumber() {
        $pay = new PaymentCard();

        $this->data['card_number'] = 'invalid card number';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid due_date length.
    */
    public function testInvalidDueDateLength() {
        $pay = new PaymentCard();

        $this->data['due_date'] = 123;

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage due_date must be numeric.
    */
    public function testDueDateNumeric() {
        $pay = new PaymentCard();

        $this->data['due_date'] = '1o2015';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage sec_code_status is invalid.
    */
    public function testInvalidSecCodeStatus() {
        $pay = new PaymentCard();

        $this->data['sec_code_status'] = '999';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid security_code length.
    */
    public function testSecurityCodeLength() {
        $pay = new PaymentCard();

        $this->data['security_code'] = 123456;

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid security_code.
    */
    public function testInvalidSecurityCode() {
        $pay = new PaymentCard();

        $this->data['sec_code_status'] = 1;
        $this->data['security_code'] = '';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage security_code must be numeric.
    */
    public function testSecurityCodeNumeric() {
        $pay = new PaymentCard();

        $this->data['security_code'] = 'aaa';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage card_holder is too long
    */
    public function testCardHolderTooLong() {
        $pay = new PaymentCard();

        $this->data['card_holder'] = implode(array_fill(0, 51, 'm'));

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown payment_type.
    */
    public function testUnknownPaymentType() {
        $pay = new PaymentCard();

        $this->data['payment_type'] = 'Unknown_payment_type';
        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid issuer for this payment type.
    */
    public function testPaymentTypeInvalidIssuer() {
        $pay = new PaymentCard();

        $this->data['payment_type'] = 'debito';
        $this->data['issuer'] = 'amex';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid installments for this payment_type.
    */
    public function testInstallmentsTooLong() {
        $pay = new PaymentCard();

        $this->data['installments'] = 9;
        $this->data['payment_type'] = 'credito_parcelado_loja';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The payment type allows only 1 installment.
    */
    public function testInstallmentsPaymentType() {
        $pay = new PaymentCard();

        $this->data['installments'] = 2;
        $this->data['payment_type'] = 'debito';

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        $pay->validate();
    }

    private function validateNotEmpty($field) {
        $pay = new PaymentCard();

        $this->data[$field] = null;

        $pay->setAuthToken('any');
        $pay->setData($this->data);
        
        try {
            $pay->validate();
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals("Invalid $field.", $e->getMessage());
            $this->assertEquals('InvalidArgumentException', get_class($e));
        }
    }
}