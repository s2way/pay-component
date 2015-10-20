<?php

require_once('Component/Validator.php');

abstract class Payment {

	private $authToken = null;
	private $data = null;

	/**
	 * Dependency injection is ON!
	 */
	function __construct($validator = null) {
		$this->validator = $validator ? $validator : new Validator();
	}

	protected abstract function rules();

	public function setData($data) {
		$this->data = $data;
	}

	public function setAuthToken($token) {
		$this->authToken = $token;
	}

	public function getData() {
		return $this->data;
	}

	public function getAuthToken() {
		return $this->authToken;
	}

	public function validate() {
		$this->data['auth_token'] = $this->authToken;

		if (!$this->validator->validate($this->rules(), $this->data)) {
			throw new InvalidArgumentException($this->validator->getError());
		}
	}


}