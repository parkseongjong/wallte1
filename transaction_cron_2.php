<?php
//require_once '/var/www/html/wallet/config/config.php';
require('/var/www/html/wallet/includes/web3/vendor/autoload.php');
require_once('/var/www/html/wallet/messente_api/vendor/autoload.php');


use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "/var/www/html/wallet/sms/bootstrap.php";

$api_key = '1234';
$api_secret = '1234';

$contractAddress = 'address';

require_once '/var/www/html/wallet/lib/MysqliDb.php';


define('DB_HOST', "localhost");
define('DB_USER', "web3_cybertronchainlocal");
define('DB_PASSWORD', "db1234");
define('DB_NAME', "wallet");


function getDbInstance()
{
	return new MysqliDb(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME); 
}


use Web3\Web3;
use Web3\Contract;
$web3 = new Web3('http://127.0.0.1:8545/');
$eth = $web3->eth;



$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';
	
$contractAddress = 'address';
$contract = new Contract($web3->provider, $testAbi);
$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";     


$apikey = "ehtkey";



$adminAccountWalletAddress = "0xe2ac9631b1426ab753b08e0eea8a3b0b0e29e015";
$adminAccountWalletPassword = "+821032824750ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";     

$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";     


	// unlock admin account
	$personal = $web3->personal;
	$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
		echo $unlocked;
		echo "<br>";

	});


$contractAddress = 'address';
$toUserAccount = "0xe2ac9631b1426ab753b08e0eea8a3b0b0e29e015";



$ctcAmountToSend= 1;
$newTransactionId = '';

			$ctcAmountToSend = bcmul ($ctcAmountToSend, 1000000000000000000);

			$amountToSend1 = dec2hex($ctcAmountToSend);
			
			$ctcAmountToSend = '0x';
			$ctcAmountToSend .= $amountToSend1;





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