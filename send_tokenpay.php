<?php 
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';


if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;


include('vendor/autoload.php');
use EthereumRPC\EthereumRPC;
$geth = new EthereumRPC('127.0.0.1', 8545);
$erc20 = new \ERC20\ERC20($geth);


$web3 = new Web3('http://127.0.0.1:8545/');
$eth = $web3->eth;

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$sendApproved = $row[0]['sendapproved'];	
$accountType = $row[0]['admin_type'];
$actualLoginText = $row[0]['register_with'];	
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";	
$walletAddress = $row[0]['wallet_address'];



$getNewBalance = 0 ;
$eth->getBalance($walletAddress, function ($err, $balance) use (&$getNewBalance) {
		
		if ($err !== null) {
			$_SESSION['failure'] = "Unable to Get User Eth Balance.";
			header('location: exchange.php');
			exit();
		}
		$getNewBalance = $balance->toString();
		$getNewBalance = $getNewBalance/1000000000000000000;
	});

$getNewCoinBalance = 0 ;
$functionName = "balanceOf";
$contract = new Contract($web3->provider, $tokenPayAbi);
$contract->at($tokenPayContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getNewCoinBalance){
	$getNewCoinBalance = reset($result)->toString();
	$getNewCoinBalance = $getNewCoinBalance/1000000000000000000;
});





///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');
	
		$toAccount = trim($_POST['address']);
		$amountToSend = trim($_POST['amount']);	
		
		$password =	$row[0]['email'].'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';
		$walletAddress = $row[0]['wallet_address'];
		if($getNewBalance < 0.0009){
			$_SESSION['failure'] = "Insufficient Eth fees.";
			header('location: send_tokenpay.php');
			exit();
		}
		
		if($getNewCoinBalance < $amountToSend){
			$_SESSION['failure'] = "Insufficient Token.";
			header('location: send_tokenpay.php');
			exit();
		}
		
		$contract = $tokenPayContractAddress; // ERC20 contract address
		$payer = $walletAddress; // Sender's Ethereum account
		$payee = $toAccount; // Recipient's Ethereum account
		$amount = $amountToSend; // Amount of tokens to transfer
		 
		// Grab instance of ERC20_Token class
		$token = $erc20->token($contract);
		// First argument is payee/recipient of this transfer
		// Second argument is the amount of tokens that will be sent
		$data = $token->encodedTransferData($payee, $amount);
		$transaction = $geth->personal()->transaction($payer, $contract) // from $payer to $contract address
		  ->amount("0") // Amount should be ZERO
		  ->data($data); // Our encoded ERC20 token transfer data from previous step
			


		// Send transaction with ETH account passphrase
		$txId = $transaction->send($password);
		if(!empty($txId)){
			$data_to_store = filter_input_array(INPUT_POST);
			$data_to_store = [];
			$data_to_store['coin_type'] ='tp';
			$data_to_store['created_at'] = date('Y-m-d H:i:s');
			$data_to_store['sender_id'] = $_SESSION['user_id'];
			$data_to_store['reciver_address'] = $_POST['address'];
			$data_to_store['amount'] = $_POST['amount'];
			$data_to_store['fee_in_eth'] =0;
			$data_to_store['status'] = 'completed';
			$data_to_store['fee_in_gcg'] = 0;
			$data_to_store['transactionId'] = $txId;
			
			//print_r($data_to_store);die;
			$db = getDbInstance();
			$last_id = $db->insert('user_transactions', $data_to_store);
			$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$txId.">" . $txId . "</a>";
			$_SESSION['success'] = $msg;
		}
		else {
			$_SESSION['failure'] = "Unable to send Token ! Try Again";
		}
	

	
	header('location: send_tokenpay.php');
		exit();
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
				<h2 class="page-header"><?php echo !empty($langArr['send_tp_token']) ? $langArr['send_tp_token'] : "Send TP Token"; ?></h2>
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
                           <img src="images/tp3_logo.png" width="100%" height="100%">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?php echo number_format($getNewCoinBalance,8); ?></div>
                            <div><?php echo !empty($langArr['tp_balance']) ? $langArr['tp_balance'] : "TP3 Balance"; ?></div>
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
					<div class="form-group col-md-6">
					<label><?php echo !empty($langArr['amount']) ? $langArr['amount'] : "Amount :"; ?> </label>
					<input autocomplete="off" required class="validate-required form-control" title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="" type="text">
					</div>
					<div class="clearfix"></div>
					<!--<div class="form-group col-md-6">
					<label><?php //echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?></label>
					<?php //echo !empty($langArr['third']) ? $langArr['third'] : "3.5%"; ?>
					</div>
					<div class="clearfix"></div>

					<div class="form-group col-md-6">
					<label><?php //echo !empty($langArr['fees_amount']) ? $langArr['fees_amount'] : "Fees Amount :"; ?></label>
					<input class="validate-required form-control" id="fees" name="fees"  readonly="" type="text">
					</div>
					<div class="clearfix"></div>
					<div class="form-group col-md-6" >
					<label><?php // echo !empty($langArr['amount_to_send']) ? $langArr['amount_to_send'] : "Amount to send :"; ?></label>
					<input class="validate-required form-control"  id="actual" name="actual_amount" readonly="" type="text">
					</div> -->
					<!--<div class="form-group col-md-6" >
					<label><?php //echo ucfirst($actualLoginText); ?> Code:</label>
					<div>
					<input placeholder="Verification code" type="text" autocomplete="false"  required="required" name="email_code" class="form-control input2" >
					<span class="send-button btn btn-info" id="get_code" style="padding: 13px 17px 13px 17px;cursor:pointer;margin-left:4px;">Get code</span>
					</div>
					<div id="show_msg"></div>
					
					</div>  -->
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
            onUnduplicatedRead: (txt, result) => {  $("#receiver_addr").val(txt);  $(".loader").hide(); scanner.hide();}
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
	});
	
   $("#customer_form").validate({
       rules: {
            f_name: {
                required: true,
                minlength: 3
            },
            l_name: {
                required: true,
                minlength: 3
            },   
        }
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
			var Fees = (getAmt*3.5)/100; 
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
</script>

<?php include_once 'includes/footer.php'; ?>
