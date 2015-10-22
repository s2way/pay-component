<?php

require_once('Component/Validator.php');

abstract class Payment {

    private $authToken = null;
    private $id = null;
    protected $data = null;

    /**
     * Dependency injection is ON!
     */
    function __construct($validator = null) {
        $this->validator = $validator ? $validator : new Validator();
    }

    protected abstract function rules();
    protected abstract function getCreationData();
    protected abstract function getProcessData();

    public function setData($data) {
        $this->data = $data;
    }

    public function setAuthToken($token) {
        $this->authToken = $token;
    }

    public function getAuthToken() {
        return $this->authToken;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function validate() {
        $this->data['auth_token'] = $this->authToken;

        if (!$this->validator->validate($this->rules(), $this->data)) {
            throw new InvalidArgumentException($this->validator->getError());
        }
    }
}