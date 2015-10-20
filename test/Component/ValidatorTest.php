<?php

require_once 'src/Component/Validator.php';

class ValidatorTest extends PHPUnit_Framework_TestCase {

    public function testError() {

    	$data['description'] = '';

    	$expectedError = 'FIELD IS EMPTY';

    	$rule = array(
    		'description' => array(
	    		'notEmpty' => array(
	    			'message' => $expectedError
	    		)
    		)
    	);

    	$validator = new Validator();
    	$validator->validate($rule, $data);
    	$this->assertEquals($expectedError, $validator->getError());
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
    	$this->assertEmpty($validator->getError());	
    }

}