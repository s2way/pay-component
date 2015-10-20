<?php

require_once 'Component/Validator.php';
require_once 'PaymentCard.php';
// require_once 'Requester.php';

class PayComponent {

    public function __construct($authToken, $paymentCard, $requester) {
        $this->payURL = 'http://192.168.100.119:1337'; // Adicionar arquivo de configuração
        $this->authToken = $authToken;

        $this->paymentCard = $paymentCard ? $paymentCard : new PaymentCard();
        $this->requester = $requester ? $requester : new PaymentCard();
    }

    public function purchaseByCard($data = null) {
        $this->paymentCard->setAuthToken($this->authToken);
        $this->paymentCard->setData($data);
        $this->paymentCard->validate();

        $this->payment = $this->paymentCard;
        $this->request();
    }

    private function request() {
        $this->requester->setURL($this->payURL);
        $this->requester->setPayment($this->payment);

        $this->requester->create();
            // $this->requester->getError();
            // return redirect $data->returnURL;
        

        $this->requester->process();
            // $this->requester->getError();
            // return redirect $data->returnURL;

        // return redirect $data->returnURL;
    }

    // preparePurchaseByToken

    // processPurchase

}