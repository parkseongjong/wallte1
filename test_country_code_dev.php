<?php
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';

// 국가코드 분리용 TEST. 사용완료. 삭제가능
return;
exit;


$db = getDbInstance();
$db->where("register_with",'phone');

$page = 1;
//$pagelimit = 20;
$select = array('id', 'register_with', 'email', 'phone', 'auth_phone');

//$db->orderBy('id', 'asc');
//$db->pageLimit = $pagelimit;

//$result = $db->arraybuilder()->paginate("admin_accounts", $page, $select);
$result = $db->get('admin_accounts');

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:index.php');
}

foreach ($result as $row) {
	$country = '';
	$phone = '';
	$email_id = '';
	
	$email_id = $row['email'];

	if ( !empty($row['auth_phone']) ) { // 인증을 한 회원이라면
		$country = '82';
		$phone = $row['auth_phone'];
	} else { // 인증 안한 회원
		$phone = $row['email'];
		if(stristr($email_id, 'undefined') == TRUE) {
			$country = '82';
			$phone = str_replace('+undefined', '', $phone);
			$phone = str_replace('~', '', $phone);
			$phone = str_replace('+', '', $phone);

		} else if(stristr($email_id, '+8210') == TRUE) {
			$country = '82';
			$phone = str_replace('-', '', $phone);
			$phone = str_replace(' ', '', $phone);
			$phone = str_replace('+8210', '010', $phone);
			$phone = str_replace('+82', '', $phone);
			$phone = str_replace('x', '', $phone);
			$phone = str_replace('+', '', $phone);
			$phone = str_replace('~', '', $phone);


		} else if(stristr($email_id, '+82010') == TRUE) {
			$country = '82';
			$phone = str_replace('-', '', $phone);
			$phone = str_replace(' ', '', $phone);
			$phone = str_replace('+82', '', $phone);
			$phone = str_replace('+', '', $phone);

		} else if(stristr($email_id, '+82,010') == TRUE) {
			$country = '82';
			$phone = str_replace('+82,', '', $phone);
			$phone = str_replace(' ', '', $phone);
			
		} else if(stristr($email_id, '+82t010') == TRUE) {
			$country = '82';
			$phone = str_replace('+82t', '', $phone);
			$phone = str_replace('@gmail.com', '', $phone);
			
		} else if(stristr($email_id, '+82-10') == TRUE) {
			$country = '82';
			$phone = str_replace('+82-10', '010', $phone);
			$phone = str_replace('-', '', $phone);
			
		} else if(stristr($email_id, '+82') == TRUE) {
			$country = '82';
			$phone = str_replace('+82', '', $phone);
			$phone = str_replace('+', '', $phone);

		} else if(stristr($email_id, '+91') == TRUE) {
			$country = '91';
			$phone = str_replace('+91', '', $phone);

		} else if(stristr($email_id, '+90') == TRUE) {
			$country = '90';
			$phone = str_replace('+90', '', $phone);

		} else if(stristr($email_id, '+880') == TRUE) {
			$country = '880';
			$phone = str_replace('+880', '', $phone);

		} else if(stristr($email_id, '+86') == TRUE) {
			$country = '86';
			$phone = str_replace('+86', '', $phone);

		} else if(stristr($email_id, '+84') == TRUE) {
			$country = '84';
			$phone = str_replace('+84', '', $phone);

		} else if(stristr($email_id, '+81') == TRUE) {
			$country = '81';
			$phone = str_replace('+81', '', $phone);

		} else if(stristr($email_id, '+65') == TRUE) {
			$country = '65';
			$phone = str_replace('+65', '', $phone);

		} else if(stristr($email_id, '+63') == TRUE) {
			$country = '63';
			$phone = str_replace('+63', '', $phone);

		} else if(stristr($email_id, '+62') == TRUE) {
			$country = '62';
			$phone = str_replace('+62', '', $phone);

		} else if(stristr($email_id, '+60') == TRUE) {
			$country = '60';
			$phone = str_replace('+60', '', $phone);

		} else if(stristr($email_id, '+55') == TRUE) {
			$country = '55';
			$phone = str_replace('+55', '', $phone);

		} else if(stristr($email_id, '+49') == TRUE) {
			$country = '49';
			$phone = str_replace('+49', '', $phone);

		} else if(stristr($email_id, '+48') == TRUE) {
			$country = '48';
			$phone = str_replace('+48', '', $phone);

		} else if(stristr($email_id, '+34') == TRUE) {
			$country = '34';
			$phone = str_replace('+34', '', $phone);

		} else if(stristr($email_id, '+225') == TRUE) {
			$country = '225';
			$phone = str_replace('+225', '', $phone);

		} else if(stristr($email_id, '+216') == TRUE) {
			$country = '216';
			$phone = str_replace('+216', '', $phone);

		} else if(stristr($email_id, '+20') == TRUE) {
			$country = '20';
			$phone = str_replace('+20', '', $phone);

		} else if(stristr($email_id, '+1') == TRUE) {
			$country = '1';
			$phone = str_replace('+1', '', $phone);
		}
	} // if

	echo $row['email'].'	'.$country.'	'.$phone.'<br />';

	
	$db = getDbInstance();
	$db->where("id", $row['id']);
	$updateArr = [] ;
	$updateArr['n_country'] =  $country;
	$updateArr['n_phone'] =  $phone;
	$last_id = $db->update('admin_accounts', $updateArr);


} // foreach
?>
