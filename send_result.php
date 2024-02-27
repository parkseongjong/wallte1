<?php
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

// 전송 결과 화면

use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

require_once 'includes/header.php'; 
	
$send_result = '';
if ($_SERVER['REQUEST_METHOD'] == 'GET' )	{
	$txid = !empty($_GET['txid']) ? $_GET['txid'] : '';
	$type = !empty($_GET['type']) ? $_GET['type'] : ''; // send

	if ( empty($txid) ) { // 전송실패
		$send_result = 'F';
	} else {
		$db = getDbInstance();
		$db->where("id", $txid);
		$row = $db->get('user_transactions_all');

		if ( empty($row[0]['transactionId']) ) { // 전송 실패
			$send_result = 'F';
		} else {
			if ($row[0]['send_sms'] == 'Y') { // 이미 발송함
				$send_result = 'Y';
			}
			$amount = $row[0]['amount'];
			$coin_type = strtoupper($row[0]['coin_type']);
			$link = 'https://etherscan.io/tx/'.$row[0]['transactionId'];

			// send시
			$db = getDbInstance();
			$db->where("wallet_address", $row[0]['to_address']);
			$to_row = $db->get('admin_accounts');
			if ( !empty($to_row[0]['auth_name']) ) {
				$to_name = $to_row[0]['auth_name'];
			} else {
				$to_name = $to_row[0]['name'];
			}
			
			$db = getDbInstance();
			$db->where("id", $row[0]['from_id']);
			$from_row = $db->get('admin_accounts');
			if ( !empty($from_row[0]['auth_name']) ) {
				$from_name = $from_row[0]['auth_name'];
			} else {
				$from_name = $from_row[0]['name'];
			}

			if ( $_SESSION['lang'] == 'ko' ) {
				$view_msg = $to_name.'님께 '.number_format($amount, 4).' '.$coin_type.' 전송하였습니다.';
			} else {
				$view_msg = 'Sent '.number_format($amount, 4).' '.$coin_type.' to '.$to_name;
			}

			$send_sms_message4 = $langArr['send_sms_message4'];
			$thanks = $langArr['thanks'];
			$send_sms_message3 = $langArr['send_sms_message3'];
			$alert_msg = '';
			$alert_msg = $from_name.$langArr['send_sms_message1'].$amount.$coin_type;
			if ( $langArr['send_sms_message2'] != '' ) {
				$alert_msg .= $langArr['send_sms_message2'];
			}
			$date = date("Y-m-d");

			
			if ( $send_result != 'Y') {
				if ($to_row[0]['register_with'] == 'email') {
					$email_address = $to_row[0]['email'];
					
					if ( $to_row[0]['email_verify'] == 'Y') {

						$mailHtml = '<table align="center" width="600"  style=" background:#fff; ">
							<tbody>
								<tr align="center" > 
									<td><img src="http://'.$_SERVER['HTTP_HOST'].'/wallet/images/logo3.png" /></td>
								</tr>
								<tr align="center">
									<td><p style="padding:0 3%; line-height:25px; text-align: justify;">'.$alert_msg.'</p></td>
								</tr>
								<tr>
									<td align="center";><div style=" font-weight:bold; padding: 12px 35px; color: #fff; border-radius:5px; text-align:center; font-size: 14px; margin: 10px 0 20px; background: #ec552b; display: inline-block; text-decoration: none;">'.$send_sms_message4.'<a href="'.$link.'">'.$link.'</a></div></td>
								</tr>
								<tr align="center">
									<td><p style="padding:0 3%; line-height:25px; text-align: justify; margin:0px;">'.$thanks.' <br/>Team Support</p></td>
								</tr>
							</tbody>
						</table>
						<table style="color:#b7bbc1;width:600px;">
							<tr><td style="text-align: center;"><h4>©'.$date.' All right reserved</h4></td></tr>
						</table>
						';
						
						require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)
						
						$email = new \SendGrid\Mail\Mail();
						$email->setFrom("michael@cybertronchain.com", "CyberTron Coin");
						$email->setSubject($send_sms_message3);
						$email->addTo($email_address);
						
						$email->addContent("text/html", $mailHtml);
						
						$sendgrid = new \SendGrid('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');
						
						/*try {
							$response = $sendgrid->send($email);

							$db = getDbInstance();
							$db->where("id", $row[0]['id']);
							$updateArr = [] ;
							$updateArr['send_sms'] =  'Y';
							$last_id = $db->update('user_transactions_all', $updateArr);

						} catch (Exception $e) {
							//echo 'Caught exception: '.  $e->getMessage(). "\n";
						}*/

					} // 

				} else { // phone
					$country = $to_row[0]['n_country'];
					$phone = $to_row[0]['n_phone'];
					/*
					if ( !empty($country) && !empty($phone) ) {
						try {
							$rest = new Message($n_api_key, $n_api_secret);

							$options = new stdClass();
							$options->to = $phone; // 수신번호
							$options->from = $n_sms_from_tel; // 발신번호
							
							$options->country = $country;
							$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
							$options->text = $alert_msg; // 문자내용

							$result = $rest->send($options);     

							if($result->success_count == '1')
							{								
								$db = getDbInstance();
								$db->where("id", $row[0]['id']);
								$updateArr = [] ;
								$updateArr['send_sms'] =  'Y';
								$last_id = $db->update('user_transactions_all', $updateArr);
							}

						} catch(CoolsmsException $e) {
						}
					} else {
						$send_result = 'F';
					}*/
					
				}
			} // if ($send_result)

		}

	} // if

}

?>

<link  rel="stylesheet" href="css/send.css"/>
</head>

<body>

<div id="page-" class="col-md-4 col-md-offset-4">
	<div id="send_result">
		<?php
		if ( $send_result == 'F') { // 전송 실패
		?>
			<div class="text1">
				<?php echo !empty($langArr['send_sms_message7']) ? $langArr['send_sms_message7'] : 'Cannot be transferred.<br />Please try again in a few minutes.'; ?>
			</div>
			<div class="btn">
				<a href="index.php" title="main"><?php echo !empty($langArr['send_sms_message6']) ? $langArr['send_sms_message6'] : 'HOME'; ?></a>
			</div>
		<?php } else { ?>
			<div class="img"><img src="images/icons/send_chk1.png" alt="send" /></div>
			<div class="text1">
				<?php echo $view_msg; ?>
			</div>
			<div class="text2">
				<?php echo !empty($langArr['send_sms_message5']) ? $langArr['send_sms_message5'] : 'It takes up to 24 hours to complete the transaction.'; ?>
			</div>
			<div class="btn">
				<a href="index.php" title="main"><?php echo !empty($langArr['send_sms_message6']) ? $langArr['send_sms_message6'] : 'HOME'; ?></a>
			</div>
		<?php } ?>

	</div>

</div>
</body>
</html>


<?php include_once 'includes/footer.php'; ?>
