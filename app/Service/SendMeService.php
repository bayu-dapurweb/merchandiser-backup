<?php

include __DIR__."/config.php";
include __DIR__."/src/Request.php";

class SendMe extends Request{	
	private $environment = "development";
	private $request;
	
	public function enableProd(){
		$this->environment = "production";
	}
	
	public function register($data=array()){
		$this->setEnvironment($this->environment);
		$this->setSection("register");
		$this->setRequestParam($data);
		$this->generateHeadersRequest();
		$this->generateUrl();
		$this->curl();
		return $this->getResponseParam();
	}
	
	public function confirm($data=array()){
		$this->setEnvironment($this->environment);
		$this->setSection("register_confirm");
		$this->setRequestParam($data);
		$this->generateHeadersRequest();
		$this->generateUrl();
		$this->curl();
		return $this->getResponseParam();
	}
	
	public function transfer($data=array()){
		$this->setEnvironment($this->environment);
		$this->setSection("transfer");
		$this->setRequestParam($data);
		$this->generateHeadersRequest();
		$this->generateUrl();
		$this->curl();
		return $this->getResponseParam();
	}
	
	public function balance_inquiry(){
		$this->setEnvironment($this->environment);
		$this->setSection("balance_inquiry");
		$this->setRequestParam();
		$this->generateHeadersRequest();
		$this->generateUrl();
		$this->curl();
		return $this->getResponseParam();
	}
	
	public function inquiry_name($data=array()){
		$this->setEnvironment($this->environment);
		$this->setSection("inquiry_name");
		$this->setRequestParam($data);
		$this->generateHeadersRequest();
		$this->generateUrl();
		$this->curl();
		return $this->getResponseParam();
	}
	
	public function mutasi($data=array()){
		$this->setEnvironment($this->environment);
		$this->setSection("mutasi");
		$this->setRequestParam($data);
		$this->generateHeadersRequest();
		$this->generateUrl();
		$this->curl();
		return $this->getResponseParam();
	}
	
	public function inquiry_status($data=array()){
		$this->setEnvironment($this->environment);
		$this->setSection("inquiry_status");
		$this->setRequestParam($data);
		$this->generateHeadersRequest();
		$this->generateUrl();
		$this->curl();		
		return $this->getResponseParam();
	}
}