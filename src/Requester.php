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

	public function getOrderByReference($reference, $authToken) {
		$this->method = METHOD_GET;
		$this->URL = "{$this->baseURL}/orders?reference={$reference}";

		if (!$this->sendGet($authToken)) {
			return false;
		}
		// Decodifica a resposta
		$response = json_decode($this->httpConnector->getResponse(), true);
		// Se houve sucesso
		if ($this->httpConnector->requestSucceded() && !empty($response))
			return $response;

		// Retorna o erro em formato de objeto
		$this->error = $response;
		return false;
	}

	public function cancel($reference, $authToken) {

		$order = $this->getOrderByReference($reference, $authToken);

		if (empty($order))
			return false;

		$this->method = METHOD_PUT;
		$this->URL = "{$this->baseURL}/orders/{$order['id']}";

		if (!$this->sendPut(null, $authToken)) {
			return false;
		}
		// Decodifica a resposta
		$response = json_decode($this->httpConnector->getResponse(), true);
		// Se houve sucesso
		if ($this->httpConnector->requestSucceded()) {
			return true;
		}
		// Retorna o erro em formato de objeto
		$this->error = $response;
		return false;
	}

	public function getStatus($reference, $authToken) {
		
		$order = $this->getOrderByReference($reference, $authToken);

		if (!empty($order)) {
			// Retorna o status do pagamento
			$paymentStatus = isset($order['status']) ? $order['status'] : null;
			if ($paymentStatus) {
				if ($paymentStatus == 'REJECTED') {
					return array(
						'status' => $paymentStatus,
						'reason' => isset($order['acquirer_message'])? $order['acquirer_message'] : null,
						'action' => isset($order['acquirer_action'])? $order['acquirer_action'] : null,
						'code' => isset($order['acquirer_code'])? $order['acquirer_code'] : null
					);
				} else {
					return array('status' => $paymentStatus);
				}
			} else {
				return false;
			}
		} else return false;

	}

	private function updatePayment($response) {
		if (!empty($response)) {
			if (array_key_exists('authentication_url', $response)) {
				$this->getPayment()->setReturnURL($response['authentication_url']);
			}
			// Adiciona o token do cartão à resposta
			if ($this->getPayment() instanceof PaymentCard && array_key_exists('token', $response)) {
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

	private function sendPut($data, $authToken) {
		$this->httpConnector->setMethod($this->method);
		$this->httpConnector->setUrl($this->URL);
		$this->httpConnector->setData($data);
		if (!$this->httpConnector->send($authToken)) {
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

	public function getStatusCode() {
		return $this->httpConnector->getStatus();
	}
}
