<?php
session_start();
ini_set('max_execution_time', 0); 
require_once './config/config.php';
require_once 'includes/auth_validate.php';

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}
$filename = time().'export.csv';
 header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$filename.'";');

$db = getDbInstance();
$db->where('email', 'ajay@mailinator.com', '!=');
//$db->where('admin_type', 'admin');
$db->orderBy('id', 'DESC');
$result = $db->get('admin_accounts'); 
 

$file = fopen('php://output', 'w');

$s1 = mb_convert_encoding( '유형', "EUC-KR", "UTF-8" );
$s2 = mb_convert_encoding( '로그인허용여부', "EUC-KR", "UTF-8" );
//$s3 = mb_convert_encoding( '암호화', "EUC-KR", "UTF-8" );
//$s4 = mb_convert_encoding( '초기', "EUC-KR", "UTF-8" );
$s5 = mb_convert_encoding( '성별', "EUC-KR", "UTF-8" );
$s6 = mb_convert_encoding( '생년월일', "EUC-KR", "UTF-8" );
$s7 = mb_convert_encoding( '지역', "EUC-KR", "UTF-8" );
$s8 = mb_convert_encoding( '본인인증여부', "EUC-KR", "UTF-8" );
$s9 = mb_convert_encoding( '본인인증일시', "EUC-KR", "UTF-8" );
$s10 = mb_convert_encoding( '본인인증 휴대폰번호', "EUC-KR", "UTF-8" );
$s11 = mb_convert_encoding( '본인인증이름', "EUC-KR", "UTF-8" );
$s12 = mb_convert_encoding( '본인인증성별', "EUC-KR", "UTF-8" );
$s13 = mb_convert_encoding( '본인인증생년월일', "EUC-KR", "UTF-8" );
$s14 = mb_convert_encoding( '본인인증 내(Kor)/외국인(For)', "EUC-KR", "UTF-8" );

$headers = array('#','Register with','Lname','Name','Email','Admin type('.$s1.')','Email_verify('.$s2.')','Wallet Address','PVT Key','CTC Balance','TP3 Balance','USDT Balance','MC Balance','KRW Balance','ETH Balance','Phone','Date','PanNo','AccountNo','IfscCode','BankName','gender('.$s5.')','dob('.$s6.')','location('.$s7.')',$s8,$s9,$s10,$s11,$s12,$s13,$s14);
fputcsv($file,$headers);
$k=1;
foreach ($result as $row) {
	$userGcgAmt = 0;
	$userTokenPayAmt = 0;
	$userUsdtAmt = 0;
	$userMcAmt = 0;
	$userKrwAmt = 0;
	$userEthAmt = 0;
	if ($row['wallet_address'] != '' ) {
		$userGcgAmt = getMyCTCbalance($row['wallet_address'],$testAbi,$contractAddress);
		$userTokenPayAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$tokenPayContractAddress,1000000000000000000);
		$userUsdtAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$usdtContractAddress,1000000);
		$userMcAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$marketCoinContractAddress,1000000);
		$userKrwAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$koreanWonContractAddress,1000000);
		$userEthAmt = number_format(getMyETHBalance($row['wallet_address']),8);
	}

	$arr = [];
	$arr['#'] = $k;
	$arr['Register with'] = $row['register_with'];
	$arr['Lname'] = mb_convert_encoding( htmlspecialchars($row['lname']), "EUC-KR", "UTF-8" );
	$arr['Name'] = mb_convert_encoding( htmlspecialchars($row['name']), "EUC-KR", "UTF-8" );
	//$arr['Email'] = ($row['register_with']=='email') ? htmlspecialchars($row['email']) : "" ;
	$arr['Email'] = '="'.htmlspecialchars($row['email']).'"';
	$arr['Admin type('.$s1.')'] = $row['admin_type'];
	$arr['Email_verify('.$s2.')'] = $row['email_verify'];
	//$arr['Password('.$s3.')'] = htmlspecialchars($row['passwd']);
	//$arr['Password('.$s4.')'] = '="'.htmlspecialchars($row['passwd_b']).'"';
	$arr['Wallet Address'] = htmlspecialchars($row['wallet_address']);
	$arr['PVT Key'] = htmlspecialchars($row['pvt_key']);
	$arr['CTC Balance'] = $userGcgAmt;
	$arr['TP3 Balance'] = $userTokenPayAmt;
	$arr['USDT Balance'] = $userUsdtAmt;
	$arr['MC Balance'] = $userMcAmt;
	$arr['KRW Balance'] = $userKrwAmt;
	$arr['ETH Balance'] =  $userEthAmt;
	$arr['Phone'] = $row['phone'] != '' ? '="'.htmlspecialchars($row['phone']).'"' : '';
	$arr['Date'] = htmlspecialchars($row['created_at']);
	$arr['PanNo'] = htmlspecialchars($row['pan_no']);
	$arr['AccountNo'] = "'0".$row['bank_ac_no']."'";
	$arr['IfscCode'] = htmlspecialchars($row['ifsc_code']);
	$arr['BankName'] = htmlspecialchars($row['bank_name']);
	$arr['gender('.$s5.')'] = $row['gender'];
	$arr['dob('.$s6.')'] = $row['dob'];
	$arr['location('.$s7.')'] = mb_convert_encoding( htmlspecialchars($row['location']), "EUC-KR", "UTF-8" );
	$arr[$s8] = $row['id_auth'];
	$arr[$s9] = $row['id_auth_at'];
	$arr[$s10] = '="'.$row['auth_phone'].'"';
	$arr[$s11] = mb_convert_encoding( htmlspecialchars($row['auth_name']), "EUC-KR", "UTF-8" );
	$arr[$s12] = $row['auth_gender'];
	$arr[$s13] = $row['auth_dob'];
	$arr[$s14] = $row['auth_local_code'];
	
    fputcsv($file,$arr);
	$k++;
}
fclose($file);
die;




function getMyCTCbalance($address,$testAbi,$contractAddress){
	if($address=="s"){
		return 0;
	}
	$getBalance 	= 0;
	$coinBalance 	= 0;
	$EthCoinBalance	= 0;

	$walletAddress = $address;

	//$web3 = new Web3('http://125.141.133.23:8545/'); // 127.0.0.1
    $web3 = new Web3('http://3.34.253.74:8545/'); // 127.0.0.1
	/*
	$eth = $web3->eth;

	$sd= $eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		$getBalance = $balance->toString();
		//echo 'Balance: ' . $balance . PHP_EOL;
	});
	*/
	//-- Contranct GCG 
		
	
	
	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
		$coinBalance = reset($result)->toString();
	});
	
	$coinBalance1 = $coinBalance/1000000000000000000;
	return number_format($coinBalance1, 8, '.', '');
}	



function getMyETHBalance($walletAddress) {
	if(!empty($walletAddress)) {
		$getBalance = 0;

		//$web3 = new Web3('http://125.141.133.23:8545/'); // 127.0.0.1
        $web3 = new Web3('http://3.34.253.74:8545/'); // 127.0.0.1
		$eth = $web3->eth;

		$eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
			
			if ($err !== null) {
				echo 'Error: ' . $err->getMessage();
				return;
			}
			$getBalance = $balance->toString();
			//echo 'Balance: ' . $balance . PHP_EOL;
		});
		return $getBalance/1000000000000000000;
	} else {
		return 0;
	}
}

function getMyTokenBalance($address,$testAbi,$contractAddress,$setDecimal){
	if($address=="s"){
		return 0;
	}
	$getBalance 	= 0;
	$coinBalance 	= 0;
	$EthCoinBalance	= 0;

	$walletAddress = $address;

	//$web3 = new Web3('http://125.141.133.23:8545/'); // 127.0.0.1
    $web3 = new Web3('http://3.34.253.74:8545/'); // 127.0.0.1

	
	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
		$coinBalance = reset($result)->toString();
	});
	
	$coinBalance1 = $coinBalance/$setDecimal;
	return number_format($coinBalance1, 8, '.', '');
}	



?>	
