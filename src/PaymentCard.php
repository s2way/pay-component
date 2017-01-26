<?php

namespace PayComponent;

use PayComponent\Payment;

class PaymentCard extends Payment {

	private $URL = null;
	private $token = null;
	private $processFields = array('issuer','card_number','due_date','sec_code_status','security_code','card_holder','payment_type','installments','auth_token', 'no_authentication', 'save_card');

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

}// End Class
