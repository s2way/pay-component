// <?php

// require_once 'src/PaymentToken.php';
// require_once 'src/Component/Validator.php';

// class PaymentTokenTest extends PHPUnit_Framework_TestCase {

//     public function setUp() {
//         $this->data = array(
//             'description' => 'Description Teste',
//             'amount'  => 2500,
//             'return_url' => 'http://www.google.com',
//             'issuer' => 'visa',
//             'payment_type'  => 'credito_a_vista',
//             'installments' => 1,
//             'token' => '32165843216543213546514'
//         );
//     }

//     /**
//      ********************************
//      ***** TESTS PARENT METHODS *****
//      ********************************
//      */

//     /*public function testSetData() {
//         $expectedData = array(
//             'some' => 'data'
//         );
//         $pay = new PaymentToken();
//         $pay->setData($expectedData);
//         $this->assertEquals($expectedData, $pay->getData());
//     }*/

//     public function testSetAuthToken() {
//         $expectedData = 'someToken';
//         $pay = new PaymentToken();
//         $pay->setAuthToken($expectedData);
//         $this->assertEquals($expectedData, $pay->getAuthToken());
//     }

//     /**
//       * @expectedException InvalidArgumentException
//       * @expectedExceptionMessage some error
//     */
//     public function testValidateException() {
//         $validator = $this->getMockBuilder('Validator')->setMethods(array('validate', 'getError'))->getMock();
//         $validator->expects($this->any())->method('validate')->willReturn(false);
//         $validator->expects($this->any())->method('getError')->willReturn('some error');
//         $pay = new PaymentToken($validator);
//         $pay->validate();
//     }

//     public function testValidatePassed() {
//         $validator = $this->getMockBuilder('Validator')->setMethods(array('validate', 'getError'))->getMock();
//         $validator->expects($this->any())->method('validate')->willReturn(true);
//         $pay = new PaymentToken($validator);
//         $pay->validate();
//         $this->assertEmpty($validator->getError());
//     }

//     /**
//      *******************************
//      ***** TESTS CLASS METHODS *****
//      *******************************
//      */

//     /**
//       * @expectedException InvalidArgumentException
//       * @expectedExceptionMessage Invalid auth_token.
//     */
//     public function testAuthTokenNotEmpty() {
//         $pay = new PaymentToken();
        
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     public function testDescriptionNotEmpty() {$this->validateNotEmpty('description');}

//     public function testAmountNotEmpty() {$this->validateNotEmpty('amount');}

//     public function testReturnURLNotEmpty() {$this->validateNotEmpty('return_url');}

//     public function testIssuerNotEmpty() {$this->validateNotEmpty('issuer');}

//     public function testPaymentTypeNotEmpty() {$this->validateNotEmpty('payment_type');}

//     public function testInstallmentsNotEmpty() {$this->validateNotEmpty('installments');}

//     public function testTokenNotEmpty() {$this->validateNotEmpty('token');}

//     /**
//      * @expectedExceptionMessage Description is too long.
//      * @expectedException InvalidArgumentException
//      */
//     public function testDescriptionMaxLength() {
//         $pay = new PaymentToken();

//         $this->data['description'] = implode(array_fill(0, 1025, 'm'));

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage The amount should be in cents.
//      */
//     public function testAmoutNaturalNumberWithDot() {
//         $pay = new PaymentToken();

//         $this->data['amount'] = 2.33;

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage The amount should be in cents.
//      */
//     public function testAmoutNaturalNumberWithComma() {
//         $pay = new PaymentToken();

//         $this->data['amount'] = '2,33';

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Amount must be positive.
//      */
//     public function testAmoutNaturalNumberNegative() {
//         $pay = new PaymentToken();

//         $this->data['amount'] = -2.33;

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Amount is too long.
//      */
//     public function testAmoutTooLong() {
//         $pay = new PaymentToken();

//         $this->data['amount'] = implode(array_fill(0, 13, '1'));;

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Return URL is too long.
//      */
//     public function testReturnURLTooLong() {
//         $pay = new PaymentToken();

//         $this->data['return_url'] = implode(array_fill(0, 2049, 'm'));

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Invalid return_url.
//      */
//     public function testReturnURLInvalid() {
//         $pay = new PaymentToken();

//         $this->data['return_url'] = 'https://www.google.';

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Unknown issuer.
//      */
//     public function testUnknownIssuer() {
//         $pay = new PaymentToken();

//         $this->data['issuer'] = 'other issuer';

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Unknown payment_type.
//     */
//     public function testUnknownPaymentType() {
//         $pay = new PaymentToken();

//         $this->data['payment_type'] = 'Unknown_payment_type';
//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Invalid issuer for this payment type.
//     */
//     public function testPaymentTypeInvalidIssuer() {
//         $pay = new PaymentToken();

//         $this->data['payment_type'] = 'debito';
//         $this->data['issuer'] = 'amex';

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Invalid installments for this payment_type.
//     */
//     public function testInstallmentsTooLong() {
//         $pay = new PaymentToken();

//         $this->data['installments'] = 9;
//         $this->data['payment_type'] = 'credito_parcelado_loja';

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage The payment type allows only 1 installment.
//     */
//     public function testInstallmentsPaymentType() {
//         $pay = new PaymentToken();

//         $this->data['installments'] = 2;
//         $this->data['payment_type'] = 'debito';

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage token is too long
//     */
//     public function testTokenTooLong() {
//         $pay = new PaymentToken();

//         $this->data['token'] = implode(array_fill(0, 101, 'm'));

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }

//     /**
//      * @expectedException InvalidArgumentException
//      * @expectedExceptionMessage Payment type debit not supported process with token
//     */
//     public function testTokenPaymentDebit() {
//         $pay = new PaymentToken();

//         $this->data['token'] = '1234567890abcdefghi';
//         $this->data['payment_type'] = 'debito';

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
//         $pay->validate();
//     }



//     private function validateNotEmpty($field) {
//         $pay = new PaymentToken();

//         $this->data[$field] = null;

//         $pay->setAuthToken('any');
//         $pay->setData($this->data);
        
//         try {
//             $pay->validate();
//             $this->fail();
//         } catch (Exception $e) {
//             $this->assertEquals("Invalid $field.", $e->getMessage());
//             $this->assertEquals('InvalidArgumentException', get_class($e));
//         }
//     }
// }