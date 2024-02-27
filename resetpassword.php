<?php
//die("Registration close for public user");
session_start();
require_once './config/config.php';

include_once 'includes/header.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
	if(!isset($_GET['vcode'])){
		
    	header('location: login.php');
    	exit();
	}
	
	if(empty($_GET['vcode'])){
		
    	header('location: login.php');
    	exit();
	}
	
	$vCode = $_GET['vcode'];
	//Get DB instance. function is defined in config.php
    $db = getDbInstance();
    $db->where("vcode", $vCode);
    $row = $db->get('admin_accounts');
    //print_r($row); die;

    if ($db->count == 0) {
		header('location: login.php');
    	exit();
	}
	$emailVerify = $row[0]['email_verify'];
	if($emailVerify=='N') {
		header('location: login.php');
    	exit();
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data_to_store = filter_input_array(INPUT_POST);
	$getVode = $_POST['vcode'];
	if(empty($getVode)){
		$_SESSION['login_failure'] = $langArr['invalid_user'];
		header('Location:login.php');
		exit;
	}
	
	$db = getDbInstance();
	
    $db->where ("vcode", $getVode);
    $row = $db->get('admin_accounts');
	if($db->count == 0) {
		$_SESSION['login_failure'] = $langArr['invalid_user'];
		header('Location:login.php');
		exit;		
	}
	
	$pass = $data_to_store['password'];
	$confirmPass = $data_to_store['confirm_password'];
	
	if($pass!=$confirmPass){
		$_SESSION['login_failure'] = $langArr['pass_conf_pass_match'];
		header('location: resetpassword.php?vcode='.$getVode);
		exit;
	}
	
	
	$db = getDbInstance();
	$db->where("vcode", $getVode);
	$last_id = $db->update('admin_accounts', ['vcode'=>"",'passwd'=>md5($pass)]);	
		
	$_SESSION['success'] = $langArr['pass_update_success'];
	header('location: login.php');
	exit();
	
}
?>
<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<style>
/* Style all input fields */
input {
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

body {
	background: #ffcd07;
}

.row.login-bg {
	margin:0;
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

.tab-content {
	padding-top: 12px;
}

.login-panel {
    margin-top: 25%;
	margin-bottom: 10%;
}

</style>
<div class="row login-bg">
<div class="col-md-4"></div>
<div id="page-" class="col-md-4">

	<form class="form loginform" method="POST" >
	<input type="hidden" name="vcode" value="<?php echo $vCode; ?>" class="form-control">
		<div class="login-panel panel panel-default">
        <div style="text-align: center;" class="logo"><img src="images/logo3.png" width='35%'/></div>
			<div class="panel-body">
				
				
				<div class="form-group">
					<label class="control-label"><?php echo !empty($langArr['new_password']) ? $langArr['new_password'] : "New Password"; ?></label>
					<input type="password" id="psw"  pattern=".{6,}" title="Must contain at least 6 or more characters" name="password" class="form-control" required="required">
				</div>
				<div id="message">
				  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
				 
				  <!--<p id="length" class="invalid"><?php echo !empty($langArr['minimum']) ? $langArr['minimum'] : "Minimum"; ?> <b><?php echo !empty($langArr['8']) ? $langArr['8'] : "8"; ?> <?php echo !empty($langArr['characters']) ? $langArr['characters'] : "characters"; ?></b></p>-->
				  <p id="length" class="invalid"><?php echo !empty($langArr['passwd_minimum_char']) ? $langArr['passwd_minimum_char'] : "Minimum 6 characters"; ?></p>
				</div>
				<div class="form-group">
					<label class="control-label"><?php echo !empty($langArr['conf_password']) ? $langArr['conf_password'] : "Confirm Password"; ?> </label>
					<input type="password" pattern=".{6,}" title="Must contain at least 6 or more characters" name="confirm_password" class="form-control" required="required">
				</div>
				
				
				<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
				</div>
				<?php } ?>
				<button type="submit" class="btn btn-success loginField" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?></button>
				<a  href="login.php" class="loginField"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?> </a>
			</div>
		</div>
	</form>
</div>
</div>
	
<?php include_once 'includes/footer.php'; ?>

			
<script>



$(function () {
	$(".loginform").submit(function(){
		var getpasslength = $("#psw").val();
		if(getpasslength.length<6){
			 document.getElementById("message").style.display = "block";
			 return false;
		}
	})
});	
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
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>