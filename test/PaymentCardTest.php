<?php

use PayComponent\PaymentCard;
use PayComponent\Component\Validator;

class PaymentCardTest extends PHPUnit_Framework_TestCase {

    private $field = null;

    public function setUp() {
        $this->data = array(
            'id' => 123,
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

    public function testSetAuthToken() {
        $expectedData = 'someToken';
        $pay = new PaymentCard();
        $pay->setAuthToken($expectedData);
        $this->assertEquals($expectedData, $pay->getAuthToken());
    }

    public function testSetId() {
        $expectedData = 123;
        $pay = new PaymentCard();
        $pay->setId($expectedData);
        $this->assertEquals($expectedData, $pay->getId());
    }

    public function testValidateErrors() {
        $validator = $this->getMockBuilder('PayComponent\Component\Validator')->setMethods(array('validate', 'getValidationErrors'))->getMock();
        $validator->expects($this->any())->method('validate')->willReturn(false);
        $validator->expects($this->any())->method('getValidationErrors')->willReturn('some error');
        $pay = new PaymentCard($validator);
        $this->assertFalse($pay->validate());
        $this->assertEquals('some error', $pay->getErrors());
    }

    public function testValidatePassed() {
        $validator = $this->getMockBuilder('PayComponent\Component\Validator')->setMethods(array('validate', 'getValidationErrors'))->getMock();
        $validator->expects($this->any())->method('validate')->willReturn(true);
        $pay = new PaymentCard($validator);
        $this->assertTrue($pay->validate());
        $this->assertNull($pay->getErrors());
        $this->assertEmpty($validator->getValidationErrors());
    }

    /**
     *******************************
     ***** TESTS CLASS METHODS *****
     *******************************
     */

    public function testCreationData() {
        $this->data['auth_token'] = 'any';

        $expectedData = array(
            'id' => $this->data['id'],
            'auth_token' => $this->data['auth_token'],
            'description' => $this->data['description'],
            'amount' => $this->data['amount'],
            'return_url' => $this->data['return_url'],
        );

        $pay = new PaymentCard();
        $pay->setAuthToken($this->data['auth_token']);
        $pay->setData($this->data);
        $this->assertEquals($expectedData, $pay->getCreationData());
    }

    public function testProcessData() {
        $this->data['auth_token'] = 'any';

        $expectedData = array(
            'issuer' => $this->data['issuer'],
            'card_number' => $this->data['card_number'],
            'due_date' => $this->data['due_date'],
            'sec_code_status' => $this->data['sec_code_status'],
            'security_code' => $this->data['security_code'],
            'card_holder' => $this->data['card_holder'],
            'payment_type' => $this->data['payment_type'],
            'installments' => $this->data['installments'],
            'auth_token' => $this->data['auth_token']
        );
        $pay = new PaymentCard();
        $pay->setAuthToken($this->data['auth_token']);
        $pay->setData($this->data);
        $this->assertEquals($expectedData, $pay->getProcessData());
    }

    public function testAuthTokenNotEmpty() {

        $expectedError = array(
            'auth_token' => 'Invalid auth_token.'
        );

        $pay = new PaymentCard();
        $pay->setData($this->data);
        $this->assertFalse($pay->validate());
        $this->assertEquals($expectedError, $pay->getErrors());
    }

    public function testDescriptionNotEmpty() {$this->validateFields('description');}

    public function testAmountNotEmpty() {$this->validateFields('amount');}

    public function testReturnURLNotEmpty() {$this->validateFields('return_url');}

    public function testIssuerNotEmpty() {

        $expectedError = array(
            'issuer' => 'Invalid issuer.',
            'payment_type' => 'Invalid issuer for this payment type.'
        );

        $this->validateFields('issuer', $expectedError);
    }

    public function testCardNumberNotEmpty() {$this->validateFields('card_number');}

    public function testDueDateNotEmpty() {$this->validateFields('due_date');}

    public function testSecCodeStatusNotEmpty() {$this->validateFields('sec_code_status');}

    public function testCardHolderNotEmpty() {$this->validateFields('card_holder');}

    public function testPaymentTypeNotEmpty() {$this->validateFields('payment_type');}

    public function testInstallmentsNotEmpty() {$this->validateFields('installments');}

    public function testDescriptionMaxLength() {
        $this->field = 'description';
        $expectedError = array($this->field => 'Description is too long.');
        $this->data[$this->field] = implode(array_fill(0, 1025, 'm'));
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutNaturalNumberWithDot() {
        $this->field = 'amount';
        $expectedError = array($this->field => 'The amount should be in cents.');
        $this->data[$this->field] = 2.33;
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutNaturalNumberWithComma() {
        $this->field = 'amount';
        $expectedError = array($this->field => 'The amount should be in cents.');
        $this->data[$this->field] = '2,33';
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutNaturalNumberNegative() {
        $this->field = 'amount';
        $expectedError = array($this->field => 'Amount must be positive.');
        $this->data[$this->field] = -2.33;
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutStringNaturalNumberNegative() {
        $this->field = 'amount';
        $expectedError = array($this->field => 'Amount must be positive.');
        $this->data[$this->field] = '-2,33';
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutTooLong() {
        $this->field = 'amount';
        $expectedError = array($this->field => 'Amount is too long.');
        $this->data[$this->field] = implode(array_fill(0, 13, '1'));;
        $this->validateFields(null, $expectedError);
    }

    public function testReturnURLTooLong() {
        $this->field = 'return_url';
        $expectedError = array($this->field => 'Return URL is too long.');
        $this->data[$this->field] = implode(array_fill(0, 2049, 'm'));
        $this->validateFields(null, $expectedError);
    }

    public function testReturnURLInvalid() {
        $this->field = 'return_url';
        $expectedError = array($this->field => 'Invalid return_url.');
        $this->data[$this->field] = 'https://www.google.';
        $this->validateFields(null, $expectedError);
    }

    public function testReturnURLWithSubdomain() {
        $this->field = 'return_url';
        $expectedError = array($this->field => 'Invalid return_url.');
        $this->data[$this->field] = 'http://desenv.localhost:a156156/cliente';
        $this->validateFields(null, $expectedError);
    }

    public function testUnknownIssuer() {
        $this->field = 'issuer';
        $expectedError = array(
            $this->field => 'Unknown issuer.',
            'payment_type' => 'Invalid issuer for this payment type.'
        );
        $this->data[$this->field] = 'other issuer';
        $this->validateFields(null, $expectedError);
    }

    public function testCardNumberTooLong() {
        $this->field = 'card_number';
        $expectedError = array($this->field => 'card_number is too long.');
        $this->data[$this->field] = implode(array_fill(0, 20, 'm'));
        $this->validateFields(null, $expectedError);
    }

    public function testInvalidCardNumber() {
        $this->field = 'card_number';
        $expectedError = array($this->field => 'Invalid card_number.');
        $this->data[$this->field] = 'invalid card number';
        $this->validateFields(null, $expectedError);
    }

    public function testInvalidDueDateLength() {
        $this->field = 'due_date';
        $expectedError = array($this->field => 'Invalid due_date length.');
        $this->data[$this->field] = 123;
        $this->validateFields(null, $expectedError);
    }

    public function testDueDateNumeric() {
        $this->field = 'due_date';
        $expectedError = array($this->field => 'due_date must be numeric.');
        $this->data[$this->field] = '1o2015';
        $this->validateFields(null, $expectedError);
    }

    public function testInvalidSecCodeStatus() {
        $this->field = 'sec_code_status';
        $expectedError = array($this->field => 'sec_code_status is invalid.');
        $this->data[$this->field] = '999';
        $this->validateFields(null, $expectedError);
    }

    public function testSecurityCodeLength() {
        $this->field = 'security_code';
        $expectedError = array($this->field => 'Invalid security_code length.');
        $this->data[$this->field] = 123456;
        $this->validateFields(null, $expectedError);
    }

    public function testInvalidSecurityCode() {
        $this->field = 'security_code';
        $expectedError = array($this->field => 'Invalid security_code.');
        $this->data['sec_code_status'] = 1;
        $this->data[$this->field] = '';
        $this->validateFields(null, $expectedError);
    }

    public function testSecurityCodeNumeric() {
        $this->field = 'security_code';
        $expectedError = array($this->field => 'security_code must be numeric.');
        $this->data[$this->field] = 'aaa';
        $this->validateFields(null, $expectedError);
    }

    public function testCardHolderTooLong() {
        $this->field = 'card_holder';
        $expectedError = array($this->field => 'card_holder is too long.');
        $this->data[$this->field] = implode(array_fill(0, 51, 'm'));
        $this->validateFields(null, $expectedError);
    }

    public function testUnknownPaymentType() {
        $this->field = 'payment_type';
        $expectedError = array($this->field => 'Unknown payment_type.');
        $this->data[$this->field] = 'Unknown_payment_type';
        $this->validateFields(null, $expectedError);
    }

    public function testPaymentTypeInvalidIssuer() {
        $this->field = 'payment_type';
        $expectedError = array($this->field => 'Invalid issuer for this payment type.');
        $this->data[$this->field] = 'debito';
        $this->data['issuer'] = 'amex';
        $this->validateFields(null, $expectedError);
    }

    public function testInstallmentsTooLong() {
        $this->field = 'installments';
        $expectedError = array($this->field => 'Invalid installments for this payment_type.');
        $this->data[$this->field] = 9;
        $this->data['payment_type'] = 'credito_parcelado_loja';
        $this->validateFields(null, $expectedError);
    }

    public function testInstallmentsPaymentType() {
        $this->field = 'installments';
        $expectedError = array($this->field => 'The payment type allows only 1 installment.');
        $this->data[$this->field] = 2;
        $this->data['payment_type'] = 'debito';
        $this->validateFields(null, $expectedError);
    }

  

    /**
     * Método auxiliar na validação dos campos
     * $field = Caso recebido, indica teste de emptyFields
     * $expectedError = Utilizado quando um campo influencia em validações de outro campo. 
     *  Ex: Issuer, que influencia no teste do campo 'payment_type'
     */
    private function validateFields($field = null, $expectedError = null) {

        // Caso for uma validação de emptyFields, utiliza mensagem padrão de erros
        if ($expectedError == null) {
            $expectedError = array(
                "$field" => "Invalid $field."
            );
        }
        // Caso campo for passado de parâmetro, indica teste emptyField
        if ($field != null){
            $this->data[$field] = null;
        }

        $pay = new PaymentCard();
        $pay->setAuthToken('any');
        $pay->setData($this->data);

        $this->assertFalse($pay->validate());
        $this->assertEquals($expectedError, $pay->getErrors());
    }

}// End Class