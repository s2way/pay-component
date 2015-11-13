<?php

use PayComponent\Component\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase {

    public function testErrorWithOneFieldOneRule() {

    	$data['description'] = '';

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
    	$validator->validate($rule, $data);
    	$this->assertEquals($expectedError, $validator->getValidationErrors());
    }

    public function testErrorWithOneFieldTwoRules() {

        $data['due_date'] = 'a1b2c3d4e5e6';

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
        $validator->validate($rule, $data);
        $this->assertEquals($expectedError, $validator->getValidationErrors());
    }

    public function testErrorWithTwoFieldsOneRule() {

        $data['description'] = '';
        $data['issuer'] = '';

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
        $validator->validate($rule, $data);
        $this->assertEquals($expectedError, $validator->getValidationErrors());
    }

    public function testErrorWithTwoFieldsTwoRules() {

        $data['due_date'] = 'a1b2c3d4e5e6';
        $data['card_number'] = '0123456789abcdefghij';

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
        $validator->validate($rule, $data);
        $this->assertEquals($expectedError, $validator->getValidationErrors());
    }

    public function testSucess(){
    	$data['description'] = 'populated';

    	$rule = array(
    		'description' => array(
	    		'notEmpty' => array(
	    			'message' => 'FIELD IS EMPTY'
	    		)
    		)
    	);

    	$validator = new Validator();
    	$validator->validate($rule, $data);
    	$this->assertEmpty($validator->getValidationErrors());	
    }
}