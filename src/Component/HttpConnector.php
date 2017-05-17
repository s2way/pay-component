<?php

namespace PayComponent\Component;

class HttpConnector {

    private $status = null;
    private $response = null;
    private $error = null;
    private $method = null;
    private $data = null;
    private $URL = null;
    private $retries = 0;

    public function send($authToken = null) {

        if ($this->method === METHOD_POST) {
            $postFields = json_encode($this->data);

            $contentLength = "Content-length: ".strlen($postFields);
            $methodOptions = Array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields
            );
        } else if ($this->method === METHOD_PUT) {

            $postFields = json_encode($this->data);

            $contentLength = "Content-length: ".strlen($postFields);

            $methodOptions = Array(
                CURLOPT_CUSTOMREQUEST => METHOD_PUT,
                CURLOPT_POSTFIELDS => $postFields
            );
        } else {
            $contentLength = null;
            $methodOptions = Array(
                CURLOPT_HTTPGET => true
            );
        }

        $options = Array(
            CURLOPT_HTTPHEADER => Array(
                "Authorization: Bearer $authToken",
                "Content-Type: application/json",
                $contentLength
            ),
            CURLOPT_URL => $this->URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => TIMEOUT, // Em segundos
        );

        $options = ($options + $methodOptions);

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $success = $this->request($curl, $this->retries);
        return $success;
    }

    public function request($curl, $retries) {
        $resp = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_errno($curl);
        $errorMessage = curl_error($curl);

        if ($error) {
            $this->error = array('code' => $error, 'message' => $errorMessage);
            return false;
        }

        $this->setStatus($info['http_code']);
        $this->setResponse($resp);

        if ($this->status == 504 && $retries > 0) {
            $this->request($curl, --$retries);
        } else {
            curl_close($curl);
            return true;
        }

    }

    public function requestSucceded() {
        return in_array($this->status, array(200, 201, 202));
    }

    public function isPayValidationError(){
        // STATUS_CODE_PAY_VALIDATION_ERROR = 422
        return $this->status == 422;
    }

    public function setMethod($method){
        $this->method = $method;
    }
    public function setURL($url){
        $this->URL = $url;
    }
    public function setData($data){
        $this->data = $data;
    }

    public function setStatus($status){
        $this->status = $status;
    }

    public function setRetries($retries = 0){
        $this->retries = $retries;
    }

    public function getStatus(){
        return $this->status;
    }

    public function setResponse($response) {
        $this->response = $response;
    }

    public function getResponse(){
        return $this->response;
    }

    public function getError() {
        return $this->error;
    }
}
