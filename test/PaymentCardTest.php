<?php

use PayComponent\PaymentCard;
use PayComponent\Component\Validator;
use PHPUnit\Framework\TestCase;

class PaymentCardTest extends TestCase {

    private $field = null;

    public function setUp() {
        $this->data = array(
            'id'            => 123,
            'description'   => 'Description test',
            'amount'        => 123456,
            'issuer'        => 'visa',
            'card_number'   => '153241251234154',
            'due_date'      => '072015',
            'security_code' => 619,
            'card_holder'   => 'TEST NAME',
            'payment_type'  => 'debit',
            'installments'  => 1,
            'Customer' => array(
                'birthdate' => '1988-04-07',
                'street' => '123',
                'number' => '123',
                'city' => '123',
                'zip_code' => '93346440',
                'state' => 'rs',
                'country' => 'brasil',
                'name' => 'Usuário Desenv',
                'email' => 'usuario@desenv.com.br',
            )
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

    /**
     *******************************
     ***** TESTS CLASS METHODS *****
     *******************************
     */

    public function testCreationData() {
        $this->data['auth_token'] = 'any';

        $expectedData = array(
            'id'          => $this->data['id'],
            'auth_token'  => $this->data['auth_token'],
            'description' => $this->data['description'],
            'amount'      => $this->data['amount'],
        );

        $pay = new PaymentCard();
        $pay->setAuthToken($this->data['auth_token']);
        $pay->setData($this->data);
        $this->assertEquals($expectedData, $pay->getCreationData());
    }

    public function testProcessData() {
        $this->data['auth_token'] = 'any';

        $expectedData = array(
            'issuer'        => $this->data['issuer'],
            'card_number'   => $this->data['card_number'],
            'due_date'      => $this->data['due_date'],
            'security_code' => $this->data['security_code'],
            'card_holder'   => $this->data['card_holder'],
            'payment_type'  => $this->data['payment_type'],
            'installments'  => $this->data['installments'],
            'auth_token'    => $this->data['auth_token'],
            'Customer' => array(
                'birthdate' => $this->data['Customer']['birthdate'],
                'street'    => $this->data['Customer']['street'],
                'number'    => $this->data['Customer']['number'],
                'city'      => $this->data['Customer']['city'],
                'zip_code'  => $this->data['Customer']['zip_code'],
                'state'     => $this->data['Customer']['state'],
                'country'   => $this->data['Customer']['country'],
                'name'      => $this->data['Customer']['name'],
                'email'     => $this->data['Customer']['email']
            )
        );
        $pay = new PaymentCard();
        $pay->setAuthToken($this->data['auth_token']);
        $pay->setData($this->data);

        $this->assertEquals($expectedData, $pay->getProcessData());
    }

}// End Class
