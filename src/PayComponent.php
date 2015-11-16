<?php

namespace PayComponent;
use PayComponent\Component\Validator;

require_once ('Constants.php');

class PayComponent {

    // Class properties
    private $payURL = null;
    private $authToken = null;
    private $error = null;
    // Injected properties
    private $paymentCard = null;
    private $requester = null;

    public function __construct($payment = null, $requester = null) {
        $this->payURL = PAY_BASE_URL;
        $this->paymentCard = $payment ? $payment : new PaymentCard();
        $this->paymentToken = $payment ? $payment : new PaymentToken();
        $this->requester = $requester ? $requester : new Requester();
    }

    public function purchaseByCard($data) {

        $this->paymentCard->setData($data);
        $this->paymentCard->setAuthToken($this->authToken);

        if (!$this->paymentCard->validate()){
            $this->error = $this->paymentCard->getErrors();
            return false;
        }
        $this->payment = $this->paymentCard;

        return $this->request();
    }

    public function purchaseByToken($data = null) {
        $this->paymentToken->setData($data);
        $this->paymentToken->setAuthToken($this->authToken);
        if (!$this->paymentCard->validate()){
            $this->error = $this->paymentCard->getErrors();
            return false;
        }

        $this->payment = $this->paymentToken;
        return $this->request();
    }

    private function request() {

        $this->requester->setBaseURL($this->payURL);
        $this->requester->setPayment($this->payment);

        if (!$this->requester->create()) {
            $this->error = $this->requester->getError();
            return false;
        }

        if (!$this->requester->process()) {
            $this->error = $this->requester->getError();
            return false;
        }

        return true;
    }

    public function setAuthToken($authToken) {
        $this->authToken = $authToken;
    }

    public function setPayURL($url) {
        $this->payURL = $url;
    }

    public function getError() {
        return $this->error;
    }

    public function getToken() {
        return $this->requester->getPayment()->getAuthToken();
    }

    public function getRedirectURL() {
        return $this->requester->getPayment()->getReturnURL();
    }

    public function getPayURL() {
        return $this->payURL;
    }

}
