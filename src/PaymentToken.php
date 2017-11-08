<?php

namespace PayComponent;

use PayComponent\Payment;

class PaymentToken extends Payment {

    private $processFields = array('issuer','payment_type','installments','auth_token','token','return_url','no_authentication','security_code');

    public function getCreationData() {
        return array_intersect_key($this->data, array_flip($this->creationFields));
    }

    public function getProcessData() {
        return array_intersect_key($this->data, array_flip($this->processFields));
    }

}
