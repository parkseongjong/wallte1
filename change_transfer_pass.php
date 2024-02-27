<?php
error_reporting("E_ALL");
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	$_POST['new_pass'] = trim($_POST['new_pass']);
	$_POST['conf_pass'] = trim($_POST['conf_pass']);
	if ( empty($_POST['new_pass']) || empty($_POST['conf_pass']) ) {
		$_SESSION['failure'] = !empty($langArr['change_transfer_pass_message1']) ? $langArr['change_transfer_pass_message1'] : "Please enter a password."; 
	} else if ($_POST['new_pass'] !== $_POST['conf_pass'] ) {
		$_SESSION['failure'] = !empty($langArr['login_fail_msg2']) ? $langArr['login_fail_msg2'] : "Passwords do not match."; 
	} else {

		if ( !empty($row[0]['transfer_passwd']) ) {
			
			$db = getDbInstance();
			$db->where("id", $_SESSION['user_id']);
			$db->where("transfer_passwd", md5($_POST['old_pass']));
			$row1 = $db->get('admin_accounts');
			if ( !empty($row1[0]['id'])) {
				$db = getDbInstance();
				$db->where("id", $_SESSION['user_id']);
				$db->where("transfer_passwd", md5($_POST['old_pass']));
				$updateArr = [] ;
				$updateArr['transfer_passwd'] =  md5($_POST['new_pass']);
				$last_id = $db->update('admin_accounts', $updateArr);
				if($last_id) {
					$_SESSION['success'] = $langArr['password_changed_successfully'];
					header('location: change_transfer_pass.php');
					exit();
				} else {
					$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred"; 
				}
			} else {
				$_SESSION['failure'] = !empty($langArr['profile_old_pass_wrong']) ? $langArr['profile_old_pass_wrong'] : "Old password do not match"; 
			}

		} else { // 값이 없으면.처음 셋팅시
			
			$db = getDbInstance();
			$db->where("id", $_SESSION['user_id']);
			$updateArr = [] ;
			$updateArr['transfer_passwd'] =  md5($_POST['new_pass']);
			$last_id = $db->update('admin_accounts', $updateArr);
			if($last_id) {
				$_SESSION['success'] = $langArr['password_changed_successfully'];
				header('location: change_transfer_pass.php');
				exit();
			} else {
				$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred"; 
			}

		}
	}
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
<style>
/* Style all input fields */
body {
	background-color: #ffcd07 !important;
}

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


#old_pass, #new_pass, #conf_pass {
	-webkit-text-security: disc;
	-moz-text-security: disc;
	text-security: disc;
}
</style>

<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-12">
			<h2 class="page-header"><?php echo !empty($langArr['change_transfer_pass']) ? $langArr['change_transfer_pass'] : "Set transmission password"; ?></h2>
		</div>
	</div>
	<?php include('./includes/flash_messages.php') ?>
	<ul class="nav nav-tabs">
		<li><a  href="profile.php"><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></a></li>
		<li><a href="change_pass.php" ><?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : "Change Password"; ?></a></li>
		<li  class="active"><a data-toggle="tab" ><?php echo !empty($langArr['change_transfer_pass']) ? $langArr['change_transfer_pass'] : "Set transmission password"; ?></a></li>
		
	</ul>
	<div class="tab-content" >
		<div class="col-md-3"></div>
			<div class="col-md-6 tab-pane fade in active" >
				<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
					<fieldset>
						<?php
						if ( !empty($row[0]['transfer_passwd']) ) { ?>
							<div class="form-group">
								<label for="f_name"><?php echo !empty($langArr['change_transfer_pass_text1']) ? $langArr['change_transfer_pass_text1'] : "Old Transfer Password"; ?> *</label>
								  <input type="number" name="old_pass" title="<?php echo $langArr['this_field_is_required']; ?>"  class="form-control" required="required" id = "old_pass" maxlength="4" >
							</div>
						<?php } ?>
						
						<div class="form-group">
							<label for="f_name"><?php echo !empty($langArr['new_password']) ? $langArr['new_password'] : "New Password"; ?> *</label>
							  <input type="number" name="new_pass" value="" pattern=".{4,}" title="Please enter in 4 digits"  class="form-control" required="required" id = "new_pass" maxlength="4" >
						</div> 
						<div id="message">
						  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
						  <p id="length" class="invalid"><?php echo !empty($langArr['transfer_pass_char']) ? $langArr['transfer_pass_char'] : "4 characters"; ?></p>
						</div>
						<div class="form-group">
							<label for="f_name"><?php echo !empty($langArr['confirm_password']) ? $langArr['confirm_password'] : "Confirm Password"; ?> *</label>
							  <input type="number" name="conf_pass" value=""  class="form-control" required="required" id = "conf_pass" maxlength="4" >
						</div> 

						<div class="form-group text-center">
							<label></label>
							<button type="submit" class="btn btn-warning" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?> <span class="glyphicon glyphicon-send"></span></button>
						</div>            
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
function pa_init(){
	for (var i = 0; i <3; i++){
		var x = document.getElementsByTagName("input")[i];
		var style = window.getComputedStyle(x);
		if(style.webkitTextSecurity){
			//do nothing
		}else{
			x.setAttribute("type","password");
		}
	}
}

$(document).ready(function(){
	pa_init();

   $("#customer_form").validate({
       rules: {
            new_pass: {
                required: true,
                minlength: 4
            },
            conf_pass: {
                required: true,
                minlength: 4
            },   
        }
    });
	
	
	var myInput = document.getElementById("new_pass");
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
		if(myInput.value.length == 4) {
			length.classList.remove("invalid");
			length.classList.add("valid");
		} else {
			length.classList.remove("valid");
			length.classList.add("invalid");
		}
	}


	$(".form").submit(function(){
		var new_pass = $("#new_pass").val();
		if(new_pass.length != 4) {
			 document.getElementById("message").style.display = "block";
			 return false;
		}
	})
});
</script>

<?php include_once 'includes/footer.php'; ?>
