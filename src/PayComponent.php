<?php

namespace PayComponent;

require_once ('Constants.php');

class PayComponent {

    // Class properties
    private $payURL = null;
    private $authToken = null;
    private $error = null;
    // Injected properties
    private $paymentCard = null;
    private $paymentToken = null;
    private $requester = null;
    private $authenticationMethod = null;

    public function __construct($payment = null, $requester = null) {
        $this->payURL = PAY_BASE_URL;
        $this->paymentCard = $payment ? $payment : new PaymentCard();
        $this->paymentToken = $payment ? $payment : new PaymentToken();
        $this->requester = $requester ? $requester : new Requester();
    }

    public function purchaseByCard($data) {
        $this->paymentCard->setAuthToken($this->authToken);
        $this->paymentCard->setData($data);
        $this->paymentCard->addAuthenticationMethod($this->authenticationMethod);
        $this->payment = $this->paymentCard;

        return $this->request();
    }

    public function purchaseByToken($data = null) {
        $this->paymentToken->setAuthToken($this->authToken);
        $this->paymentToken->setData($data);
        $this->paymentToken->addAuthenticationMethod($this->authenticationMethod);
        $this->payment = $this->paymentToken;

        return $this->request();
    }

    public function cancel($id) {
        $this->requester->setBaseURL($this->payURL);
        return $this->requester->cancel($id, $this->authToken);
    }

    public function status($id) {
        $this->requester->setBaseURL($this->payURL);
        return $this->requester->getStatus($id, $this->authToken);
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

    public function setRetries($retries) {
        $this->requester->setRetries($retries);
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

    public function getStatusCode() {
        return $this->requester->getStatusCode();
    }

    public function getStatus() {
        return $this->requester->getPayment()->getStatus();
    }

    public function getToken() {
        return $this->requester->getPayment()->getToken();
    }

    public function getRedirectURL() {
        return $this->requester->getPayment()->getReturnURL();
    }

    public function getPayURL() {
        return $this->payURL;
    }

    public function setNoAuthentication($value) {
        $this->authenticationMethod = $value;
    }

}
