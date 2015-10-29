<?php

use PayComponent\PaymentToken;
use PayComponent\Component\Validator;

class PaymentTokenTest extends PHPUnit_Framework_TestCase {

    private $field = null;

    public function setUp() {
        $this->data = array(
            'id' => 'order_id',
            'description' => 'Description Teste',
            'amount'  => 2500,
            'return_url' => 'http://www.google.com',
            'issuer' => 'visa',
            'payment_type'  => 'credito_a_vista',
            'installments' => 1,
            'token' => '32165843216543213546514'
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

    public function testValidateErrors() {
        $validator = $this->getMockBuilder('PayComponent\Component\Validator')->setMethods(array('validate', 'getValidationErrors'))->getMock();
        $validator->expects($this->any())->method('validate')->willReturn(false);
        $validator->expects($this->any())->method('getValidationErrors')->willReturn('some error');
        $pay = new PaymentToken($validator);
        $this->assertFalse($pay->validate());
        $this->assertEquals('some error', $pay->getErrors());
    }

    public function testValidatePassed() {
        $validator = $this->getMockBuilder('PayComponent\Component\Validator')->setMethods(array('validate', 'getValidationErrors'))->getMock();
        $validator->expects($this->any())->method('validate')->willReturn(true);
        $pay = new PaymentToken($validator);
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

        $pay = new PaymentToken();
        $pay->setAuthToken($this->data['auth_token']);
        $pay->setData($this->data);
        $this->assertEquals($expectedData, $pay->getCreationData());
    }

    public function testProcessData() {
        $this->data['auth_token'] = 'any';

        $expectedData = array(
            'issuer' => $this->data['issuer'],
            'payment_type' => $this->data['payment_type'],
            'installments' => $this->data['installments'],
            'auth_token' => $this->data['auth_token'],
            'token' => $this->data['token']
        );
        $pay = new PaymentToken();
        $pay->setAuthToken($this->data['auth_token']);
        $pay->setData($this->data);
        $this->assertEquals($expectedData, $pay->getProcessData());
    }

    public function testAuthTokenNotEmpty() {

        $expectedError = array(
            'auth_token' => array('Invalid auth_token.')
        );

        $pay = new PaymentToken();
        $pay->setData($this->data);
        $this->assertFalse($pay->validate());
        $this->assertEquals($expectedError, $pay->getErrors());
    }

    public function testDescriptionNotEmpty() {$this->validateFields('description');}

    public function testAmountNotEmpty() {$this->validateFields('amount');}

    public function testReturnURLNotEmpty() {$this->validateFields('return_url');}

    // public function testIssuerNotEmpty() {

    //     $expectedError = array(
    //         'issuer' => array('Invalid issuer.'),
    //         'payment_type' => array('Invalid issuer for this payment type.')
    //     );

    //     $this->validateFields('issuer', $expectedError);
    // }

    public function testPaymentTypeNotEmpty() {$this->validateFields('payment_type');}

    public function testInstallmentsNotEmpty() {$this->validateFields('installments');}

    public function testDescriptionMaxLength() {
        $this->field = 'description';
        $expectedError = array($this->field => array('Description is too long.'));
        $this->data[$this->field] = implode(array_fill(0, 1025, 'm'));
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutNaturalNumberWithDot() {
        $this->field = 'amount';
        $expectedError = array($this->field => array('The amount should be in cents.'));
        $this->data[$this->field] = 2.33;
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutNaturalNumberWithComma() {
        $this->field = 'amount';
        $expectedError = array($this->field => array('The amount should be in cents.'));
        $this->data[$this->field] = '2,33';
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutNaturalNumberNegative() {
        $this->field = 'amount';
        $expectedError = array($this->field => array('Amount must be positive.'));
        $this->data[$this->field] = -2.33;
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutStringNaturalNumberNegative() {
        $this->field = 'amount';
        $expectedError = array($this->field => array('Amount must be positive.'));
        $this->data[$this->field] = '-2,33';
        $this->validateFields(null, $expectedError);
    }

    public function testAmoutTooLong() {
        $this->field = 'amount';
        $expectedError = array($this->field => array('Amount is too long.'));
        $this->data[$this->field] = implode(array_fill(0, 13, '1'));;
        $this->validateFields(null, $expectedError);
    }

    public function testReturnURLTooLong() {
        $this->field = 'return_url';
        $expectedError = array($this->field => array('Return URL is too long.'));
        $this->data[$this->field] = implode(array_fill(0, 2049, 'm'));
        $this->validateFields(null, $expectedError);
    }

    public function testReturnURLInvalid() {
        $this->field = 'return_url';
        $expectedError = array($this->field => array('Invalid return_url.'));
        $this->data[$this->field] = 'https://www.google.';
        $this->validateFields(null, $expectedError);
    }

    public function testReturnURLWithSubdomain() {
        $this->field = 'return_url';
        $expectedError = array($this->field => array('Invalid return_url.'));
        $this->data[$this->field] = 'http://desenv.localhost:a156156/cliente';
        $this->validateFields(null, $expectedError);
    }

    // public function testUnknownIssuer() {
    //     $this->field = 'issuer';
    //     $expectedError = array(
    //         $this->field => array('Unknown issuer.'),
    //         'payment_type' => array('Invalid issuer for this payment type.')
    //     );
    //     $this->data[$this->field] = 'other issuer';
    //     $this->validateFields(null, $expectedError);
    // }

    public function testUnknownPaymentType() {
        $this->field = 'payment_type';
        $expectedError = array($this->field => array('Unknown payment_type.'));
        $this->data[$this->field] = 'Unknown_payment_type';
        $this->validateFields(null, $expectedError);
    }

    // public function testPaymentTypeInvalidIssuer() {
    //     $this->field = 'payment_type';
    //     $expectedError = array($this->field => array('Invalid issuer for this payment type.'));
    //     $this->data[$this->field] = 'debito';
    //     $this->data['issuer'] = 'amex';
    //     $this->validateFields(null, $expectedError);
    // }

    public function testInstallmentsTooLong() {
        $this->field = 'installments';
        $expectedError = array($this->field => array('Invalid installments for this payment_type.'));
        $this->data[$this->field] = 9;
        $this->data['payment_type'] = 'credito_parcelado_loja';
        $this->validateFields(null, $expectedError);
    }

    public function testInstallmentsPaymentType() {
        $this->field = 'installments';
        $expectedError = array($this->field => array('The payment type allows only 1 installment.'));
        $this->data[$this->field] = 2;
        $this->data['payment_type'] = 'credito_a_vista';
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
                "$field" => array("Invalid $field.")
            );
        }
        // Caso campo for passado de parâmetro, indica teste emptyField
        if ($field != null){
            $this->data[$field] = null;
        }

        $pay = new PaymentToken();
        $pay->setAuthToken('any');
        $pay->setData($this->data);

        $this->assertFalse($pay->validate());
        $this->assertEquals($expectedError, $pay->getErrors());
    }

}