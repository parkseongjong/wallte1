<?php
require_once './config/config.php';

session_start();
// send_token, send_other

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$mode = $_POST['mode'];

	switch($mode) {
		case 'get_name':
			$r_name = '';
			$waddr = $_POST['waddr'];
			$db = getDbInstance();
			$db->where ("wallet_address", $waddr);
			$userData = $db->get('admin_accounts');
			if ($db->count >= 1) {
				$name = $userData[0]['name']; // 이름
				$lname = $userData[0]['lname']; // 성
				$auth_name = $userData[0]['auth_name'];
			
				if ( !empty($auth_name) ) {
					$r_name = $auth_name;
				} else {
					if ( !empty($lname) ) {
						$r_name = $lname.$name;
					} else {
						$r_name = $name;
					}
				}
			}

			//$title = !empty($langArr['send_member_name']) ? $langArr['send_member_name'] : "Receiver";
			echo json_encode($r_name);
			//echo json_encode(array('result'=>$r_name));
			break;

		// token
		case 'get_token_history':
			
			$useragent=$_SERVER['HTTP_USER_AGENT'];
			$mobile=0;
			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
			{
				$mobile=1;
			}

			$wallertAddress = $_POST['waddr'];
			$tokenName = $_POST['token'];

			$curl = curl_init();
			$setContractAddr = $contractAddressArr[$tokenName]['contractAddress'];
			$decimalDivide = $contractAddressArr[$tokenName]['decimal'];
			if($tokenName!='eth') {
				$ethUrl = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$setContractAddr."&address=".$wallertAddress."&page=1&offset=10000&sort=desc&apikey=".$ethApiKey;
			}
			else {
				$ethUrl = "http://api.etherscan.io/api?module=account&action=txlist&address=".$wallertAddress."&sort=desc&apikey=".$ethApiKey;
			}
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $ethUrl,
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

			//$result = array();


			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			$getResultDecode = json_decode($response,true);
			$getRecords = $getResultDecode['result']; 

			if(!empty($getRecords)) {
				$preDate = "";
				$getTime = '';
				$cudate = date("M d, Y");
				foreach($getRecords as $getRecordSingle) {
					if($getRecordSingle['value'] <= 0 ){ continue; }
					//$txId = $getRecordSingle['hash'];
					$txId = '';
					$getDate = date("M d, Y",$getRecordSingle['timeStamp']);
					$getTime = ' ('.date("H:i:s", $getRecordSingle['timeStamp']).')';
					$amount = number_format((float)$getRecordSingle['value']/$decimalDivide,4);
					$type = ($getRecordSingle['from']==$wallertAddress) ? "send" : "receive";
					$sign = ($getRecordSingle['from']==$wallertAddress) ? "-" : "+";
					
					if ($type == 'receive') {
						$txId = $getRecordSingle['from'];
					} else {
						$txId = $getRecordSingle['to'];
					}
					
					// 이름 표시
					$name = '';
					$db = getDbInstance();
					$db->where ("wallet_address", $txId);
					$rowm = $db->get('admin_accounts');
					if ( !empty($rowm[0]['auth_name']) ) { // 본인인증 완료한 경우 실명 표시, Real name indication when self-certification is complete
						$name = $rowm[0]['auth_name'];
					} else if ( !empty($rowm[0]['name']) ) { // 사용자 입력한 이름, Show user-populated names
						$name = $rowm[0]['name'];
						if ( !empty($rowm[0]['lname']) ) {
							$name = $rowm[0]['lname'].$name;
						}
					}
					$name = $name != '' ? ' ('.$name.')' : '';

					$textLength = strlen($txId);
					$maxChars = 14;
					$txIdresult = substr_replace($txId, '...', $maxChars/2, $textLength-$maxChars);
					$txId = ($mobile==1) ? $txIdresult : $txId;
					?>

					 <div class="col-lg-12">
						<?php if($preDate!=$getDate) { ?>
							<h3><?php echo ($cudate==$getDate) ? "Today" : $getDate; ?></h3>
						<?php } ?>
						<ul class="send-receive">
							<li>
								<img src="images/tx_<?php echo $type; ?>.png">
							</li>
							<li class="srcode">
								<h6><?php echo ucfirst($type); ?></h6><span class="t1"><?php if ( !empty($getTime) ) { echo $getTime; } ?></span>
								<p><?php echo $txId; ?><?php echo $name; ?></p>
							</li>
							<li>
								<span><?php echo $sign.$amount." ".strtoupper($tokenName); ?></span>
							</li>
						</ul>					  
					</div>
					<?php
					$preDate=$getDate;
				}
			}
			//echo json_encode($result);

			break;
	} // switch
}
?>