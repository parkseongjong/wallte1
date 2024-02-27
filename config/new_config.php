<?php
// �׽�Ʈ��. ���� ��

$n_wallet_pass_key = 'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';
$n_master_email = 'michael@cybertronchain.com';
$n_master_id = 45;
$n_master_wallet_address = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$n_master_wallet_pass = $n_master_email.$n_wallet_pass_key;

// tongkni.co.kr
//$n_connect_ip= '125.141.133.23';
$n_connect_ip= '3.34.253.74';
$n_connect_port = 8545;

// SMS �߼۽� ������ ��ȭ��ȣ. Phone number when sending SMS
$n_sms_from_tel = '0234893237';

// SMS �߼� Ű
$n_api_key = '1234';
$n_api_secret = '1234';

// ������ ���� �� �� ���� ��ٷ��� ������ �� �ִ��� ����. Set how long to wait after sending the last transmission
$n_send_re_time = 3; // 3��

/*


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


// IP Ȯ��
// return : IP Address
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

// WHOIS OpenAPI
// return : �����ڵ�(KR, DE, ...)
function kisa_ip_chk()
{
	$ip = getUserIpAddr();
	$key = "2020032517154809084222";
	$url ="http://whois.kisa.or.kr/openapi/ipascc.jsp?query=".$ip."&key=".$key."&answer=json";
	$ch = curl_init();

	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_NOSIGNAL, 1);
	//curl_setopt($ch,CURLOPT_POST, 1); //Method�� POST. ������ GET
	$data = curl_exec($ch);
	$curl_errno = curl_errno($ch);
	$curl_error = curl_error($ch);
	curl_close($ch);
	$decodeJsonData = json_decode($data, true);
	return $decodeJsonData['whois']['countryCode'];
}

// ipinfo.io
// return : �����ڵ�(KR, DE, ...)
function ipinfo_ip_chk($key)
{
	// https://ipinfo.io/
	if ($key == '1') {
		$access_token = 'd5b65ce795f734'; // ���� version key (50,000��)
	} else {
		$access_token = '7c984c718aef66'; // ���� version key (50,000��)
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
	return $country; // ���� : KR
}


// ���� �߻���(try-catch ��) ���Ͽ� ����
//		$log : �޼���(message)
function fn_logSave($log)
{
	$logPathDir = "/var/www/html/wallet/_log";  //�α���ġ ����

	$filePath = $logPathDir."/".date("Y")."/".date("n");
	$folderName1 = date("Y"); //���� 1 �⵵ ����
	$folderName2 = date("n"); //���� 2 �� ����

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


// sendlog_list.pro
function check_eth_result($txhash, $ethApiKey) {
	$result = '';
	$eurl = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$txhash.'&apikey='.$ethApiKey; // status�� 1�� ��쿡�� ����
		
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


*/


?>