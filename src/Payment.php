<?php

namespace PayComponent;

use PayComponent\Component\Validator;

abstract class Payment {

    private $authToken = null;
    private $noAutentication = null;
    private $id = null;
    protected $data = null;
    private $errors = null;
    protected $creationFields = array('id', 'auth_token', 'description', 'amount', 'return_url');

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

    public function setNoAutentication($noAutentication){
        $this->noAutentication = $noAutentication;
    }

    public function getNoAutentication(){
        return $this->noAutentication;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setReturnURL($url) {
        $this->returnURL = $url;
    }

    public function getReturnURL() {
        return $this->returnURL;
    }

    public function setErrors($errors){
        $this->errors = $errors;
    }

    public function getErrors(){
        return $this->errors;
    }

    public function validate() {
        $this->data['auth_token'] = $this->authToken;
        $this->data['no_autentication'] = $this->noAutentication;

        if (!$this->validator->validate($this->rules(), $this->data)) {
            $this->setErrors($this->validator->getValidationErrors());
            return false;
        }
        return true;
    }
}