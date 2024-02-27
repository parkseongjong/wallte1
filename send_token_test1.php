<?php 
// 테스트용.
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

//$web3 = new Web3('http://127.0.0.1:8545/');
//$web3 = new Web3('http://125.141.133.23:8545/');
$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
$eth = $web3->eth;

$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$sendApproved = $row[0]['sendapproved'];
$sendApprovedCompleted = $row[0]['sendapproved_completed'];
$checkApproved = $row[0]['usdt_approved'];	
$accountType = $row[0]['admin_type'];
$actualLoginText = $row[0]['register_with'];	
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";	
$walletAddress = $row[0]['wallet_address'];


// (2020-05-25, YMJ)
if ( empty($row[0]['transfer_passwd']) ) {
	$_SESSION['failure'] = !empty($langArr['send_message5']) ? $langArr['send_message5'] : 'A transfer password is required to transfer. Please use it after setting the transfer password.';
	header('location: change_transfer_pass.php');
	exit();
}

$getNewBalance = 0 ;
$eth->getBalance($walletAddress, function ($err, $balance) use (&$getNewBalance,&$langArr) {
		
		if ($err !== null) {
			//$_SESSION['failure'] = "Unable to Get User Eth Balance.";
			$_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.'; // (2020-05-22, YMJ)
			header('location: exchange.php');
			exit();
		}
		$getNewBalance = $balance->toString();
		$getNewBalance = $getNewBalance/1000000000000000000;
	});
$getNewBalance = ($getNewBalance>0.0045 && $checkApproved=='N') ? $getNewBalance-0.0045 :$getNewBalance ;
$getNewCoinBalance = 0 ;
$functionName = "balanceOf";
$contract = new Contract($web3->provider, $testAbi);
$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getNewCoinBalance){
	if ( !empty( $result ) ) { // Add (2020-05-18, YMJ)
		$getNewCoinBalance = reset($result)->toString();
		$getNewCoinBalance = $getNewCoinBalance/1000000000000000000;
	}
});


$getCtcFee = $db->where("module_name", 'send_ctc_fee')->getOne('settings');
$getCtcFeeVal = $getCtcFee['value'];

$getTokenFee = $db->where("module_name", 'send_token_fee')->getOne('settings');
$getTokenFeeVal = $getTokenFee['value'];

$return_page = 'send_token_test1.php';


///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

	// 마지막 전송 시간 구하기
	
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
		if ($now_time - $created_time < $n_send_re_time * 60) { // 3분 (180) : 마지막 전송 후 3분이 되지 않았으면 전송 불가
			$_SESSION['failure'] = !empty($langArr['send_retry_time_message1']) ? $langArr['send_retry_time_message1'] : 'You cannot retransmit for ';
			$_SESSION['failure'] .= $n_send_re_time;
			$_SESSION['failure'] .= !empty($langArr['send_retry_time_message2']) ? $langArr['send_retry_time_message2'] : '	minutes after transmission. Please try again in a few minutes.';
			header('location: '.$return_page);
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
		header('location: '.$return_page);
		exit();	
	}
	

	// Changed it to set it at once on that page : config/new_config.php
	//$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
	//$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";                           
									
		
		$totalAmt = trim($_POST['amount']);
		/* $emailCode = trim($_POST['email_code']);
		
		
		
		
		
		 if(empty($emailCode)) {
			$_SESSION['failure'] = "Please Enter Verification Code";
			header('location: '.$return_page);
			exit();
		}
		
		$sessionVerificationCode = $_SESSION['emailcode'];
		if($emailCode!=$sessionVerificationCode){
			$_SESSION['failure'] = "Please Enter Correct Verification Code";
			header('location: '.$return_page);
			exit();
		} */
		if($sendApproved=='N' && $accountType=='user'){
			$_SESSION['failure'] = $langArr['you_dont_have_permission_for_transfer'];
			header('location: '.$return_page);
			exit();
		}					
		
		if($sendApprovedCompleted=='N' && $accountType=='user'){
			
			

			$db = getDbInstance();
			$db->where ("user_id", $userId);
			$db->where ("coin_type", 'ctc');
			$db->where ("ethmethod", 'approve');
			$ethSendRowFound = $db->get('ethsend');
			if($db->count>0){
				$txId = $ethSendRowFound[0]['tx_id'];
				
				//check for transaction completed
				
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => "https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash=".$txId."&apikey=".$ethApiKey,
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
				if (isset($decodeData['result']['status'])) {
					if ($decodeData['result']['status']==1) {
						$db = getDbInstance();
						$db->where("id", $userId);
						$updateColData = $db->update('admin_accounts', ['sendapproved_completed'=>'Y']);
					} else {
						$_SESSION['failure'] = $langArr['you_dont_have_permission_for_transfer'];
						header('location: '.$return_page);
						exit();
					}
						
				} else {
					$_SESSION['failure'] = $langArr['you_dont_have_permission_for_transfer'];
					header('location: '.$return_page);
					exit();
				}
			}
		}		


		
								
	// send transactions start

	//if($_POST['address']=='' or  ($_SESSION['eth_balance']<=0 || $_SESSION['Token_balance'] <=trim($_POST['amount']))){
		
		/* if($_SESSION['eth_balance'] < 0.0005){
			$_SESSION['failure'] = "Insufficient Eth fees.";
			header('location: '.$return_page);
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
	$adminPassword =	$n_master_wallet_pass;
	$adminAddress =	$n_master_wallet_address;
	if($_SESSION['user_id']==$n_master_id){ // 45 : Changed it to set it at once on that page : config/new_config.php
		$adminPassword =	$n_master_wallet_pass;
		$password =	$n_master_wallet_pass;
		$walletAddress = $row[0]['wallet_address'];
		
		//$password  = "E54R2507c16VipAjaImmuAM";
		//$walletAddress  = "0xf7c6ecbbbac3fe7ec61e09d53b92dda060cd90fb";
	}else{
		//$password =	$row[0]['email'].'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';
		$password =	$row[0]['email'].$n_wallet_pass_key; // Changed it to set it at once on that page : config/new_config.php
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


	
	
	
	
	
	
	
	$contract = new Contract($web3->provider, $testAbi);
	
	$functionName = "transfer";
	$toAccount = trim($_POST['address']);
	$fromAccount = $walletAddress;
	$amountToSend = trim($_POST['amount']);
	

	
	// if admin send token than call transfer Method 
	if($_SESSION['user_id']==$n_master_id){  // 45 : Changed it to set it at once on that page : config/new_config.php
		$amountToSend = $amountToSend*1000000000000000000;

		$amountToSend = dec2hex($amountToSend);
		$gas = '0x9088';
		$transactionId = '';
		$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
				'from' => $fromAccount,
				'gas' => '0x186A0',   //100000
				'gasprice' =>'0x4A817C800'    //20000000000wei // 20 gwei */
				//'gas' => '0x186A0',   //100000
				//'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
				//'gas' => '0xD2F0'
			], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId) {
				if ($err !== null) {
					throw $err;
				}
				if ($result) {
					$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
					$_SESSION['success'] = $msg;
				}
				$transactionId = $result;
				if(!empty($transactionId))
				{
					
					$data_to_store = filter_input_array(INPUT_POST);
					$data_to_store = [];
					$data_to_store['created_at'] = date('Y-m-d H:i:s');
					$data_to_store['sender_id'] = $_SESSION['user_id'];
					$data_to_store['reciver_address'] = $_POST['address'];
					$data_to_store['amount'] = $_POST['amount'];
					$data_to_store['fee_in_eth'] =0;
					$data_to_store['status'] = 'completed';
					$data_to_store['fee_in_gcg'] = $_POST['amount'] * 0.05;
					$data_to_store['transactionId'] = $transactionId;
					
					//print_r($data_to_store);die;
					$db = getDbInstance();
					$last_id = $db->insert('user_transactions', $data_to_store);
					
					
				}  
				else {
					//$_SESSION['failure'] = "Unable to send Token ! Try Again";
					$_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again."; // (2020-05-22, YMJ)
				}
				
				// Add log records (2020-05-18, YMJ)
				$data_to_send_logs = [];
				$data_to_send_logs['send_type'] = 'send';
				$data_to_send_logs['coin_type'] = 'ctc';
				$data_to_send_logs['from_id'] = $_SESSION['user_id'];
				//$data_to_send_logs['to_id'] = '';
				$data_to_send_logs['from_address'] = $fromAccount;
				$data_to_send_logs['to_address'] = $toAccount;
				$data_to_send_logs['amount'] = $_POST['amount'];
				$data_to_send_logs['fee'] = $_POST['amount'] * 0.05;
				if ( !empty($transactionId) ) {
					$data_to_send_logs['transactionId'] = $transactionId;
				}
				$data_to_send_logs['status'] = !empty($transactionId) ? 'send' : 'fail';
				$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

				$db = getDbInstance();
				$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);

			});
			
			header('location: '.$return_page);
			exit();
	}
	else {
		
	/* 	if($totalAmt<=10) {
			$_SESSION['failure'] = "Amount Should Be Grater Than 10";
			header('location: '.$return_page);
			exit();
		} */
		$feePercent = $getTokenFeeVal;
		$adminFee = $getTokenFeeVal;
		$adminFee = number_format((float)$adminFee,2);
		//$actualAmountToSend = $amountToSend-$adminFee;
		$actualAmountToSend = $amountToSend;
		$actualAmountToSendWithoutDecimal = $actualAmountToSend;
		$actualAmountToSend = $actualAmountToSend*1000000000000000000;
		
		//if($_SESSION['Token_balance'] < (trim($_POST['amount'])+$adminFee)){
		if($getNewCoinBalance < (trim($_POST['amount'])+$adminFee)){
			//$_SESSION['failure'] = "Token balance not sufficient";
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient'; // (2020-05-22, YMJ)
			header('location: '.$return_page);
			exit();
		}
		
		
		//echo $adminFee; die;
		$actualAmountToSend = dec2hex($actualAmountToSend);
		$gas = '0x9088';
		$transactionId = '';
		
		//$senderAccount = $fromAccount;
		$senderAccount = $n_master_wallet_address;
		$ownerAccount = $walletAddress;
		
		// send CTC Token to destination Address
		//$contract->at($contractAddress)->send('transfer',$toAccount, $actualAmountToSend, [
		$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $toAccount, $actualAmountToSend, [
                        'from' => $senderAccount,
						/* 'gas' => '0x186A0',   //100000
						'gasprice' =>'0x12A05F200'    //5000000000wei // 5 gwei */
						//'gas' => '0x186A0',   //100000
						//'gasprice' =>'0x6FC23AC00'    //30000000000 // 9 gwei
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
		$data_to_send_logs['coin_type'] = 'ctc';
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
			//$contract->at($contractAddress)->send('transfer', $n_master_wallet_address, $adminFeeInDecimal, [
			$senderAccount = $n_master_wallet_address;
			$toAccount = $n_master_wallet_address;
			
			
			$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $toAccount, $adminFeeInDecimal, [
							'from' => $senderAccount,
							/* 'gas' => '0x186A0',   //100000
							'gasprice' =>'0x12A05F200'    //5000000000wei // 5 gwei */
							//'gas' => '0x186A0',   //100000
							//'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
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
				$data_to_store_admin['reciver_address'] = $n_master_wallet_address;
				$data_to_store_admin['amount'] = $adminFee;
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
			
			
			/* send points  to user */
			///*
			//// Only used when sending tp3, (2020.05.06)
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
			//*/
			/* send points  to user */
			
		} 
		else {
			//$_SESSION['failure'] = "Unable to send Token ! Try Again";
			$_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again."; // (2020-05-22, YMJ)
			
		}	
		
		

		header('location: '.$return_page);
		exit();
		
	}
						
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

/* ( 2020-05-25, YMJ) */
#send_token #passwd {
	-webkit-text-security: disc;
	-moz-text-security: disc;
	text-security: disc;
}

</style>
<link  rel="stylesheet" href="css/send.css"/>
  <script src="https://cdn.jsdelivr.net/npm/dynamsoft-javascript-barcode@7/dist/dbr.min.js" data-productKeys="t0068NQAAAGvSIp5Eop5g1BERYu7svRtf69fVAGjbYlaQllzCcaVvOiAH+CigIESSr0IL62dRFRzKVp3PJSy5JfOOrhtvx/Q="></script>
<!--<div class="loader" style="display:none;"> <img src="images/loader.gif"></div>-->
<div class="loader"  style="display:none;"  id="div-video-container" >
<div class="camera-part" >
       <!-- <video class="dbrScanner-video" width="200" height="200" playsinline="true"></video>-->
	   <video id="video1" class="dbrScanner-video" playsinline="true">
		
	  </video>
    </div></div>
<div id="page-wrapper">
	<div id="send_token">
		<div class="row">
		
			<h5 style="color:#000; text-align:center;"><?php //echo !empty($langArr['exchange_heading']) ? $langArr['exchange_heading'] : "The amount of complimentary ETH upon registration has been raised from 0.0004 to 0.0007. If you're still seeing 0.0004 in your wallet, log out and log in again. If you're able to see the updated amount, then you need to repeat the process to be able to send it other wallet."; ?></h5>

			<div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['send_ctc_token']) ? $langArr['send_ctc_token'] : "Send CTC Token"; ?></h2>
			</div>
					
		</div>
		<?php include('./includes/flash_messages.php') ?>
		<div class="row">
			
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
									<label class="address_area">
										<?php echo !empty($langArr['send_text1']) ? $langArr['send_text1'] : "Address"; ?>
										<div id="to_name">
											<img src="images/icons/send_name_chk_t.png" alt="success" />
											<span id="receiver_addr_name"></span>
										</div>
										<div id="to_message">
											<img src="images/icons/send_name_chk_f.png" alt="fail" />
											<span id="receiver_message"></span>
										</div>
									</label>
									<!-- <textarea required autocomplete="off" name="address" id="receiver_addr" class=""></textarea>-->
									<div>
										<input type=text required title="<?php echo $langArr['this_field_is_required']; ?>" autocomplete="off" id="receiver_addr" name="address" class="" placeholder="<?php echo !empty($langArr['send_explain1']) ? $langArr['send_explain1'] : 'Please paste your wallet address or take a barcode.'; ?>"><img src="images/icons/send_barcode.png" id="qrimg" alt="barcode" class="barcode_img" />
									</div>
								</div>
								<div class="clearfix"></div>
								<input type="hidden" name="get_name_result" id="get_name_result" value="0" />
								<?php
									// Add (2020.05.18, YMJ)
									// get_name_result : 받는이가 회원인 경우 1, 회원이 아니면 0
								?>

								<div class="form-group col-md-6">
									<label class="address_area">
										<?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?>
										<span class="fee1"><?php echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?> <?php echo $getTokenFeeVal; //!empty($langArr['third']) ? $langArr['third'] : "3.5%"; ?> </span>
									</label>
									<input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="number">
								</div>
								<div class="clearfix"></div>

								<div class="form-group col-md-6">
									<label><?php echo !empty($langArr['send_text3']) ? $langArr['send_text3'] : "Fees Amount"; ?></label>
									<input id="fees" name="fees"  readonly="" type="text" placeholder="<?php echo !empty($langArr['send_explain3']) ? $langArr['send_explain3'] : 'It is automatically calculated when you enter the quantity.'; ?>">
								</div>
								<div class="clearfix"></div>
								<div class="form-group col-md-6" >
									<label><?php echo !empty($langArr['send_text4']) ? $langArr['send_text4'] : "Amount to send"; ?></label>
									<input  id="actual" name="actual_amount" readonly="" type="text" placeholder="<?php echo !empty($langArr['send_explain3']) ? $langArr['send_explain3'] : 'It is automatically calculated when you enter the quantity.'; ?>">
								</div> 
								<!--<div class="form-group col-md-6" >
								<label><?php //echo ucfirst($actualLoginText); ?> Code:</label>
								<div>
								<input placeholder="Verification code" type="text" autocomplete="false"  required="required" name="email_code" class=" input2" >
								<span class="send-button btn btn-info" id="get_code" style="padding: 13px 17px 13px 17px;cursor:pointer;margin-left:4px;">Get code</span>
								</div>
								<div id="show_msg"></div>
								
								</div>  -->
								<div class="clearfix"></div>
								<div class="form-group col-md-6">
									<input name="submit" class="btn" value="<?php echo !empty($langArr['send_amount']) ? $langArr['send_amount'] : "Send Amount"; ?>" type="submit">
								</div>
							</form>
						</div>

					</div>
				</div>
			</div>

			<!--end of row-->
		</div>
	</div>
</div>
	
	
	


<script type="text/javascript">
/*function pa_init(){
	var x = document.getElementById("passwd");
	var style = window.getComputedStyle(x);
	if(style.webkitTextSecurity){
		//do nothing
	}else{
		x.setAttribute("type","password");
	}
}
*/

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
	//pa_init();

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
            onFrameRead: function(results) { console.log(results);},
            onUnduplicatedRead: function(txt, result) {  $("#receiver_addr").val(txt);  $(".loader").hide(); scanner.hide(); addr_check();}
        }).then(function(s) {
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
            scanner.show().catch(function(ex){
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
		if ( !amount) {
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
                minlength: 6
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
		$("#to_name").removeClass('to_name');
		$("#receiver_addr_name").html('');
		$("#to_message").addClass('to_name');
		$("#receiver_message").html("<?php echo $langArr['invalid_eth_address']; ?>");
		$("#get_name_result").val('0');
	} else {
		var get = isAddress(addr);
		if (get == false) {
			$("#to_name").removeClass('to_name');
			$("#receiver_addr_name").html('');
			$("#to_message").addClass('to_name');
			$("#receiver_message").html("<?php echo $langArr['invalid_eth_address']; ?>");
			$("#get_name_result").val('0');
		} else {
			$("#to_message").removeClass('to_name');
			
			$.ajax({
				url : 'send.pro.php',
				type : 'POST',
				data : {mode: 'get_name', waddr : addr},
				dataType : 'json',
				success : function(resp){
					if (resp != '') {
						$("#to_name").addClass('to_name');
						$("#receiver_addr_name").html(resp);
						$("#to_message").removeClass('to_name');
						$("#receiver_message").html("");
						$("#get_name_result").val('1');
					} else {
						$("#to_name").removeClass('to_name');
						$("#receiver_addr_name").html('');
						$("#to_message").addClass('to_name');
						$("#receiver_message").html("<?php echo $langArr['send_member_msg1']; ?>");
						$("#get_name_result").val('0');
					}
				},
				error : function(resp){
					$("#to_name").removeClass('to_name');
					$("#receiver_addr_name").html('');
					$("#to_message").addClass('to_name');
					$("#receiver_message").html("<?php echo $langArr['profile_err_occurred']; ?>");
					$("#get_name_result").val('0');
				}
			});
		}
	}
}
/*
function open_pop() { // userid
	var frmPop1 = document.frmpop;
	if( navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1 || navigator.userAgent.indexOf("android-web-view") > - 1 ) {
		var winopts  = "toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
		var pop = window.open('','popView', winopts);
		alert('e');
	} else {
		alert('v');
		
		var width  = 410;
		var height = 500;

		var leftpos = screen.width  / 2 - ( width  / 2 );
		var toppos  = screen.height / 2 - ( height / 2 );

		var winopts  = "width=" + width   + ", height=" + height + ", toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
		var position = ",left=" + leftpos + ", top="    + toppos;

		var pop = window.open('','popView', winopts + position);
	}
	frmPop1.action='password_frm.php';
	frmPop1.target='popView';
	frmPop1.submit();
	

}
*/
</script>

<form method="post" name="frmpop" action="password_frm.php">
	<input type="hidden" name="uid" value="<?php echo $_SESSION['user_id']; ?>" />
</form>

<?php include_once 'includes/footer.php'; ?>
