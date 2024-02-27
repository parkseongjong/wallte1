<?php
// 테스트용. 잔액조회
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';
// https://cybertronchain.com/wallet/includes/get_balances.php

/*

$contractAddressArr['eth']['decimal'];
$contractAddressArr['ctc']['decimal'];
$contractAddressArr['tp3']['decimal'];
$contractAddressArr['usdt']['decimal'];
$contractAddressArr['mc']['decimal'];
$contractAddressArr['krw']['decimal'];

$contractAddressArr['ctc']['contractAddress']; // $contractAddress
$contractAddressArr['tp3']['contractAddress']; // $tokenPayContractAddress
$contractAddressArr['usdt']['contractAddress']; // $usdtContractAddress
$contractAddressArr['mc']['contractAddress']; // $marketCoinContractAddress
$contractAddressArr['krw']['contractAddress']; // $koreanWonContractAddress
*/

$walletAddress = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';



$balances = array('eth'=>0, 'ctc'=>0, 'tp3'=>	0, 'usdt'=>0, 'mc'=>0, 'krw'=>0);

$getBalance = 0;
$balance_eth = 0; // ETH
$balance_ctc = 0; // CTC
$balance_tp3 = 0; // TP3
$balance_usdt = 0; // USDT
$balance_mc = 0; // MC
$balance_krw = 0; // KRW



require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$functionName = "balanceOf";

$web3 = new Web3('http://'.$connect_ip.':'.$connect_port.'/');
$eth = $web3->eth;



try {
	// ETH
	$eth->getBalance($walletAddress, function ($err, $balance) use (&$balance_eth) {
		if ( !empty( $result )) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		$balance_eth = $balance->toString();
		$balance_eth = $balance_eth/$contractAddressArr['eth']['decimal']; // 1000000000000000000
	});

	// CTC
	$tk = 'ctc';
	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_ctc){
		if ( !empty( $result )) {
			$balance_ctc = reset($result)->toString();
			$balance_ctc = $balance_ctc/$contractAddressArr[$tk]['decimal']; // 1000000000000000000
		}
	});

	// TP3
	$tk = 'tp3';
	$contract = new Contract($web3->provider, $tokenPayAbi);
	$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_tp3){
		if ( !empty( $result )) {
			$balance_tp3 = reset($result)->toString();
			$balance_tp3 = $balance_tp3/$contractAddressArr[$tk]['decimal']; // 1000000000000000000
		}
	});

	// USDT
	$tk = 'usdt';
	$contract = new Contract($web3->provider, $tokenPayAbi);
	$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_usdt){
		if ( !empty( $result )) {
			$balance_usdt = reset($result)->toString();
			$balance_usdt = $balance_usdt/$contractAddressArr[$tk]['decimal']; // 1000000
		}
	});

	// MC
	$tk = 'mc';
	$contract = new Contract($web3->provider, $tokenPayAbi);
	$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_mc){
		if ( !empty( $result )) {
			$balance_mc = reset($result)->toString();
			$balance_mc = $balance_mc/$contractAddressArr[$tk]['decimal']; // 1000000
		}
	});

	// KRW
	$tk = 'krw';
	$contract = new Contract($web3->provider, $tokenPayAbi);
	$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_krw){
		if ( !empty( $result )) {
			$balance_krw = reset($result)->toString();
			$balance_krw = $balance_krw/$contractAddressArr[$tk]['decimal']; // 1000000
		}
	});

	$balances = array(
		'eth'	=>	$balance_eth,
		'ctc'		=>	$balance_ctc,
		'tp3'		=>	$balance_tp3,
		'usdt'	=>	$balance_usdt,
		'mc'		=>	$balance_mc,
		'krw'		=>	$balance_krw
	);

} catch(Exception $e) {
	//fn_logSave( 'Error: ' . $e->getMessage() );
	error_reporting(0);
	//fn_logSave( 'Exception Error (Check balance): ' . $e->getMessage() . ', File : ' . $e->getFile().' on line ' . $e->getLine());
	//exit();

}




// ----------------------------------------------------------------------------------
/*
// index


require('vendor/autoload.php');
use EthereumRPC\EthereumRPC;
use ERC20\ERC20;

$geth = new EthereumRPC($connect_ip, $connect_port);
$erc20 = new ERC20($geth);

try {
	$getBalance = $geth->eth()->getBalance($walletAddress);
	$balance_eth = $getBalance;
	//$balance_eth = $getBalance/1000000000000000000;

	// CTC
	$tk = 'ctc';
	$ethObj = $erc20->token($contractAddressArr[$tk]['contractAddress']);
	$balance_ctc = $ethObj->balanceOf($walletAddress,false);
	$scale = 18;
	$balance_ctc = bcdiv($balance_ctc, bcpow("10", strval($scale), 0), $scale);
	
		
	// TP3
	$tk = 'tp3';
	$tokenPay = $erc20->token($contractAddressArr[$tk]['contractAddress']);
	$balance_tp3 = $tokenPay->balanceOf($walletAddress,false);
	$scale = 18;
	$balance_tp3 = bcdiv($balance_tp3, bcpow("10", strval($scale), 0), $scale);
	
	// USDT
	$tk = 'usdt';
	$usdtObj = $erc20->token($contractAddressArr[$tk]['contractAddress']);
	$balance_usdt = $usdtObj->balanceOf($walletAddress,false);
	$scale = 6;
	$balance_usdt = bcdiv($balance_usdt, bcpow("10", strval($scale), 0), $scale);

	// MC
	$tk = 'mc';
	$mcObj = $erc20->token($contractAddressArr[$tk]['contractAddress']);
	$balance_mc = $mcObj->balanceOf($walletAddress,false);
	$scale = 6;
	$balance_mc = bcdiv($balance_mc, bcpow("10", strval($scale), 0), $scale);

	// KRW
	$tk = 'krw';
	$krwObj = $erc20->token($contractAddressArr[$tk]['contractAddress']);
	$balance_krw = $krwObj->balanceOf($walletAddress,false);
	$scale = 6;
	$balance_krw = bcdiv($balance_krw, bcpow("10", strval($scale), 0), $scale);

	$balances = array(
		'eth'	=>	$balance_eth,
		'ctc'		=>	$balance_ctc,
		'tp3'		=>	$balance_tp3,
		'usdt'	=>	$balance_usdt,
		'mc'		=>	$balance_mc,
		'krw'		=>	$balance_krw
	);

} catch(Exception $e) {
	//fn_logSave( 'Error: ' . $e->getMessage() );
	error_reporting(0);
	//fn_logSave( 'Exception Error (Check balance): ' . $e->getMessage() . ', File : ' . $e->getFile().' on line ' . $e->getLine());
	//exit();

}
*/


?>	
