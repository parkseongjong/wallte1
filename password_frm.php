<?php 
session_start();
require_once './config/config.php';

$password_length = 6;
?>
<link  rel="stylesheet" href="css/send.css"/>
<!--<script src="js/jquery.min.js" type="text/javascript"></script> -->
<style>

#password_frm .top {
	background-color: #FFEB61;
	height: 50%;
}

#password_frm .subject {
	color: #000000;
	font-size: 2.23rem;
}

#password_frm .explain {
	color: #000000;
	font-size: 1.5rem;
	margin-top: 17.3px;
}

#password_frm .password_area {
	margin-top: 52.5px;
}
#password_frm .password_area span img {
	width: 25px;
	height: auto;
}
#password_frm .password_area span {
	display: inline-block;
	margin-left: 10.8px;
}
#password_frm .password_area span:nth-child(1) {
	margin-left: 0px !important;
}


#password_frm .number {
	height: 50%;
}
#password_frm .number span {
	width: 33%;
	display: inline-block;
	text-align: center;
	color: #000000;
	font-size: 3.25rem;
	box-sizing: border-box;
	height: 24%;
	border: 1px solid #000;
	cursor: pointer;
}
#password_frm .number span img {
	width: 31.3px;
	height: auto;

}
</style>

<script src="js/jquery.min.js" type="text/javascript"></script> 


<div id="password_frm">
	<div class="top">
		<p class="subject"><?php echo !empty($langArr['password_frm_text1']) ? $langArr['password_frm_text1'] : 'Set payment password'; ?></p>
		<p id="explain1" class="explain"><?php echo !empty($langArr['password_frm_text2']) ? $langArr['password_frm_text2'] : 'Please set your '; ?><?php echo $password_length; ?><?php echo !empty($langArr['password_frm_text3']) ? $langArr['password_frm_text3'] : ' digit payment password.'; ?></p>
		<p id="explain2" class="explain"><?php echo !empty($langArr['password_frm_text4']) ? $langArr['password_frm_text4'] : 'Please enter again to confirm.'; ?></p>
		<div class="password_area">
			<?php
			for($i = 0; $i < $password_length; $i++) {
				?><span id="pass_area_<?php echo $i; ?>"><img src="images/icons/pass_input_n.png" alt="password" /></span><?
			} // foreach
			?>
		</div>
	</div>
	<div class="number">
		<?php
		$num_arr = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
		foreach ($num_arr as $k1=>$v1) {
			?><span id="pass_number_<?php echo $v1;?>" data-num="<?php echo $v1;?>"><?php echo $v1; ?></span><?php
		} // foreach
		?><span data-num="">&nbsp;</span><span id="pass_number_0" data-num="0">0</span><span id="pass_number_del" data-num="del"><img src="images/icons/pass_input_del.png" alt="delete" /></span>
	</div>
</div>

<script>
var pass = '';
var pass_length = 0;
$(function(){
	$("#password_frm .number span").on('click tap', function(){
		var num = $(this).attr('data-num');
		if (num == 'del') { // 삭제
			if ( pass_length > 0 ) {
				if (pass_length == 1) {
					pass_length = 0;
					pass = '';
					$("#pas1").val(pass);
					$("#pass_area_"+pass_length+" img").attr('src','images/icons/pass_input_n.png');
				} else {
					pass_length = pass_length - 1;
					pass = pass.substr(0, pass_length);
					$("#pas1").val(pass);
					$("#pass_area_"+pass_length+" img").attr('src','images/icons/pass_input_n.png');
				}
			}
		} else if (num != '' && pass_length < 6) { // 0~9
			pass = pass + num;
			$("#pas1").val(pass);
			$("#pass_area_"+pass_length+" img").attr('src','images/icons/pass_input_y.png');
			pass_length = pass_length + 1;
			if (pass_length == 6) {
				
				document.pass_frm.submit();
				if ( $("#pas2").val() == '') { // 값이 DB와 일치하는지 확인


				} else { // // pas1과 pas2 일치하는지 확인
					if ( pass == $("#pass2").val() ) {
						// 성공
					} else { // 불일치. 
						// 다시 처음부터 입력할것
					}
				}
			}
		}
		return false; // 브라우저에 따라서 중복실행하는 경우 방지
	});
});
</script>
<form method="post" name="pass_frm">
	<input type="text" name="uid" id="uid" value="<?php echo !empty($_POST['uid']) ? $_POST['uid'] : ''; ?>" />
	<input type="text" name="pas1" id="pas1" value="" />
	<input type="text" name="pas2" id="pas2" value="<?php echo !empty($_POST['pas1']) ? $_POST['pas1'] : ''; ?>" /><!-- 넘어온 값 -->
</form>
