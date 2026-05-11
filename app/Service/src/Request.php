<?php
include __DIR__."/Http.php";

class Request extends Http{	
	private $config;	
	private $faspayKey;
	private $timestamp;
	private $signature;
	private $authorization;
	private $token;
	private $accessToken;
	private $errors;
	
	public function __construct(){
		
	}
	
	protected function generateHeadersRequest(){		
		$this->config 			= require(__DIR__ . '/../config.php');
		$this->timestamp 		= date("Y-m-d H:i:s");
		$this->faspayKey 		= $this->config[$this->getEnvironment()]["faspay_key"];
		$this->authorization 	= $this->generateAuthorization();
		
		$this->accessToken = $this->getToken();
		$this->generateToken();
		$this->signature 	= $this->generateSignature();		
		$this->setHeaders($this->faspayKey, $this->timestamp, $this->signature, $this->authorization);
	}
	
	private function getToken(){
		$section = "get_token";
		$this->generateToken($section);
		$this->signature 	= $this->generateSignature($this->method[$section]);
		$this->setHeaders($this->faspayKey, $this->timestamp, $this->signature, $this->authorization);
		$this->url = $this->host[$this->getEnvironment()].$this->path[$section];
		$this->curl();
		$rspd = $this->getResponseParam();
		if($rspd){
			if(isset($rspd["status"])){
				if($rspd["status"] == 2){
					return $rspd["access_token"];
				}
			}
		}
		
		return false;
	}
	
	private function generateAuthorization(){
		$faspaySecret 	= $this->config[$this->getEnvironment()]["faspay_secret"];
		$appKey 		= $this->config[$this->getEnvironment()]["app_key"];
		$appSecret 		= $this->config[$this->getEnvironment()]["app_secret"];
		
		$string			= $appKey.":".$appSecret;
		return $this->encryptAES256($string, $faspaySecret);
	}
	
	private function generateToken($section=null){
		switch($section){
			case 'get_token':		
				$this->token	= base64_encode($this->config[$this->getEnvironment()]["client_key"].":".$this->config[$this->getEnvironment()]["client_secret"]);
			break;
			default :	
				$this->token	= base64_encode($this->config[$this->getEnvironment()]["client_key"].":".$this->config[$this->getEnvironment()]["client_secret"].":".$this->accessToken);
			break;
		}
	}
	
	private function generateSignature($method=null, $path=null){
		$requestBody			= "";	
		
		if(!$method){
			$method				= $this->getMethod();
		}
		
		if(!$path){
			$path				= $this->getPath();
		}
		
		$stringToSign 			= $method.":".$path.":".$this->timestamp.":".$this->token.":".$requestBody;			
		return $this->encryptAES256($stringToSign, $this->config[$this->getEnvironment()]["faspay_secret"]);
	}
	
	private function encryptAES256($string, $key){
		$plaintext 	= $string;
		$method 	= "aes-256-cbc";
		$password 	= substr(hash('sha256', $key, true), 0, 32);            
		$iv 		= substr(md5($key.$this->config[$this->getEnvironment()]["iv"]), -16);
		
		return base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
	}
	
	private function setErrors($code, $message){
		$this->errors = array(
			"error" => true,
			"code" => $code,
			"message" => $message,
		);
	}
}
