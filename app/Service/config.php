<?php
/*
* Get credential in your inbox mail from email by faspay
* Version 1.0
*/


$param["development"]["host"] 				= "https://sendme-sandbox.faspay.co.id";
$param["development"]["virtual_account"] 	= "";
$param["development"]["faspay_key"] 		= "";
$param["development"]["faspay_secret"] 		= "";
$param["development"]["app_key"]			= "";
$param["development"]["app_secret"] 		= "";
$param["development"]["client_key"] 		= "";
$param["development"]["client_secret"] 		= "";
$param["development"]["iv"]					= "";

$param["production"]["host"] 				= "https://sendme.faspay.co.id";
$param["production"]["virtual_account"] 	= "";
$param["production"]["faspay_key"]			= "";
$param["production"]["faspay_secret"] 		= "";
$param["production"]["app_key"]				= "";
$param["production"]["app_secret"]			= "";
$param["production"]["client_key"]			= "";
$param["production"]["client_secret"] 		= "";
$param["production"]["iv"]					= "";

return $param; 