<?php

namespace PayComponent;
use PayComponent\Component\HttpConnector;

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
		if (!$this->sendPost($this->getPayment()->getCreationData())) {
			return false;
		}
		$response = json_decode($this->httpConnector->getResponse(), true);
		if ($this->httpConnector->requestSucceded()) {
			$this->getPayment()->setId($response['order_id']);
		}else if ($this->httpConnector->isPayValidationError()){
			// TODO: Verificar o que fazer
			$this->error = $response;
			return false;
		}else{
			// Retorna o erro em formato de objeto
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

		if (!$this->sendGet()) {
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
					return array('status' => $paymentStatus, 'reason' => $response['acquirer_message'], 'action' => $response['acquirer_action']);
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
		// Se for pagamento por cartão
		if ($this->getPayment() instanceof PaymentCard) {
			// Testa se a resposta contém uma url de autenticação; isto é importante porque, caso
			// a forma de autorização pule a etapa de autenticação, somente a return_url é recebida
			if (array_key_exists('authentication_url', $response)) {
				$this->getPayment()->setReturnURL($response['authentication_url']);
			} else {
				$this->getPayment()->setReturnURL($response['return_url']);
			}
			// Adiciona o token do cartão à resposta
			if (array_key_exists('token', $response)) {
				$this->getPayment()->setToken($response['token']);
			}
		} else { // Se for pagamento por token
			$this->getPayment()->setReturnURL($response['return_url']);
		}
	}

	private function sendPost($data) {
		$this->httpConnector->setMethod($this->method);
		$this->httpConnector->setUrl($this->URL);
		$this->httpConnector->setData($data);
		if (!$this->httpConnector->send()) {
			$this->error = $this->httpConnector->getError();
			return false;
		}
		return true;
	}

	private function sendGet() {
		$this->httpConnector->setMethod($this->method);
		$this->httpConnector->setUrl($this->URL);
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

	public function getError() {
		return $this->error;
	}
}
