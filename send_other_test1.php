<?php 
// �׽�Ʈ��
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

//$web3 = new Web3('http://127.0.0.1:8545/');
//$web3 = new Web3('http://125.141.133.23:8545/');
$web3 = new Web3('http://3.34.253.74:8545/');
$eth = $web3->eth;

$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$sendApproved = $row[0]['sendapproved'];
$checkApproved = $row[0]['usdt_approved'];	
$accountType = $row[0]['admin_type'];
$actualLoginText = $row[0]['register_with'];	
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";	
$walletAddress = $row[0]['wallet_address'];

$allowTokenArr = ['tp3','mc','krw','usdt'];
if(!isset($_GET['token']) || empty($_GET['token']) || !in_array($_GET['token'],$allowTokenArr)){
	$_SESSION['failure'] = !empty($langArr['send_message4']) ? $langArr['send_message4'] : 'Invalid Token'; // (2020-05-22, YMJ)
	header('location: index.php');
	exit();
}

// (2020-05-25, YMJ)
if ( empty($row[0]['transfer_passwd']) ) {
	$_SESSION['failure'] = !empty($langArr['send_message5']) ? $langArr['send_message5'] : 'A transfer password is required to transfer. Please use it after setting the transfer password.';
	header('location: change_transfer_pass.php');
	exit();
}

$token = strtolower($_GET['token']);
$tokenArr = $contractAddressArr[$token];
$columnName = ($token=='tp3') ? 'tp_approved' : $token.'_approved'; 
$tokenApproved = $row[0][$columnName];



// new change
$columnShortName = ($token=='tp3') ? 'tp' : $token; 
$columnNameCompleted = $columnName."_completed"; 
$tokenApprovedCompleted = $row[0][$columnNameCompleted];

$getNewBalance = 0 ;
$eth->getBalance($walletAddress, function ($err, $balance) use (&$getNewBalance,&$token,&$langArr) {
		
		if ($err !== null) {
			//$_SESSION['failure'] = "Unable to Get User Eth Balance.";
			$_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.'; // (2020-05-22, YMJ)
			header('location: send_other_test1.php?token='.$token);
			exit();
		}
		$getNewBalance = $balance->toString();
		$getNewBalance = $getNewBalance/1000000000000000000;
	});
$getNewBalance = ($getNewBalance>0.0045 && $checkApproved=='N') ? $getNewBalance-0.0045 :$getNewBalance ;
$getNewCoinBalance = 0 ;
$functionName = "balanceOf";
$tokenAbi = $tokenArr['abi'];
$tokenContractAddress = $tokenArr['contractAddress'];
$decimalDigit = $tokenArr['decimal'];
$otherTokenContract = new Contract($web3->provider, $tokenAbi);
$otherTokenContract->at($tokenContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getNewCoinBalance,$decimalDigit){
	if ( !empty( $result ) ) { // Add (2020-05-18, YMJ)
		$getNewCoinBalance = reset($result)->toString();
		$getNewCoinBalance = $getNewCoinBalance/$decimalDigit;
	}
});


$getCtcFee = $db->where("module_name", 'send_ctc_fee')->getOne('settings');
$getCtcFeeVal = $getCtcFee['value'];

$getTokenFee = $db->where("module_name", 'send_token_fee')->getOne('settings');
$getTokenFeeVal = $getTokenFee['value'];

// (2020-05-25, YMJ)
$getMinAmountVal = 0;
if ($token == 'tp3') {
	$getMinAmount = $db->where("module_name", 'min_transfer_amount_tp3')->getOne('settings');
	$getMinAmountVal = $getMinAmount['value'];	
} else if ($token == 'mc') {
	$getMinAmount = $db->where("module_name", 'min_transfer_amount_mc')->getOne('settings');
	$getMinAmountVal = $getMinAmount['value'];	
}

///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
	
	// ������ ���� �ð� ���ϱ�
	$send_re_time = 3; // 3��
	$db = getDbInstance();
	$db->where("from_id", $_SESSION['user_id']);
	$db->where("send_type", 'send');
	$db->pageLimit = 1;
	$db->orderBy('id', 'desc');
	$row_last = $db->getOne('user_transactions_all');
	if ( !empty($row_last['id']) ) {
		$last_send_time = $row_last['created_at'];
	} else {
		$db = getDbInstance();
		$db->where("sender_id", $_SESSION['user_id']);
		$db->pageLimit = 1;
		$db->orderBy('id', 'desc');
		$row_last2 = $db->getOne('user_transactions');
		$last_send_time = $row_last2['created_at'];
	}
	if ( !empty($last_send_time) ) {
		$created_time = strtotime($last_send_time);
		$now_time = strtotime("Now");
		if ($now_time - $created_time < $send_re_time * 60) { // 3�� (180) : ������ ���� �� 3���� ���� �ʾ����� ���� �Ұ�
			$_SESSION['failure'] = !empty($langArr['send_retry_time_message1']) ? $langArr['send_retry_time_message1'] : 'You cannot retransmit for ';
			$_SESSION['failure'] .= $send_re_time;
			$_SESSION['failure'] .= !empty($langArr['send_retry_time_message2']) ? $langArr['send_retry_time_message2'] : '	minutes after transmission. Please try again in a few minutes.';
			header('location: send_other_test1.php?token='.$token);
			exit();	
		}
	}

	// send_token : password add (2020-05-22, YMJ) - Test
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$db->where("transfer_passwd", md5($_POST['passwd']));
    $pas = $db->get('admin_accounts');
	if ($db->count == 0) { // password not
		$_SESSION['failure'] = !empty($langArr['login_fail_msg2']) ? $langArr['login_fail_msg2'] : 'Passwords do not match';
		header('location: send_other_test1.php?token='.$token);
		exit();	
	}
	


	$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
	$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";                           
								//print_r($_POST);
								
		
		$totalAmt = trim($_POST['amount']);
		/* $emailCode = trim($_POST['email_code']);
		
		
		
		
		
		 if(empty($emailCode)) {
			$_SESSION['failure'] = "Please Enter Verification Code";
			header('location: send_token.php');
			exit();
		}
		
		$sessionVerificationCode = $_SESSION['emailcode'];
		if($emailCode!=$sessionVerificationCode){
			$_SESSION['failure'] = "Please Enter Correct Verification Code";
			header('location: send_token.php');
			exit();
		} */
		if($tokenApproved=='N' && $accountType=='user'){
			$_SESSION['failure'] = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
			header('location: send_other_test1.php?token='.$token);
			exit();
		}

		// (2020-05-25, YMJ)
		if ( !empty($getMinAmountVal) && $getMinAmountVal > 0 && $totalAmt < $getMinAmountVal) { // �ݾ��� �ʹ� ����
			$ma_tmp = $getMinAmountVal.' '.strtoupper($token);
			$_SESSION['failure'] = !empty($langArr['send_min_amount']) ? $langArr['send_min_amount'].$ma_tmp : "The minimum limit is : ".$ma_tmp;
			header('location: send_other_test1.php?token='.$token);
			exit();
		}
								
		

		if($tokenApprovedCompleted=='N' && $accountType=='user'){
			
			

			$db = getDbInstance();
			$db->where ("user_id", $userId);
			$db->where ("coin_type", $columnShortName);
			$db->where ("ethmethod", 'approve');
			$ethSendRowFound = $db->get('ethsend');
			if($db->count>0){
					$txId = $ethSendRowFound[0]['tx_id'];
					
					//check for transaction completed
					
					$curl = curl_init();

					curl_setopt_array($curl, array(
					  CURLOPT_URL => "https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash=".$txId."&apikey=ehtkey",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "GET",
					  CURLOPT_HTTPHEADER => array(
						"cache-control: no-cache",
						"postman-token: bf5e409c-28bf-4abb-2670-d47bdf8f690e"
					  ),
					));

					$response = curl_exec($curl);
					$err = curl_error($curl);

					curl_close($curl);

					$decodeData = json_decode($response,true);
					if(isset($decodeData['result']['status'])){
						if($decodeData['result']['status']==1){
							$db = getDbInstance();
							$db->where("id", $userId);
							$updateColData = $db->update('admin_accounts', [$columnNameCompleted=>'Y']);
						}
						else {
							$_SESSION['failure'] = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
							header('location: send_other_test1.php?token='.$token);
							exit();
						}
							
					}
					else {
						$_SESSION['failure'] = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
						header('location: send_other_test1.php?token='.$token);
						exit();
					}
					
			}
			
			
		}			
	// send transactions start

	//if($_POST['address']=='' or  ($_SESSION['eth_balance']<=0 || $_SESSION['Token_balance'] <=trim($_POST['amount']))){
		


		/* if($_SESSION['eth_balance'] < 0.0005){
			$_SESSION['failure'] = "Insufficient Eth fees.";
			header('location: send_token.php');
			exit();
		} */
		
		
	//}
	
	/* if($getNewBalance < 0.003){
			$_SESSION['failure'] = "Insufficient Eth fees.";
			header('location: send_token_test.php');
			exit();
		}  */
	
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');
	
	
	//echo $_SESSION['user_id'];
	//exit();
	$adminPassword =	$adminAccountWalletPassword;
	$adminAddress =	$adminAccountWalletAddress;
	if($_SESSION['user_id']==45){
		$adminPassword =	$adminAccountWalletPassword;
		$password =	$adminAccountWalletPassword;
		$walletAddress = $row[0]['wallet_address'];
		
		//$password  = "E54R2507c16VipAjaImmuAM";
		//$walletAddress  = "0xf7c6ecbbbac3fe7ec61e09d53b92dda060cd90fb";
	}else{
		$password =	$row[0]['email'].'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';
		$walletAddress = $row[0]['wallet_address'];
	}
	
	// unlock account

	$personal = $web3->personal;
	$personal->unlockAccount($adminAddress, $adminPassword, function ($err, $unlocked) {
	//$personal->unlockAccount($walletAddress, $password, function ($err, $unlocked) {
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


	
	
	
	
	
	

	$functionName = "transfer";
	$toAccount = trim($_POST['address']);
	$fromAccount = $walletAddress;
	$amountToSend = trim($_POST['amount']);
	

	///*
	// if admin send token than call transfer Method 
	//if($_SESSION['user_id']==45){
	if($accountType=='admin'){

		 // (2020-05-25, YMJ)
		if($getNewCoinBalance < trim($_POST['amount'])){
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('location: send_other_test1.php?token='.$token);
			exit();
		}

		$fromAccountPass =	$row[0]['email'].'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';
		$personal = $web3->personal;
		$personal->unlockAccount($fromAccount, $fromAccountPass, function ($err, $unlocked) {
		//$personal->unlockAccount($walletAddress, $password, function ($err, $unlocked) {
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
		
		$amountToSend = $amountToSend*$decimalDigit;

		$amountToSend = dec2hex($amountToSend);
		$gas = '0x9088';
		$transactionId = '';
		
		/*$otherTokenContract->at($tokenContractAddress)->send('transfer', $toAccount, $amountToSend, [
				'from' => $fromAccount,
				'gas' => '0x186A0',
				'gasprice' =>'0x4A817C800' 
			], function ($err, $result) use ( $fromAccount, $toAccount,&$transactionId, &$langArr) {
				if ($err !== null) {
					throw $err;
				} 
				if ($result) {
					$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
					$_SESSION['success'] = $msg;
				}
				$transactionId = $result;
				
			});
			if(!empty($transactionId)){
				
				$data_to_store = filter_input_array(INPUT_POST);
				$data_to_store = [];
				$data_to_store['created_at'] = date('Y-m-d H:i:s');
				$data_to_store['coin_type'] = $token;
				$data_to_store['sender_id'] = $_SESSION['user_id'];
				$data_to_store['reciver_address'] = $_POST['address'];
				$data_to_store['amount'] = $_POST['amount'];
				$data_to_store['fee_in_eth'] =0;
				$data_to_store['status'] = 'completed';
				$data_to_store['fee_in_gcg'] = 0;
				$data_to_store['transactionId'] = $transactionId;
				
				//print_r($data_to_store);die;
				$db = getDbInstance();
				$last_id = $db->insert('user_transactions', $data_to_store);
				
				
			}  
			else {
				$_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again."; // (2020-05-22, YMJ)
			}

			// Add log records (2020-05-18, YMJ)
			$data_to_send_logs = [];
			$data_to_send_logs['send_type'] = 'send';
			$data_to_send_logs['coin_type'] = $token;
			$data_to_send_logs['from_id'] = $_SESSION['user_id'];
			//$data_to_send_logs['to_id'] = '';
			$data_to_send_logs['from_address'] = $fromAccount;
			$data_to_send_logs['to_address'] = $toAccount;
			$data_to_send_logs['amount'] = $_POST['amount'];
			$data_to_send_logs['fee'] =0;
			if ( !empty($transactionId) ) {
				$data_to_send_logs['transactionId'] = $transactionId;
			}
			$data_to_send_logs['status'] = !empty($transactionId) ? 'send' : 'fail';
			$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

			$db = getDbInstance();
			$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);
			*/
			header('location: send_other_test1.php?token='.$token);
			exit();
	}
	else {
		

		$feePercent = $getTokenFeeVal;
		$adminFee = $getTokenFeeVal;
		$adminFee = number_format((float)$adminFee,2);
		//$actualAmountToSend = $amountToSend-$adminFee;
		$actualAmountToSend = $amountToSend;
		$actualAmountToSendWithoutDecimal = $actualAmountToSend;
		$actualAmountToSend = $actualAmountToSend*$decimalDigit;
		
		 
		$getCtcTokenBalance = 0 ;
		$functionName = "balanceOf";
		$contract = new Contract($web3->provider, $testAbi);
		$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getCtcTokenBalance){
			if ( !empty( $result ) ) { // Add (2020-05-18, YMJ)
				$getCtcTokenBalance = reset($result)->toString();
				$getCtcTokenBalance = $getCtcTokenBalance/1000000000000000000;
			}
		});
		
		if($getCtcTokenBalance < $adminFee){
			//$_SESSION['failure'] = "Insufficient CTC Fee for trasfer Token";
			$_SESSION['failure'] = !empty($langArr['send_message3']) ? $langArr['send_message3'] : "Insufficient CTC Fee for trasfer Token"; // (2020-05-22, YMJ)
			header('location: send_other_test1.php?token='.$token);
			exit();
		}
		
		//if($_SESSION['Token_balance'] < (trim($_POST['amount'])+$adminFee)){
		if($getNewCoinBalance < trim($_POST['amount'])){
			//$_SESSION['failure'] = $langArr['token_balance_not_sufficient'];
			//$_SESSION['failure'] = "Token balance not sufficient";
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient'; // (2020-05-22, YMJ)
			header('location: send_other_test1.php?token='.$token);
			exit();
		}
		
		
		//echo $adminFee; die;
		$actualAmountToSend = dec2hex($actualAmountToSend);
		$gas = '0x9088';
		$transactionId = '';
		
		//$senderAccount = $fromAccount;
		$senderAccount = $adminAccountWalletAddress;
		$ownerAccount = $walletAddress;
		
		// send CTC Token to destination Address
		//$contract->at($contractAddress)->send('transfer',$toAccount, $actualAmountToSend, [
		$otherTokenContract->at($tokenContractAddress)->send('transferFrom',$ownerAccount, $toAccount, $actualAmountToSend, [
                        'from' => $senderAccount,
					], function ($err, $result) use ($contract, $ownerAccount, $toAccount, &$transactionId) {
						if ($err !== null) {
							//print_r($err); die;
							//$transactionId = '';
						}
						else {
							$transactionId = $result;
						}
					});

		// Add log records (2020-05-18, YMJ)
		$data_to_send_logs = [];
		$data_to_send_logs['send_type'] = 'send';
		$data_to_send_logs['coin_type'] = $token;
		$data_to_send_logs['from_id'] = $_SESSION['user_id'];
		//$data_to_send_logs['to_id'] = '';
		$data_to_send_logs['from_address'] = $ownerAccount;
		$data_to_send_logs['to_address'] = $toAccount;
		$data_to_send_logs['amount'] = $actualAmountToSendWithoutDecimal;
		$data_to_send_logs['fee'] = $adminFee;
		if ( !empty($transactionId) ) {
			$data_to_send_logs['transactionId'] = $transactionId;
		}
		$data_to_send_logs['status'] = !empty($transactionId) ? 'send' : 'fail';
		$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

		$db = getDbInstance();
		$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);

		if(!empty($transactionId))
		{
			
			$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$transactionId.">" . $transactionId . "</a>";
			$_SESSION['success'] = $msg;
			
			$data_to_store = filter_input_array(INPUT_POST);
			$data_to_store = [];
			$data_to_store['created_at'] = date('Y-m-d H:i:s');
			$data_to_store['sender_id'] = $_SESSION['user_id'];
			$data_to_store['reciver_address'] = $_POST['address'];
			$data_to_store['amount'] = $actualAmountToSendWithoutDecimal;
			$data_to_store['fee_in_eth'] = 0;
			$data_to_store['coin_type'] = $token;
			$data_to_store['status'] = 'completed';
			$data_to_store['fee_in_gcg'] = $adminFee;
			$data_to_store['transactionId'] = $transactionId;
			
			//print_r($data_to_store);die;
			$db = getDbInstance();
			$last_id = $db->insert('user_transactions', $data_to_store);
			
			
			// send CTC Token to destination Address START
			
			$adminTransactionId = '';
			
			$adminFeeInDecimal = $adminFee*1000000000000000000;
			$adminFeeInDecimal = dec2hex($adminFeeInDecimal);
			//$contract->at($contractAddress)->send('transfer', $adminAccountWalletAddress, $adminFeeInDecimal, [
			$senderAccount = $adminAccountWalletAddress;
			$toAccount = $adminAccountWalletAddress;
			
			$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $toAccount, $adminFeeInDecimal, [
							'from' => $senderAccount,
						], function ($err, $result) use ($contract, $ownerAccount,  &$adminTransactionId) {
							if ($err !== null) {
								$adminTransactionId = '';
							}
							else {
								$adminTransactionId = $result;
							}
						});
			
			if(!empty($adminTransactionId))
			{			
				$data_to_store_admin = filter_input_array(INPUT_POST);
				$data_to_store_admin = [];
				$data_to_store_admin['created_at'] = date('Y-m-d H:i:s');
				$data_to_store_admin['sender_id'] = $_SESSION['user_id'];
				$data_to_store_admin['reciver_address'] = $adminAccountWalletAddress;
				$data_to_store_admin['amount'] = $adminFee;
				$data_to_store_admin['coin_type'] = 'ctc';
				$data_to_store_admin['fee_in_eth'] = 0;
				$data_to_store_admin['fee_in_gcg'] = 0;
				$data_to_store_admin['status'] = 'completed';
				$data_to_store_admin['transactionId'] = $adminTransactionId;
				
				//print_r($data_to_store);die;
				$db = getDbInstance();
				$last_id = $db->insert('user_transactions', $data_to_store_admin); 		
			}
			// send CTC Token to destination Address END
			
			
			// Add log records (2020-05-18, YMJ)
			$data_to_send_logs = [];
			$data_to_send_logs['send_type'] = 'send';
			$data_to_send_logs['coin_type'] = 'ctc';
			$data_to_send_logs['from_id'] = $_SESSION['user_id'];
			//$data_to_send_logs['to_id'] = '';
			$data_to_send_logs['from_address'] = $ownerAccount;
			$data_to_send_logs['to_address'] = $toAccount;
			$data_to_send_logs['amount'] = $adminFee;
			$data_to_send_logs['fee'] = '0';
			if ( !empty($adminTransactionId) ) {
				$data_to_send_logs['transactionId'] = $adminTransactionId;
			}
			$data_to_send_logs['status'] = !empty($adminTransactionId) ? 'send' : 'fail';
			$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

			$db = getDbInstance();
			$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);
			
			
			if($token=='tp3'){
			    $db = getDbInstance();
				$db->where("store_wallet_address", $toAccount);
				$getStores = $db->getOne('stores');
				 if ($db->count >= 1) {
					 //$points = $actualAmountToSendWithoutDecimal*25/100;
					 $points = $actualAmountToSendWithoutDecimal*20/100;
					 $newSaveArr = [];
					 $newSaveArr['user_id'] = $_SESSION['user_id'];
					 $newSaveArr['user_wallet_address'] = $walletAddress;
					 $newSaveArr['store_id'] = $getStores['id'];
					 $newSaveArr['store_wallet_address'] = $getStores['store_wallet_address'];
					 $newSaveArr['tx_id'] = $transactionId;
					 $newSaveArr['points'] = $points;
					 $newSaveArr['amount'] = $actualAmountToSendWithoutDecimal;
					 
					 //print_r($data_to_store);die;
					$db = getDbInstance();
					$last_id = $db->insert('store_transactions', $newSaveArr); 		
				 } 
			}
			 
		} 
		else {
			//$_SESSION['failure'] = "Unable to send Token ! Try Again";
			$_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again."; // (2020-05-22, YMJ)
			
		}	
		
		

		header('location: send_other_test1.php?token='.$token);
		exit();
		
	}
	//*/				
	// send transactions end					

   
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

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>

<style>

.qrcode-text-btn {
	display:inline-block; 
	background:url(//dab1nmslvvntp.cloudfront.net/wp-content/uploads/2017/07/1499401426qr_icon.svg) 50% 50% no-repeat; 
	margin-left:-1.7em; 
	cursor:pointer;
	float:left; 
	border:1px solid;
	height: 54%;
	width: 8%;
	position: absolute;
	right: 20px;
	top: 28px;
}
.qrcode-text-btn > input[type=file] {position:absolute; overflow:hidden; width:1px; height:1px; opacity:0}

.form-control {
	flex-basis: 90%;
	color: #000;
	padding: 12px 12px;
	border: none;
	box-sizing: border-box;
	outline: none;
	letter-spacing: 1px;
	font-size: 17px;
	border-left: 1px solid #fff;
	border-bottom: 1px solid #fff;
	background: #eee;
	height:auto;
}

.panel-body {
	padding: 0 15px;
	background: none;
}

#receiver_addr {
	float:left;
	position: relative;
}

.submit-button {
	width: 50%;
	background: rgb(51, 232, 255);
	outline: none;
	color: #000;
	margin: 10px 0px;
	font-size: 14px;
	font-weight: 400;
	border: 1px solid #33e8ff;
	padding: 11px 11px;
	letter-spacing: 1px;
	text-transform: uppercase;
	border-radius: 20px;
	cursor: pointer;
	transition: 0.5s all;
	-webkit-transition: 0.5s all;
	-o-transition: 0.5s all;
	-moz-transition: 0.5s all;
	-ms-transition: 0.5s all;
	margin-top: 24px;
}

.submin-bttn-part {
	text-align:center;	
}

.panel {
	background: none;
}

.huge {
	font-size: 29px;
}

#qrimg {
	position: absolute;
	right: 20px;
	top: 28px;	
}

#qrfield {
	position: absolute;
	right: 21px;
	top: 28px;
	z-index: 1;
	height: 40px;
	width: 40px;
	opacity: 0;
}

#receiver_addr {
	width: 90%;
}
.loader{     position: fixed;
			width: 100%;
			top: 0;
			right: 0;
			z-index: 9;
			background: #000000bf;
			height: 100vh;
			text-align: center;}
.loader img{    margin-top: 20%;    margin-left: 249px;}	
.panel-heading{    min-height: 90px;}
#video1 {
	width: 600px;
	max-width: 100%;
	margin-left: 225px;
	margin-top: 10%;	
}
@media only screen and (max-width: 767px) {
	
#video1 {
	width: 300px;
	margin-left: 0;
	margin-top: 30%;	
}

.camera-part {
	height:400px;
	overflow:hidden;
}
.qrcode-text-btn {
	height: 38px;
	width: 12%;
	top: 29px;
}
#receiver_addr {
	width: 82%;
}
.loader img {
    margin-top: 100%;
    margin-left: 0;
}
}

/* ( 2020-05-25, YMJ) */
#passwd {
	-webkit-text-security: disc;
	-moz-text-security: disc;
	text-security: disc;
}
</style>
  <script src="https://cdn.jsdelivr.net/npm/dynamsoft-javascript-barcode@7/dist/dbr.min.js" data-productKeys="t0068NQAAAGvSIp5Eop5g1BERYu7svRtf69fVAGjbYlaQllzCcaVvOiAH+CigIESSr0IL62dRFRzKVp3PJSy5JfOOrhtvx/Q="></script>
<!--<div class="loader" style="display:none;"> <img src="images/loader.gif"></div>-->
<div class="loader"  style="display:none;"  id="div-video-container" >
<div class="camera-part" >
       <!-- <video class="dbrScanner-video" width="200" height="200" playsinline="true"></video>-->
	   <video id="video1" class="dbrScanner-video" playsinline="true">
		
	  </video>
    </div></div>
<div id="page-wrapper">
	<div class="row">
	
<h5 style="color:#000; text-align:center;"><?php //echo !empty($langArr['exchange_heading']) ? $langArr['exchange_heading'] : "The amount of complimentary ETH upon registration has been raised from 0.0004 to 0.0007. If you're still seeing 0.0004 in your wallet, log out and log in again. If you're able to see the updated amount, then you need to repeat the process to be able to send it other wallet."; ?></h5>

		 <div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['send_'.$token.'_token']) ? $langArr['send_'.$token.'_token'] : "Send ".strtoupper($token)." Token"; ?></h2>
			</div>
	</div>
	<?php include('./includes/flash_messages.php') ?>
	<div class="row">
    <div class="col-lg-2 col-md-2"></div>
		<div class="col-lg-4 col-md-4">
            <div class="panel panel-primary" style="border-color:#ffd602;">
                <div class="panel-heading" style="background:#ffd602;height:130px;">
                    <div class="row" style="color:#333;">
                        <div class="col-xs-3">
                           <img src="images/<?php echo $token; ?>_logo.png" width="100%" height="100%">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?php echo number_format($getNewCoinBalance,8); ?></div>
                            <div><?php echo !empty($langArr[$token.'_balance']) ? $langArr[$token.'_balance'] : strtoupper($token)." Balance"; ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="panel panel-primary" style="border-color:#ffd602;">
                <div class="panel-heading" style="background:#ffd602;height:130px;">
                    <div class="row" style="color:#333;">
                        <div class="col-xs-3">
                            <img src="https://cybertronchain.com/wallet/images/1321_64.png" height="70px">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" ><?php echo number_format($getNewBalance,8); ?></div>
                            <div><?php echo !empty($langArr['eth_balance']) ? $langArr['eth_balance'] : "ETH Balance"; ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
	
	

       
		<div class="col-sm-12 col-md-12 form-part-token">
		<div class="panel">
		<!-- main content -->
	       <div id="main_content" class="panel-body">
	       <!-- page heading -->
           <div class="card"> 
	
				<div id="validate_msg" ></div>
                <div class="boxed bg--secondary boxed--lg boxed--border">
				
				<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
					<div class="form-group col-md-6">
						<label><?php echo !empty($langArr['address']) ? $langArr['address'] : "ETH Address :"; ?></label>
						<!-- <textarea required autocomplete="off" name="address" id="receiver_addr" class="form-control"></textarea>-->
					<div>
					<input type=text required title="<?php echo $langArr['this_field_is_required']; ?>" autocomplete="off" id="receiver_addr" name="address" class="form-control qrcode-text"><img src="images/qr-code-scanner-icon-16.jpg" id="qrimg" width="40" />
					</div>
					</div>
					<div class="clearfix"></div>
					
					<div class="form-group col-md-6"><div id="receiver_addr_name"></div></div>
					<div class="clearfix"></div>
					<input type="hidden" name="get_name_result" id="get_name_result" value="0" />
					<?php
						// Add (2020.05.18, YMJ)
						// get_name_result : �޴��̰� ȸ���� ��� 1, ȸ���� �ƴϸ� 0
					?>

					<div class="form-group col-md-6">
					<label><?php echo !empty($langArr['amount']) ? $langArr['amount'] : "Amount :"; ?> </label>
					<input autocomplete="off" required class="validate-required form-control" title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="" type="text">
					</div>
					<div class="clearfix"></div>
					<div class="form-group col-md-6">
					<label><?php echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?></label>
					<?php echo $getTokenFeeVal; //!empty($langArr['third']) ? $langArr['third'] : "3.5%"; ?> CTC
					</div>
					
					<!--<div class="form-group col-md-6" >
					<label><?php //echo ucfirst($actualLoginText); ?> Code:</label>
					<div>
					<input placeholder="Verification code" type="text" autocomplete="false"  required="required" name="email_code" class="form-control input2" >
					<span class="send-button btn btn-info" id="get_code" style="padding: 13px 17px 13px 17px;cursor:pointer;margin-left:4px;">Get code</span>
					</div>
					<div id="show_msg"></div>
					
					</div>  -->
										<div class="clearfix"></div>

					<!-- send_token : password add (2020-05-22, YMJ) - Test -->
					<div class="form-group col-md-6" >
					<label><?php echo !empty($langArr['change_transfer_pass']) ? $langArr['change_transfer_pass'] : "Transmission password"; ?> : </label>
					<input autocomplete="off" required class="validate-required form-control" title="<?php echo $langArr['this_field_is_required']; ?>" id="passwd" name="passwd" type="number" maxlength="4">
					</div> 
					<div class="clearfix"></div>

					<div class="form-group col-md-6 submin-bttn-part">
					<input name="submit" class="submit-button btn btn-danger btn-sm" value="<?php echo !empty($langArr['send_amount']) ? $langArr['send_amount'] : "Send Amount"; ?>" type="submit">
					</div>
				</form>
                </div>
            </div>
        </div>
        <!--end of row-->
    </div> 


</div>

    </div>
	
	
	


<script type="text/javascript">
function pa_init(){
	var x = document.getElementById("passwd");
	var style = window.getComputedStyle(x);
	if(style.webkitTextSecurity){
		//do nothing
	}else{
		x.setAttribute("type","password");
	}
}

function openQRCamera(node) {
/*   var reader = new FileReader();
  reader.onload = function() {
    node.value = "";
    qrcode.callback = function(res) {
	
      if(res instanceof Error) {
        alert("No QR code found. Please make sure the QR code is within the camera's frame and try again.");
      } else {
        //node.parentNode.previousElementSibling.value = res;
        node.previousElementSibling.value = res;
      }
	  
    };
    qrcode.decode(reader.result);
  };
  reader.readAsDataURL(node.files[0]); */
  
  
  
    /*  let scanner = null;
        Dynamsoft.BarcodeScanner.createInstance({
            onFrameRead: results => {console.log(results);},
            onUnduplicatedRead: (txt, result) => {alert(txt);}
        }).then(s => {
            scanner = s;
            scanner.show().catch(ex=>{
                console.log(ex);
                alert(ex.message || ex);
                scanner.hide();
            });
        }); */
}

$(document).ready(function(){
	pa_init();

    var target_id = "#qrimg"
	if (navigator.userAgent == "android-web-view"){
		target_id = "#qrnull";
		var element = document.getElementById('qrimg');
		var href_el = document.createElement('a');
		href_el.href = 'activity://scanner_activity';
		element.parentNode.insertBefore(href_el, element);
		href_el.appendChild(element);
	}

	$(target_id).click(function(){
		$(".loader").show();
		let scanner = null;
        Dynamsoft.BarcodeScanner.createInstance({
			UIElement: document.getElementById('div-video-container'),
            onFrameRead: results => { console.log(results);},
            onUnduplicatedRead: (txt, result) => {  $("#receiver_addr").val(txt);  $(".loader").hide(); scanner.hide(); addr_check();}
        }).then(s => {
            scanner = s;
			$("#div-video-container").click(function(){
				scanner.hide();
			});
			// Use back camera in mobile. Set width and height.
			// Refer [MediaStreamConstraints](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia#Syntax).
			//scanner.setVideoSettings({ video: { width: 200, height: 220, facingMode: "environment" } });

			let runtimeSettings = scanner.getRuntimeSettings();
			// Only decode OneD and QR
			runtimeSettings.BarcodeFormatIds = Dynamsoft.EnumBarcodeFormat.OneD | Dynamsoft.EnumBarcodeFormat.QR_CODE;
			// The default setting is for an environment with accurate focus and good lighting. The settings below are for more complex environments.
			runtimeSettings.localizationModes = [2,16,4,8,0,0,0,0];
			// Only accept results' confidence over 30
			runtimeSettings.minResultConfidence = 30;
			scanner.updateRuntimeSettings(runtimeSettings);

			let scanSettings = scanner.getScanSettings();
			// The same code awlways alert? Set duplicateForgetTime longer.
			scanSettings.duplicateForgetTime = 20000;
			// Give cpu more time to relax
			scanSettings.intervalTime = 300;
			scanner.setScanSettings(scanSettings);
            scanner.show().catch(ex=>{
                console.log(ex);
				 alert(ex.message || ex);
				scanner.hide();
            });
        });
		
		//$('#qrfield').trigger('click'); 
	})
	
	$("#customer_form").submit(function(){
		 /*
		 // Changed to check when a keyup event occurs (2020-05-18, YMJ)
		 // It can only be sent to members.
		  		  
		$(this).find("input[type='submit']").prop('disabled',true);
		$("#loading-o").removeClass('none');

		var addr = $("#receiver_addr").val();
		var get = isAddress(addr);
		if( get == false ){
			
			$("#validate_msg").html("<div class='alert alert-danger'><?php echo $langArr['invalid_eth_address']; ?></div>");  
			$(this).find("input[type='submit']").prop('disabled',false);
			$("#loading-o").addClass('none');

			return false;
			
		} else {
			
		}
		*/
		$("#loading-o").removeClass('none');
		var get_name_result = $("#get_name_result").val();
		if (get_name_result == '0') { // It can only be sent to members.
			$("#loading-o").addClass('none');
			return false;
		}


		// Add (2020-05-22, YMJ)
		var amount = $("#amount").val();
		var passwd = $("#passwd").val();
		if ( !amount || !passwd)
		{
			$("#loading-o").addClass('none');
			return false;
		}

	});
	
	// send_token : password add (2020-05-22, YMJ) - Test
   $("#customer_form").validate({
       rules: {
            amount: {
                required: true,
                minlength: 1
            },
            passwd: {
                required: true,
                minlength: 4
            },   
        }
    });

	$("#amount").on('keyup change', function(){
		addr_check();
	});
	// Add (2020-05-18, YMJ)
	// It can only be sent to members.
	$("#receiver_addr").on('propertychange change keyup paste input', function(){
		addr_check();
	});

    $('#amount').keyup(function () {
    if($(this).val() == '')
        {
            $("#actual").val('0.0');
            $("#fees").val('');
        }
        else
        {
			var getAmt = $('#amount').val();
			//var Fees = (getAmt*<?php echo $getCtcFeeVal; ?>)/100; 
			var Fees = <?php echo $getTokenFeeVal; ?>; 
            var totalAmt = $(this).val();
			var actalAmt  = parseFloat(totalAmt)+parseFloat(Fees);
			var actalAmt = parseFloat(actalAmt).toFixed(8);
            $("#actual").val(actalAmt);
            $("#fees").val(Fees);
        }
    });
 
/* 	$("#get_code").click(function(){
		$.ajax({
			beforeSend:function(){
				$("#show_msg").html('<img src="images/ajax-loader.gif" />');
			},
			url : 'sendemailcode.php',
			type : 'POST',
			dataType : 'json',
			success : function(resp){
				$("#show_msg").html('<div class="alert alert-success">Verification code send to your <?php //echo $codeSendTo; ?>.</div>');
				setTimeout(function(){ $("#show_msg").hide(); }, 10000);
			},
			error : function(resp){
				$("#show_msg").html('<div class="alert alert-success">Verification code send to your <?php //echo $codeSendTo; ?>.</div>');
				setTimeout(function(){ $("#show_msg").hide(); }, 10000);
			}
		}) 
	 }); */
	
});


/**
 * Checks if the given string is an address
 *
 * @method isAddress
 * @param {String} address the given HEX adress
 * @return {Boolean}
*/  
  
    var isAddress = function (address) {
		if (!/^(0x)?[0-9a-f]{40}$/i.test(address)) {
			// check if it has the basic requirements of an address
			return false;
		} else if (/^(0x)?[0-9a-f]{40}$/.test(address) || /^(0x)?[0-9A-F]{40}$/.test(address)) {
			// If it's all small caps or all all caps, return true
			return true;
		} else {
			// Otherwise check each case
			return isChecksumAddress(address);
		}
};

/**
 * Checks if the given string is a checksummed address
 *
 * @method isChecksumAddress
 * @param {String} address the given HEX adress
 * @return {Boolean}
*/
	var isChecksumAddress = function (address) {
		// Check each case
		address = address.replace('0x','');
		var addressHash = sha3(address.toLowerCase());
		for (var i = 0; i < 40; i++ ) {
			// the nth letter should be uppercase if the nth digit of casemap is 1
			if ((parseInt(addressHash[i], 16) > 7 && address[i].toUpperCase() !== address[i]) || (parseInt(addressHash[i], 16) <= 7 && address[i].toLowerCase() !== address[i])) {
				return false;
			}
		}
		return true;
	};

	function addr_check(){
		var addr = $("#receiver_addr").val();
		var addr_length = addr.length;
		
		if( addr_length < 42){
			$("#validate_msg").html("<div class='alert alert-danger'><?php echo $langArr['invalid_eth_address']; ?></div>");
			$("#receiver_addr_name").html('');
			$("#get_name_result").val('0');
		} else {
			var get = isAddress(addr);
			if (get == false) {
				$("#validate_msg").html("<div class='alert alert-danger'><?php echo $langArr['invalid_eth_address']; ?></div>");
				$("#receiver_addr_name").html('');
				$("#get_name_result").val('0');
			} else {
				$("#validate_msg").html("");  
			
				$.ajax({
					url : 'send.pro.php',
					type : 'POST',
					data : {mode: 'get_name', waddr : addr},
					dataType : 'json',
					success : function(resp){
						if (resp != '') {
							$("#receiver_addr_name").html(resp);
							$("#get_name_result").val('1');
						} else {
							$("#validate_msg").html("<div class='alert alert-danger'><?php echo $langArr['send_member_msg1']; ?></div>");
							$("#receiver_addr_name").html('');
							$("#get_name_result").val('0');
						}
					},
					error : function(resp){
						$("#receiver_addr_name").html('');
						$("#get_name_result").val('0');
					}
				});
			}
		}
	}
</script>

<?php include_once 'includes/footer.php'; ?>
