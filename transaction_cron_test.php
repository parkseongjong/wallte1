<?php
require_once './config/config.php';
require('includes/web3/vendor/autoload.php');
require_once(__DIR__ . '/messente_api/vendor/autoload.php');

use \Messente\Omnichannel\Api\OmnimessageApi;
use \Messente\Omnichannel\Configuration;
use \Messente\Omnichannel\Model\Omnimessage;
use \Messente\Omnichannel\Model\SMS; 
// 테스트용. 삭제가능



use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

$api_key = '1234';
$api_secret = '1234';





use Web3\Web3;
use Web3\Contract;
//$web3 = new Web3('http://127.0.0.1:8545/');
//$web3 = new Web3('http://125.141.133.23:8545/');
$web3 = new Web3('http://3.34.253.74:8545/');
$eth = $web3->eth;


echo "\n\nstart  ";
echo date("Y-m-d H:i:s");




$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';
	
$contractAddress = 'address';
$contract = new Contract($web3->provider, $testAbi);
$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";     

$db = getDbInstance();
$db->where("sender_id", '5137');
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
			$toUserId = $row[0]['id']; // Add
			
			// send CTC Token To User Account
			$actualAmountToSendWithoutDecimal = $ctcAmountToSend;
			$actualAmountToSendWithoutDecimal = round($actualAmountToSendWithoutDecimal,8);


			$ctcAmountToSend = bcmul ($ctcAmountToSend, 1000000000000000000);
			$amountToSend1 = dec2hex($ctcAmountToSend);
			
			$ctcAmountToSend = '0x';
			$ctcAmountToSend .= $amountToSend1;

	

			// Add log records (2020-05-21, YMJ)
			$data_to_send_logs = [];
			$data_to_send_logs['send_type'] = 'exchange_r';
			$data_to_send_logs['coin_type'] = 'ctc';
			$data_to_send_logs['from_id'] = '45';
			if ( !empty($toUserId) ) {
				$data_to_send_logs['to_id'] = $toUserId;
			}
			$data_to_send_logs['from_address'] = $adminAccountWalletAddress;
			$data_to_send_logs['to_address'] = $toUserAccount;
			$data_to_send_logs['amount'] = $actualAmountToSendWithoutDecimal;
			$data_to_send_logs['fee'] =0;
			if ( !empty($newTransactionId) ) {
				$data_to_send_logs['transactionId'] = $newTransactionId;
			}
			$data_to_send_logs['status'] = !empty($newTransactionId) ? 'send' : 'fail';
			$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

			$db = getDbInstance();
			$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);


				
			
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