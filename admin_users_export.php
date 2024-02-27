<?php
session_start();
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
$db->orderBy('id', 'DESC');
$result = $db->get('admin_accounts'); 
 

$file = fopen('php://output', 'w');

$headers = array('#','Lname','Name','Email','Wallet Address','CTC Balance','Phone','Date','PanNo','AccountNo','IfscCode','BankName');
fputcsv($file,$headers);
$k=1;
foreach ($result as $row) {
	$userGcgAmt = getMyCTCbalance($row['wallet_address'],$testAbi,$contractAddress);
	$arr = [];
	$arr['#'] = $k;
	$arr['Lname'] = mb_convert_encoding( htmlspecialchars($row['lname']), "EUC-KR", "UTF-8" );
	$arr['Name'] = mb_convert_encoding( htmlspecialchars($row['name']), "EUC-KR", "UTF-8" );
	$arr['Email'] = ($row['register_with']=='email') ? htmlspecialchars($row['email']) : "" ;
	//$arr['Password'] = htmlspecialchars($row['passwd_b']);
	$arr['Wallet Address'] = htmlspecialchars($row['wallet_address']);
	$arr['CTC Balance'] = $userGcgAmt;
	//$arr['Phone'] = htmlspecialchars($row['phone']);
	$arr['Phone'] = $row['phone'] != '' ? '="'.htmlspecialchars($row['phone']).'"' : '';
	$arr['Date'] = htmlspecialchars($row['created_at']);
	$arr['PanNo'] = htmlspecialchars($row['pan_no']);
	$arr['AccountNo'] = "'0".$row['bank_ac_no']."'";
	$arr['IfscCode'] = htmlspecialchars($row['ifsc_code']);
	$arr['BankName'] = htmlspecialchars($row['bank_name']);
	
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

?>	
