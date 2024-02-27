<?php
session_start();
require_once './config/config.php';

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
	
	header('Location:index.php');

}

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




$db = getDbInstance();

$row = $db->get('admin_accounts');
 

//serve POST method, After successful insert, redirect to customers.php page.
require_once 'includes/header.php'; 
	


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']))	
{
/*
	echo '<pre>';
	var_export($_POST);
	die();
*/
	$country = $_POST['country'];	
	$phone2 = $_POST['phone2'];	


    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
    
	if(empty($data_to_store['user_id']) && empty($data_to_store['phone'])) {
		
		$_SESSION['login_failure'] = $langArr['em_ph_req'];
		header('location: forgetpassword.php');
		exit();
	}
	
	$user_id = !empty($data_to_store['user_id']) ? $data_to_store['user_id'] : str_replace(" ","",str_replace("-","",$data_to_store['phone']));

    $db = getDbInstance();

	
	$db->where("email",$user_id );
	$row = $db->get('admin_accounts');

	if (!empty($row)) {
		
		$checkVerify = $row[0]['email_verify'];
		
		if ($checkVerify=="N"){

			$_SESSION['login_failure'] = "Verify your account first";
			header('location: forgetpassword.php');
			exit();
			
		}

		$password=rand(9999,99999);
		//$password = $row[0]['passwd_b'];
		$name = $row[0]['name'];
		$email = $row[0]['email'];
		$myVcode = rand(100000,999999);
		$generateVcode = generateVcode($myVcode);
		$vCode = !empty($data_to_store['user_id']) ? md5($email.time()) : $generateVcode ;
		$db = getDbInstance();
		$db->where("email", $user_id);
	
		$last_id = $db->update('admin_accounts', ['vcode'=>$vCode]);

		$date = date('Y');
		$email = $user_id;
		
		if(!empty($data_to_store['user_id'])) 
		{

			$verifyLink = "http://".$_SERVER['HTTP_HOST']."/wallet/resetpassword.php?vcode=".$vCode;
			
			$hi = $langArr['hi'];
			$emailtext = $langArr['email'];
			$click_below_link_to_reset_password = $langArr['click_below_link_to_reset_password'];
			$reset_link_text = $langArr['reset_link'];
			$thanks = $langArr['thanks'];
			$team_support = $langArr['team_support'];
			$all_right_reserved = $langArr['all_right_reserved'];
			$reset_password_link_for_cybertron_coin = $langArr['reset_password_link_for_cybertron_coin'];
			$mailHtml = '	<table style="background:#f6f6f6; width:100%;    height: 100vh;">
								<tr>
								<td>
								<table align="center" width="600"  style=" background:#fff; ">
								<tbody>
								<tr align="center" > 
								<td>
								<img src="http://'.$_SERVER['HTTP_HOST'].'/wallet/images/logo3.png" />
								</td>
								</tr>	
								
								<tr>
								<td><h4 style="text-align: left;
								padding-left: 16px; margin:0px;">'.$hi.' '.$name.',</h4></td>
								</tr>
								
								
								
								<tr align="center">
								<td><p style="padding:0 3%; line-height:25px;    text-align: justify;">'.$emailtext.' : '.$email.'</p></td>
								</tr>
								
								<tr align="center">
								<td><p style="padding:0 3%; line-height:25px;    text-align: justify;">'.$click_below_link_to_reset_password .'</p></td>
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
								text-decoration: none;">'.$reset_link_text.': <a href="'.$verifyLink.'">'.$verifyLink.'</a></div></td>
								</tr>
								
								<tr align="center">
								<td><p style="padding:0 3%; line-height:25px;    text-align: justify;
								margin:0px;">'.$thanks.', <br/>'.$team_support.'</p></td>
								</tr>
								
								
								
								</tbody>
								</table>
								
								<table align="center" width="600"  style=" background:#f3f5f7; color:#b7bbc1 ">
								
								<tr>
								<td>
								<h4>©'.$date.' '.$all_right_reserved.'</h4>
								</td>
								</tr>  
							</table>
			';
			
			require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)
			
			$email = new \SendGrid\Mail\Mail();
			$email->setFrom("michael@cybertronchain.com", "CyberTron Coin");
			$email->setSubject($reset_password_link_for_cybertron_coin);
			$email->addTo($user_id);//$email_id;
			
			$email->addContent("text/html", $mailHtml);
			
			$sendgrid = new \SendGrid('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');
			
			try {
				
				$response = $sendgrid->send($email);
			
			} catch (Exception $e) {
				
				echo 'Caught exception: '.  $e->getMessage(). "\n";
			
			}
			
			$_SESSION['success'] = $langArr['forgot_success_msg'];
			header('location: login.php');
			exit();
		}
		
		else {
			// send sms start
			

			 


			$cybertchain_verification_code = $langArr['cybertchain_verification_code'];	



			try {

				$rest = new Message($api_key, $api_secret);

				$options = new stdClass();
				$options->to = $phone2; // 수신번호
				$options->from = '0234893237'; // 발신번호
				
				$options->country = $country;
				$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
				$options->text = $cybertchain_verification_code." : ".$vCode; // 문자내용



				$result = $rest->send($options);     

				if($result->success_count == '1')
				{
					//echo 'success';
					$_SESSION['success'] = $langArr['verification_code_send_no'];
					header('location: resetpass.php');
				}
				else
				{
					//echo 'fail';
					$_SESSION['login_failure'] = $langArr['unable_verification_code_send_no'];
					header('location: forgetpassword.php');
				}

			} catch(CoolsmsException $e) {

					//echo 'fail';
					$_SESSION['login_failure'] = $langArr['unable_verification_code_send_no'];
					header('location: forgetpassword.php');

//				echo $e->getMessage(); // get error message
//				echo $e->getCode(); // get error code
			}

/*
			// Configure HTTP basic authorization: basicAuth
			$config = Configuration::getDefaultConfiguration()
				->setUsername('18b81e07d18425210db7925f39b3eb7c')
				->setPassword('31a06fb96198843422635716b114a32a');
			
			$apiInstance = new OmnimessageApi(
				new GuzzleHttp\Client(),
				$config
			);
			
			$omnimessage = new Omnimessage([
				"to" => $user_id
			]);
			
			
			$sms = new SMS(
				["text" => $cybertchain_verification_code." : ".$vCode, "sender" => "CyberTChain"]
			);
			
			
			$omnimessage->setMessages([$sms]);
			try {

				$result = $apiInstance->sendOmnimessage($omnimessage);
				$_SESSION['success'] = $langArr['verification_code_send_no'];
				header('location: resetpass.php');

			} catch (Exception $e) {			

				$_SESSION['login_failure'] = $langArr['unable_verification_code_send_no'];
				header('location: forgetpassword.php');
				
			}	

*/

			// send sms end
		}
		
		
		
	}
	       
	else{

		$_SESSION['login_failure'] = $langArr['invalid_email_id'];
    	header('location: forgetpassword.php');
    	exit();
	 
	} 
}

//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

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

?>


</head>
<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<style>

body {
	background-color: #ffcd07 !important;
}

.row.login-bg {
	margin:0;
	height:100%;
	background: #ffcd07;
}

.login-panel {
	background-color: #fff;
	background-position: center;
	background-size: cover;
	padding: 0.5em 2em;
	margin: 0em auto;
	border: rgba(23, 19, 19, 0.95) !important;
	box-shadow: 0px 0px 5px 4px rgba(121, 121, 121, 0.36) !important;
}

.form-group .form-control {
	flex-basis: 90%;
	color: #000;
	padding: 12px 12px;
	border: none;
	box-sizing: border-box;
	outline: none;
	letter-spacing: 1px;
	font-size: 17px;
	font-weight: 700;
	border: 1px solid #bbb;
	background: #e1e1e1;
	height:auto;
}

.panel-body {
	padding:0 !important;
	text-align:center;
	color:#000;	
}
#page- {
	padding:0;
}

.btn-success.loginField {
	width: 100%;
	background: #ffcd07;
	outline: none;
	color: #000;
	margin: 10px 0px;
	font-size: 18px;
	font-weight: 400;
	border: 1px solid #ffcd07;
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
}

.login-panel {
    margin-top: 30%;
}

.nav.nav-tabs select {
	float: right;
	background: #ffcd07;
	border: none;
	text-align: center;
	padding: 11px;
	border-radius: 3px;
}
</style>

<body>
<div id="page-" class="col-md-4 col-md-offset-4">


<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
				</div>
				<?php } ?>
<form class="form loginform" action='' method='post'>
             <?php
				if(isset($_SESSION['success'])){ ?>
				<div class="alert alert-success alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['success']; unset($_SESSION['success']);?>
				</div>
				<?php } ?>
				<div class="login-panel panel panel-default">
					<div style="text-align: center;" class="logo"><img src="images/eth_logo.png" width='35%'/></div>
					<ul class="nav  nav-tabs" role="tablist">
						<li role="presentation" class="active"  onclick="callPhoneClick()"><a href="#phonebox" aria-controls="sign-up" role="tab" data-toggle="tab"><?php echo !empty($langArr['phone']) ? $langArr['phone'] : "Phone"; ?></a></li>
						<li role="presentation" onClick="callEmailClick()"><a href="#emailbox" aria-controls="login" role="tab" data-toggle="tab"><?php echo !empty($langArr['email']) ? $langArr['email'] : "Email"; ?></a></li>
						
                        
                      <select name="getlang" onChange="changeLanguage(this);">
                        <option <?php echo ($_SESSION['lang']=='ko') ? 'selected' : ""; ?> value="ko">Korea</option>
                        <option <?php echo ($_SESSION['lang']=='en') ? 'selected' : ""; ?> value="en">English</option>
                      </select>
                      
					</ul>			
					<div class="panel-body">
						<div class="tab-content">

					<div id="phonebox" class="form-group tab-pane fade in active">
						<label class="control-label"><?php echo !empty($langArr['phone']) ? $langArr['phone'] : "Phone"; ?></label>
						<input type="text" id="phone" name="phone" class="form-control" >
						<input type="hidden" id="phone2" name="phone2"  >
						<input type="hidden" id="country" name="country"  >

					</div>

					<div id="emailbox" class="form-group tab-pane fade">
						<label class="control-label"><?php echo !empty($langArr['email']) ? $langArr['email'] : "Email"; ?></label>
						<input type="email" name="user_id" id="emailfield" class="form-control">
					</div>


				</div>
						<div class="form-group">
							<input class="btn btn-success loginField" type='submit' name='submit' value='<?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?>'/>
						</div>
						<div style="margin: 10px;"><a  href="register_agree.php" class="loginField" ><?php echo !empty($langArr['register']) ? $langArr['register'] : "Register"; ?></a></div>
						<div><a href="login.php"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a></div>
					</div>	
				</div>
<!---<h3>Forgot Password<h3>
<form action='' method='post'>
<table cellspacing='5' align='center'>
<tr><td>Email id</td><td><input type='text' name='user_id'/></td></tr>
<tr><td></td><td><input type='submit' name='submit' value='Submit'/></td></tr>
</table>--->
</form>

</div>
</body>
</html>


<script type="text/javascript">
$(document).ready(function(){
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
});
</script>
<script>

function callEmailClick(){
	$("#phone").val('');
}
function callPhoneClick(){
	$("#emailfield").val('');
}
    $(function () {
      /*   $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        }); */
		$("#phone").intlTelInput({
		  initialCountry: "kr",
		    preferredCountries : ['cn','jp','us','kr'],
		  geoIpLookup: function(callback) {
			$.get('https://ipinfo.io/json?token=6ad007f53defcc', function() {}, "jsonp").always(function(resp) {
			  var countryCode = (resp && resp.country) ? resp.country : "";
			  callback(countryCode);
			});
		  },
		  utilsScript: "flag/build/js/utils.js" // just for formatting/placeholders etc
		});
		$(".loginform").submit(function(){
			var countryData = $("#phone").intlTelInput("getSelectedCountryData");
			var getPhoneVal = $("#phone").val();
			var getFirstChar = getPhoneVal.charAt(0);

			$("#phone2").val(getPhoneVal);
			$("#country").val(countryData.dialCode);

			if(getPhoneVal!='') {
				if(countryData.dialCode==82 && getFirstChar==0){
					getPhoneVal = getPhoneVal.substr(1);
				}
				$("#phone").val("+"+countryData.dialCode+getPhoneVal);
			}
		});
		
    });
</script>
<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>