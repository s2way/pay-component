<?php

namespace PayComponent;
use PayComponent\Component\HttpConnector;

class Requester {

	private $error = null;
	private $status = null;
	private $response = null;
	private $baseURL = null;
	private $URL = null;
	private $authToken = null;
	private $payment = null;

	 public function __construct($httpConnector = null) {
        $this->httpConnector = $httpConnector ? $httpConnector : new HttpConnector();
    }

	/**
	 * Encapsula a lógica do POST de criação de pagamento do Pay
	 */
	public function create() {
		$this->method = METHOD_POST;
		$this->URL = "{$this->baseURL}/orders";
		if (!$this->sendPost($this->getPayment()->getCreationData())) {
			return false;
		}
		$response = json_decode($this->httpConnector->getResponse(), true);

		if ($this->httpConnector->requestSucceded()) {
			$this->getPayment()->setId($response['order_id']);
			return true;
		}

		$this->error = $response;
		return false;
	}

	/**
	 * Encapsula a lógica de processamento do pagamento do Pay
	 */
	public function process() {
		$this->method = METHOD_POST;
		$this->URL = "{$this->baseURL}/orders/{$this->getPayment()->getId()}";

		if (!$this->sendPost($this->getPayment()->getProcessData())) {
			return false;
		}
		// Decodifica a resposta
		$response = json_decode($this->httpConnector->getResponse(), true);
		// Se houve sucesso
		if ($this->httpConnector->requestSucceded()) {
			// Atualiza o objeto pagamento
			$this->updatePayment($response);
			return true;
		}
		$this->error = $response;
		return false;
	}

	public function getStatus($id, $authToken) {
		$this->method = METHOD_GET;
		$this->URL = "{$this->baseURL}/orders?reference={$id}";

		if (!$this->sendGet($authToken)) {
			return false;
		}
		// Decodifica a resposta
		$response = json_decode($this->httpConnector->getResponse(), true);
		// Se houve sucesso
		if ($this->httpConnector->requestSucceded()) {
			// Retorna o status do pagamento
			$paymentStatus = isset($response['status']) ? $response['status'] : null;
			if ($paymentStatus) {
				if ($paymentStatus == 'REJECTED') {
					return array(
						'status' => $paymentStatus,
						'reason' => $response['acquirer_message'],
						'action' => $response['acquirer_action']
					);
				} else {
					return array('status' => $paymentStatus);
				}
			} else {
				return false;
			}
		}
		// Retorna o erro em formato de objeto
		$this->error = $response;
		return false;
	}

	private function updatePayment($response) {
		if (!empty($response)) {
			if (array_key_exists('authentication_url', $response)) {
				$this->getPayment()->setReturnURL($response['authentication_url']);
			}
			// Adiciona o token do cartão à resposta
			if (array_key_exists('token', $response)) {
				$this->getPayment()->setToken($response['token']);
			}
		}
	}

	private function sendPost($data) {
		$this->httpConnector->setMethod($this->method);
		$this->httpConnector->setUrl($this->URL);
		$this->httpConnector->setData($data);
		if (!$this->httpConnector->send($this->getPayment()->getAuthToken())) {
			$this->error = $this->httpConnector->getError();
			return false;
		}
		return true;
	}

	private function sendGet($authToken) {
		$this->httpConnector->setMethod($this->method);
		$this->httpConnector->setUrl($this->URL);
		if (!$this->httpConnector->send($authToken)) {
			$this->error = $this->httpConnector->getError();
			return false;
		}
		return true;
	}

	public function setRetries($retries) {
        $this->httpConnector->setRetries = $retries;
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

	public function getError() {
		return $this->error;
	}

	public function setAuthToken($authToken) {
		$this->authToken = $authToken;
	}
}
