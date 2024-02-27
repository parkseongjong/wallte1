<?php
//die("Registration close for public user");
session_start();
require_once './config/config.php';
//If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
}
?>
<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<style>
/* Style all input fields */
body {
	background-color: #ffcd07 !important;
}

.input1 {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
  margin-top: 6px;
  margin-bottom: 16px;
}

/* Style the submit button */
input[type=submit] {
  background-color: #4CAF50;
  color: white;
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
	border-left: 1px solid #fff;
	border-bottom: 1px solid #fff;
	background: rgba(255, 255, 255, 0.81);
	height:auto;
}

/* Style the container for inputs */
.container {
  background-color: #f1f1f1;
  padding: 20px;
}

/* The message box is shown when the user clicks on the password field */
#message {
  display:none;
  background: #f1f1f1;
  color: #000;
  position: relative;
  padding: 20px;
  margin-top: 10px;
}

#message p {
  padding: 10px 35px;
  font-size: 18px;
}

/* Add a green text color and a checkmark when the requirements are right */
.valid {
  color: green;
}

.valid:before {
  position: relative;
  left: -35px;
  content: "✔";
}

/* Add a red text color and an "x" when the requirements are wrong */
.invalid {
  color: red;
}

.invalid:before {
  position: relative;
  left: -35px;
  content: "✖";
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
    margin-top: 30px !important;
	margin-bottom: 30px;
}

.tab-content {
	padding-top: 12px;
}

.nav.nav-tabs select {
	float: right;
	background: #ffcd07;
	border: none;
	text-align: center;
	padding: 11px;
	border-radius: 3px;
}


.auth_input {
	width: 100%;
	height: 45px;
	border: 0;
	font-size: 17px;
	color: #000000;
	background-color: #fafafa;
	line-height: 45px;
	position: relative;
}
.auth_input img {
	width: 15px;
	height: auto;
	position: absolute;
	right: 10px;
}
.auth_input:after {
	width: 15px;
	height: auto;
	position: absolute;
	right: 10px;
	top: 16.5px;
	content: url('/wallet/images/auth_input_check.png');
}

.input_p {
	width: 100%;
	margin-bottom: 30px;
}
.input_n {
	width: 100%;
	margin-bottom: 4.5px;
}
.input_d {
	width: 49%;
	margin-bottom: 35px;
	float: left;
}
.input_g {
	width: 49%;
	margin-bottom: 35px;
	float: right;
}
.input_g:after {
	content:'';
	display: block;
	clear: both;
}
.text1 {
	font-size:  14px;
	color: #4e4dd7;
	margin-bottom: 16px;
	margin-top: 38px;
}
.text2 {
	font-size:  23px;
	color: #2b2726;
	margin-bottom: 80px;
}
.text3 {
	font-size:  12px;
	color: #989898;
}

</style>


<?php
$t_id = '';
$auth_phone_no = '';
$auth_name = '';
$auth_dob = '';
$auth_gender = '';
$auth_local_code = '';
$id_auth = 'N';
$id_auth_at = '';
$auth_gender_local = '';


function getUserIpAddr()
{
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		//ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		//ip pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

// Check country code by IP
function kisa_ip_chk(){

	$ip = getUserIpAddr();
	$key = "2020032517154809084222";
	$url ="http://whois.kisa.or.kr/openapi/ipascc.jsp?query=".$ip."&key=".$key."&answer=json";
	$ch = curl_init();

	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_NOSIGNAL, 1);
	//curl_setopt($ch,CURLOPT_POST, 1); //Method를 POST. 없으면 GET
	$data = curl_exec($ch);
	$curl_errno = curl_errno($ch);
	$curl_error = curl_error($ch);
	curl_close($ch);
	$decodeJsonData = json_decode($data, true);
	return $decodeJsonData['whois']['countryCode'];
}


function ipinfo_ip_chk($key) { // 수량 체크 테스트용. whois 대신 사용 가능한지 check (2020.05.14, YMJ)
	// https://ipinfo.io/
	if ($key == '1') {
		$access_token = 'd5b65ce795f734'; // 무료 version key (50,000건)
	} else {
		$access_token = '7c984c718aef66'; // 무료 version key (50,000건)
	}
	$ip_address = getUserIpAddr();
	$country = '';

	//$url = "https://ipinfo.io/{$ip_address}?token=".$access_token;
	//$details = json_decode(@file_get_contents($url));
	//if ( !empty($details->country) ) {
	//	return $details->country;
	//}
	$url = "https://ipinfo.io/{$ip_address}/country?token=".$access_token;
	//try {
		$country = @file_get_contents($url);
		//if ( empty($country) ) {
		//}
	//} catch (Exception $e) {
	//}
	return $country; // 국내 : KR
}

$ip_kor = '';
$ip_kor = trim(ipinfo_ip_chk('2'));
if ($ip_kor == '') {
	$ip_kor = kisa_ip_chk();
}

if ( empty($_GET['tid']) ) {
	header('Location:login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
	if ( !empty($_GET['tid']) ) {
		$t_id = $_GET['tid'];
		
		$db = getDbInstance();
		$db->where("id", $t_id);
		$row_t = $db->get('temp_accounts');
		
		$user_ip = getUserIpAddr();

		if ( empty($row_t[0]['id']) ) {
			fn_logSave("Personal Identification Error1 : It's a wrong approach , File : " . $_SERVER['SCRIPT_FILENAME'] );
			header('Location:login.php');
			exit();
		}

		if ( $ip_kor != 'KR') {
			fn_logSave("Personal Identification Error2 : It's a wrong approach , File : " . $_SERVER['SCRIPT_FILENAME'] );
			header('Location:login.php');
			exit();
		} else if ($row_t[0]['user_ip'] != $user_ip ) { // 인증한 아이피와 현재 아이피 다르면 -> 다시 인증해라
			$db->where('id', $t_id);
			$stat = $db->delete('temp_accounts');
			fn_logSave("Personal Identification Error3 : It's a wrong approach , File : " . $_SERVER['SCRIPT_FILENAME'] );
			header('Location:login.php');
			exit();
		} else {
			$id_auth = 'Y';
			$auth_phone_no = $row_t[0]['phone'];
			$auth_name = $row_t[0]['name'];
			$auth_dob = $row_t[0]['dob'];
			$auth_gender = $row_t[0]['gender'];
			$auth_local_code = $row_t[0]['local_code'];
			$id_auth_at = $row_t[0]['id_auth_at'];
			$tmp1 = $auth_local_code == 'Kor' ? $langArr['korean'] : $langArr['foreigner'];
			$tmp2 = $auth_gender == 'male' ? $langArr['male'] : $langArr['female'];
			$auth_gender_local = $tmp1.' / '.$tmp2;
			
		}
	}
}

// IP 자동판별 값이 KR(국내)인 사용자 중 본인인증 미진행한 경우(강제로 이 페이지로 접근한 경우) 로그인 페이지로이동
if ($ip_kor == 'KR' && $id_auth != 'Y') {
	fn_logSave( "Personal Identification Error : It's a wrong approach , File : " . $_SERVER['SCRIPT_FILENAME'] );
	header('Location:login.php');
	exit();
}

include_once 'includes/header.php';

?>

<div class="row login-bg">
<div class="col-md-4"></div>
<div id="page-" class="col-md-4">

	<form class="form loginform" method="POST" action="save_register_au.php" >
		<input type="hidden" name="id_auth" id="id_auth" value="<?php echo $id_auth; ?>" />
		<input type="hidden" name="tid" value="<?php echo $t_id; ?>" />
		<input type="hidden" name="phone" id="phone" value="<?php echo $auth_phone_no; ?>" />				
		<input type="hidden" name="phone_code" id="phone_code" value="" />

		<div class="login-panel panel panel-default">
        <div style="text-align: center;" class="logo"><img src="images/eth_logo.png" width='35%'/></div>
		<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
				</div>
				<?php } ?>
			<div class="panel-body">
										
				<p class="text1"><?php echo $langArr['register_text1']; ?></p>
				<p class="text2">
					<?php echo $langArr['register_text2']; ?><br /><?php echo $langArr['register_text3']; ?>
				</p>
				<p class="text3"><?php echo $langArr['register_text4']; ?></p>

				<div id="phone_t" class="input_p"><div class="auth_input"><?php echo $auth_phone_no; ?></div></div>
				<div class="input_n"><div class="auth_input"><?php echo $auth_name; ?></div></div>
				<div class="input_d"><div class="auth_input"><?php echo $auth_dob; ?></div></div>
				<div class="input_g"><div class="auth_input"><?php echo $auth_gender_local; ?></div></div>
				
				
				<div class="form-group">
					<label class="control-label"><?php echo !empty($langArr['password']) ? $langArr['password'] : "Password"; ?></label>
					<input type="password" id="psw"  pattern=".{6,}" title="Must contain at least 6 or more characters" name="passwd" class="input1 form-control" required="required" >
				</div>
				
				<div class="form-group">
					<label class="control-label"><?php echo !empty($langArr['confirm_password']) ? $langArr['confirm_password'] : "Confirm Password"; ?></label>
					<input type="password" id="confirm_psw"  pattern=".{6,}" title="Must contain at least 6 or more characters" name="cofirm_passwd" class="input1 form-control" required="required" >
				</div>
				<div id="message">
				  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
				 
				  <!--<p id="length" class="invalid"><?php echo !empty($langArr['minimum']) ? $langArr['minimum'] : "Minimum"; ?> <b><?php echo !empty($langArr['8']) ? $langArr['8'] : "8"; ?> <?php echo !empty($langArr['characters']) ? $langArr['characters'] : "characters"; ?></b></p>-->
				  <p id="length" class="invalid"><?php echo !empty($langArr['passwd_minimum_char']) ? $langArr['passwd_minimum_char'] : "Minimum 6 characters"; ?></p>
				</div>
				<div id="show_msg"></div>
								
				<button type="submit" class="btn btn-success loginField" ><?php echo !empty($langArr['sign_up']) ? $langArr['sign_up'] : "Sign Up"; ?></button>
				<a  href="login.php" class="loginField"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a>
			</div>
		</div>
	</form>
</div>
</div>



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
		fwrite($log_file, date("Y-m-d H:i:s ").$log."\r\n");
		fclose($log_file);
}
?>
			
<script>
var myInput = document.getElementById("psw");
var letter = document.getElementById("letter");
var capital = document.getElementById("capital");
var number = document.getElementById("number");
var length = document.getElementById("length");

// When the user clicks on the password field, show the message box
myInput.onfocus = function() {
  document.getElementById("message").style.display = "block";
}

// When the user clicks outside of the password field, hide the message box
myInput.onblur = function() {
  document.getElementById("message").style.display = "none";
}

// When the user starts to type something inside the password field
myInput.onkeyup = function() {
  // Validate lowercase letters
 /*  var lowerCaseLetters = /[a-z]/g;
  if(myInput.value.match(lowerCaseLetters)) {  
    letter.classList.remove("invalid");
    letter.classList.add("valid");
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
  }
  
  // Validate capital letters
  var upperCaseLetters = /[A-Z]/g;
  if(myInput.value.match(upperCaseLetters)) {  
    capital.classList.remove("invalid");
    capital.classList.add("valid");
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
  }

  // Validate numbers
  var numbers = /[0-9]/g;
  if(myInput.value.match(numbers)) {  
    number.classList.remove("invalid");
    number.classList.add("valid");
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
  } */
  
  // Validate length
  if(myInput.value.length >= 6) {
    length.classList.remove("invalid");
    length.classList.add("valid");
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
  }
}
</script>
<script>
/* $(window).load(function() {
		
document.getElementById("refer_code").value = localStorage.getItem("ref_code");
	}); */
    $(function () {
      /*   $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        }); */
		// initialCountry: "auto", -> kr
		$("#phone_t").intlTelInput({
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
			$("#loading-o").removeClass('none');
			var getpasslength = $("#psw").val();
			if(getpasslength.length<6){
				 $("#loading-o").addClass('none');
				 document.getElementById("message").style.display = "block";
				 return false;
			}
			var countryData = $("#phone_t").intlTelInput("getSelectedCountryData");
			var getPhoneVal = $("#phone").val();
			if(getPhoneVal!='') {
				$("#phone_code").val(countryData.dialCode);
			}
			 var getPassword = $("#psw").val();
			 var getConfirmPass = $("#confirm_psw").val();
			 if(getConfirmPass != getPassword){
				 $("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['password_and_confirm_password_should_be_match']) ? $langArr['password_and_confirm_password_should_be_match'] : "Password and Confirm Password should be match";  ?></div>').show();
				 setTimeout(function(){ $("#show_msg").hide(); }, 10000);
				 $("#loading-o").addClass('none');
				 return false;
			 }
			
		});

		
    });
</script>
<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>