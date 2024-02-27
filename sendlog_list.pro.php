<?php
session_start();
require_once './config/config.php';
/*require_once 'includes/auth_validate.php';

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:./index.php');
	 exit;
}

$return_url = 'sendlog_list.php';
*/
echo 'Start : '.date("Y-m-d H:i:s").'\n';
$db = getDbInstance();
$db->where('status', 'send');
//$db->where('id', '175');
$infos = $db->get('user_transactions_all');

foreach($infos as $row) {
	$status = '';
	$status_r = '';
	
	$status = check_eth_result($row['transactionId'], $ethApiKey); // 1 - success, 0 - faile, 값없음 - 조회안됨
	
	if ($status == '1') { // success
		$status_r = 'success';
	} else { // fail
		$status_r = 'fail';
	}
	//echo $row['transactionId'].' : '.$status_r.'<br />';

	$db = getDbInstance();
	$db->where("id", $row['id']);
	$updateArr = [] ;
	$updateArr['status'] =  $status_r;
	$last_id = $db->update('user_transactions_all', $updateArr);

} // foreach


echo 'Finish : '.date("Y-m-d H:i:s").'\n';

function check_eth_result($txhash, $ethApiKey) {
	$result = '';
	$eurl = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$txhash.'&apikey='.$ethApiKey; // status가 1인 경우에만 성공
		
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
	return $result;
}


?>
