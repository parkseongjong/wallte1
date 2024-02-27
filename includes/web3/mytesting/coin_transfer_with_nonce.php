<?php die('sdf');
require('./config.php');
use Web3\Web3;
use Web3\Contract;

$personal = $web3->personal;
$password  = "E54R2507c16VipAjaImmuAM";
$fromWalletAddress  = "address";
//$toWalletAddress  = $_POST['toWalletAddress'];

$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";  

$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
	if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	}
/* 	if ($unlocked) {
        echo 'New account is unlocked!' . PHP_EOL;
	} else {
	    echo 'New account isn\'t unlocked' . PHP_EOL;
	} */
});



$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';



$contract = new Contract($web3->provider, $testAbi);
$coinAmount  =500;
$contractAddress = 'address'; 
$functionName = "transfer";
$toAccount = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$fromAccount = $adminAccountWalletAddress;
$coinAmount = $coinAmount*1000000000000000000;

$coinAmount = dec2hex($coinAmount);


$transactionId = '';







/* 
 $contract->at($contractAddress)->send($functionName, $toAccount, $coinAmount, [
                        'from' => $fromAccount,
						'gas' => '0x186A0',
						'gasprice' =>'0x9502F9000',
						'nonce' => '0x511'
						//'gas' => '0xD2F0'
					], function ($err, $result) use ($contract, $fromAccount, $toAccount, &$transactionId){
						if ($err !== null) {
							print_r($err);  
						}
						 
						$transactionId = $result;   

					}); */
$ownerAccount = '0x4207a1d716809b6efc9520af280f542309067619';	
$toAccount = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $toAccount, $coinAmount, [
                        'from' => $adminAccountWalletAddress,
						/* 'gas' => '0x186A0',   //100000
						'gasprice' =>'0x12A05F200'    //5000000000wei // 5 gwei */
						'gas' => '0x186A0',   //100000
						'gasprice' =>'0x6FC23AC00' ,   //30000000000 // 9 gwei
						'nonce' => '0x54D'
					], function ($err, $result) use ($contract, $ownerAccount, $toAccount, &$transactionId) {
						if ($err !== null) {
							print_r($err);
							$transactionId = '';
						}
						else {
							$transactionId = $result;
						}
					});					

echo $transactionId;
//return $newAccount;





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