<style>
.page-header {
	display:inline-block;
}
.head-nam-id {
	display:inline-block;
	font-weight: bold;
	margin-top: 46px;
    float: right;
}
.head-nam-id p {
	margin:0px;
}

.panel.panel-primary > .panel-heading {
	color: #000;
	background-color: #ffd602;
	border-color: #ffd602;
}

.panel.panel-green > .panel-heading {
	border-color: #d93233;
	color: white;
	background-color: #d93233;
}

.panel.panel-green {
	border:none;
}

.panel.panel-primary {
	border:none;
}

.panel-footer {
	color: #000;
	background-color: #fff;
}

.box-button { 
	background:#595959;
	padding: 8px 23px;
	font-size: 16px !important;
	color:#fff;
	border-radius: 7px;
 }





</style>
<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';

/*
if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}
*/

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;


/*
require('vendor/autoload.php');
use EthereumRPC\EthereumRPC;
use ERC20\ERC20;
$geth = new EthereumRPC('127.0.0.1', 8545);
// Instantiate ERC20 lib by passing Instance of EthereumRPC lib as constructor argument
$erc20 = new ERC20($geth);
*/


$getBalance = 0;
$coinBalance = 0;
$EthCoinBalance=0;

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$walletAddress = $row[0]['wallet_address'];
$email = $row[0]['email'];
$username = $row[0]['name'];
$registerWith = $row[0]['register_with'];
$showHeader = ($registerWith=="email") ? $email : $row[0]['phone'];



$tokenPayBalance = 0;
$usdtBalance = 0;

$walletAddress = '0xe2ac9631b1426ab753b08e0eea8a3b0b0e29e015';
$walletAddress = '0xcea66e2f92e8511765bc1e2a247c352a7c84e895';


if(!empty($walletAddress)) {


	fn_logSave($walletAddress);

//	$web3 = new Web3('http://127.0.0.1:8545/');
//	$web3 = new Web3('https://mainnet.infura.io/v3/e9bbe05f9dc949838c685503e32c4334');



	$timeout = 30;
	$web3 = new Web3('https://mainnet.infura.io/v3/e9bbe05f9dc949838c685503e32c4334', $timeout);

//	$web3 = new Web3('wss://mainnet.infura.io/ws/v3/e9bbe05f9dc949838c685503e32c4334', $timeout);



	$eth = $web3->eth;

/**/
	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		
		if ($err !== null) {
			echo 'Error: eth ' . $err->getMessage();
			fn_logSave( 'Error: eth ' . $err->getMessage() );
			return;
		}
		$getBalance = $balance->toString();
		//echo 'Balance: ' . $balance . PHP_EOL;
	});




	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){

		if ($err !== null) {
			echo 'Error: contract ' . $err->getMessage();
			fn_logSave( 'Error: contract ' . $err->getMessage() );
			return;
		}

		if ( !empty( $result ) ) {
			$coinBalance = reset($result)->toString();
		}
		else {
			fn_logSave('reset error');
		}
	});
	
	

	$contract->at($tokenPayContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$tokenPayBalance){

		if ($err !== null) {
			echo 'Error: 3 ' . $err->getMessage();
			fn_logSave( 'Error: 3 ' . $err->getMessage() );
			return;
		}

		if ( !empty( $result ) ) {
			$tokenPayBalance = reset($result)->toString();
		}
		else {
			fn_logSave('reset error');
		}
	});
	



/*
for($x=0;$x<5;$x++)
  {
*/



	$contract->at($usdtContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$usdtBalance){

		if ($err !== null) {
			echo 'Error: 4 ' . $err->getMessage();
			fn_logSave( 'Error: 4 ' . $err->getMessage() );
			return;
		}

		if ( !empty( $result ) ) {
			$usdtBalance = reset($result)->toString();
		}
		else {
			fn_logSave('reset error');
		}
	});

/*
  }
*/


}


$getVal = $getBalance/1000000000000000000;
$_SESSION['eth_balance']	=	$getVal;


$coinBalance = $coinBalance/1000000000000000000;
$_SESSION['Token_balance']	=	$coinBalance;


$tokenPayBalance = $tokenPayBalance/1000000000000000000;



$usdtBalance = $usdtBalance/1000000;




//Get DB instance. function is defined in config.php
$db = getDbInstance();

//Get Dashboard information
$numCustomers = $db->getValue ("customers", "count(*)");


/*
$tokenPay = $erc20->token($tokenPayContractAddress);
$tokenPayBalance = $tokenPay->balanceOf($walletAddress);


$usdtObj = $erc20->token($usdtContractAddress);
$usdtBalance = $usdtObj->balanceOf($walletAddress);
*/

//Get DB instance. function is defined in config.php
$db = getDbInstance();
$db->where("user_id", $_SESSION['user_id']);
$pointSum = $db->getValue("store_transactions", "sum(points)");

include_once('includes/header.php');
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?php echo !empty($langArr['dashboard']) ? $langArr['dashboard'] : "Dashboard"; ?></h1>
			<div class="head-nam-id"><p><?php echo $showHeader;?></p><span><?php echo $username;?></span></div>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
	<?php include('./includes/flash_messages.php') ?>
	
	  <!--<div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/logo3.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php //echo number_format($coinBalance,8); ?></div>
                            <div><?php //echo !empty($langArr['tp_balance']) ? $langArr['tp_balance'] : "CTC Balance"; ?></div>
                        </div>
						
                    </div>
                </div>
                <a href="send_token.php">
                    <div class="panel-footer">
                        <span class="pull-right"><?php //echo !empty($langArr['send_tp_token']) ? $langArr['send_tp_token'] : "Send TP Token"; ?></span>
                        <span class="pull-right"><i class="fa fa-share"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>-->
	
	
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/logo3.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php echo number_format($coinBalance,8); ?></div>
                            <div><?php echo !empty($langArr['ctc_balance']) ? $langArr['ctc_balance'] : "CTC Balance"; ?></div>
                        </div>
						
                    </div>
                </div>
                <a href="send_token.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['ctc_token']) ? $langArr['ctc_token'] : "CTC Token"; ?></span>
						<span class="pull-right box-button"><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
		
		
	  <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/tp3_logo.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php echo number_format($tokenPayBalance,8); ?></div>
                            <div><?php echo !empty($langArr['tp_balance']) ? $langArr['tp_balance'] : "TP3 Balance"; ?></div>
                        </div>
						
                    </div>
                </div>
                <a href="send_tokenpay.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['tp3_token']) ? $langArr['tp3_token'] : "TP3 Token"; ?></span>
                        <span class="pull-right box-button"><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
		
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/1321_64.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php echo number_format($getVal,8); ?></div>
                            <div><?php echo !empty($langArr['eth_balance']) ? $langArr['eth_balance'] : "ETH Balance"; ?></div>
                        </div>
                    </div>
                </div>
                <a href="receive_token.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['eth']) ? $langArr['eth'] : "ETH"; ?></span>
                        <span class="pull-right box-button"><?php echo !empty($langArr['receive']) ? $langArr['receive'] : "Receive"; ?></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>

	  <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/tether-usdt.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php echo number_format($usdtBalance,8); ?></div>
                            <div><?php echo !empty($langArr['usdt_balance']) ? $langArr['usdt_balance'] : "USDT Balance"; ?></div>
                        </div>
						
                    </div>
                </div>
                <a href="send_usdt.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['usdt_token']) ? $langArr['usdt_token'] : "USDT Token"; ?></span>
                        <span class="pull-right box-button"><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>		
	   
	   <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/bee_points.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;">₩ <?php echo number_format($pointSum,2); ?></div>
                            <div><?php echo !empty($langArr['bee_points']) ? $langArr['bee_points'] : "Bee Points"; ?></div>
                        </div>
                    </div>
                </div>
                <a href="store_transactions_user.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['customer_stores']) ? $langArr['customer_stores'] : "Our Stores"; ?></span>
                        <span class="pull-right box-button"><?php echo !empty($langArr['search']) ? $langArr['search'] : "Search"; ?></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
	   
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/exchange.png" class="dash-img" style="max-height:80px;">
                        </div>
                    </div>
                </div>
                <a href="coin_bank.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['ctc_to_krw']) ? $langArr['ctc_to_krw'] : "CTC to KRW"; ?></span>
                        <span class="pull-right box-button"><?php echo !empty($langArr['transfer']) ? $langArr['transfer'] : "Transfer"; ?></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
		
		

		
		
    </div>
   
</div>
<!-- /#page-wrapper -->

<?php include_once('includes/footer.php'); ?>

<?php
function fn_logSave($log){ //로그내용 인자
		$logPathDir = "/var/www/html/wallet/_log";  //로그위치 지정

		$filePath = $logPathDir."/".date("Y")."/".date("n");
		$folderName1 = date("Y"); //폴더 1 년도 생성
		$folderName2 = date("n"); //폴더 2 월 생성

		if(!is_dir($logPathDir."/".$folderName1)){
			mkdir($logPathDir."/".$folderName1, 0777);
		}
		
		if(!is_dir($logPathDir."/".$folderName1."/".$folderName2)){
			mkdir(($logPathDir."/".$folderName1."/".$folderName2), 0777);
		}
			
			$log_file = fopen($logPathDir."/".$folderName1."/".$folderName2."/".date("Ymd").".txt", "a");
			fwrite($log_file, date("Y-m-d h:i:s ").$log."\r\n");
			fclose($log_file);
	}


?>