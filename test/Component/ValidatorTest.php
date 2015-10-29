<?php

use PayComponent\Component\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase {

    // public function testErrorWithOneFieldOneRule() {

    // 	$data['description'] = '';

    // 	$expectedError = array(
    //         'description' => array('Invalid descripion.')
    //     );

    // 	$rule = array(
    // 		'description' => array(
	   //  		'notEmpty' => array(
	   //  			'message' => 'Invalid descripion.'
	   //  		)
    // 		)
    // 	);

    // 	$validator = new Validator();
    // 	$validator->validate($rule, $data);
    // 	$this->assertEquals($expectedError, $validator->getValidationErrors());
    // }

    /**
     * No caso dos campos com duas regras, deverá retornar apenas a primeira regra deste campo.
     */
    public function testErrorWithOneFieldTwoRules() {

        $data['due_date'] = 'a1b2c3d4e5e6';

        $expectedError = array(
            'due_date' => array(
                'Invalid due_date length.'
            )
        );

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
            'description' => array('Invalid description.'),
            'issuer' => array('Invalid issuer.')
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
            'due_date' => array(
                'Invalid due_date length.',
            ),
            'card_number' => array(
                'card_number is too long.',
            )
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