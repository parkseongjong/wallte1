<?php
// IP, �����ڵ� üũ test��. ��������
return;
exit;
// https://ipinfo.io/ - free version TEST
//error_reporting(0);

function kisa_ip_chk(){
	// https://������˻�.�ѱ�/kor/openkey/keyCre.do
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

function ipinfo_ip_chk() { // ���� üũ �׽�Ʈ��. whois ��� ��� �������� check (2020.05.14, YMJ)
	// https://ipinfo.io/
	//$access_token = 'd5b65ce795f734'; // ���� version key (50,000��)
	$access_token = '7c984c718aef66'; // ���� version key (50,000��)
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

//echo 'WHOIS OpenAPI : '.kisa_ip_chk().'<br />';

//$ip_kor = trim(ipinfo_ip_chk());
//echo 'IPinfo : /'.$ip_kor.'/<br />';


/*

{ "ip": "109.169.23.83", "city": "Maidenhead", "region": "England", "country": "GB", "loc": "51.5228,-0.7199", "org": "AS20860 IOMART CLOUD SERVICES LIMITED", "postal": "SL6", "timezone": "Europe/London" }
{ "ip": "178.162.205.226", "city": "Frankfurt am Main", "region": "Hesse", "country": "DE", "loc": "50.1025,8.6299", "org": "AS28753 Leaseweb Deutschland GmbH", "postal": "60326", "timezone": "Europe/Berlin" }

*/
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

//////////////////////////////////


// etherscan api
$ethApiKey = 'ehtkey';

$tx = '0x1f5c5172ec0407f02661f864c6e0c216d86ac9fe7d679c47544e023d7aeee8e3'; // ��ȸ�Ұ�
// getstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 0 [errDescription] => ) ) 
// gettxreceiptstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [status] => ) ) 
// proxy(eth_getTransactionByHash) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => ) 
// proxy(eth_getTransactionReceipt) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => ) 


//$tx = '0x09b100b01144c0fd1ceb1f5afca9043d8e5f2d61965b234f9dbd3b5cfb83312a'; // fail
// getstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 1 [errDescription] => Reverted ) ) 
// gettxreceiptstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [status] => 0 ) ) 
// proxy(eth_getTransactionByHash) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => Array ( [blockHash] => 0x42e15dd6f02fbb7b382b0dd30fa614e13907ce961a71478ade53426a92074a38 [blockNumber] => 0x97dbf3 [from] => 0xcea66e2f92e8511765bc1e2a247c352a7c84e895 [gas] => 0x186a0 [gasPrice] => 0x6fc23ac00 [hash] => 0x09b100b01144c0fd1ceb1f5afca9043d8e5f2d61965b234f9dbd3b5cfb83312a [input] => 0x23b872dd000000000000000000000000f4a587c23316691f8798cf08e3b541551ec1ffcb00000000000000000000000006978f9023a79138376b722db285da08bd068ad3000000000000000000000000000000000000000000000000016345785d8a0000 [nonce] => 0x3af2 [to] => address [transactionIndex] => 0x26 [value] => 0x0 [v] => 0x25 [r] => 0x762c2ae26e6c16c57e70b5784cc19581b03f94c6591320d84378b3fab2aefdec [s] => 0x75be12fc409129996ff52e51688f77e7126b5ad6b601ca21470f7a638c2157d7 ) ) 
// proxy(eth_getTransactionReceipt) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => Array ( [blockHash] => 0x42e15dd6f02fbb7b382b0dd30fa614e13907ce961a71478ade53426a92074a38 [blockNumber] => 0x97dbf3 [contractAddress] => [cumulativeGasUsed] => 0x148e0c [from] => 0xcea66e2f92e8511765bc1e2a247c352a7c84e895 [gasUsed] => 0x933d [logs] => Array ( ) [logsBloom] => 0x00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000 [status] => 0x0 [to] => address [transactionHash] => 0x09b100b01144c0fd1ceb1f5afca9043d8e5f2d61965b234f9dbd3b5cfb83312a [transactionIndex] => 0x26 ) ) 


//$tx = '0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e'; // success
// getstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 0 [errDescription] => ) ) 
// gettxreceiptstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [status] => 1 ) ) 
// proxy(eth_getTransactionByHash) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => Array ( [blockHash] => 0x2f447d0c4bf4ca096b752f5ad45ac80dc0199991011d2341be7effed753beccd [blockNumber] => 0x97de8c [from] => 0xcea66e2f92e8511765bc1e2a247c352a7c84e895 [gas] => 0x186a0 [gasPrice] => 0x6fc23ac00 [hash] => 0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e [input] => 0x23b872dd000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d0000000000000000000000000000000000000000000003cfc82e37e9a7400000 [nonce] => 0x3afa [to] => address [transactionIndex] => 0x19 [value] => 0x0 [v] => 0x26 [r] => 0x45f916d6d67037bf2ce2b37ef0954106cb9238b3501a6276fa7bf3220c58a920 [s] => 0x3f7755dc96c079acb0f89fd03d8ee059fbb0488e30ff8a2733aecfccd2f6cce6 ) ) 
// proxy(eth_getTransactionReceipt) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => Array ( [blockHash] => 0x2f447d0c4bf4ca096b752f5ad45ac80dc0199991011d2341be7effed753beccd [blockNumber] => 0x97de8c [contractAddress] => [cumulativeGasUsed] => 0x127b57 [from] => 0xcea66e2f92e8511765bc1e2a247c352a7c84e895 [gasUsed] => 0x8e7b [logs] => Array ( [0] => Array ( [address] => address [topics] => Array ( [0] => 0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef [1] => 0x000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d [2] => 0x000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d ) [data] => 0x0000000000000000000000000000000000000000000003cfc82e37e9a7400000 [blockNumber] => 0x97de8c [transactionHash] => 0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e [transactionIndex] => 0x19 [blockHash] => 0x2f447d0c4bf4ca096b752f5ad45ac80dc0199991011d2341be7effed753beccd [logIndex] => 0x15 [removed] => ) [1] => Array ( [address] => address [topics] => Array ( [0] => 0x8c5be1e5ebec7d5bd14f71427d1e84f3dd0314c0f7b2291e5b200ac8c7c3b925 [1] => 0x000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d [2] => 0x000000000000000000000000cea66e2f92e8511765bc1e2a247c352a7c84e895 ) [data] => 0x00000000000000000000000000000000000000001027e0179ff5669fe7e80000 [blockNumber] => 0x97de8c [transactionHash] => 0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e [transactionIndex] => 0x19 [blockHash] => 0x2f447d0c4bf4ca096b752f5ad45ac80dc0199991011d2341be7effed753beccd [logIndex] => 0x16 [removed] => ) ) [logsBloom] => 0x00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000002000000280000000000004000000000000008010000000000000000000000000000001000000000000000000000000000000000800000000000000000000000000010000000000000000000000000000000000000000000000010000000000000000000000000020000000000000000000000000000000000000000000000000000000000000000000002000000000000000000000100000000000000000000000000000000000010000000002000000000000000000000000000000000000000000000000000 [status] => 0x1 [to] => address [transactionHash] => 0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e [transactionIndex] => 0x19 ) ) 

//$eurl = 'https://api.etherscan.io/api?module=transaction&action=getstatus&txhash='.$tx.'&apikey='.$ethApiKey;
$eurl = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$tx.'&apikey='.$ethApiKey; // status�� 1�� ��쿡�� ����
//$eurl = 'https://api.etherscan.io/api?module=proxy&action=eth_getTransactionByHash&txhash='.$tx.'&apikey='.$ethApiKey;
//$eurl = 'https://api.etherscan.io/api?module=proxy&action=eth_getTransactionReceipt&txhash='.$tx.'&apikey='.$ethApiKey;

//https://api.etherscan.io/api?module=transaction&action=getstatus&txhash=0x1f5c5172ec0407f02661f864c6e0c216d86ac9fe7d679c47544e023d7aeee8e3&apikey=ehtkey

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

$tx = '0x1f5c5172ec0407f02661f864c6e0c216d86ac9fe7d679c47544e023d7aeee8e3'; // ��ȸ�Ұ�
echo $tx.' : '.check_eth_result($tx, $ethApiKey);
echo '<br />';
$tx = '0x09b100b01144c0fd1ceb1f5afca9043d8e5f2d61965b234f9dbd3b5cfb83312a'; // fail
echo $tx.' : '.check_eth_result($tx, $ethApiKey);
echo '<br />';
$tx = '0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e'; // success
echo $tx.' : '.check_eth_result($tx, $ethApiKey);

?>