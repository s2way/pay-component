<?php

// require_once 'Component/HttpConnector.php';
require_once(APP . 'Vendor' . DS . 'PayComponent' . DS .  'src' . DS . 'Component' . DS .  'HttpConnector.php');

class Requester {

	private $error = null;
	private $status = null;
	private $response = null;
	private $baseURL = null;
	private $URL = null;
	private $authenticationURL = null;

	 public function __construct($httpConnector = null) {
        $this->httpConnector = $httpConnector ? $httpConnector : new HttpConnector();
    }

	/**
	 * Encapsula a lógica do POST de criação de pagamento do Pay
	 */
	public function create() {
		$this->method = METHOD_POST;
		$this->URL = "{$this->baseURL}/orders";
		if (!$this->sendRequest($this->getPayment()->getCreationData())) {
			return false;
		}
		$response = json_decode($this->httpConnector->getResponse());
		if ($this->httpConnector->requestSucceded()) {
			$this->getPayment()->setId($response);
		} else {
			$this->error = $response;
			return false;
		}
		return true;
	}

	/**
	 * Encapsula a lógica de processamento do pagamento do Pay
	 */
	public function process() {
		$this->method = METHOD_POST;
		$this->URL = "{$this->baseURL}/orders/{$this->getPayment()->getId()}";
		if (!$this->sendRequest($this->getPayment()->getProcessData())) {
			return false;
		}
		// Decodifica a resposta
		$response = json_decode($this->httpConnector->getResponse());
		// Se houve sucesso
		if ($this->httpConnector->requestSucceded()) {
			// Atualiza o objeto pagamento
			$this->updatePayment($response);
		} else {
			// Retorna o erro em formato de objeto
			$this->error = $response;
			return false;
		}
		return true;
	}

	private function updatePayment($response) {
		// Se for pagamento por cartão
		if ($this->getPayment() instanceof PaymentCard) {
			// Testa se a resposta contém uma url de autenticação; isto é importante porque, caso 
			// a forma de autorização pule a etapa de autenticação, somente a return_url é recebida
			if (property_exists($response, 'authentication_url')) {
				$this->getPayment()->setReturnURL($response->authentication_url);
			} else {
				$this->getPayment()->setReturnURL($response->return_url);
			}
			// Adiciona o token do cartão à resposta
			$this->getPayment()->setToken($response->token);
		} else { // Se for pagamento por token
			$this->getPayment()->setReturnURL($response->return_url);
		}
	}

	private function sendRequest($data) {
		$this->httpConnector->setMethod($this->method);
		$this->httpConnector->setUrl($this->URL);
		$this->httpConnector->setData($data);
		if (!$this->httpConnector->send()) {
			$this->error = $this->httpConnector->getError();
			return false;
		}

		return true;
	}


	public function setBaseURL($url) {
		$this->baseURL = $url;
	}

	public function setPayment($payment) {
		$this->payment = $payment;
	}

	public function getPayment() {
		return $this->payment;
	}

	public function getToken() {
		return $this->token();
	}

	public function getError() {
		return $this->error;
	}
}