<?php
require_once './config/config.php';
require('includes/web3/vendor/autoload.php');
require_once(__DIR__ . '/messente_api/vendor/autoload.php');

use \Messente\Omnichannel\Api\OmnimessageApi;
use \Messente\Omnichannel\Configuration;
use \Messente\Omnichannel\Model\Omnimessage;
use \Messente\Omnichannel\Model\SMS; 




use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

$api_key = '1234';
$api_secret = '1234';





use Web3\Web3;
use Web3\Contract;
$web3 = new Web3('http://127.0.0.1:8545/');
$eth = $web3->eth;



$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';
	
$contractAddress = 'address';
$contract = new Contract($web3->provider, $testAbi);
$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";     

$db = getDbInstance();
$db->where("status", 'pending');
$userTransactions = $db->get('user_transactions');
$apikey = "ehtkey";
if(!empty($userTransactions)){
	foreach($userTransactions as $userTransaction){
		$transcationId = $userTransaction['transactionId'];
		$ethAmount = $userTransaction['amount'];
		$recordId = $userTransaction['id'];
		// check status 
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash=".$transcationId."&apikey=".$apikey,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"postman-token: 8b1efa98-e4d4-9221-cded-86fb915c3780"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		$jsonDecode = json_decode($response,true);
		$transactionStatus = $jsonDecode['result']['status'];
		if(!empty($jsonDecode['result']['status']) && $jsonDecode['result']['status'] == "1"){


			echo "1";
			
			$db = getDbInstance();
			$db->where("module_name", 'exchange_rate');
			$getSetting = $db->get('settings');

			$getExchangePrice = $getSetting[0]['value'];
			
			
			$newTransactionId = '';
			$ctcAmountToSend = $ethAmount*$getExchangePrice;
			$ctcAmountToSend = round($ctcAmountToSend,8);
			$receiverUserId = $userTransaction['sender_id'];
			$db = getDbInstance();
			$db->where("id", $receiverUserId);
			$row = $db->get('admin_accounts');
			$firstName = $row[0]['name'];	
			$toUserAccount = $row[0]['wallet_address'];	
			$registerWith = $row[0]['register_with'];	
			$userEmail = $row[0]['email'];	
			
			// send CTC Token To User Account
			$actualAmountToSendWithoutDecimal = $ctcAmountToSend;
			$actualAmountToSendWithoutDecimal = round($actualAmountToSendWithoutDecimal,8);
			$ctcAmountToSend = $ctcAmountToSend*1000000000000000000;
			$ctcAmountToSend = dec2hex($ctcAmountToSend);
			
			// unlock admin account
			$personal = $web3->personal;
			$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
				
			});
			
			echo "2";

			$contract->at($contractAddress)->send('transfer', $toUserAccount, $ctcAmountToSend, [
					'from' => $adminAccountWalletAddress,
					'gas' => '0x186A0',   //100000
					'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
				], function ($err, $result) use ($contract,&$newTransactionId) {
						if ($err !== null) {
							//continue;
							echo 'Error:  ' . $err->getMessage(); 
						}
						
						$newTransactionId =$result; 
				});
				
			if(!empty($newTransactionId)){


				echo "3";


				//$data_to_store = filter_input_array(INPUT_POST);
				$data_to_store = [];
				$data_to_store['created_at'] = date('Y-m-d H:i:s');
				$data_to_store['sender_id'] = 45;
				$data_to_store['reciver_address'] = $toUserAccount;
				$data_to_store['amount'] = $actualAmountToSendWithoutDecimal;
				$data_to_store['status'] = 'completed';
				$data_to_store['fee_in_eth'] = 0;
				$data_to_store['fee_in_gcg'] = 0;
				$data_to_store['transactionId'] = $newTransactionId;
				
				//print_r($data_to_store);die;
				$db = getDbInstance();
				$last_id = $db->insert('user_transactions', $data_to_store);
				$date = date("Y");
				// update record 
				$db = getDbInstance();
				$db->where("id", $recordId);
				$last_id = $db->update('user_transactions', ['status'=>"completed"]);	
				
				
				// send alert
				if($registerWith=="phone"){


					$alertMsg = number_format((float)$actualAmountToSendWithoutDecimal,8)." CTC Token added to your account";



					$koreaphone=false;
					$phone2 = '';
					$country = '';

					$phone = $row[0]['email'];	


					if ( strncmp("+82", $phone, 3) ==0 )
					{
						$phone2 = substr($phone, 3, 13);
						if ( strncmp("10", $phone2, 2) ==0 )
						{
							$koreaphone=true;
							$country = "+82";
						}
					}

					if($koreaphone ==true)
					{

						$rest = new Message($api_key, $api_secret);

						$options = new stdClass();
						$options->to = $phone2; // 수신번호
						$options->from = '0234893237'; // 발신번호
						
						$options->country = $country;
						$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
						$options->text = $alertMsg; // 문자내용



						$result = $rest->send($options);     

						if($result->success_count == '1')
						{
							//echo 'success';
						}
						else
						{
							//echo 'fail';
						}

					}




/*

					// send sms start
					$config = Configuration::getDefaultConfiguration()
						->setUsername('18b81e07d18425210db7925f39b3eb7c')
						->setPassword('31a06fb96198843422635716b114a32a');

					$apiInstance = new OmnimessageApi(
						new GuzzleHttp\Client(),
						$config
					);
					 
					$omnimessage = new Omnimessage([
						"to" => $userEmail
					]);


					$sms = new SMS(
						["text" => $alertMsg, "sender" => "CyberTChain"]
					);


					$omnimessage->setMessages([$sms]);
					$result = $apiInstance->sendOmnimessage($omnimessage);
*/					
					// send sms end
					
					
				}
				else {
					$alertMsg = number_format((float)$actualAmountToSendWithoutDecimal,8)." CTC Token added to your account";
					 $verifyLink = "http://".$_SERVER['HTTP_HOST']."/login.php";
					 $mailHtml = '<table style="background:#f6f6f6; width:100%;    height: 100vh;">
							<tr>
								<td>
									<table align="center" width="600"  style=" background:#fff; ">
								<tbody>
								<tr align="center" > 
									<td>
										<img src="http://'.$_SERVER['HTTP_HOST'].'/images/logo3.png" />
									</td>
								</tr>	
									
								  <tr>
								  <td><h4 style="text-align: left;
						padding-left: 16px; margin:0px;">Hi '.$firstName.',</h4></td>
								  </tr>
								  
								  
								  <tr align="center">
									<td><p style="padding:0 3%; line-height:25px;    text-align: justify;">Congratulation </p></td>
								  </tr>
								 
								  <tr align="center">
									<td><p style="padding:0 3%; line-height:25px;    text-align: justify;">'.$alertMsg.'</p></td>
								  </tr>
								  
									<tr>
										  <td align="center";><div style=" font-weight:bold;   padding: 12px 35px;
									color: #fff;
									border-radius:5px;
									text-align:center
									font-size: 14px;
									margin: 10px 0 20px;
									background: #ec552b;
									display: inline-block;
									text-decoration: none;">Click To Login: <a href="'.$verifyLink.'">'.$verifyLink.'</a></div></td>
									</tr>
								  
								  <tr align="center">
									<td><p style="padding:0 3%; line-height:25px;    text-align: justify;
									margin:0px;">Thanks, <br/>Team Support</p></td>
								  </tr>

								  
							
							</tbody>
							</table>
							
						  <table align="center" width="600"  style=" background:#f3f5f7; color:#b7bbc1 ">
								  
							<tr>
							<td>
							<h4>©'.$date.' All right reserved</h4>
							</td>
							</tr>  
							
								  
								  
								 
								</table>
						';
					 
						require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)

						$email = new \SendGrid\Mail\Mail();
						$email->setFrom("michael@cybertronchain.com", "CyberTron Coin");
						$email->setSubject($alertMsg);
						$email->addTo($userEmail);//$email_id;

						$email->addContent("text/html", $mailHtml);


						$sendgrid = new \SendGrid('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');
						$response = $sendgrid->send($email);
						
				}
			
			}	
				
			
		}
		
		
	}
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