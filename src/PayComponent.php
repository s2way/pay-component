<?php

require_once 'Component/Validator.php';
require_once 'PaymentCard.php';
require_once 'Requester.php';
require_once 'Constants.php';

class PayComponent {

    // Class properties
    private $payURL = null;
    private $authToken = null;
    private $error = null;
    // Injected properties
    private $paymentCard = null;
    private $requester = null;

    public function __construct($paymentCard = null, $requester = null) {
        $this->payURL = PAY_BASE_URL;
        $this->paymentCard = $paymentCard ? $paymentCard : new PaymentCard();
        $this->requester = $requester ? $requester : new Requester();
    }

    public function purchaseByCard($data = null) {
        $this->paymentCard->setData($data);
        $this->paymentCard->setAuthToken($this->authToken);
        $this->paymentCard->validate();

        $this->payment = $this->paymentCard;
        return $this->request();
    }

    public function setAuthToken($authToken) {
        $this->authToken = $authToken;
    }

    public function getError() {
        return $this->error;
    }

    public function getAuthenticationURL() {
        return $this->requester->getPayment()->getAuthenticationURL();
    }

    public function getToken() {
        return $this->requester->getPayment()->getToken();
    }

    private function request() {
        $this->requester->setURL($this->payURL);
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

    // preparePurchaseByToken

}