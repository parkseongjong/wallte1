<?php
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
	$sendPageUrl = "send_token_test1.php";
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
	<?php

$useragent=$_SERVER['HTTP_USER_AGENT'];
$mobile=0;
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
{
	$mobile=1;
}
	if(!empty($getRecords)) {
		$preDate = "";
		$getTime = '';
		$cudate = date("M d, Y");
			foreach($getRecords as $getRecordSingle) {
				if($getRecordSingle['value'] <= 0 ){ continue; }
				//$txId = $getRecordSingle['hash'];
				$getDate = date("M d, Y",$getRecordSingle['timeStamp']);
				$getTime = ' ('.date("H:i:s", $getRecordSingle['timeStamp']).')';
				$amount = number_format((float)$getRecordSingle['value']/$decimalDivide,4);
				$type = ($getRecordSingle['from']==$wallertAddress) ? "send" : "receive";
				$sign = ($getRecordSingle['from']==$wallertAddress) ? "-" : "+";
				
				if ($type == 'receive') {
					$txId = $getRecordSingle['from'];
				} else {
					$txId = $getRecordSingle['to'];
				}
				
				// 이름 표시, Show names (2020.05.12, YMJ)
				$name = '';
				$db = getDbInstance();
				$db->where ("wallet_address", $txId);
				$rowm = $db->get('admin_accounts');
				if ( !empty($rowm[0]['auth_name']) ) { // 본인인증 완료한 경우 실명 표시, Real name indication when self-certification is complete
					$name = $rowm[0]['auth_name'];
				} else if ( !empty($rowm[0]['name']) ) { // 사용자 입력한 이름, Show user-populated names
					$name = $rowm[0]['name'];
					if ( !empty($rowm[0]['lname']) ) {
						$name = $rowm[0]['lname'].$name;
					}
				}
				$name = $name != '' ? ' ('.$name.')' : '';
				

				$textLength = strlen($txId);
				$maxChars = 14;

				$txIdresult = substr_replace($txId, '...', $maxChars/2, $textLength-$maxChars);
				$txId = ($mobile==1) ? $txIdresult : $txId;
	?>
        <div class="col-lg-12">
			<?php if($preDate!=$getDate) { ?>
			<h3><?php echo ($cudate==$getDate) ? "Today" : $getDate; ?></h3>
			<?php } ?>
			<ul class="send-receive">
			<li>
			<img src="images/tx_<?php echo $type; ?>.png">
			</li>
			
			<li class="srcode">
			<h6><?php echo ucfirst($type); ?></h6><span class="t1"><?php if ( !empty($getTime) ) { echo $getTime; } ?></span>
			<p><?php echo $txId; ?><?php echo $name; ?></p>
			</li>
			
			<li>
			
			<span><?php echo $sign.$amount." ".strtoupper($tokenName); ?></span>
			</li>
			</ul>
			
			
          
        </div>
	<?php $preDate=$getDate; } } ?>
        <!-- /.col-lg-8 -->
        <div class="col-lg-4">

            <!-- /.panel .chat-panel -->
        </div>
        <!-- /.col-lg-4 -->
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
</script>
<?php include_once('includes/footer.php'); ?>
