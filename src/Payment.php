<?php

namespace PayComponent;

abstract class Payment {

    private $id = null;
    protected $data = null;
    private $errors = null;
    private $authToken = null;
    private $returnURL = null;
    protected $creationFields = array('id', 'auth_token', 'description', 'amount', 'client_app');

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

    public function getNoAutentication(){
        return $this->data['no_authentication'];
    }

    public function addAuthenticationMethod($authenticationMethod) {
        $this->data['no_authentication'] = $authenticationMethod;
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
}
