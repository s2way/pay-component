<?php

require_once 'Component/HttpConnector.php';

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

	public function create() {
		$this->method = METHOD_POST;
		$this->URL = "{$this->baseURL}/orders";
		if (!$this->sendRequest($this->getPayment()->getCreationData())) {
			return false;
		}
		$this->getPayment()->setId(json_decode($this->response));
		return true;
	}

	public function process() {
		$this->method = METHOD_POST;
		$this->URL = "{$this->baseURL}/orders/{$this->getPayment()->getId()}";
		if (!$this->sendRequest($this->getPayment()->getProcessData())) {
			return false;
		}
		$response = json_decode($this->response);
		$this->getPayment()->setAuthenticationURL($response->authentication_url);
		$this->getPayment()->setToken($response->token);
		return true;
	}

	private function sendRequest($data) {

		$this->httpConnector->setMethod($this->method);
		$this->httpConnector->setUrl($this->URL);
		$this->httpConnector->setData($data);
		if (!$this->httpConnector->send()) {
			$this->error = $this->httpConnector->getError();
			return false;
		}

		$this->status = $this->httpConnector->getStatus();
		$this->response = $this->httpConnector->getResponse();
		return true;
	}


	public function setURL($url) {
		$this->baseURL = $url;
	}

	public function setPayment($payment) {
		$this->payment = $payment;
	}
	
	public function getPayment() {
		return $this->payment;
	}

	public function getAuthenticationURL() {
		return $this->authenticationURL();
	}

	public function getToken() {
		return $this->token();
	}

	public function getError() {
		return $this->error;
	}
}