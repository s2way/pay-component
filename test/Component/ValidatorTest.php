<?php

use PayComponent\Component\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase {

    public function testErrorWithOneFieldOneRule() {

    	$this->data['description'] = '';

    	$expectedError = array(
            'description' => 'Invalid descripion.'
        );

    	$rule = array(
    		'description' => array(
	    		'notEmpty' => array(
	    			'message' => 'Invalid descripion.'
	    		)
    		)
    	);

    	$validator = new Validator();
    	$validator->validate($rule, $this->data);
    	$this->assertEquals($expectedError, $validator->getValidationErrors());
    }

    public function testErrorWithOneFieldTwoRules() {

        $this->data['due_date'] = 'a1b2c3d4e5e6';

        $rule = array(
            'due_date' => array(
                'equalLength' => array(
                    'params' => 6,
                    'message' => 'Invalid due_date length.'
                ),
                'numeric' => array(
                    'message' => 'due_date must be numeric.'
                )
            )
        );

        $expectedError = array(
            'due_date' => 'Invalid due_date length.'
        );

        $validator = new Validator();
        $validator->validate($rule, $this->data);
        $this->assertEquals($expectedError, $validator->getValidationErrors());
    }

    public function testErrorWithTwoFieldsOneRule() {

        $this->data['description'] = '';
        $this->data['issuer'] = '';

        $rule = array(
            'description' => array(
                'notEmpty' => array(
                    'message' => 'Invalid description.'
                )
            ),
            'issuer' => array(
                'notEmpty' => array(
                    'message' => 'Invalid issuer.'
                )
            )
        );

        $expectedError = array(
            'description' => 'Invalid description.',
            'issuer' => 'Invalid issuer.'
        );

        $validator = new Validator();
        $validator->validate($rule, $this->data);
        $this->assertEquals($expectedError, $validator->getValidationErrors());
    }

    public function testErrorWithTwoFieldsTwoRules() {

        $this->data['due_date'] = 'a1b2c3d4e5e6';
        $this->data['card_number'] = '0123456789abcdefghij';

        $rule = array(
            'due_date' => array(
                'equalLength' => array(
                    'params' => 6,
                    'message' => 'Invalid due_date length.'
                ),
                'numeric' => array(
                    'message' => 'due_date must be numeric.'
                )
            ),
            'card_number' => array(
                'maxLength' => array(
                    'params' => 19,
                    'message' => 'card_number is too long.'
                ),
                'numeric' => array(
                    'message' => 'Invalid card_number.'
                )
            )
        );

        $expectedError = array(
            'due_date' => 'Invalid due_date length.',
            'card_number' => 'card_number is too long.'
        );

        $validator = new Validator();
        $validator->validate($rule, $this->data);
        $this->assertEquals($expectedError, $validator->getValidationErrors());
    }

    public function testSucess(){
    	$this->data['description'] = 'populated';

    	$rule = array(
    		'description' => array(
	    		'notEmpty' => array(
	    			'message' => 'FIELD IS EMPTY'
	    		)
    		)
    	);

    	$validator = new Validator();
    	$validator->validate($rule, $this->data);
    	$this->assertEmpty($validator->getValidationErrors());	
    }

    public function testInstallmentsPaymentTypeFailed(){
        $this->check = 2;
        $this->params = ['other_payment_type', 'another_payment_type'];
        $this->data = ['payment_type' => 'other_payment_type'];
        $this->executeTest(false, 'installmentsPaymentType');
    }

    public function testInstallmentsPaymentTypeSucessWithPaymentType(){
        $this->check = 2;
        $this->params = ['other_payment_type', 'another_payment_type'];
        $this->data = ['payment_type' => 'any_payment_type'];
        $this->executeTest(true, 'installmentsPaymentType');
    }

    public function testInstallmentsPaymentTypeSucessWithInstallments(){
        $this->check = 1;
        $this->params = ['other_payment_type', 'another_payment_type'];
        $this->data = ['payment_type' => 'any_payment_type'];
        $this->executeTest(true, 'installmentsPaymentType');
    }

    public function testInstallmentsMaxValueFailed() {
        $this->check = 10;
        $this->params = 5;
        $this->data = null;
        $this->executeTest(false, 'installmentsMaxValue');
    }

    public function testInstallmentsMaxValueSuccess() {
        $this->check = 1;
        $this->params = 5;
        $this->data = null;
        $this->executeTest(true, 'installmentsMaxValue');
    }

    public function testSecurityCodeSecCodeStatusFailed() {
        $this->check = '';
        $this->params = null;
        $this->data = ['sec_code_status' => 1];
        $this->executeTest(false, 'securityCodeSecCodeStatus');
    }

    public function testSecurityCodeSecCodeStatusTrueWithSecCodeStatusDifferent(){
        $this->check = '';
        $this->params = null;
        $this->data = ['sec_code_status' => 2];
        $this->executeTest(true, 'securityCodeSecCodeStatus');
    }

    public function testSecurityCodeSecCodeStatusTrueWithCheckNotEmpty(){
        $this->check = '123';
        $this->params = null;
        $this->data = ['sec_code_status' => 1];
        $this->executeTest(true, 'securityCodeSecCodeStatus');
    }

    public function testInvalidUrl_1(){
        $this->check = 'http://123456';
        $this->params = '';
        $this->data = '';
        $this->executeTest(false, 'url');
    }

    public function testInvalidUrl_2(){
        $this->check = '123456';
        $this->params = '';
        $this->data = '';
        $this->executeTest(false, 'url');
    }

    public function testInvalidUrl_3(){
        $this->check = 'hhhh://valid.com.br';
        $this->params = '';
        $this->data = '';
        $this->executeTest(false, 'url');
    }

    public function testInvalidUrl_4(){
        $this->check = 'http://valid@com@br';
        $this->params = '';
        $this->data = '';
        $this->executeTest(false, 'url');
    }

    public function testValidUrlHttp(){
        $this->check = 'http://pay.com.br';
        $this->params = '';
        $this->data = '';
        $this->executeTest(true, 'url');
    }

    public function testValidUrlHttps(){
        $this->check = 'https://pay.com.br';
        $this->params = '';
        $this->data = '';
        $this->executeTest(true, 'url');
    }

    public function testPaymentTypeIssuerFailedWithoutIssuer(){
        $this->check = 'debito';
        $this->params = null;
        $this->data = [];
        $this->executeTest(false, 'paymentTypeIssuer');
    }

    public function testPaymentTypeIssuerFailedNotInList(){
        $this->check = 'debito';
        $this->params = null;
        $this->data = ['issuer' => 'elo'];
        $this->executeTest(false, 'paymentTypeIssuer');
    }

    public function testPaymentTypeIssuerSuccessWithCheckDebito(){
        $this->check = 'credito_a_vista';
        $this->params = null;
        $this->data = ['issuer' => 'invalid_issuer'];
        $this->executeTest(true, 'paymentTypeIssuer');
    }

    public function testTokenPaymentTypeFailed(){
        $this->check = null;
        $this->params = null;
        $this->data = ['token' => 'any_token', 'payment_type' => 'debito'];
        $this->executeTest(false, 'tokenPaymentType');
    }

    public function testTokenPaymentTypeSuccessWithoutToken(){
        $this->check = null;
        $this->params = null;
        $this->data = ['payment_type' => 'debito'];
        $this->executeTest(true, 'tokenPaymentType');
    }

    public function testTokenPaymentTypeSuccessPaymentDebito(){
        $this->check = null;
        $this->params = null;
        $this->data = ['token' => 'any_token', 'payment_type' => 'credito_a_vista'];
        $this->executeTest(true, 'tokenPaymentType');
    }

    public function testNotEmptyStrFailed(){
        $this->check = '';
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'notEmpty');
    }

    public function testNotEmptyArrayFailed(){
        $this->check = array();
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'notEmpty');
    }

    public function testNotEmptyArrayFailedWithCountryDefault(){
        $this->check = array('country' => 'any');
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'notEmpty');
    }

    public function testNotEmptySuccess(){
        $this->check = '123';
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'notEmpty');
    }

    public function testAlphaNumericStrFailed(){
        $this->check = '';
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'alphaNumeric');
    }

    public function testAlphaNumericArrayFailed(){
        $this->check = array();
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'alphaNumeric');
    }

    public function testAlphaNumericSpecialCharacterFailed(){
        $this->check = '!@#$%*&*()';
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'alphaNumeric');
    }

    public function testAlphaNumericSuccessStr(){
        $this->check = 'abc';
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'alphaNumeric');
    }

    public function testAlphaNumericSuccessIntStr(){
        $this->check = '123';
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'alphaNumeric');
    }

    public function testAlphaNumericSuccessInteger(){
        $this->check = 123;
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'alphaNumeric');
    }

    public function testBetweenFailed(){
        $this->check = 1;
        $this->params = ['min' => 5, 'max' => 10];
        $this->data = null;
        $this->executeTest(false, 'between');
    }

    public function testBetweenSuccess(){
        $this->check = 8;
        $this->params = ['min' => 5, 'max' => 10];
        $this->data = null;
        $this->executeTest(true, 'between');
    }

    public function testGreaterThanFailedCheckNull(){
        $this->check = null;
        $this->params = 5;
        $this->data = null;
        $this->executeTest(false, 'greaterThan');
    }

    public function testGreaterThanFailed(){
        $this->check = 1;
        $this->params = 5;
        $this->data = null;
        $this->executeTest(false, 'greaterThan');
    }

    public function testGreaterThanSuccess(){
        $this->check = 2;
        $this->params = 1;
        $this->data = null;
        $this->executeTest(true, 'greaterThan');
    }

    public function testComparisonInvalidOperator(){
        $this->check = 123;
        $this->params = '#';
        $this->data = 123;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsGreaterFailed(){
        $this->check = 1;
        $this->params = 'isgreater';
        $this->data = 10;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsGreaterSymbolFailed(){
        $this->check = 1;
        $this->params = '>';
        $this->data = 10;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsGreaterSuccess(){
        $this->check = 10;
        $this->params = 'isgreater';
        $this->data = 1;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsGreaterSuccessSymbol(){
        $this->check = 10;
        $this->params = '>';
        $this->data = 1;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsLessFailed(){
        $this->check = 10;
        $this->params = 'isless';
        $this->data = 1;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsLessSymbolFailed(){
        $this->check = 10;
        $this->params = '<';
        $this->data = 1;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsLessSuccess(){
        $this->check = 1;
        $this->params = 'isless';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsLessSuccessSymbol(){
        $this->check = 1;
        $this->params = '<';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsGreaterOrEqualFailed(){
        $this->check = 1;
        $this->params = 'greaterorequal';
        $this->data = 10;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsGreaterOrEqualSymbolFailed(){
        $this->check = 1;
        $this->params = '>=';
        $this->data = 10;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsGreaterOrEqualSuccessEqual(){
        $this->check = 10;
        $this->params = 'greaterorequal';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsGreaterOrEqualSuccessEqualSymbol(){
        $this->check = 10;
        $this->params = '>=';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsGreaterOrEqualSuccessGreater(){
        $this->check = 10;
        $this->params = 'greaterorequal';
        $this->data = 1;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsGreaterOrEqualSuccessGreaterSymbol(){
        $this->check = 10;
        $this->params = '>=';
        $this->data = 1;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsLessOrEqualFailed(){
        $this->check = 10;
        $this->params = 'islessorequal';
        $this->data = 1;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsLessOrEqualSymbolFailed(){
        $this->check = 10;
        $this->params = '<=';
        $this->data = 1;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonIsLessOrEqualSuccessEqual(){
        $this->check = 10;
        $this->params = 'lessorequal';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsLessOrEqualSuccessEqualSymbol(){
        $this->check = 10;
        $this->params = '<=';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsLessOrEqualSuccessLess(){
        $this->check = 1;
        $this->params = 'lessorequal';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonIsLessOrEqualSuccessLessSymbol(){
        $this->check = 1;
        $this->params = '<=';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonEqualToFailed(){
        $this->check = 1;
        $this->params = 'equalto';
        $this->data = 10;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonEqualToSymbolFailed(){
        $this->check = 1;
        $this->params = '==';
        $this->data = 10;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonEqualToSuccess(){
        $this->check = 10;
        $this->params = 'equalto';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonEqualToSuccessSymbol(){
        $this->check = 10;
        $this->params = '==';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonNoteEqualFailed(){
        $this->check = 10;
        $this->params = 'notequal';
        $this->data = 10;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonNoteEqualSymbolFailed(){
        $this->check = 10;
        $this->params = '!=';
        $this->data = 10;
        $this->executeTest(false, 'comparison');
    }

    public function testComparisonNoteEqualSuccess(){
        $this->check = 1;
        $this->params = 'notequal';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testComparisonNoteEqualSuccessSymbol(){
        $this->check = 1;
        $this->params = '!=';
        $this->data = 10;
        $this->executeTest(true, 'comparison');
    }

    public function testBooleanSpecialCharacterFailed_1(){
        $this->check = '#';
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'boolean');
    }

    public function testBooleanSpecialCharacterFailed_2(){
        $this->check = '@';
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'boolean');
    }

    public function testBooleanInt0(){
        $this->check = 0;
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'boolean');
    }
    public function testBooleanInt1(){
        $this->check = 1;
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'boolean');
    }

    public function testBooleanStr0(){
        $this->check = '0';
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'boolean');
    }

    public function testBooleanStr1(){
        $this->check = '1';
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'boolean');
    }

    public function testBooleanTrue(){
        $this->check = true;
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'boolean');
    }

    public function testBooleanFalse(){
        $this->check = false;
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'boolean');
    }

    public function testMinLengthFailed(){
        $this->check = '123456';
        $this->params = 10;
        $this->data = null;
        $this->executeTest(false, 'minLength');
    }

    public function testMinLengthSucess(){
        $this->check = '123456';
        $this->params = 3;
        $this->data = null;
        $this->executeTest(true, 'minLength');
    }

    public function testMaxLengthFailed(){
        $this->check = '123456';
        $this->params = 3;
        $this->data = null;
        $this->executeTest(false, 'maxLength');
    }

    public function testMaxLengthSucess(){
        $this->check = '123456';
        $this->params = 10;
        $this->data = null;
        $this->executeTest(true, 'maxLength');
    }

    public function testEqualLengthFailed(){
        $this->check = '123456';
        $this->params = 3;
        $this->data = null;
        $this->executeTest(false, 'equalLength');
    }

    public function testEqualLengthSucess(){
        $this->check = '123456';
        $this->params = 6;
        $this->data = null;
        $this->executeTest(true, 'equalLength');
    }

    public function testBetweenLengthFailed(){
        $this->check = '123456789';
        $this->params = ['min' => 3, 'max' => 6];
        $this->data = null;
        $this->executeTest(false, 'betweenLength');
    }

    public function testBetweenLengthSucess(){
        $this->check = '123456';
        $this->params = ['min' => 1, 'max' => 10];
        $this->data = null;
        $this->executeTest(true, 'betweenLength');
    }

    public function testNumericSpecialCharacterFailed_1(){
        $this->check = '@';
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'numeric');
    }

    public function testNumericSpecialCharacterFailed_2(){
        $this->check = '&';
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'numeric');
    }

    public function testNumericStringFailed(){
        $this->check = 'abcd';
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'numeric');
    }

    public function testNumericStrSucess(){
        $this->check = '123';
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'numeric');
    }

    public function testNumericIntSucess(){
        $this->check = 123;
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'numeric');
    }

    public function testNaturalNumberFailed_1(){
        $this->check = -1;
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'naturalNumber');
    }

    public function testNaturalNumberFailed_2(){
        $this->check = 0.00;
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'naturalNumber');
    }

    public function testNaturalNumberFailed_3(){
        $this->check = 0.69;
        $this->params = null;
        $this->data = null;
        $this->executeTest(false, 'naturalNumber');
    }

    public function testNaturalNumberSuccess(){
        $this->check = 1.00;
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'naturalNumber');
    }

    public function testNaturalNumberSuccess_1(){
        $this->check = 1;
        $this->params = null;
        $this->data = null;
        $this->executeTest(true, 'naturalNumber');
    }

    public function testInListFailed(){
        $this->check = 'any_value';
        $this->params = ['other_value', 'another_value'];
        $this->data = null;
        $this->executeTest(false, 'inList');
    }

    public function testInListSuccess(){
        $this->check = 'any_value';
        $this->params = ['other_value', 'another_value', 'any_value'];
        $this->data = null;
        $this->executeTest(true, 'inList');
    }


    private function executeTest($assertTrueOrFalse, $methodName){
        $validator = new Validator();
        if ($assertTrueOrFalse) {
            if ($this->params === null and $this->data === null){
                $this->assertTrue($validator->$methodName($this->check));
            }else{
                $this->assertTrue($validator->$methodName($this->check, $this->params, $this->data));
            }
        }else{
            if ($this->params === null and $this->data === null){
                $this->assertFalse($validator->$methodName($this->check));
            }else{
                $this->assertFalse($validator->$methodName($this->check, $this->params, $this->data));
            }
        }
    }
}