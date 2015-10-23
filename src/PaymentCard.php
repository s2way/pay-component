<?php

require_once 'src/Payment.php';

class PaymentCard extends Payment {

	private $URL = null;
	private $token = null;
	private $creationFields = array('id', 'auth_token', 'description', 'amount', 'return_url');
	private $processFields = array('issuer','card_number','due_date','sec_code_status','security_code','card_holder','payment_type','installments','auth_token');

	public function getCreationData() {
		return array_intersect_key($this->data, array_flip($this->creationFields));
	}

	public function getProcessData() {
		return array_intersect_key($this->data, array_flip($this->processFields));
	}

	public function setToken($token) {
		$this->token = $token;
	}

	public function getToken() {
		return $this->token;
	}

	public function rules() {
		return array(
			'auth_token' => array(
				'notEmpty' => array(
					'message' => 'Invalid auth_token.'
				)
			),
			'description' => array(
				'notEmpty' => array(
					'message' => 'Invalid description.'
				),
				'maxLength' => array(
					'params' => 1024,
					'message' => 'Description is too long.'
				)
			),
			'amount' => array(
				'notEmpty' => array(
					'message' => 'Invalid amount.'
				),
				'maxLength' => array(
					'params' => 12,
					'message' => 'Amount is too long.'
				),
				'greaterThan' => array(
					'params' => 0,
					'message' => 'Amount must be positive.'
				),
				'naturalNumber' => array(
					'message' => 'The amount should be in cents.'
				)
			),
			'return_url' => array(
				'notEmpty' => array(
					'message' => 'Invalid return_url.'
				),
				'maxLength' => array(
					'params' => 2048,
					'message' => 'Return URL is too long.'
				),
				'url' => array(
					'message' => 'Invalid return_url.'
				)
			),
			'issuer' => array(
				'notEmpty' => array(
					'message' => 'Invalid issuer.'
				),
				'inList' => array(
					'params' => array('visa','mastercard','diners','discover','elo','amex','jcb','aura'),
					'message' => 'Unknown issuer.'
				)
			),
			'card_number' => array(
				'notEmpty' => array(
					'message' => 'Invalid card_number.'
				),
				'maxLength' => array(
					'params' => 19,
					'message' => 'card_number is too long.'
				),
				'numeric' => array(
					'message' => 'Invalid card_number.'
				)
			),
			'due_date' => array(
				'notEmpty' => array(
					'message' => 'Invalid due_date.'
				),
				'equalLength' => array(
					'params' => 6,
					'message' => 'Invalid due_date length.'
				),
				'numeric' => array(
					'message' => 'due_date must be numeric.'
				)
			),
			'sec_code_status' => array(
				'notEmpty' => array(
					'message' => 'Invalid sec_code_status.'
				),
				'inList' => array(
					'params' => array(0,1,2,9),
					'message' => 'sec_code_status is invalid.'
				)
			),
			'security_code' => array(
				'notEmpty' => array(
					'message' => 'Invalid security_code.'
				),
				'betweenLength' => array(
					'params' => array('min' => 3, 'max' => 4),
					'message' => 'Invalid security_code length.'
				),
				'securityCodeSecCodeStatus' => array(
					'message' => 'Invalid security_code.'
				),
				'numeric' => array(
					'message' => 'security_code must be numeric.'
				)
			),
			'card_holder' => array(
				'notEmpty' => array(
					'message' => 'Invalid card_holder.'
				),
				'maxLength' => array(
					'params' => 50,
					'message' => 'card_holder is too long.'
				)
			),
			'payment_type' => array(
				'notEmpty' => array(
					'message' => 'Invalid payment_type.'
				),
				'inList' => array(
					'params' => array('credito_a_vista', 'credito_parcelado_loja', 'debito'),
					'message' => 'Unknown payment_type.'
				),
				'paymentTypeIssuer' => array(
					'message' => 'Invalid issuer for this payment type.'
				)
			),
			'installments' => array(
				'notEmpty' => array(
					'message' => 'Invalid installments.'
				),
				'installmentsPaymentType' => array(
					'params' => array('debito', 'credito_a_vista'),
					'message' => 'The payment type allows only 1 installment.'
				),
				'installmentsMaxValue' => array(
					'params' => 8,
					'message' => 'Invalid installments for this payment_type.'

				)
			),
		);
	}// End Method 'rules'
}// End Class