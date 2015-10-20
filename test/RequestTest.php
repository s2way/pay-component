<?php

require_once 'src/Requester.php';

class RequestTest extends PHPUnit_Framework_TestCase {

    // public function setUp() {
    //     $this->data = array(
    //         'description' => 'Description test',
    //         'amount' => 123456,
    //         'return_url' => 'http://www.google.com.br',
    //         'issuer' => 'visa',
    //         'card_number' => '153241251234',
    //         'due_date' => '072015',
    //         'sec_code_status' => 1,
    //         'security_code' => 619,
    //         'card_holder' => 'TEST NAME',
    //         'payment_type' => 'debito',
    //         'installments' => 1
    //     );
    // }

    public function testProcess() {
        $requester = new Requester();
        $a = $requester->process();
        die(var_dump($a));
    }

}
