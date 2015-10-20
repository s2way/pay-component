<?php

class Requester {

	public function setURL($url) {
		$this->URL = $url;
	}

	public function setPayment($url) {
		$this->URL = $url;
	}

	public function create() {

	}

	public function process() {

		$method = 'POST';
		// $url = 'http://localhost:1337/orders/960c2b34129311fcbb2c';
		$url = 'http://192.168.100.109:1337/orders/960c2b34129311fcbb2c';
		$data = array(
			"issuer"=>"visa",
		    "card_number"=> "153241251234",
		    "due_date"=> "202012",
		    "sec_code_status"=> 1,
		    "security_code"=> 414,
		    "card_holder"=> "MAIQUE RIETH",
		    "payment_type" => "credito_a_vista",
		    "installments"=> 1,
		    "auth_token" => "token1"
		);
		$timeout = 5000;
		$charset = "";

		$this->curlConnection($method, $url, $data, $timeout, $charset);

	}

	private function curlConnection($method = 'GET', $url, Array $data = null, $timeout, $charset) {
		
		if (strtoupper($method) === 'POST') {
			// $postFields    = ($data ? http_build_query($data, '', '&') : "");
			$postFields    = json_encode($data);

			$contentLength = "Content-length: ".strlen($postFields);
			$methodOptions = Array(
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $postFields,
			);			
		} else {
			$contentLength = null;
			$methodOptions = Array(
				CURLOPT_HTTPGET => true
			);				
		}
		
		$options = Array(
			// CURLOPT_HTTPHEADER => Array(
			// 	"Content-Type: application/x-www-form-urlencoded; charset=".$charset,
			// 	$contentLength
			// ),	
			CURLOPT_HTTPHEADER => Array(
				"Content-Type: application/json",
				$contentLength
			),	
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_CONNECTTIMEOUT => $timeout,
			//CURLOPT_TIMEOUT => $timeout
		); 
		$options = ($options + $methodOptions);
		
		$curl = curl_init();
		curl_setopt_array($curl, $options);			
		$resp  = curl_exec($curl);
		$info  = curl_getinfo($curl);
		$error = curl_errno($curl);
		$errorMessage = curl_error($curl);
		curl_close($curl);

		die(var_dump($info['http_code'], $resp));
		$this->setStatus((int)$info['http_code']);
		$this->setResponse((String)$resp);
		if ($error) {
			throw new Exception(__("CURL can't connect: $errorMessage"));
			return false;
		} else {
			return true;
		}
	}


}