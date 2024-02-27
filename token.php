<?php



















/*
2020-06-01, YMJ

페이지가 변경되었습니다.
The page has been changed.

아래 적힌 페이지에서 수정해주시기 바랍니다.
Please correct it in the page written below.

/var/www/html/wallet2/token.php

*/

























session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';

if(!isset($_GET['token']) || empty($_GET['token'])){
	header("Location:index.php");
}
if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}



require('includes/web3/vendor/autoload.php');

use Web3\Web3;

$wallertAddress = '';

$tokenName = $_GET['token'];
// for check wallertAddress is empty or not start 
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$userEmail = $row[0]['email'];
if ($db->count > 0) {
	$wallertAddress = $row[0]['wallet_address'];
}
else
{
	return;
	exit;
}

// for check wallertAddress is empty or not end


//Get Dashboard information
$numCustomers = $db->getValue ("customers", "count(*)");
include_once('includes/header.php');


if(empty($wallertAddress)){
	$web3 = new Web3('http://139.162.29.60:8545/');
	$personal = $web3->personal;
	$newAccount = '';
	// create account
	$personal->newAccount($userEmail, function ($err, $account) use (&$newAccount) {
		/* if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		} */
		$newAccount = $account;
		//echo 'New account: ' . $account . PHP_EOL;
	});

	$personal->unlockAccount($newAccount, $userEmail, function ($err, $unlocked) {
		/* if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		if ($unlocked) {
			echo 'New account is unlocked!' . PHP_EOL;
		} else {
			echo 'New account isn\'t unlocked' . PHP_EOL;
		} */
	});
	$wallertAddress = $newAccount;
	// update walletAddress into database
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->update('admin_accounts',['wallet_address'=>$wallertAddress]);
}

//$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=ethereum:".$wallertAddress;
$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=".$wallertAddress;



$curl = curl_init();
$setContractAddr = $contractAddressArr[$tokenName]['contractAddress'];
$decimalDivide = $contractAddressArr[$tokenName]['decimal'];
//$sendPageUrl = $contractAddressArr[$tokenName]['sendPage'];
if ($tokenName == 'ctc') {
	$sendPageUrl = "send_token.php";
} else if ($tokenName == 'eth') {
	$sendPageUrl = "send_eth.php";
} else {
	$sendPageUrl = "send_other.php?token=".strtolower($tokenName);
}
if($tokenName!='eth') {
	$ethUrl = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$setContractAddr."&address=".$wallertAddress."&page=1&offset=10000&sort=desc&apikey=".$ethApiKey;
}
else {
	$ethUrl = "http://api.etherscan.io/api?module=account&action=txlist&address=".$wallertAddress."&sort=desc&apikey=".$ethApiKey;
}
curl_setopt_array($curl, array(
  CURLOPT_URL => $ethUrl,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 3000,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "postman-token: 89d13eeb-278c-730c-b720-b521c178b500"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);
$getResultDecode = json_decode($response,true);
//print_r($getResultDecode); die;
$getRecords = $getResultDecode['result']; 

/*  if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}  */
?>

<style>
.showtxt{ text-align:center;}
.showtxtpop{ text-align:center;}
.showtxt1{ text-align:center; font-size:25px;}
.panel.panel-primarys {
	text-align: center;
	color: #000;
	box-shadow: 1px 1px 5px #0000009c;
	background:#f1ecf7;
}

.panel {

}
.send-part li {
	display: inline-block;
	width: 20%;
}
.send-part li p {
	margin-top: 5px;
	color:#3375bb;
	font-weight: bold;
}
.send-part {
	padding: 0;
	margin-top: 12px;
}
.send-receive {
	padding: 0;
}
.send-receive li {
    display: inline-block;
    vertical-align: middle;
    margin-right: 15px;
}
.send-receive li {
    display: inline-block;
    vertical-align: middle;
    margin-right: 15px;
}
.send-receive li:last-child  {
	float:right;
}
.send-receive li img {
	width:50px
}
.send-receive h6 {
	margin-bottom: 2px;
	font-size: 17px;
	font-weight: bold;
	display: inline;
}
.send-receive p {
	color: #868686;
}
.send-receive span {
	font-size: 17px;
	font-weight: bold;
	color:#23cc00;
}
.send-rr {
	max-height:500px;
	overflow-y:scroll;
}
.send-part-2 li {
	width: 28%;
}

@media only screen and (max-width: 767px) {
.send-receive span {
	font-size: 15px;
}
.send-receive li:last-child  {
	margin-top: 17px;
}
.send-receive li img {
	width:40px
}
.send-receive li {
    margin-right: 5px;
}
.send-rr {
	overflow-y: inherit;
	max-height:auto;
}
.modal-dialog {
	width: 90%;
	margin:0 auto;
}
.send-receive li:last-child {
    float: none;
}
.srcode {
	width: 50%;
}
.srcode p {
	    word-break: break-all;
}
}
.srcode .t1 {
	font-size: 13px;
	font-weight: normal;
	color:#868686;
}

#history_new #loading_history {
	margin: 30px auto 0;
	text-align: center;
	font-size: 3rem;
}
#history_new #loading_history img {
	width: 30px;
	height: auto;
}
</style>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?php echo strtoupper($tokenName)." Token" //echo !empty($langArr['receive_token']) ? $langArr['receive_token'] : "Receive Token"; ?></h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
    <div class="col-lg-3"></div>
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-primarys">
                <div class="panel-heading">
                    <div class="row">
                       <div class="col-md-12">
					   <img src="images/<?php echo $tokenName."_logo.png" ?>" width="100" />
					   </div>
                        <div class="col-xs-12 text-right">
                            <div class="showtxt1"><?php echo !empty($langArr['wallet_address']) ? $langArr['wallet_address'] : "Wallet Address"; ?></div>
                            <div style="word-break:break-all"  class="showtxt"><?php echo $wallertAddress; ?></div>
						
                        </div>
						
						
                    </div>
					<ul class="send-part">
						<li><a href="<?php echo $sendPageUrl; ?>"><img src="images/1.png" width="50px">
						  <p><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></p></a>
						</li>
						<li onClick="showReceive();" style="cursor:pointer;"><img src="images/2.png" width="50px">
						  <p><?php echo !empty($langArr['receive']) ? $langArr['receive'] : "Receive"; ?></p>
						</li>
						<li onclick="myFunction()" style="cursor:pointer;"><img  src="images/4.png" width="50px">
						  <p><?php echo !empty($langArr['copy']) ? $langArr['copy'] : "Copy"; ?></p>
						</li>
						</ul>
                </div>
               
            </div>
        </div>
   
        <div class="col-lg-3 col-md-6">
        
        </div>
        <div class="col-lg-3 col-md-6">
            
        </div>
    </div>
    <!-- /.row -->
    <div class="row send-rr">
		<div id="history_new">
			<div id="loading_history" class="none">
				<img src="images/ajax-loader6.gif" alt="loading" />
				<span>Loading...</span>
			</div>
		</div>
	<?php
	// I changed to ajax because the loading speed was slow. (2020-05-28, YMJ)
	// The previous file was backed up there. : token_20200528.php
	?>
    </div>
    <!-- /.row -->
</div>
<!-- /#page-wrapper -->


<!-- Modal -->
  <div class="modal fade" id="myModalReceive" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Receive <?php echo strtoupper($tokenName); ?></h4>
        </div>
        <div class="modal-body">
             <div class="row">
			
					<div class="col-lg-12 col-md-12">
						<div class="panel panel-primaryss">
							<div class="panel-heading" style="text-align:center;">
								<div class="row">
								   <div class="col-md-12">
								   <img id="barcodeimage" src="<?php echo $barCodeUrl; ?>" />
								   </div>
									<div class="col-xs-12 text-right">
										<div class="showtxt1"><?php echo !empty($langArr['wallet_address']) ? $langArr['wallet_address'] : "Wallet Address"; ?></div>
										<div style="word-break:break-all" class="showtxtpop"><?php echo $wallertAddress; ?></div>
										<div id="show_set_amount" class="showtxt1" style="color:#3375bb;"></div>
									</div>
									<br/>
									<ul class="send-part send-part-2">
									
									<li onclick="showInputBox()" style="cursor:pointer;"><img src="images/5.png" width="50px">
									  <p><?php echo !empty($langArr['set_amount']) ? $langArr['set_amount'] : "Set Amount"; ?></p>
									</li>
									<li onclick="myFunctionPop()" style="cursor:pointer;"><img  src="images/4.png" width="50px">
									  <p><?php echo !empty($langArr['copy']) ? $langArr['copy'] : "Copy"; ?></p>
									</li>
									</ul>
									<div id="set_amt" style="display:none;" class="col-md-6 col-md-offset-3">
										<input type="text" placeholder="<?php echo !empty($langArr['amount1']) ? $langArr['amount1'] : "Amount"; ?>" class="form-control" name="setamt" id="setamt" />
										<input type="submit" onclick="submitClick()" class="btn btn-default" name="submit" value="<?php echo !empty($langArr['confirm']) ? $langArr['confirm'] : "Confirm"; ?>" id="confirm" />
									</div>
								</div>
							</div>
						   
						</div>
					</div>
			   
				</div>
        </div>

      </div>
      
    </div>
  </div>
  
<script>
function myFunctionPop() {
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val($(".showtxtpop").text()).select();
  document.execCommand("copy");
  $temp.remove();
} 

function myFunction() {
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val($(".showtxt").text()).select();
  document.execCommand("copy");
  $temp.remove();
} 

function showReceive(){
	$("#myModalReceive").modal('show');
} 
function showInputBox(){
	$("#set_amt").toggle();
}

function submitClick(){
	var getAmt = $("#setamt").val();
	if(getAmt <=0){
		return false;
	}
	var showSet = "+"+getAmt+" <?php echo strtoupper($tokenName); ?>";
	var barCodeUrl = "<?php echo $barCodeUrl; ?>?amount="+getAmt;
	$("#show_set_amount").html(showSet);
	$("#barcodeimage").attr('src',barCodeUrl);
	$("#set_amt").toggle();
}


$(function(){
	get_token_history();
	$("#loading_history").removeClass('none');
});
function get_token_history() {
	var waddr = $(".showtxtpop").text();
	var token ="<?php echo $tokenName; ?>";
	$.ajax({
		url : 'send.pro.php',
		type : 'POST',
		data : {mode: 'get_token_history', waddr : waddr, token : token},
		success : function(resp){
			$("#loading_history").addClass('none');
			$("#history_new").html(resp);
		},
		error : function(resp){
		}
	});
	
}



</script>
<?php include_once('includes/footer.php'); ?>
