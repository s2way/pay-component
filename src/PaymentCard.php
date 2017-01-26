<?php

namespace PayComponent;

use PayComponent\Payment;

class PaymentCard extends Payment {

	private $URL = null;
	private $token = null;
	private $processFields = array('issuer','card_number','due_date','sec_code_status','security_code','card_holder','payment_type','installments','auth_token', 'no_authentication', 'save_card', 'return_url');

	public function getCreationData() {
		return array_intersect_key($this->data, array_flip($this->creationFields));
	}

	public function getProcessData() {
		return array_intersect_key($this->data, array_flip($this->processFields));
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
					'params' => 15,
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
					'params' => 1024,
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
					'params' => array('visa','master','diners','discover','elo','amex','jcb','aura'),
					'message' => 'Unknown issuer.'
				)
			),
			'card_number' => array(
				'notEmpty' => array(
					'message' => 'Invalid card_number.'
				),
				'maxLength' => array(
					'params' => 16,
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
			),
			'security_code' => array(
				'betweenLength' => array(
					'params' => array('min' => 3, 'max' => 4),
					'message' => 'Invalid security_code length.'
				),
				'numeric' => array(
					'message' => 'security_code must be numeric.'
				)
			),
			'payment_type' => array(
				'notEmpty' => array(
					'message' => 'Invalid payment_type.'
				),
				'inList' => array(
					'params' => array('credit', 'debit'),
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
					'params' => array('debit'),
					'message' => 'The payment type allows only 1 installment.'
				),
				'installmentsMaxValue' => array(
					'params' => 12,
					'message' => 'Invalid installments for this payment_type.'

				)
			),
		);
	}// End Method 'rules'
}// End Class
