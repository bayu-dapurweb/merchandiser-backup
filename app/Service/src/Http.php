<?php

class Http{
	
	private $headKey 	= "faspay-key";
	private $headTime 	= "faspay-timestamp";
	private $headSign 	= "faspay-signature";
	private $headAuth 	= "faspay-authorization";
	
	protected $host = [
		"development" => "https://sendme-sandbox.faspay.co.id",
		"production" => "https://sendme.faspay.co.id"
	];
	
	protected $path = [
		"get_token" 		=> "/account/api/tokens",
		"register" 			=> "/account/api/register",
		"register_confirm" 	=> "/account/api/register/confirm",
		"transfer" 			=> "/account/api/transfer",
		"balance_inquiry" 	=> "/account/api/balance_inquiry",
		"inquiry_name" 		=> "/account/api/inquiry_name",
		"mutasi" 			=> "/account/api/mutasi",
		"inquiry_status" 	=> "/account/api/inquiry_status"
	];
	
	protected $method = [
		"get_token" 		=> "GET",
		"register" 			=> "POST",
		"register_confirm" 	=> "POST",
		"transfer" 			=> "POST",
		"balance_inquiry" 	=> "GET",
		"inquiry_name" 		=> "GET",
		"mutasi" 			=> "GET",
		"inquiry_status" 	=> "GET"
	];
	
	private $env 			= "development";
	private $section		= "get_token";
	protected $url 			= NULL;
	private $current_method	= NULL;
	private $current_path	= NULL;
	private $content_type 	= "application/json";
	private $headers 		= array();
	
	private $reqd			= array();
	private $reqx			= NULL;
	private $rspd			= array();
	private $rspx			= NULL;
	private $virtual_account;
	
	protected $info			= array();	
	
	protected function setEnvironment($env){
		$this->env = $this->info["environment"] = $env;
	}
	
	protected function getEnvironment(){
		return $this->env;
	}
	
	protected function setSection($section){
		$this->section = $this->info["section"] = $section;
	}
	
	protected function getSection(){
		return $this->section;
	}
	
	protected function setRequestParam($data=array()){
		$config 			= require(__DIR__ . '/../config.php');
		
		if(!isset($data["virtual_account"])){
			$this->virtual_account = $config[$this->getEnvironment()]["virtual_account"];
			$data = array_merge(array("virtual_account" => $this->virtual_account), $data);
		}
		
		if(isset($config[$this->getEnvironment()]["host"]) && $config[$this->getEnvironment()]["host"]){
			$this->host[$this->getEnvironment()] = $config[$this->getEnvironment()]["host"];
		}
		
		$this->reqd = $this->info["request"]["array"] = $data;
		$this->array2json();
	}
	
	protected function getResponseParam(){
		$this->json2array();
		return $this->rspd;
	}
	
	protected function setHeaders($valueKey, $valueTime, $valueSign, $valueAuth){
		$this->headers = $this->info["headers"] = array(
			$this->headKey.":".$valueKey,
			$this->headTime.":".$valueTime,
			$this->headSign.":".$valueSign,
			$this->headAuth.":".$valueAuth
		);
	}
	
	protected function getMethod(){
		$this->current_method = $this->method[$this->getSection()];
		return $this->current_method;
	}
	
	protected function getPath(){
		$this->current_path = $this->path[$this->getSection()];
		return $this->current_path;
	}
	
	protected function generateUrl(){
		if(!$this->env || !$this->section){
			return false;
		}
		
		$host 					= $this->info["host"] 		= $this->host[$this->env];
		$path 					= $this->info["path"] 		= $this->path[$this->section];
		$this->url 				= $this->info["url"] 		= $host.$path;
		$this->current_method 	= $this->info["method"] 	= $this->method[$this->section];
		
		if($this->current_method == "GET" && $this->reqd){
			$queryString = "/".implode("/", array_values($this->reqd));
			$this->url = $this->info["url"] = $this->url.$queryString;
		}
	}
	
	private function array2json($param = NULL){
		$ack 	= false;
		if(!$param) $param = $this->reqd;

		if(is_array($param)){
			$this->reqx = json_encode($param);
		}
		
		$this->info["request"]["json"] 	= $this->reqx;
	}
	
	private function json2array($param = NULL){
		$ack = false;
		if(!$param) $param = $this->rspx;
		
		if(is_string($param)){			
			$this->rspd = json_decode($param, true);
		}
		
		$this->info["response"]["array"] 	= $this->rspd;
		$this->info["response"]["json"] 	= $this->rspx;
	}
	
	protected function curl(){		
		$header[] = "Accept: ".$this->content_type;
		$header[] = "Content-Type: ".$this->content_type;
		
		if($this->headers){
			$header = array_merge($header, $this->headers);
		}
		
		$this->info["headers"] = $header;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, (isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : ""));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if ($this->current_method == "POST"){	
			curl_setopt($ch, CURLOPT_POST, true);
		}else{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->current_method);
		}
		
		if($this->reqx){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->reqx);
		}		
		
		$rs = curl_exec($ch);
		
		if(empty($rs)){
			$error = curl_error($ch);
			$rs = json_encode(array("error"=>true, "message"=>$error));
		}
		
		if(strpos($rs, "Internal Server Error")){
			$rs = json_encode(array("error"=>true, "message"=>$rs));
		}
		
		if(strpos($rs, "couldn't connect to host")){
			$rs = json_encode(array("error"=>true, "message"=>$rs));
		}
		
		curl_close($ch);
		
		$this->rspx = $rs;
	}
	
}