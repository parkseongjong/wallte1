<?php

session_start();
require_once './config/config.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

//$web3 = new Web3('http://127.0.0.1:8545/');
//$web3 = new Web3('http://125.141.133.23:8545/');
$web3 = new Web3('http://3.34.253.74:8545/');
$eth = $web3->eth;
	$personal = $web3->personal;

	
require_once(__DIR__ . '/messente_api/vendor/autoload.php');

use \Messente\Omnichannel\Api\OmnimessageApi;
use \Messente\Omnichannel\Configuration;
use \Messente\Omnichannel\Model\Omnimessage;
use \Messente\Omnichannel\Model\SMS;



//error_reporting(E_ALL);
if(empty($_SESSION['lang'])) {
	$_SESSION['lang'] = "ko";
}
$langFolderPath = file_get_contents("lang/".$_SESSION['lang']."/index.json");
$langArr = json_decode($langFolderPath,true);

//If User has already logged in, redirect to dashboard page.
//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	
	
	$tid = $_POST['tid'];
	
	
	//Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
	//$verify_code = $data_to_store['verify_code'];
	
	$userIP = getUserIpAddr();
	// blocked IP Code 
	
	$db = getDbInstance();
	$db->where("ip_name", $userIP);
	$row = $db->get('blocked_ips');
	if ($db->count > 0) { 
		header('location: login.php');
		exit();
	}
	
	//$email = $_POST['email'];//filter_input(INPUT_POST, 'email');
	$pass =  $_POST['passwd']; //filter_input(INPUT_POST, 'passwd');
	$phone =  str_replace("-","",$_POST['phone']); //filter_input(INPUT_POST, 'passwd');
	$phone =  str_replace(" ","",$phone); //filter_input(INPUT_POST, 'passwd');
	$phone_code = $_POST['phone_code'];

	$auth_phone = $phone;
	

	if (empty($_POST['phone_code']) ) {
		$_SESSION['login_failure'] = $langArr['register_phone_code_confirm'];
		header('location: register_au.php?tid='.$tid);
		exit();
	}


	$phone_result = '';
	if ($phone_code == '82' && substr($phone, 0, 1) == '0'){
		$phone_result = '+' . $phone_code . substr($phone, 1);
	} else {
		$phone_result = '+' . $phone_code . $phone;
	}
	$phone = $phone_result;
	



	if(empty($phone)) {
		$_SESSION['login_failure'] = $langArr['plz_fill_eth_em_ph'];
		header('location: register_au.php?tid='.$tid);
		exit();
	}


	
	if(!empty($phone)) {
		$db = getDbInstance();
		$db->where("phone", $phone);
		//$db->where("auth_phone", $auth_phone);
		//$db->where("id_auth", 'Y');
		$row = $db->get('admin_accounts');
		 //print_r($row); die;

		if ($db->count > 0) {
			$_SESSION['login_failure'] = $langArr['phone_already_rg'];
			header('location: register_au.php?tid='.$tid);
			exit();
		}
	}
	
	$dbt = getDbInstance();
	$dbt->where("id", $tid);
	$row_t = $dbt->get('temp_accounts');
	
	$lname = mb_substr($row_t[0]['name'], 0, 1); // 성
	$name = mb_substr($row_t[0]['name'], 1); // 이름

//if(empty($row)) {

	//$newAccount = $account;
	//echo 'New account: ' . $account . PHP_EOL;
	$register_with = 'phone';
	$mainEmail = $phone;
	/*
	$source_value_register_with =  str_replace("-","",$_SESSION['source_value_'.$register_with]); 
	$source_value_register_with =  str_replace(" ","",$source_value_register_with); 

	if((empty($verify_code) || ($verify_code != $_SESSION['verify_code'])) || $mainEmail!=$source_value_register_with ){

		//error insert start
		$data_to_store_error = [];

		$data_to_store_error['lname'] = $lname;
		$data_to_store_error['user_name'] = $name;
		$data_to_store_error['register_with'] = $register_with;
		$data_to_store_error['verify_code_sms'] = $verify_code;
		$data_to_store_error['verify_code_write'] = $_SESSION['verify_code'];
		$data_to_store_error['phone'] = $phone;
		$data_to_store_error['mainEmail'] = $mainEmail;
		$data_to_store_error['source_value_'] = $source_value_register_with;
		$db_error_insert = getDbInstance();
		//$db_error_insert->insert('admin_accounts_error', $data_to_store_error);/////////////////
		//error insert end


		$_SESSION['login_failure'] = $langArr['invalid_verification_code'];
		//header('location: register_au.php?tid='.$tid);////////////////////////////////////////////
		exit();
	}
	*/
	
	
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
	$data_to_store['admin_type'] = 'user';
	$data_to_store['user_name'] = "oo";
	$data_to_store['name'] = $name;
	$data_to_store['lname'] = $lname;
	$data_to_store['email'] = $phone;
	$data_to_store['phone'] = $phone;
	$data_to_store['gender'] = $row_t[0]['gender'];
	$data_to_store['dob'] = str_replace('-', '/', $row_t[0]['dob']);
	$data_to_store['register_with'] = $register_with;
	$data_to_store['user_ip'] = $userIP;
	 //print_r($mb); die;
	//$phoneNumber = $_POST['mobileno'];
	
	$newAccount = '';
	$personal->newAccount($data_to_store['email'].'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM', function ($err, $account) use (&$newAccount) {//////////////////////////////
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			
		}
		else {
			$newAccount = $account;
		}
	});
	
	$myVcode = rand(100000,999999);
	$generateVcode = generateVcode($myVcode);
	$vCode = ($register_with=='email') ? md5($_POST[$register_with].time()) : $generateVcode;
	$data_to_store['passwd'] = md5($_POST['passwd']);
	//$data_to_store['passwd_b'] = $_POST['passwd'];
	//$data_to_store['vcode'] = $vCode;
	$data_to_store['email_verify'] = 'Y';
	$data_to_store['wallet_address'] = $newAccount;
	//$data_to_store['wallet_address'] = "";

	$data_to_store['id_auth'] = $_POST['id_auth'];
	$data_to_store['id_auth_at'] = $row_t[0]['id_auth_at'];	
	$data_to_store['auth_ci'] = $row_t[0]['auth_ci'];
	$data_to_store['auth_di'] = $row_t[0]['auth_di'];
	$data_to_store['auth_phone'] = $row_t[0]['phone'];
	$data_to_store['auth_name'] = $row_t[0]['name'];
	$data_to_store['auth_gender'] = $row_t[0]['gender'];
	$data_to_store['auth_dob'] = $row_t[0]['dob'];
	$data_to_store['auth_local_code'] = $row_t[0]['local_code'];
	$data_to_store['n_country'] = $data_to_store['phone_code']; // (2020-05-26, YMJ) 15:07
	$data_to_store['n_phone'] = $row_t[0]['phone'];

	$db = getDbInstance();

	unset($data_to_store['tid']);
	unset($data_to_store['dob_y']);
	unset($data_to_store['dob_m']);
	unset($data_to_store['dob_d']);
	unset($data_to_store['full_name']);
	unset($data_to_store['phone_code']);

	unset($data_to_store['getlang']);
	unset($data_to_store['verify_code']);
	unset($data_to_store['cofirm_passwd']);
	unset($_SESSION['verify_code']);
	//print_r($data_to_store);
    $last_id = $db->insert('admin_accounts', $data_to_store);
	
	
    if($last_id)
    { 	
		$userId = $last_id;	
		
		// send 50 token to new register users start
		if($register_with == 'phone') {

			$getCountryCode = substr($phone, 0, 3);
			if($getCountryCode == "+82") {
			
				$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
				$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
				// unlock account

				// Add (2020-05-22, YMJ)
				$getSendFreeCtc = $db->where("module_name", 'send_free_ctc')->getOne('settings');
				if ( !empty($getSendFreeCtc['value']) ) {
					$getSendFreeCtcVal = $getSendFreeCtc['value'];
				}
				if ( empty($getSendFreeCtcVal) ) {
					$getSendFreeCtcVal = 3;
				}

				$personal = $web3->personal;
				$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
					if ($err !== null) {
						echo 'Error: ' . $err->getMessage();
						return;
					}
					if ($unlocked) {
						//echo 'New account is unlocked!' . PHP_EOL;
					} else {
						//echo 'New account isn\'t unlocked' . PHP_EOL;
					}
				});
				
				
				$fromAccount = $adminAccountWalletAddress;
				$toAccount = $newAccount;
				//$amountToSendInteger = 30;
				//$amountToSendInteger = 3; // 5 -> 3 (2020.05.12 14:58, YMJ)
				$amountToSendInteger = $getSendFreeCtcVal; // (2020-05-22, YMJ)
				$amountToSend = $amountToSendInteger*1000000000000000000;

				$amountToSend = dec2hex($amountToSend);
				$gas = '0x9088';
				$transactionId = '';
				$txid = '';
				$contract = new Contract($web3->provider, $testAbi);
				$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
					'from' => $fromAccount,
					'gas' => '0x186A0',   //100000
					'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
					//'gas' => '0xD2F0'
				], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId,$amountToSendInteger, &$txid) {
					// if ($err !== null) {
					//	throw $err;
					//}
					// if ($result) {
					//	$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
					//	$_SESSION['success'] = $msg;
					//}
					$transactionId = $result;
					$txid = $result;
					if(!empty($transactionId))
					{
						
						$data_to_store = filter_input_array(INPUT_POST);
						$data_to_store = [];
						$data_to_store['created_at'] = date('Y-m-d H:i:s');
						$data_to_store['sender_id'] = 45;
						$data_to_store['reciver_address'] = $toAccount;
						$data_to_store['amount'] = $amountToSendInteger;
						$data_to_store['fee_in_eth'] =0;
						$data_to_store['status'] = 'completed';
						$data_to_store['fee_in_gcg'] = 0;
						$data_to_store['transactionId'] = $transactionId;
						
						//print_r($data_to_store);die;
						$db = getDbInstance();
						$last_id = $db->insert('user_transactions', $data_to_store);
						
						
					}  
					else {
						//$_SESSION['failure'] = "Unable to send Token ! Try Again";
					}
				}); 
				
				// Add log records (2020-05-19, YMJ)
				$data_to_send_logs = [];
				$data_to_send_logs['send_type'] = 'register';
				$data_to_send_logs['coin_type'] = 'ctc';
				$data_to_send_logs['from_id'] = '45';
				$data_to_send_logs['to_id'] = !empty($last_id) ? $last_id : '';
				$data_to_send_logs['from_address'] = $fromAccount;
				$data_to_send_logs['to_address'] = $toAccount;
				$data_to_send_logs['amount'] = $amountToSendInteger;
				$data_to_send_logs['fee'] =0;
				if ( !empty($txid) ) {
					$data_to_send_logs['transactionId'] = $txid;
				}
				$data_to_send_logs['status'] = !empty($txid) ? 'send' : 'fail';
				$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

				$db = getDbInstance();
				$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);
				
				// send transaction
				
			}
		}
		// send 50 token to new register users end
				
		$_SESSION['success'] = $langArr['reg_success_phone'];

		$db = getDbInstance();
		$db->where('id', $tid);
		$stat = $db->delete('temp_accounts');

		header('location: login.php'); 

    } else{
		
		$_SESSION['login_failure'] = "error!";
    	header('location: register_au.php?tid='.$tid);
    	exit();
	}
	
//});
//}

	
}

function validate_mobile($mobile)
{
    return preg_match('/^[0-9]{10}+$/', $mobile);
}

function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function dec2hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
               '8','9','A','B','C','D','E','F');
    $hexval = '';
     while($number != '0')
     {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}
?>