<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';
// 테스트용. 지갑주소로 히스토리 조회. 삭제가능


// 실패 : 0xb41acb7b9e0d5ca778d1fdda8bdc28ba2b927cd9b3fb87662e3df84f67d792ea
// 성공 : 0x24c18a5e4cc535791a24efa228193076a4452fea9a1e263b03f41aedf31045f1

$txhash = '0xb41acb7b9e0d5ca778d1fdda8bdc28ba2b927cd9b3fb87662e3df84f67d792ea';
// 성공 Array ( [status] => 1 [message] => OK [result] => Array ( [status] => 1 ) ) Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 0 [errDescription] => ) )  
// 실패 Array ( [status] => 1 [message] => OK [result] => Array ( [status] => ) ) Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 0 [errDescription] => ) ) 
// 펜딩 Array ( [status] => 1 [message] => OK [result] => Array ( [status] => ) ) Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 0 [errDescription] => ) ) 
// 펜딩 Array ( [status] => 1 [message] => OK [result] => Array ( [status] => ) ) Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 0 [errDescription] => ) ) 

$result = '';
$eurl = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$txhash.'&apikey='.$ethApiKey; // status가 1인 경우에만 성공
echo $eurl.'<br />';
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $eurl,
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

$result = !empty($getResultDecode['result']['status']) ? $getResultDecode['result']['status'] : '';
print_r($getResultDecode);





$eurl = 'https://api.etherscan.io/api?module=transaction&action=getstatus&txhash='.$txhash.'&apikey='.$ethApiKey;


$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $eurl,
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

print_r($getResultDecode);





// https://cybertronchain.com/wallet/test_get_token_history.php?token=tp3
return;
exit;


//$waddr = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';
//$waddr = '0xd75663b674a025e9acd422c7260f809e921c28cc';
$waddr = '0xd75663b674a025e9acd422c7260f809e921c28cc';

require('includes/web3/vendor/autoload.php');
use Web3\Web3;

$wallertAddress = '';

$tokenName = $_GET['token'];
// for check wallertAddress is empty or not start 
$db = getDbInstance();
$db->where("wallet_address", $waddr);
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
//include_once('includes/header.php');


if(empty($wallertAddress)){
	//$web3 = new Web3('http://125.141.133.23:8545/');
    $web3 = new Web3('http://3.34.253.74:8545/');
	$personal = $web3->personal;
	$newAccount = '';
	// create account
	$personal->newAccount($userEmail, function ($err, $account) use (&$newAccount) {
		$newAccount = $account;
	});

	$personal->unlockAccount($newAccount, $userEmail, function ($err, $unlocked) {
		
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
$sendPageUrl = "send_other.php?token=".strtolower($tokenName);
if($tokenName!='eth') {
	$ethUrl = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$setContractAddr."&address=".$wallertAddress."&page=1&offset=10000&sort=desc&apikey=".$ethApiKey;

	//$ethUrl = "http://api.etherscan.io/api?module=account&action=txlistinternal&address=".$wallertAddress."&startblock=0&endblock=99999999&sort=asc&apikey=".$ethApiKey;

	//$ethUrl = "http://api.etherscan.io/api?module=account&action=txlistinternal&address=".$wallertAddress."&page=1&offset=10000&sort=desc&apikey=".$ethApiKey;
	//$ethUrl = "http://api.etherscan.io/api?module=account&action=txlistinternal&address=".$wallertAddress."&startblock=0&endblock=999999999&sort=desc&apikey=".$ethApiKey;
	//http://api.etherscan.io/api?module=account&action=txlistinternal&address=  &startblock=0&endblock=2702578&sort=asc&apikey=YourApiKeyToken
	// https://api.etherscan.io/api?module=account&action=txlist&address=&startblock=0&endblock=99999999&page=1&offset=10&sort=asc&apikey=YourApiKeyToken

	//http://api.etherscan.io/api?module=account&action=txlistinternal&address=0x2c1ba59d6f58433fb1eaee7d20b26ed83bda51a3&startblock=0&endblock=2702578&sort=asc&apikey=YourApiKeyToken
	// https://api.etherscan.io/api?module=account&action=getminedblocks&address=0x9dd134d14d1e65f84b706d6f205cd5b1cd03a46b&blocktype=blocks&apikey=YourApiKeyToken
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
		$cudate = date("M d, Y");
			foreach($getRecords as $getRecordSingle) {
				//if($getRecordSingle['value'] <= 0 ){ continue; }
				$txId = $getRecordSingle['hash'];
				$getDate = date("M d, Y",$getRecordSingle['timeStamp']);
				$getTime = ' ('.date("H:i:s", $getRecordSingle['timeStamp']).')';
				$amount = number_format((float)$getRecordSingle['value']/$decimalDivide,4);
				$type = ($getRecordSingle['from']==$wallertAddress) ? "send" : "receive";
				$sign = ($getRecordSingle['from']==$wallertAddress) ? "-" : "+";
				
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
			<p><a href="https://etherscan.io/tx/<?php echo $txId; ?>" target="_blank"><?php echo $txId; ?></a></p>
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


<?php include_once('includes/footer.php'); ?>
