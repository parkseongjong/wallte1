<?php



















/*
2020-06-01, YMJ

페이지가 변경되었습니다.
The page has been changed.

아래 적힌 페이지에서 수정해주시기 바랍니다.
Please correct it in the page written below.

/var/www/html/wallet2/profile.php

*/

























session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';


if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}

/* ============================================================================== */
/* =   PAGE : 인증 요청 PAGE                                                    = */
/* = -------------------------------------------------------------------------- = */
/* =   Copyright (c)  2012.02   KCP Inc.   All Rights Reserved.                 = */
/* ============================================================================== */

/* ============================================================================== */
/* =   환경 설정 파일 Include                                                   = */
/* = -------------------------------------------------------------------------- = */
include "./config/kcp_config.php";      // 환경설정 파일 include
/* = -------------------------------------------------------------------------- = */




$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

// 본인인증
$id_auth = '';
if ( !empty($row[0]['id_auth']) ) {
	$id_auth = $row[0]['id_auth'];
} else {
	$id_auth = 'N';
}


//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
    //Insert timestamp
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
    $db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$updateArr = [] ;
	if ($_POST['id_auth_t'] !='Y') {
		$updateArr['name'] =  $data_to_store['fname'];
		$updateArr['lname'] =  $data_to_store['lname'];
		$updateArr['gender'] =  $data_to_store['gender'];
		$updateArr['dob'] =  $data_to_store['dob'];
	}
	$updateArr['location'] =  $data_to_store['location'];
    $last_id = $db->update('admin_accounts', $updateArr);
    
    if($last_id)
    {
    	$_SESSION['success'] = $langArr['profile_updated_successfully'];
    	header('location: profile.php');
    	exit();
    }  
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
   <!-- MetisMenu CSS -->
        <link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
		 <script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 
<style>
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

.submit-button {
	width: 70%;
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
}

#show_pay_btn {
	margin-top: 10px;
	width: 100%;
	text-align: left;
}


.auth_fin {
	padding: 10px;
	overflow: hidden;
}
.auth_fin .input_p {
	width: 100%;
	margin-bottom: 30px;
}
.auth_fin .auth_input {
	width: 100%;
	height: 45px;
	padding-left: 17px;
	border: 0;
	font-size: 16px;
	color: #000000;
	background-color: #fafafa;
	line-height: 45px;
	position: relative;
}
.auth_fin .auth_input_right {
	font-size: 11px;
	color: #989898;
	position: absolute;
	top: 30%;
	right: 10px;
}

</style>
<div id="page-wrapper">
	<div class="row">
		     <div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
	<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="tab" href="#home"><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></a></li>
		<li><a  href="change_pass.php"><?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : "Change Password"; ?></a></li>
		<!--<li><a href="change_transfer_pass.php"><?php echo !empty($langArr['change_transfer_pass']) ? $langArr['change_transfer_pass'] : "Set transmission password"; ?></a></li>-->
		
	</ul>
	<div class="tab-content" >
		<div class="col-md-3"></div>
		<div class="col-md-6 tab-pane fade in active" >


			<?php

			// 본인인증 완료시 인증정보 보여줌
			if ($id_auth == 'Y') {
				$auth_gender_local = '';
				$tmp1 = $row[0]['auth_local_code'] == 'Kor' ? $langArr['korean'] : $langArr['foreigner'];
				$tmp2 = $row[0]['auth_gender'] == 'male' ? $langArr['male'] : $langArr['female'];
				$auth_gender_local = $tmp1.' / '.$tmp2;


				?>
				<div class="auth_fin">
					<div class="input_p"><div class="auth_input"><?php echo $row[0]['auth_phone']; ?></div></div>
					<div class="input_p"><div class="auth_input"><?php echo $row[0]['auth_name']; ?></div></div>
					<div class="input_p"><div class="auth_input"><?php echo $row[0]['auth_dob']; ?></div></div>
					<div class="input_p"><div class="auth_input"><?php echo $auth_gender_local; ?></div></div>
				</div>
			<?php } ?>

			<!-- 본인인증 -->
			<form method="post" name="form_auth">
				<input type="hidden" name="ordr_idxx" id="auth_ordr_idxx" class="frminput" value="" readonly="readonly" maxlength="40"/>
				<?php
				if ($id_auth != 'Y' ) {
				?>
					<div id="show_pay_btn">
						<input type="submit" id="id_auth_btn" class="btn btn-success" onclick="return auth_type_check();" value="<?php echo $langArr['personal_identification']; ?>" />
					</div>
				<?php } ?>

				<input type="hidden" name="req_tx" value="cert" /><!-- 요청종류 -->
				<input type="hidden" name="cert_method" value="01" /><!-- 요청구분 -->
				<input type="hidden" name="web_siteid"   value="<?= $g_conf_web_siteid ?>" /><!-- 웹사이트아이디 : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
				<!-- <input type="hidden" name="fix_commid" value="KTF"/>--><!-- 노출 통신사 default 처리시 아래의 주석을 해제하고 사용하십시요 - SKT : SKT , KT : KTF , LGU+ : LGT-->
				<input type="hidden" name="site_cd" value="<?= $g_conf_site_cd ?>" /><!-- 사이트코드 : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
				<input type="hidden" name="Ret_URL" value="<?= $g_conf_Ret_URL ?>" /><!-- Ret_URL : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
				<input type="hidden" name="cert_otp_use" value="Y" /><!-- cert_otp_use 필수 ( 메뉴얼 참고) - Y : 실명 확인 + OTP 점유 확인 , N : 실명 확인 only -->
				<input type="hidden" name="cert_enc_use" value="Y" /><!-- cert_enc_use 필수 (고정값 : 메뉴얼 참고) -->
				<input type="hidden" name="cert_enc_use_ext" value="Y" />      <!-- 리턴 암호화 고도화 -->          
				<input type="hidden" name="res_cd" value="" />
				<input type="hidden" name="res_msg" value="" />
				<input type="hidden" name="veri_up_hash" value="" /><!-- up_hash 검증 을 위한 필드 -->
				<input type="hidden" name="cert_able_yn" value="Y" /><!-- 본인확인 input 비활성화 -->
				<input type="hidden" name="web_siteid_hashYN" value="Y" /><!-- web_siteid 을 위한 필드 -->
				<input type="hidden" name="param_opt_1"  value="member" /> <!-- 가맹점 사용 필드 (인증완료시 리턴), member.pro.res-->
				<input type="hidden" name="param_opt_2"  value="<?php if ( !empty($_SESSION['admin_type']) ) { echo $_SESSION['admin_type']; } ?>" /> 
				<input type="hidden" name="param_opt_3"  value="<?php if ( !empty($_SESSION['user_id']) ) { echo $_SESSION['user_id']; } ?>" /> 
			</form>
			
			<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
				<input type="hidden" name="id_auth_t" value="<?php echo $id_auth; ?>" />

				<?php
				if ( !empty($_SESSION['user_id']) && $_SESSION['user_id'] == '6135') { ?>
				<div style="margin-top:10px;">
					<div id="pvt_btn" class="btn btn-success"><?php echo $langArr['show_private_key'] ?></div>
					<div id="pvt_resp" style="word-break:break-all;"></div>
				</div>
				<?php } ?>
				<fieldset>
					<?php if ($id_auth != 'Y') { ?>
						<div class="form-group">
							<label for="f_name"><?php echo !empty($langArr['first_name']) ? $langArr['first_name'] : "First Name"; ?></label>
							  <input type="text" name="fname" value="<?php echo $row[0]['name']; ?>" placeholder="<?php echo !empty($langArr['first_name']) ? $langArr['first_name'] : "First Name"; ?>" class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "fname">
						</div> 
						<div class="form-group">
							<label for="f_name"><?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?></label>
							  <input type="text" name="lname" value="<?php echo $row[0]['lname']; ?>" placeholder="<?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?>" class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "lname">
						</div> 
						<div class="form-group">
							<label for="f_name"><?php echo !empty($langArr['gender']) ? $langArr['gender'] : "Gender"; ?></label>
							  <select class="form-control" id="gender" name="gender">
							  <option  value=""><?php echo !empty($langArr['select']) ? $langArr['select'] : "Select"; ?></option>
							  <option <?php echo ($row[0]['gender']=="male") ? "Selected" : ""; ?> value="male"><?php echo !empty($langArr['male']) ? $langArr['male'] : "Male"; ?></option>
							  <option <?php echo ($row[0]['gender']=="female") ? "Selected" : ""; ?> value="female"><?php echo !empty($langArr['female']) ? $langArr['female'] : "Female"; ?></option>
							  <option <?php echo ($row[0]['gender']=="other") ? "Selected" : ""; ?> value="other"><?php echo !empty($langArr['other']) ? $langArr['other'] : "Other"; ?></option>
							 </select>
						</div> 
						<div class="form-group">
							<label for="f_name"><?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?></label>
							  <input type="text" name="dob" readonly value="<?php echo $row[0]['dob']; ?>" placeholder="<?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?>" class="form-control" title="<?php echo $langArr['this_field_is_required']; ?>" required="required" id = "dob">
						</div> 
					<?php } ?>
					<div class="form-group">
						<label for="f_name"><?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?></label>
						  <input type="text" name="location" value="<?php echo $row[0]['location']; ?>" placeholder="<?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?>" title="<?php echo $langArr['this_field_is_required']; ?>" class="form-control" required="required" id = "location">
					</div> 
					 <!-- <select class="form-control">
					  <option>KYC</option>
					  <option>PAN NO</option>
					  <option>BANK A/C NO</option>
					  <option>IFSC CODE</option>
					  <option>BANK NAME</option>
					 </select> -->
					 <br/>
					<div class="form-group text-center">
						<label></label>
						<button type="submit" class="btn btn-warning submit-button" ><?php echo !empty($langArr['update']) ? $langArr['update'] : "Update"; ?> <span class="glyphicon glyphicon-send"></span></button>
					</div>            
				</fieldset>
			</form>
		</div>
	</div>
</div>
<iframe id="kcp_cert" name="kcp_cert" width="100%" height="700" frameborder="0" scrolling="no" style="display: none;"></iframe>

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
	$('#dob').datepicker({format: "yyyy/mm/dd"});
	
	
	$("#pvt_btn").click(function(){
		$.ajax({
			beforeSend:function(){
				$("#pvt_resp").html('<img src="images/ajax-loader.gif" />');
			},
			url : 'showpvt.php',
			type : 'POST',
			//dataType : 'json',
			success : function(resp){
				$("#pvt_resp").html(resp);
			},
			error : function(resp){
				$("#pvt_resp").html(resp);
			}
		}) 
	 }); 


	// 본인인증 추가
	init_orderid();
});



// 본인인증 추가
// MOBILE(SMART)
function auth_type_check() {
	var auth_form = document.form_auth;
	
	if (auth_form.ordr_idxx.value == '')
	{
		//alert( "요청번호는 필수 입니다." );
		return false;
	}
	else
	{
		
		if( navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1 || navigator.userAgent.indexOf("android-web-view") > - 1 )
		{
			auth_form.target = "kcp_cert";
			
			document.getElementById( "page-wrapper" ).style.display = "none";
			document.getElementById( "kcp_cert"  ).style.display = "";
			$("#wrapper nav").css('display', 'none');
		}
		else
		{
			var return_gubun;
			var width  = 410;
			var height = 500;

			var leftpos = screen.width  / 2 - ( width  / 2 );
			var toppos  = screen.height / 2 - ( height / 2 );

			var winopts  = "width=" + width   + ", height=" + height + ", toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
			var position = ",left=" + leftpos + ", top="    + toppos;
			var AUTH_POP = window.open('','auth_popup', winopts + position);
			
			auth_form.target = "auth_popup";
		}

		auth_form.action = "./auth.pro.req.php"; // 인증창 호출 및 결과값 리턴 페이지 주소
		
		return true;
	}
}

// 본인인증 : 요청번호 생성 예제 ( up_hash 생성시 필요 ) 
function init_orderid()
{
	var today = new Date();
	var year  = today.getFullYear();
	var month = today.getMonth()+ 1;
	var date  = today.getDate();
	var time  = today.getTime();

	if (parseInt(month) < 10)
	{
		month = "0" + month;
	}

	var vOrderID = year + "" + month + "" + date + "" + time;
	document.form_auth.ordr_idxx.value = vOrderID;
}

</script>


<?php include_once 'includes/footer.php'; ?>