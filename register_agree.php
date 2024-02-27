<?php
//die("Registration close for public user");
session_start();

require_once './config/config.php';
//If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
}
include_once 'includes/header.php';

/* ============================================================================== */
/* =   PAGE : 인증 요청 PAGE                                                    = */
/* = -------------------------------------------------------------------------- = */
/* =   Copyright (c)  2012.02   KCP Inc.   All Rights Reserved.                 = */
/* ============================================================================== */

/* ============================================================================== */
/* =   환경 설정 파일 Include                                                   = */
/* = -------------------------------------------------------------------------- = */
include "./config/kcp_config.php";      // 환경설정 파일 include
$g_conf_Ret_URL      = "https://cybertronchain.com/wallet/auth.pro.res_r.php"; // 수정금지


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

// Check country code by IP
function kisa_ip_chk(){

	$ip = getUserIpAddr();
	$key = "2020032517154809084222";
	$url ="http://whois.kisa.or.kr/openapi/ipascc.jsp?query=".$ip."&key=".$key."&answer=json";
	$ch = curl_init();

	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_NOSIGNAL, 1);
	//curl_setopt($ch,CURLOPT_POST, 1); //Method를 POST. 없으면 GET
	$data = curl_exec($ch);
	$curl_errno = curl_errno($ch);
	$curl_error = curl_error($ch);
	curl_close($ch);
	$decodeJsonData = json_decode($data, true);
	return $decodeJsonData['whois']['countryCode'];
}


function ipinfo_ip_chk($key) { // 수량 체크 테스트용. whois 대신 사용 가능한지 check (2020.05.14, YMJ)
	// https://ipinfo.io/
	if ($key == '1') {
		$access_token = 'd5b65ce795f734'; // 무료 version key (50,000건)
	} else {
		$access_token = '7c984c718aef66'; // 무료 version key (50,000건)
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
	return $country; // 국내 : KR
}

$ip_kor = '';
$ip_kor = trim(ipinfo_ip_chk('2'));
if ($ip_kor == '') {
	$ip_kor = kisa_ip_chk();
}

if ($ip_kor != 'KR') {
	header('Location:register.php');
	exit();
}
?>

<style>

.loginc_text1 {
	font-size: 1.35rem;
	color: #4e4dd7;
	margin-top: 40px;
	margin-bottom: 16px;
}
.loginc_text2 {
	font-size: 2.25rem;
	color: #2b2726;
	margin-bottom: 51px;
}
.loginc_text2 {
	margin-bottom: 101px !important;
}
.agree_box {
	position: relative;
	overflow: hidden;
	display: table;
	height: 40px;
	width: 100%;
}
.agree_box .check_box {
	display: table-cell;
	height: 100%;
	vertical-align: middle;
	width: 30px;

}
.agree_box .check {
	display: table-cell;
	height: 100%;
	color: #4e4dd7;
	font-size: 1.35rem;
	vertical-align: middle;
}
.agree_box label {
	display: table-cell;
	vertical-align: middle;
	height: 100%;
}
.agree_box .text1 {
	display: table-cell;
	height: 100%;
	color: #2b2726;
	font-size: 1.35rem;
	vertical-align: middle;
	padding-left: 11px;
}
.agree_box .more_box {
	display: table-cell;
	height: 100%;
	width: 56px;
	height: 100%;
	vertical-align: middle;
}
.agree_box .more {
	font-size: 1.14rem;
	color: #000000;
	border: 1px solid #c1c1c1;
	width: 56px;
	display: inline-block;
	text-align: center;
	height: 20px;
	line-height: 20px;
	cursor: pointer;
	box-sizing: border-box;
}


#agree_more1_contents {
	overflow: scroll; -webkit-overflow-scrolling : touch;
	height: 100px;
	width: 100%;
	border: 1px solid #848484; 
	margin-bottom: 20px;
	padding: 5px;
	font-size: 1.35rem;
}
#agree_more2_contents {
	overflow: scroll; -webkit-overflow-scrolling : touch;
	height: 100px;
	width: 100%;
	border: 1px solid #848484; 
	margin-bottom: 20px;
	padding: 5px;
	font-size: 1.35rem;
}
#show_msg {
	margin: 20px 0 40px;
	font-size: 15px;
	color: #a94442;
	background-color: #f2dede;
	border: 1px solid #ebccd1;
	border-radius: 4px;
	padding: 15px;
}
#show_pay_btn {
	margin-top: 10px;
	text-align: center;
}
#contents {
	padding: 0 10px;
}

.bottom {
	margin-top: 20px;
	text-align: center;
	line-height: 25px;
}
</style>


<div class="login-bg" id="cert_info">
<div class="col-md-4"></div>
<div id="page-" class="col-md-4">


		<div id="contents">
		
			<?php
			if(isset($_SESSION['failure']))
			{
			echo '<div class="alert alert-danger alert-dismissable">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
						<strong>Oops! </strong>'. $_SESSION['failure'].'
				  </div>';
			  unset($_SESSION['failure']);
			}
			?>

			<p class="loginc_text1"><?php echo $langArr['login_agree_text1']; ?></p>
			<p class="loginc_text2"><?php echo $langArr['login_agree_text2']; ?><br /><?php echo $langArr['login_agree_text3']; ?></p>
			
			<div class="agree_box">
				<div class="check_box"><input type="checkbox" name="agree_chk1" class="checkbox1" id="agree_chk1"  /></div>
				<label for="agree_chk1">
					<span class="check"><?php echo $langArr['login_agree_check']; ?></span>
					<span class="text1"><?php echo $langArr['login_agree_agree1']; ?></span>
				</label>
				<div class="more_box">
					<span class="more more1" onclick="login_agree_check('1');"  id="agree_more1_more"><?php echo $langArr['login_agree_more']; ?></span>
					<span class="more more1 none" onclick="login_agree_check('1');" id="agree_more1_close"><?php echo $langArr['login_agree_close']; ?></span>
				</div>
			</div>

			<div id="agree_more1_contents" class="none">
※이용약관<br />
제1조 (목적)<br />
제2조 (정의)<br />
제3조 (약관의 명시와 설명 및 개정)<br />
제4조 (서비스의 제공 및 변경)<br />
제5조 (서비스의 중단)<br />
제6조 (회원가입 )<br />
제7조 (회원탈퇴 및 자격상실)<br />
제8조 (회원에 대한 통지)<br />
제9조 (계약의 성립)<br />
제10조 (서비스 대상 물품)<br />
제11조 (지급 및 취소,환불방법)<br />
제12조 (서비스별 요금결제, 재화등의 공급 및 보관)<br />
제13조 (운송 및 통관 )<br />
제14조 (반품, 환급 등 )<br />
제15조 (차액정산)<br />
제16조 (긴급조치)<br />
제17조 (개인정보보호)<br />
제18조 ("코인쇼핑서비스"의 의무)<br />
제19조 (회원의 id 및 비밀번호에 대한 의무)<br />
제20조 (회원의 의무)<br />
제21조 (연결"몰"과 "피연결"몰" 간의 관계)<br />
제22조 (저작권의 귀속 및 이용제한)<br />
제23조 (분쟁해결)<br />
제24조 (재판권 및 준거법)<br />
<br />
제1조 (목적)<br />
이 약관은 주식회사 한가족몰 ( 이하 "회사" 라 함 )가 운영하는 인터넷 홈페이지 ('https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet "코인쇼핑서비스"라 함 ) 에서 제공하는 " p2p대행 서비스" 및 "직접배송대행 서비스 "를 이용함에 있어 회사와 회원의 권리·의무 및 책임사항을 규정함을 목적으로 합니다.<br />
<br />
제2조 (정의)<br />
① "코인쇼핑서비스"라 함은 회사가 본 약관에 의하여 재화나 용역을 코인전환으로 회원에게 제공하기 위하여 컴퓨터등 정보통신설비를 이용하여 재화나 용역을 코인전환으로 거래할 수 있도록 설정한 가상의 영업장을 말하며, 아울러 가상폐전환서비스를 운영하는 회사의 의미로도 사용합니다.<br />
② 코인전환서비스와 회원간에 발생하는 거래계약 유형은 "수입대행형 ( 경매대행 서비스 포함 ) "과 "배송대행형"이 있으며 각각의 거래계약유형에 대한 정의는 다음과 같습니다.<br />
가 . "수입대행형" 거래계약 유형은 해외 인터넷 쇼핑몰 등에서 판매하는 상품에 대하여 회원이 코인쇼핑서비스에 수입구매대행을 의뢰하면코인쇼핑서비스가 해당 상품을 판매하는 국내/해외 인터넷 쇼핑몰 등에서 회원을 대신해 구매 및 결제를 수취처까지 운송을 하여 회원이 수령할 수 있도록 하는 것을 의미합니다.<br />
나 . "배송대행형" 거래계약 유형은 코인쇼핑서비스가 회원에게 제공하는 중간배송처에 입고된 물품을 회원의 수취처까지 운송을 하여 회원이 수령할 수 있도록 하는 것을 의미합니다.<br />
다. "수입대행형"과 "배송대행형"이외에 국내 및 해외에 재고를 보유한 후 판매하는 서비스는 제공하지 않습니다.<br />
③ 코인쇼핑서비스에서 제공하는 서비스에 대한 정의는 다음과 같습니다 .<br />
가. "코인쇼핑서비스"이라 함은 "수입대행형" 거래계약 유형에 해당하는 서비스로서 , 코인쇼핑서비스 인터넷 홈페이지내 "코인쇼핑서비스" 상품 카테고리에 게시된 물품에 대하여 회원이 수입대행에 따르는 총 비용을 결제시 코인쇼핑서비스는 가상화폐사용이 가능한 신용카드 및 해외송금 (Wire Transfer) 등을 통해 해당 상품을 판매하는 해외 인터넷 쇼핑몰 등에서 회원을 대신해 구매하여 회원이 지정하는 국내 수취처까지 배송하여 이를 회원이 수령할 수 있도록 하는 서비스를 의미합니다 .<br />
④ "회원" 이라 함은 해외쇼핑서비스에 개인정보를 제공하여 회원등록을 하거나, 타사의 서비스에 회원등록을 하여 해외쇼핑서비스에 회원정보를 공개 및 이관하는 것에 동의하고 본 이용약관에 동의한 개인 이용자 또는 사업자로서, 해외쇼핑서비스의 정보를 지속적으로 제공받으며 해외쇼핑서비스가 제공하는 서비스를 계속적으로 이용할 수 있는 자를 말합니다.<br />
<br />
제3조 (약관의 명시와 설명 및 개정)<br />
① 코인쇼핑서비스는 이 약관의 내용과 상호 및 대표자 성명 , 영업소 소재지 주소 (소비자의 불만을 처리할 수 있는 곳의 주소를 포함 ), 전화번호 , 모사전송번호 , 전자우편주소 , 사업자등록번호 , 통신판매업 신고번호 , 개인정보 관리책임자 등을 회원이 쉽게 알 수 있도록  서비스화면 에 게시합니다 . 다만 , 약관의 내용은 회원이 연결화면을 통하여 볼 수 있도록 할 수 있습니다 .<br />
② 코인쇼핑서비스는 회원이 약관에 동의하기에 앞서 약관에 정하여져 있는 내용 중 청약철회 , 배송책임 , 환불조건 등과 같은 중요한 내용을 회원이 이해할 수 있도록 별도의 연결화면 또는 팝업화면 등을 제공하여 회원의 확인을 구하여야 합니다 .<br />
③ 코인쇼핑서비스는 전자상거래 등에서의 소비자보호에 관한 법률 , 약관의 규제에 관한 법률 , 전자거래기본법 , 전자서명법 , 정보통신망이용촉진 등에 관한 법률 , 방문판매 등에 관한 법률 , 소비자보호법 등 관련법을 위배하지 않는 범위에서 이 약관을 개정할 수 있습니다 .<br />
④이 약관에서 정하지 아니한 사항과 이 약관의 해석에 관하여는 전자상거래등에서의 소비자보호에 관한 법률 , 약관의 규제 등에 관한 법률 , 공정거래위원회가 정하는 전자상거래 등에서의 소비자보호 지침 및 관계법령 또는 상관례에 따릅니다 .<br />
<br />
제4조 (서비스의 제공 및 변경)<br />
① 코인쇼핑서비스는 다음과 같은 업무를 수행합니다 .<br />
가 . 재화 또는 용역에 대한 정보제공 및 대행계약의 체결<br />
나 . 회원이 코인쇼핑에서 구매 또는 수입대행을 의뢰한 물건에 대한 운송계약의 체결<br />
다 . 회원이 코인쇼핑구매 또는 수입대행을 의뢰한 재화의 배송<br />
라 . 코인대행 서비스<br />
마 . 기타 코인쇼핑서비스가 정하는 업무<br />
② 코인쇼핑서비스는 재화 또는 용역의 품절 또는 기술적 사양의 변경 등의 경우에는 장차 체결되는 계약에 의해 제공할 재화 또는 용역의 내용을 변경할 수 있습니다 . 이 경우에는 변경된 재화 또는 용역의 내용 및 제공일자를 명시하여 현재의 재화 또는 용역의 내용을 게시한 곳에 즉시 공지합니다 .<br />
③ 코인쇼핑서비스가 제공하기로 회원과 계약을 체결한 서비스의 내용을 재화 등의 품절 또는 기술적 사양의 변경 등의 사유로 변경할 경우에는 그 사유를 회원에게 즉시 통지합니다 .<br />
④ 전항의 경우 코인쇼핑서비스는 이로 인하여 회원이 입은 손해를 배상합니다 . 다만 , 코인쇼핑서비스의 고의 또는 과실이 없음을 입증하는 경우에는 그러하지 아니합니다.<br />
<br />
제5조 (서비스의 중단)<br />
① 코인쇼핑서비스는 컴퓨터 등 정보통신설비의 보수점검 , 교체 및 고장 , 통신의 두절 등의 사유가 발생한 경우에는 서비스의 제공을 일시적으로 중단할 수 있습니다 .<br />
② 코인쇼핑서비스는 본조 ①항의 사유로 서비스의 제공이 일시적으로 중단됨으로 인하여 회원 또는 제 3 자가 입은 손해에 대하여 배상합니다 . 단, 코인쇼핑서비스의 고의 또는 과실이 없음을 입증하는 경우에는 그러하지 아니합니다 .<br />
③ 사업종목의 전환 , 사업의 포기 , 업체간의 통합 등의 이유로 서비스를 제공할 수 없게 되는 경우에는 코인쇼핑서비스는 제8조에 정한 방법으로 회원에게 통지하고 회원이 이로 인하여 손해를 입은 경우에는 당초 코인쇼핑서비스에서 제시한 조건에 따라 소비자에게 보상합니다.<br />
<br />
제6조 (회원가입 )<br />
① 회원은 코인쇼핑서비스가 정한 가입양식에 따라 회원정보를 기입한 후 이 약관에 동의한다는 의사표시를 함으로서 회원가입을 신청하거나, 타사 서비스에 회원가입을 한 후 회원정보를 코인쇼핑서비스에 공개 및 이관하는 것에 동의하고 본 이용약관에 동의하는 것으로 코인쇼핑서비스 회원가입을 대신 신청합니다 .<br />
② 코인쇼핑서비스는 제 1 항과 같이 회원으로 가입할 것을 신청한 회원 중 다음 각호에 해당하지 않는 한 회원으로 등록합니다 .<br />
가. 가입신청자가 이 약관 제 7 조 제 3 항에 의하여 이전에 회원자격을 상실한 적이 있는 경우 , 다만 제 7 조 제 3 항에 의한 회원자격 상실 후 3 년이 경과한 자로서 코인쇼핑서비스의 회원 재가입 승낙을 얻은 경우에는 예외로 합니다 .<br />
나. 등록 내용에 허위 , 기재누락 , 오기가 있는 경우<br />
다. 기타 회원으로 등록하는 것이 코인쇼핑서비스의 기술상 현저히 지장이 있다고 판단되는 경우<br />
라. 가입신청자의 연령이 만 14 세 미만인 경우 . ( 단 , 14 세 이상일 경우라도 미성년자일 경우 코인쇼핑서비스 이용에 제한을 받을 수 있습니다 .)<br />
마. 자가 사용 이외의 목적으로 구매대행 또는 수입대행 서비스를 이용하기 위해 법인 또는 사업자로 가입하는 경우<br />
③ 회원가입계약의 성립시기는 코인쇼핑서비스의 회원가입 승낙이 회원에게 도달한 시점으로 합니다 .<br />
④ 회원은 회원가입시 기입한 회원정보에 변경이 있는 경우 , 즉시 전자우편이나 기타 방법으로 코인쇼핑서비스에 그 변경사항을 알려야 합니다 .<br />
⑤ 회원가입 정보를 받은 경우도 본조 2 항 가 , 나 , 다 , 라 , 마 항목에 해당될 시에는 회원가입이 승인되지 않은 것으로 합니다 .<br />
<br />
제7조 (회원탈퇴 및 자격상실)<br />
① 회원은코인쇼핑서비스에 언제든지 회원탈퇴를 요청할 수 있으며 코인쇼핑서비스는 즉시 회원탈퇴를 처리합니다 . 다만 고객이 요금을 결제한 서비스가 진행 중일 경우 서비스의 성실한 이행을 위하여 해당 서비스가 종료될 때까지 회원 탈퇴를 임시적으로 유보할 수 있습니다 .<br />
② 회원이 다음 각 호의 사유에 해당하는 경우 , 코인쇼핑서비스는 회원자격을 제한 및 정지시킬 수 있습니다 .<br />
가 . 가입신청 내역에 허위내용이 발견된 경우<br />
나 . 코인쇼핑서비스 사용과 관련하여 서비스요금 미납 등 회원이 부담하는 채무를 기일 내에 이행하지 않는 경우<br />
다 . 다른 사람의 코인쇼핑서비스 이용을 방해하거나 그 정보를 도용하는 등 전자상거래 질서를 위협하는 경우<br />
라 . 회원이 제출한 주소 또는 연락처의 변경통지를 하지 않는 등 회원의 귀책사유로 인해 회원이 소재불명 되어 코인쇼핑서비스가 회원에게 통지 , 연락을 할 수 없다고 판단되는 경우<br />
마 . 코인쇼핑서비스를 이용하여 관련법령과 이 약관이 금지하거나 공서양속에 반하는 행위를 하는 경우<br />
③ 코인쇼핑서비스는 다음 각 호의 경우에 회원자격을 상실시킬 수 있습니다 .<br />
가 . 코인쇼핑서비스가 본조 2 항에 의해 회원자격을 제한 또는 정지시킨 후 , 동일한 행위가 2 회 이상 반복 되거나 30 일 이내에 그 사유가 시정되지 아니하는 경우<br />
나 . 회원이 구매한 물품에 대한 서비스요금 결제를 30 일 이내에 이행하지 않을 경우<br />
다 . 회원이 위법 , 불법 혹은 부정한 목적으로 본 서비스를 사용하였다고 코인쇼핑서비스가 객관적 자료에 의거 합리적으로 판단했을 경우<br />
④ 코인쇼핑서비스가 회원자격을 상실시키는 경우에는 회원등록을 말소합니다 . 이 경우 회원에게 이를 통지하고 , 회원등록 말소전에 최소한 30 일 이상의 기간을 정하여 소명할 기회를 부여합니다 .( 회원의 소재불명 등으로 인해 소명 기회의 제공이 어려운 경우는 코인쇼핑서비스의 판단으로 회원등록을 말소할 수 있습니다 .)<br />
<br />
제8조 (회원에 대한 통지)<br />
① 코인쇼핑서비스가 회원에 대한 통지를 하는 경우 , 회원이 코인쇼핑서비스와 미리 약정하여 지정한 전자우편 주소로 할 수 있습니다 .<br />
② 코인쇼핑서비스는 불특정다수 회원에 대한 통지의 경우 1 주일이상 코인쇼핑서비스 게시판에 게시함으로서 개별 통지에 갈음할 수 있습니다 . 다만 , 회원 본인의 거래와 관련하여 중대한 영향을 미치는 사항에 대하여는 개별통지를 합니다 .<br />
<br />
제9조 (계약의 성립)<br />
코인쇼핑서비스는 코인쇼핑서비스 이용과 관련하여 회원과 아래의 절차를 통해 수입대행 및 배송대행 계약을 체결합니다 .<br />
① 배송대행형 서비스 회원이 코인쇼핑서비스 회원가입 후 코인쇼핑서비스에서 부여한 해외 물품 수취 주소로 해외에서 구매한 물품을 입고시키거나 구매 내역을 사전에 통보한 경우 코인쇼핑서비스는 서비스요금 결제통지서를 회원에게 통보하여 회원이 서비스요금 결제시 코인쇼핑서비스와 회원간에 배송대행계약이 성립합니다 .<br />
가 . 코인쇼핑서비스와 회원의 계약성립은 국제운송 및 수입통관 등 운송 전과정을 코인쇼핑서비스에 일임하였음을 의미하며 , 또한 회원은 수입통관 등의 진행에 있어 코인쇼핑서비스의 요청이 있을 경우 이를 이행하겠다는 동의로 간주합니다 .<br />
② 코인쇼핑서비스는 회원의 수입대행 및 배송대행계약 요청에 대해 다음 각 호에 해당하면 승낙하지 않을 수 있습니다 .<br />
가 . 신청내용에 허위 , 기재누락 , 오기가 있는 경우<br />
나 . 회원이 관련법령 및 동 약관에서 금지하는 재화에 대해 수입대행 또는 운송을 요청하는 경우<br />
다 . 기타 수입대행 또는 배송대행신청을 승낙하는 것이 코인쇼핑서비스 기술상 현저히 지장이 있다고 판단하는 경우<br />
<br />
제10조 (서비스 대상 물품)<br />
코인쇼핑서비스는 회원이 별도의 의사표시가 없는 경우 자가사용의 목적으로 구매하여 수입하는 경우로 서비스를 제공하며, 한편 회원의 상품 수취주소가 불명확하거나 배송신청서 필수기재 항목이 일부 누락된 경우 문자메시지, 전자우편 등을 통해 상당한 기간 내에 이를 수정, 보완할 것을 통지합니다. 고객이 상당한 기간 내에 이를 수정, 보완하지 아니하는 경우로서 고객의 소재를 파악할 수 없어 상품을 배송할 수 없는 경우에는 고객의 비용으로 발송인에게 반송하거나, 임의대로 물품을 처리하여 소요비용에 충당할 수 있습니다.<br />
<br />
관세법<br />
제 234조(수출입의 금지) 다음 각호의 1에 해당하는 물품은 수출 또는 수입할 수 없다.<br />
1. 헌법질서를 문란하게 하거나 공공의 안녕질서 또는 풍속을 해치는 서적,간행물,도화,영화,음반,비디오물,조각물 기타 이에 준하는 물품<br />
2. 정부의 기밀을 누설하거나 첩보활동에 사용되는 물품<br />
3. 화폐,채권 기타 유가증권의 위조품,변조품 또는 모조품<br />
<br />
제11조 (지급 및 취소,환불방법)<br />
① 코인쇼핑서비스 이용에 대한 대금지급방법은 다음 각 호의 방법 중 가용한 방법으로 할 수 있습니다.<br />
가 . 폰뱅킹 , 인터넷 뱅킹 등의 계좌이체<br />
나 . 신용카드 결제<br />
다 . 온라인 무통장 입금<br />
라 . 쿠폰 및 회사가 정하는 가상화폐 및 현금성 전자머니<br />
마 . 기타 코인쇼핑서비스가 인정하는 결제수단<br />
② 코인쇼핑서비스의 취소 및 환불은 다음 각 호의 방법 중 가용한 방법으로 할 수 있습니다.<br />
가 . 카드 승인 취소<br />
나 . 은행 계좌 이체<br />
다 . 코인쇼핑서비스에서 정하는 가상화폐 및 현금성 전자머니 (*"가상화폐 및 현금성 전자머니"의경우 포인트로 전환이되며 전환된 포인트는 환불할수없다.)<br />
라 . 코인쇼핑서비스와 제휴 관계에 있는 서비스에서 제공하는 가상화폐 및 현금성 전자머니<br />
마 . 가상화폐 및 현금성 전자머니의 현금 인출은 코인쇼핑서비스 및 코인쇼핑서비스의 제휴사의 규정을 따르며 전환된 포인트는 환불할수없다.<br />
③ 코인쇼핑서비스는 (주)한가족몰에서 제공하는 결제대행서비스를 대금 결제 수단으로 사용할 수 있습니다. 이 경우 본조 ①항의 결제수단은 그 적용을 배제합니다. 그러나 이 때에도 취소 및 환불처리는 본조 ②항을 따를 수 있습니다. 코인쇼핑서비스는 (주)한가족몰에서 제공하는 결제대행서비스를 결제 수단으로 채택하는 경우에도 대금의 결제 및 재화나 용역의 거래와 관련한 사항에 대하여 (주)한가족몰는 일절 관여하지 않습니다.<br />
④ 본조 ③항에도 불구하고 결제대금 중 보증금, 예치금의 경우는 (주)한가족몰에서 제공하는 제3항의 수단 외에 다른 결제수단을 이용하도록 정할 수 있습니다. 이 때의 취소 및 환불처리는 본조 ②항을 따릅니다.<br />
<br />
제12조 (서비스별 요금결제, 재화등의 공급 및 보관)<br />
① 수입대행형 서비스<br />
가. 회원이 코인쇼핑서비스에서 상품의 수입대행을 의뢰할 경우에는 코인쇼핑서비스내 각 상품에 구현된 물품의 가격을 제11조 각 항의 형태로 결제해야 합니다.<br />
나. 코인쇼핑서비스와 회원간의 수입대행계약에 의해 코인쇼핑서비스 주소에 입고된 물품에 대해 검수 후 국내의 수취처까지 배송하여 회원이 수령할 수 있도록 합니다.<br />
다. 코인쇼핑서비스는 해외상품의 수입대행을 수행함에 따른 전체 금액을 회원이 결제하기 전에 각각의 거래계약 유형별로 총지불가격, 수입대행가격, 운송료형태로 구분하여 회원에게 고지합니다.<br />
라. 코인쇼핑서비스에 명기된 가격은 해외 인터넷쇼핑몰 등에서의 물품 구입가격과 해외 코인쇼핑서비스 물류센터까지의 운송료, 현지 제세금, 국제운송료, 수입관세, 수입부가세, 기타세금에 코인쇼핑서비스의 수입대행수수료가 모두 포함된 가격이며 회원이 이외에 추가적으로 부담해야 할 금액이 발생할 경우 별도로 회원에게 사전 통지하도록 합니다.<br />
마. 코인쇼핑서비스는 상품을 판매하는 해외 인터넷쇼핑몰 등 공급자 정보(쇼핑몰 명 등)와 코인쇼핑서비스에 명기된 가격의 상세내역에 대하여 회원의 별도 요청이 있을시 이를 E-mail 또는 기타의 방법을 통해 제공합니다.<br />
바. 코인쇼핑서비스는 회원이 수입대행을 의뢰한 상품에 대해서 회원의 대금 결제단위를 기준으로 포장하여 배송함을 원칙으로 합니다. 다만 결제 단위당 복수의 상품의 경우 각 상품의 해외 인터넷쇼핑몰 등 공급자가 다를 경우에는 이를 해외물류센터에 도착하는 순서대로 배송할 수 있으며, 의도적으로 관세 등 제세금을 절감하기 위한 조작(분할 재포장 등)은 하지 않습니다.<br />
사. 회원의 운송료를 최소화하기 위해서 과대포장 여부와 부실한 포장상태의 점검을 하여 더 저렴한 가격으로 물품을 안전하게 배송하여 드리기 위해서 회원의 사전동의 없이 재포장을 할 수 있습니다. 해외 공급처의 포장이 미비하여 물품의 파손 위험이 있다고 판단되는 경우 파손 방지를 위해 회원에게 동의를 구한 후 재포장을 할 수 있으며 이 경우 재포장 비용을 회원이 부담합니다.<br />
② 배송대행형 서비스<br />
가. 회원이 코인쇼핑서비스에서 부여한 해외 물품 수취 주소를 사용하여 물품을 구매할 경우에는 코인쇼핑서비스의 주소에 회원이 주문한 물품이 도착하거나 회원이 구매내역을 사전 통지한 후 관세 등 제세금, 운송료로 구분된 서비스 요금 결제요청 통지(e-Mail 등)를 회원에게 하고 회원은 제11조 각 항의 형태로 해당 금액을 결제 해야 하며 미결제시 회원이 주문한 물품은 코인쇼핑서비스의 주소에서 회원에게로 배송되지 않습니다.<br />
나. 회원은 전항의 기간 내에 금액을 결제하지 않을 경우 대금지급 확인일까지의 기간에 대해 소정의 보관료 및 지연손해금이 추가로 부과될 수 있으며 부과된 금액에 대하여 반드시 지급하여야 합니다.<br />
다. 고의 또는 중과실로 인한 경우를 제외하고 물품이 코인쇼핑서비스 주소에 도착 후 코인쇼핑서비스가 회원에게 서비스 요금 결제요청 통지를 하고 30일 경과이후에 발생하는 물품의 도난, 훼손, 멸실 등에 대하여 코인쇼핑서비스는 책임을 지지 아니하며, 결제요청 통지 후 3개월 이후에는 임의대로 처분하여 보관료로 충당합니다.<br />
라. 회원의 운송료를 최소화하기 위해서 과대포장 여부와 부실한 포장상태의 점검을 하여 더 저렴한 가격으로 물품을 안전하게 배송하여 드리기 위해서 코인쇼핑서비스는 회원의 사전동의 없이 재포장을 할 수 있습니다. 해외 공급처의 포장이 미비하여 물품의 파손 위험이 있다고 판단되는 경우 파손 방지를 위해 재포장을 할 수 있으며 이 경우 회원에게 해당 사실을 사전 통보하며 재포장 비용을 회원이 부담합니다.<br />
<br />
제13조  (운송 및 통관 )<br />
가. "코인쇼핑서비스"는 운송계약 대행자로서 운송제휴사의 택배 서비스 를 통해 물류센터에서부터 회원이 지정한 수취처까지의 배송의 용역을 제공합니다.<br />
나. 상기 "가"항에서의 "코인쇼핑서비스" 물류센터에서 회원이 지정한 수취처까지의 운송구간에서 "코인쇼핑서비스" 또는 운송제휴사의 귀책 사유로 물품의 파손 등 하자가 발생하였을 때, 배송대행형 거래계약 유형에서는 물품구매원가 및 해당 거래건에 대해서 회원이 결제한 총 금액을, 대행형 거래계약 유형에서는 해당 거래건에 대해서 고객이 결제한 총 금액을 보상합니다.<br />
② 통관<br />
가. "코인쇼핑서비스"는 개인이 자가사용 목적으로 수입하는 개인수입통관원칙에 의거, 회원을 납세의무자로 하고 수입요건을 구비하여 운송제휴사를 통하여 통관절차를 수행합니다. 단 사업자 회원일 경우 해당 사업자의 명의로 수입신고되며 일반수입통관원칙에 따릅니다.<br />
나. 이 때 발생하는 관세 및 수입부가세 등 제세금은 "코인쇼핑서비스"이 회원을 대신하여 대한민국 세관에 대납하고 코인쇼핑서비스는 회원이 기 결제한 관세 및 수입부가세 등 제세금을 운송제휴사와 정산하는 절차를 거칩니다.<br />
다. 관세법에 따라 다음에 해당하는 상품은 수입가격에 관계 없이 목록통관이 배제될 수 있습니다.<br />
라. 회원께서 주문한 상품의 과세가격이 15만원 이하인 경우에도 합산하여 과세대상에 포함될 수 있습니다.<br />
목록통관 배제대상물품<br />
1. 의약품<br />
2. 한약재<br />
3. 야생동물 관련 제품<br />
4. 농림축수산물등 검역대상물품<br />
5. 건강기능식품<br />
6. 지식재산권 위반 의심물품<br />
7. 식품류.과자류<br />
8. 화장품(기능성화장품, 태반함유화장품, 스테로이드제 함유화장품 및 성분미상 등 유해화장품에 한함)<br />
9. '전자상거래물품 등의 특별통관절차에 관한 고시' 제3-3조 제3항에 따라 특별통관대상업체로 지정되지 아니한 전자상거래업체가 수입하는 물품<br />
10. 통관목록 중 품명,규격,수량,가격 등이 부정확하게 기재된 물품<br />
11. 그 밖에 법 제226조에 따른 세관장확인대상물품 등 목록통관이 타당 하지 아니하다고 세관장이 인정하는 물품<br />
<br />
제14조 (반품, 환급 등 ) (*중요사항* "가상화폐 및 현금성 전자머니"의경우 포인트로 전환이되며 전환된 포인트는 환불할수없다.)<br />
① 상품 가격 변경, 책정된 운송 무게(kg)와 실제 운송된 무게차, 관세율표 개정, 세번분류 변경, 전산시스템 및 데이터 오류 등으로 인해 회원이 수입대행 또는 배송대행계약시 지불한 금액과 "코인쇼핑서비스"에서 발생한 실제 비용에 차이가 발생하는 경우에 대해 과부족금액을 "코인쇼핑서비스"는 회원과 사후 정산하여야 합니다.<br />
⑴ 단순변심에 의한 반품 서비스 이행구간별로 공급처에서 회원을 대신하여 구매하는데 기발생한 운송비 및 제세금 등의 실비용을 결제 후 원결제 취소로 환급하는 것을 조건으로 반품을 받을 수 있습니다. 또한, 반품시 발생하는 국제 운송료 및 현지 반송료 등의 실비용은 회원의 부담으로 하며 해외 공급처의 신용도가 확실하지 않을 경우 반송 후 "코인쇼핑서비스"가 기결제한 물품 구매 대금을 공급처로부터 반환 받은 이후에 회원에게 환급하도록 합니다.<br />
⑵ 공급처의 귀책사유에 의한 상품 상이, 결실, 파손, 손상, 오염에 의한 반품 "단순변심에 의한 반품" 규정과 동일하며 다만, 고객의 편의를 위해 해외 공급처에 귀책사유를 전달하고 기결제한 물품구매대금 또는 추가 보상을 받을 수 있는 절차를 대행해 드릴 수 있습니다.<br />
⑶ "코인쇼핑서비스"의 귀책사유에 의한 상품 상이, 결실, 파손, 손상, 오염에 의한 반품 "코인쇼핑서비스" 또는 해외 공급자의 귀책사유에 의한 것인지 책임관계를 파악하여 "코인쇼핑서비스"의 귀책사유에 의한 경우 회원의 원결제금액에 대해 전액 환급합니다.<br />
다. "코인쇼핑서비스"는 반품접수된 상품을 반환받은 경우 3영업일 이내에 이미 지급받은 상품 등의 대금을 환급합니다.(다만, 환급액에서 "코인쇼서비스"의 귀책사유가 없어 환급하지 않아도 될 비용항목을 제외할 수 있습니다)<br />
② 배송대행형 서비스<br />
가. "코인쇼핑서비스"가 부여한 물품 수취 주소에 회원이 주문한 물품이 도착하여 한국으로 발송되기 전에 회원의 당해 물품에 대한 배송대행계약의 중도해지요청(반품 등)이 "코인쇼핑서비스"에 도달한 경우 당해 물품은 회원의 요청에 따라 반송하며 이를 위해 소요되는 일체의 비용은 회원이 부담합니다.<br />
나. "코인쇼핑서비스"가 부여한 해외 물품 수취 주소에 회원이 주문한 물품이 도착되어 회원이 결제한 후 한국으로 발송된 시점 이후에 전항의 중도해지요청이 접수될 경우 원 배송대행계약의 효력은 계속 존재하여 "코인쇼핑서비스"가 당해 물품을 국내의 수취처까지 배송완료함으로써 원 배송대행계약에 대한 이행을 완료하게 되며, 중도해지요청에 대해서는 비용일체를 회원이 부담하는 것을 전제조건으로 하여 반송을 대행해 줄 수 있습니다.<br />
다. 회원이 지정한 수취처 이외의 제3의 지역으로 송부할 것을 요청하거나 해당 판매자에게 반송하였을 때 수취자가 물품을 수취거부 하는 경우는 해당 물품을 회원에게 송부하고 그에 따른 일체의 비용은 회원이 부담합니다.<br />
<br />
제15조 (차액정산)<br />
① 상품 가격 변경, 책정된 운송 무게(kg)와 실제 운송된 무게차, 관세율표 개정, 세번분류 변경, 전산시스템 및 데이터 오류 등으로 인해 회원이 수입대행 또는 배송대행계약시 지불한 금액과 "코인쇼핑서비스"에서 발생한 실제 비용에 차이가 발생하는 경우에 대해 과부족금액을 "코인쇼핑서비스"는 회원과 사후 정산하여야 합니다.<br />
<br />
제16조 (긴급조치)<br />
① 회원이 위법, 불법 또는 부당한 목적을 위해 서비스를 이용한다고 "코인쇼핑서비스"가 판단하는 때에는 물품의 수취나 배송을 거절할 권리를 가집니다.<br />
② 관할관청 또는 당국에 의해 "코인쇼핑서비스"에 의해 서비스되는 물품에 대해 제재를 받았을 때 "코인쇼핑서비스"는 해당 물품을 관할관청 또는 당국에 인도하는 것을 원칙으로 합니다. 이로 인하여 회원이 손해를 입었다고 할지라도 해당 손해에 대해서 "코인쇼핑서비스"는 일체의 책임을 지지 않으며 또한 회원은 해당 물품에 대한 서비스 이용요금 및 관련비용 등의 지급의무를 면하지 아니합니다.<br />
③ "코인쇼핑서비스" 주소로 배송된 물품에 악취, 액체누수 그 외 이상이 있다고 인정될 경우 및 기타 긴급을 필요로 하고 정당한 이유가 있다고 인정될 경우 "코인쇼핑서비스"는 회원에게 해당 사실을 통지하고 해당 물품을 별도 장소로 이동 보관하는 등 임시조치를 취할 수 있습니다. 이로 인해 발생하는 추가비용은 회원이 부담하여야 하며 조치 과정상 회사의 고의 또는 중과실로 회원에게 손해가 발생한 경우 이에 대한 책임을 부담합니다.<br />
<br />
제17조 (개인정보보호)<br />
① "코인쇼핑서비스"는 회원의 정보수집 시 구매계약 이행에 필요한 최소한의 정보를 수집합니다. 다음 사항을 필수사항으로 하며 그 외 사항은 추가사항으로 합니다.<br />
- 성명, 회원아이디, 전화번호, 휴대전화, 이메일, 주소, SMS수신여부, 이메일수신여부<br />
(사업자의 경우 상호명, 사업자번호 또는 법인등록번호 수집)<br />
- 추가사항 : 회사가 제공하는 서비스 이용에 따른 대금결제, 물품배송 및 수입통관, 환불 등에 필요한 정보를 추가로 수집할 수 있습니다.<br />
② "코인쇼핑서비스"는 회원의 개인식별이 가능한 개인정보를 수집하는 때에는 반드시 당해 회원의 동의를 받습니다.<br />
③ 제공된 개인정보는 당해 회원의 동의없이 목적 외의 이용이나 제3자에게 제공할 수 없으며, 이에 대한 모든 책임은 "코인쇼핑서비스"가 집니다. 다만, 다음의 경우에는 예외로 합니다.<br />
가. 배송업무상 배송업체에게 배송에 필요한 최소한의 회원의 정보(성명, 주소, 전화번호)를 알려주는 경우<br />
나. 재화등의 거래에 따른 대금정산을 위하여 필요한 경우<br />
다. 도용방지를 위하여 본인확인에 필요한 경우<br />
라. 법률의 규정 또는 법률에 의하여 필요한 불가피한 사유가 있는 경우<br />
④ "코인쇼핑서비스"가 제2항과 제3항에 의해 회원의 동의를 받아야 하는 경우에는 개인정보관리 책임자의 신원 (소속, 성명 및 전화번호, 기타 연락처), 정보의 수집목적 및 이용목적, 제3자에 대한 정보제공 관련사항(제공받은 자, 제공목적 및 제공할 정보의 내용) 등 정보통신망이용촉진등에관한법률 제22조 ②항이 규정한 사항을 미리 명시하거나 고지해야 하며 회원은 언제든지 이 동의를 철회할 수 있습니다.<br />
⑤ 회원은 언제든지 "코인쇼핑서비스"가 가지고 있는 자신의 개인정보에 대해 열람 및 오류정정을 요구할 수 있으며 "코인쇼핑서비스"는 이에 대해 지체없이 필요한 조치를 취할 의무를 집니다. 회원이 오류의 정정을 요구한 경우에는 "코인쇼핑서비스"는 그 오류를 정정할 때까지 당해 개인정보를 이용하지 않습니다.<br />
⑥ "코인쇼핑서비스"는 개인정보 보호를 위하여 관리자를 한정하여 그 수를 최소화하며 신용카드, 은행계좌 등을 포함한 회원의 개인정보의 분실, 도난, 유출, 변조 등으로 인한 회원의 손해에 대하여 모든 책임을 집니다.<br />
⑦ "(주)한가족몰" 또는 그로부터 개인정보를 제공받은 제3자는 개인정보의 수집목적 또는 제공받은 목적을 달성한 때에는 당해 개인정보를 지체없이 파기합니다.<br />
⑧ "코인쇼핑서비스"는 회원에게 "코인쇼핑서비스"가 제공하는 다양한 서비스의 질적향상 및 신규 서비스등을 위하여 회원의 개인식별이 가능한 개인정보를 회원의 동의를 득하여 이을 수집하여 판촉활동(이메일광고, 모바일광고, 텔레마케팅광고 등)에 이용할 수 있습니다.<br />
⑨ 회원탈퇴로 이용계약이 종료된 경우, 회사는 당해 회원의 정보를 파기하는 것을 원칙으로 합니다. 다만, 아래의 경우에는 회원정보를 보관합니다. 이 경우 회사는 보관하고 있는 회원정보를 그 보관의 목적으로만 이용합니다.<br />
가. 상법, 전자상거래등에서의소비자보호에관한법률 등 관계법령의 규정에 의하여 보존할 필요가 있는 경우 회사는 관계법령에서 정한 일정한 기간 동안 회원정보를 보관합니다.<br />
나. 비방이나 허위사실 유포 등으로 타인에게 피해를 입힌 경우, 지적재산권 침해상품 판매 기타 인터넷 사기행위 등으로부터 회원과 회사를 보호하고 법적 절차에 따른 수사 협조를 위한 목적 등으로 회사는 이용계약 종료 후 2개월간 물품거래내역이 존재하는 회원의 아이디, 성명 또는 상호, 연락처, 주소, 해지 및 회원자격정지 관련정보 등 필요한 최소한의 정보를 보관합니다.<br />
다. 회원이 회사에 대하여 미결제요금이 있는 경우, 그 수금을 위하여 수금완료 시까지 해당 회원의 아이디, 성명 또는 상호, 연락처, 주소 등 최소한의 필요정보를 보관합니다.<br />
라. 기타 정보수집에 관한 동의를 받을 때 보유기관을 명시한 경우에는 그 보유기간까지 회원정보를 보관합니다.<br />
<br />
제18조 ("코인쇼핑서비스"의 의무)<br />
① "코인쇼핑서비스"는 법령과 이 약관이 금지하거나 공서양속에 반하는 행위를 하지 않으며 이 약관이 정하는 바에 따라 지속적이고, 안정적으로 재화·용역을 제공하는데 최선을 다하여야 합니다.<br />
② "코인쇼핑서비스"는 회원이 안전하게 인터넷 서비스를 이용할 수 있도록 회원의 개인정보 (신용정보 포함) 보호를 위한 보안 시스템을 갖추어야 합니다.<br />
③ "코인쇼핑서비스" 상품이나 용역에 대하여 「표시·광고의 공정화에 관한 법률」 제3조 소정의 부당한 표시·광고 행위를 함으로써 회원이 손해를 입은 때에는 이를 배상할 책임을 집니다.<br />
④ "코인쇼핑서비스"는 회원이 원하지 않는 영리목적의 광고성 전자우편을 발송하지 않습니다.<br />
<br />
제19조 (회원의 id 및 비밀번호에 대한 의무)<br />
① 제18조의 경우를 제외한 ID와 비밀번호에 관한 관리책임은 회원에게 있습니다.<br />
② 회원은 자신의 ID 및 비밀번호를 제3자에게 이용하게 해서는 안됩니다.<br />
③ 회원이 자신의 ID 및 비밀번호를 도난당하거나 제3자가 사용하고 있음을 인지한 경우에는 바로 "코인쇼핑서비스"에 통보하고 "코인쇼핑서비스"의 안내가 있는 경우에는 그에 따라야 합니다.<br />
<br />
제20조 (회원의 의무)<br />
회원은 다음 행위를 하여서는 안됩니다.<br />
① 신청 또는 변경시 허위 내용의 등록<br />
② 타인의 정보 도용<br />
③ "코인쇼핑서비스"에 게시된 정보의 변경<br />
④ "코인쇼핑서비스"가 정한 정보 이외의 정보(컴퓨터 프로그램 등) 등의 송신 또는 게시<br />
⑤ "코인쇼핑서비스", 기타 제3자의 저작권 등 지적재산권에 대한 침해<br />
⑥ "코인쇼핑서비스", 기타 제3자의 명예를 손상시키거나 업무를 방해하는 행위<br />
⑦ 외설 또는 폭력적인 메시지, 화상, 음성, 기타 공서양속에 반하는 정보를 몰에 공개 또는 게시하는 행위 또는 타인을 비방하는 게시 행위 (단, 이 경우 "코인쇼핑서비스"는 임의적으로 게시물을 삭제할 수 있습니다)<br />
<br />
제21조 (연결"몰"과 "피연결"몰" 간의 관계)<br />
① 상위 몰과 하위 몰이 하이퍼 링크(예: 하이퍼 링크의 대상에는 문자, 그림 및 동화상 등이 포함됨) 방식 등으로 연결된 경우, 전자를 연결 몰(웹 사이트) 이라고 하고 후자를 피연결 몰(웹사이트)이라고 합니다.<br />
② 연결 몰은 피연결 몰이 독자적으로 제공하는 재화등에 의하여 회원과 행하는 거래에 대해서 보증책임을 지지 않는다는 뜻을 연결 몰의 초기화면 또는 연결되는 시점의 팝업화면으로 명시한 경우에는 그 거래에 대한 보증책임을 지지 않습니다.<br />
<br />
제22조 (저작권의 귀속 및 이용제한)<br />
① "코인쇼핑서비스"가 작성한 저작물에 대한 저작권 기타 지적재산권은 "코인쇼핑서비스"에 귀속합니다.<br />
② 회원은 "코인쇼핑서비스"를 이용함으로써 얻은 정보 중 "코인쇼핑서비스"에게 지적재산권이 귀속된 정보를 "코인쇼핑서비스"의 사전 승낙없이 복제, 송신, 출판, 배포, 방송 기타 방법에 의하여 영리목적으로 이용하거나 제3자에게 이용하게 하여서는 안됩니다.<br />
③ "코인쇼핑서비스"는 약정에 따라 회원에게 귀속된 저작권을 사용하는 경우 당해 회원에게 통보하여야 합니다.<br />
<br />
제23조 (분쟁해결)<br />
① "코인쇼핑서비스"는 회원이 제기하는 정당한 의견이나 불만을 반영하고 그 피해를 보상처리하기 위하여 피해보상 처리기구를 설치·운영합니다.<br />
② "코인쇼핑서비스"는 회원으로부터 제출되는 불만사항 및 의견은 우선적으로 그 사항을 처리합니다. 다만, 신속한 처리가 곤란한 경우에는 회원에게 그 사유와 처리일정을 즉시 통보해 드립니다.<br />
③ "코인쇼핑서비스"와 회원간에 발생한 전자상거래 분쟁과 관련하여 회원의 피해구제신청이 있는 경우에는 공정거래위원회 또는 시·도지사가 의뢰하는 분쟁조정기관의 조정에 따를 수 있습니다.<br />
<br />
제24조 (재판권 및 준거법)<br />
① "코인쇼핑서비스"와 회원간에 발생한 전자상거래 분쟁에 관한 소송은 제소 당시의 회원의 주소에 의하고, 주소가 없는 경우에는 거소를 관할하는 지방법원의 전속관할로 합니다. 다만, 제소 당시 회원의 주소 또는 거소가 분명하지 않거나 외국 거주자의 경우에는 민사소송법상의 관할법원에 제기합니다.<br />
② "코인쇼핑서비스"와 회원간에 제기된 전자상거래 소송에는 한국법을 적용합니다.<br />
<br />
부칙 제1조 (제정일자)<br />
① 이 약관은 2019년 07월 26일 제정되었습니다.<br />
			</div>

			<div class="agree_box">
				<div class="check_box"><input type="checkbox" name="agree_chk2" class="checkbox1" id="agree_chk2"  /></div>
				<label for="agree_chk2">
					<span class="check"><?php echo $langArr['login_agree_check']; ?></span>
					<span class="text1"><?php echo $langArr['login_agree_agree2']; ?></span>
				</label>
				<div class="more_box">
					<span class="more more2" onclick="login_agree_check('2');" id="agree_more2_more"><?php echo $langArr['login_agree_more']; ?></span>
					<span class="more more2 none" onclick="login_agree_check('2');" id="agree_more2_close"><?php echo $langArr['login_agree_close']; ?></span>
				</div>
			</div>
			<div id="agree_more2_contents" class="none">
&lt;(주)한가족몰는&gt;('https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은(는) 개인정보보호법에 따라 이용자의 개인정보 보호 및 권익을 보호하고 개인정보와 관련한 이용자의 고충을 원활하게 처리할 수 있도록 다음과 같은 처리방침을 두고 있습니다.<br />
<br />
○ 본 방침은부터 2019년 7월 25일부터 시행됩니다.<br />
 <br />
1. 개인정보의 처리 목적 &lt;(주)한가족몰는&gt;('http://https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은(는) 개인정보를 다음의 목적을 위해 처리합니다. 처리한 개인정보는 다음의 목적이외의 용도로는 사용되지 않으며 이용 목적이 변경될 시에는 사전동의를 구할 예정입니다.<br />
가. 홈페이지 회원가입 및 관리<br />
회원 가입의사 확인, 회원제 서비스 제공에 따른 본인 식별ㆍ인증, 회원자격 유지ㆍ관리, 제한적 본인확인제 시행에 따른 본인확인, 서비스 부정이용 방지, 만14세 미만 아동 개인정보 수집 시 법정대리인 동의 여부 확인, 각종 고지ㆍ통지, 고충처리, 분쟁 조정을 위한 기록 보존 등을 목적으로 개인정보를 처리합니다.<br />
 <br />
나. 재화 또는 서비스 제공<br />
물품배송, 서비스 제공, 청구서 발송, 콘텐츠 제공, 요금결제ㆍ정산 등을 목적으로 개인정보를 처리합니다.<br />
 <br />
다. 마케팅 및 광고에의 활용<br />
신규 서비스(제품) 개발 및 맞춤 서비스 제공, 이벤트 및 광고성 정보 제공 및 참여기회 제공 , 접속빈도 파악 또는 회원의 서비스 이용에 대한 통계 등을 목적으로 개인정보를 처리합니다.<br />
 <br />
 <br />
2. 개인정보 파일 현황<br />
 <br />
1. 개인정보 파일명 :한가족몰 개인정보<br />
- 개인정보 항목 : 이메일, 휴대전화번호, 자택주소, 자택전화번호, 비밀번호 질문과 답, 비밀번호, 로그인ID, 성별, 생년월일, 이름, 서비스 이용 기록, 접속 로그, 쿠키, 접속 IP 정보, 결제기록<br />
- 수집방법 : 홈페이지<br />
- 보유근거 : 쇼핑몰 이용을 위한 필수정보<br />
- 보유기간 : 탈퇴시 모든정보 삭제<br />
- 관련법령 : 소비자의 불만 또는 분쟁처리에 관한 기록 : 3년, 대금결제 및 재화 등의 공급에 관한 기록 : 5년, 계약 또는 청약철회 등에 관한 기록 : 5년<br />
 <br />
 <br /> 
3. 개인정보의 처리 및 보유 기간<br />
 <br />
① &lt;(주)한가족몰&gt;은(는) 법령에 따른 개인정보 보유ㆍ이용기간 또는 정보주체로부터 개인정보를 수집시에 동의 받은 개인정보 보유,이용기간 내에서 개인정보를 처리,보유합니다.<br />
 <br />
② 각각의 개인정보 처리 및 보유 기간은 다음과 같습니다.<br />
1.&lt;홈페이지 회원가입 및 관리&gt;<br />
&lt;홈페이지 회원가입 및 관리&gt;와 관련한 개인정보는 수집.이용에 관한 동의일로부터&lt;탈퇴시 모든정보 파기&gt;까지 위 이용목적을 위하여 보유.이용됩니다.<br />
-보유근거 : 홈페이지 이용에 필수정보<br />
-관련법령 : 1)소비자의 불만 또는 분쟁처리에 관한 기록 : 3년<br />
2) 대금결제 및 재화 등의 공급에 관한 기록 : 5년<br />
3) 계약 또는 청약철회 등에 관한 기록 : 5년<br />
 <br />
-예외사유 : <br />
 <br />
 <br />
4. 개인정보의 제3자 제공에 관한 사항<br />
 <br />
① &lt;(주)한가족몰는&gt;('http://https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은(는) 정보주체의 동의, 법률의 특별한 규정 등 개인정보 보호법 제17조 및 제18조에 해당하는 경우에만 개인정보를 제3자에게 제공합니다.<br />
② &lt;(주)한가족몰는&gt;('http://https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은(는) 다음과 같이 개인정보를 제3자에게 제공하고 있습니다.<br />
 <br />
 <br />
5. 개인정보처리 위탁<br />
 <br />
① &lt;(주한가족몰는&gt;('http://https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은(는) 원활한 개인정보 업무처리를 위하여 다음과 같이 개인정보 처리업무를 위탁하고 있습니다.<br />
 <br />
②  &lt;(주)한가족몰는&gt;('http://https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은(는) 위탁계약 체결시 개인정보 보호법 제25조에 따라 위탁업무 수행목적 외 개인정보 처리금지, 기술적ㆍ관리적 보호조치, 재위탁 제한, 수탁자에 대한 관리ㆍ감독, 손해배상 등 책임에 관한 사항을 계약서 등 문서에 명시하고, 수탁자가 개인정보를 안전하게 처리하는지를 감독하고 있습니다.<br />
 <br />
③ 위탁업무의 내용이나 수탁자가 변경될 경우에는 지체없이 본 개인정보 처리방침을 통하여 공개하도록 하겠습니다.<br />
<br />
<br />
6. 정보주체와 법정대리인의 권리ㆍ의무 및 그 행사방법 이용자는 개인정보주체로써 다음과 같은 권리를 행사할 수 있습니다.<br />
① 정보주체는 '몰'에 대해 언제든지 개인정보 열람,정정,삭제,처리정지 요구 등의 권리를 행사할 수 있습니다.<br />
② 제1항에 따른 권리 행사는 '몰'에 대해 개인정보 보호법 시행령 제41조제1항에 따라 서면, 전자우편, 모사전송(FAX) 등을 통하여 하실 수 있으며 '몰'은(는) 이에 대해 지체 없이 조치하겠습니다.<br />
③ 제1항에 따른 권리 행사는 정보주체의 법정대리인이나 위임을 받은 자 등 대리인을 통하여 하실 수 있습니다. 이 경우 개인정보 보호법 시행규칙 별지 제11호 서식에 따른 위임장을 제출하셔야 합니다.<br />
④ 개인정보 열람 및 처리정지 요구는 개인정보보호법 제35조 제5항, 제37조 제2항에 의하여 정보주체의 권리가 제한 될 수 있습니다.<br />
⑤ 개인정보의 정정 및 삭제 요구는 다른 법령에서 그 개인정보가 수집 대상으로 명시되어 있는 경우에는 그 삭제를 요구할 수 없습니다.<br />
⑥ 회사은(는) 정보주체 권리에 따른 열람의 요구, 정정ㆍ삭제의 요구, 처리정지의 요구 시 열람 등 요구를 한 자가 본인이거나 정당한 대리인인지를 확인합니다.<br />
 <br />
 <br />
7. 처리하는 개인정보의 항목 작성 <br />
 <br />
① &lt;(주)한가족몰는&gt;('http://https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은(는) 다음의 개인정보 항목을 처리하고 있습니다.<br />
1&lt;홈페이지 회원가입 및 관리&gt;<br />
- 필수항목 : 이메일, 휴대전화번호, 비밀번호 질문과 답, 비밀번호, 로그인ID, 성별, 생년월일, 이름, 서비스 이용 기록, 접속 로그, 쿠키, 접속 IP 정보, 결제기록<br />
- 선택항목 : 자택주소, 자택전화번호<br />
 <br />
 <br /> 
8. 개인정보의 파기&lt;(주)한가족몰는&gt;('https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은(는) 원칙적으로 개인정보 처리목적이 달성된 경우에는 지체없이 해당 개인정보를 파기합니다. 파기의 절차, 기한 및 방법은 다음과 같습니다.<br />
-파기절차<br />
이용자가 입력한 정보는 목적 달성 후 별도의 DB에 옮겨져(종이의 경우 별도의 서류) 내부 방침 및 기타 관련 법령에 따라 일정기간 저장된 후 혹은 즉시 파기됩니다. 이 때, DB로 옮겨진 개인정보는 법률에 의한 경우가 아니고서는 다른 목적으로 이용되지 않습니다.<br />
 <br />
-파기기한<br />
이용자의 개인정보는 개인정보의 보유기간이 경과된 경우에는 보유기간의 종료일로부터 5일 이내에, 개인정보의 처리 목적 달성, 해당 서비스의 폐지, 사업의 종료 등 그 개인정보가 불필요하게 되었을 때에는 개인정보의 처리가 불필요한 것으로 인정되는 날로부터 5일 이내에 그 개인정보를 파기합니다.<br />
-파기방법<br />
전자적 파일 형태의 정보는 기록을 재생할 수 없는 기술적 방법을 사용합니다.<br />
 <br />
 <br />
9. 개인정보 자동 수집 장치의 설치ㆍ운영 및 거부에 관한 사항<br />
① (주) 회사 은 개별적인 맞춤서비스를 제공하기 위해 이용정보를 저장하고 수시로 불러오는 ‘쿠키(cookie)’를 사용합니다. ② 쿠키는 웹사이트를 운영하는데 이용되는 서버(http)가 이용자의 컴퓨터 브라우저에게 보내는 소량의 정보이며 이용자들의 PC 컴퓨터내의 하드디스크에 저장되기도 합니다. 가. 쿠키의 사용 목적 : 이용자가 방문한 각 서비스와 웹 사이트들에 대한 방문 및 이용형태, 인기 검색어, 보안접속 여부, 등을 파악하여 이용자에게 최적화된 정보 제공을 위해 사용됩니다. 나. 쿠키의 설치ㆍ운영 및 거부 : 웹브라우저 상단의 도구&gt;인터넷 옵션&gt;개인정보 메뉴의 옵션 설정을 통해 쿠키 저장을 거부 할 수 있습니다. 다. 쿠키 저장을 거부할 경우 맞춤형 서비스 이용에 어려움이 발생할 수 있습니다.<br />
 <br />
 <br />
10. 개인정보 보호책임자 작성 <br />
 <br />
① &lt;(주)한가족몰는&gt;('https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet') 은(는) 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다.<br />
▶ 개인정보 보호책임자 <br />
성명 : 한백희<br />
직책 : 솔루션사업부서<br />
직급 : cs팀<br />
연락처 :02-3489-3239, dmmall2020@gmail.com<br />
※ 개인정보 보호 담당부서로 연결됩니다.<br />
 <br />
▶ 개인정보 보호 담당부서<br />
부서명 : 솔루션사업부<br />
담당자 : 한백희<br />
연락처 : 02-3489-3239, dmmall2020@gmail.com <br />
<br />
② 정보주체께서는 (주)한가족몰는&gt;('https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet') 의 서비스(또는 사업)을 이용하시면서 발생한 모든 개인정보 보호 관련 문의, 불만처리, 피해구제 등에 관한 사항을 개인정보 보호책임자 및 담당부서로 문의하실 수 있습니다. &lt;(주)한가족몰는&gt;('http://https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet') 은(는) 정보주체의 문의에 대해 지체 없이 답변 및 처리해드릴 것입니다.<br />
 <br />
 <br />
11. 개인정보 처리방침 변경 <br />
①이 개인정보처리방침은 시행일로부터 적용되며, 법령 및 방침에 따른 변경내용의 추가, 삭제 및 정정이 있는 경우에는 변경사항의 시행 7일 전부터 공지사항을 통하여 고지할 것입니다.<br />
 <br />
 <br />
12. 개인정보의 안전성 확보 조치 &lt;(주)트리&gt;('샵온몰')은(는) 개인정보보호법 제29조에 따라 다음과 같이 안전성 확보에 필요한 기술적/관리적 및 물리적 조치를 하고 있습니다.<br />
1. 정기적인 자체 감사 실시<br />
 개인정보 취급 관련 안정성 확보를 위해 정기적(분기 1회)으로 자체 감사를 실시하고 있습니다.<br />
 <br />
2. 개인정보 취급 직원의 최소화 및 교육<br />
 개인정보를 취급하는 직원을 지정하고 담당자에 한정시켜 최소화 하여 개인정보를 관리하는 대책을 시행하고 있습니다.<br />
 <br />
3. 내부관리계획의 수립 및 시행<br />
 개인정보의 안전한 처리를 위하여 내부관리계획을 수립하고 시행하고 있습니다.<br />
 <br />
4. 해킹 등에 대비한 기술적 대책<br />
&lt;(주)한가족몰는&gt;('https://cybertronchain.com/wallet'이하  'https://cybertronchain.com/wallet')은 해킹이나 컴퓨터 바이러스 등에 의한 개인정보 유출 및 훼손을 막기 위하여 보안프로그램을 설치하고 주기적인 갱신ㆍ점검을 하며 외부로부터 접근이 통제된 구역에 시스템을 설치하고 기술적/물리적으로 감시 및 차단하고 있습니다.<br />
 <br />
5. 개인정보의 암호화<br />
 이용자의 개인정보는 비밀번호는 암호화 되어 저장 및 관리되고 있어, 본인만이 알 수 있으며 중요한 데이터는 파일 및 전송 데이터를 암호화 하거나 파일 잠금 기능을 사용하는 등의 별도 보안기능을 사용하고 있습니다.<br />
 <br />
6. 접속기록의 보관 및 위변조 방지<br />
 개인정보처리시스템에 접속한 기록을 최소 6개월 이상 보관, 관리하고 있으며, 접속 기록이 위변조 및 도난, 분실되지 않도록 보안기능 사용하고 있습니다.<br />
 <br />
7. 개인정보에 대한 접근 제한<br />
 개인정보를 처리하는 데이터베이스시스템에 대한 접근권한의 부여,변경,말소를 통하여 개인정보에 대한 접근통제를 위하여 필요한 조치를 하고 있으며 침입차단시스템을 이용하여 외부로부터의 무단 접근을 통제하고 있습니다.<br />
 <br />
8. 비인가자에 대한 출입 통제 <br />
개인정보를 보관하고 있는 물리적 보관 장소를 별도로 두고 이에 대해 출입통제 절차를 수립, 운영하고 있습니다.<br />
			</div>

			<div id="show_msg" style="display: none;"><?php echo $langArr['login_agree_check_msg']; ?></div>
		
			<form method="post" name="form_auth">
				<input type="hidden" name="ordr_idxx" id="auth_ordr_idxx" class="frminput" value="" readonly="readonly" maxlength="40"/>

				<div id="show_pay_btn">
					<input type="submit" id="id_auth_btn" class="btn btn-success" onclick="return auth_type_check();" value="<?php echo $langArr['login_agree_btn1']; ?>" /><!-- personal_identification -->
				</div>

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
				<input type="hidden" name="param_opt_1"  value="register" /> <!-- 가맹점 사용 필드 (인증완료시 리턴)-->
				<input type="hidden" name="param_opt_2"  value="" /> 
				<input type="hidden" name="param_opt_3"  value="" /> 

			</form>


			<div class="bottom">
				<a  href="login.php" class="loginField"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a><br />
				<a href="forgetpassword.php"><?php echo !empty($langArr['forgot_password']) ? $langArr['forgot_password'] : "Forgot Password ?"; ?></a>
			</div>


		</div>
		
		
		</div>
</div>

<iframe id="kcp_cert" name="kcp_cert" width="100%" height="700" frameborder="0" scrolling="no" style="display: none;"></iframe>


<script type="text/javascript">
$(function() {
	init_orderid();
});


// MOBILE(SMART)
function auth_type_check() {

	if ( document.getElementById('agree_chk1').checked == false || document.getElementById('agree_chk2').checked == false)
	{
		$("#show_msg").css('display', 'block');
		return false;
	}
	$("#show_msg").css('display', 'none');


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
			
			document.getElementById( "cert_info" ).style.display = "none";
			document.getElementById( "kcp_cert"  ).style.display = "";
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

		auth_form.action = "./auth.pro.req_r.php"; // 인증창 호출 및 결과값 리턴 페이지 주소
		
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


function login_agree_check(num){
	if ($("#agree_more"+num+"_contents").attr('class') == 'none')
	{
		$("#agree_more"+num+"_contents").removeClass('none');
		$("#agree_more"+num+"_more").addClass('none');
		$("#agree_more"+num+"_close").removeClass('none');
	} else {
		$("#agree_more"+num+"_contents").addClass('none');
		$("#agree_more"+num+"_more").removeClass('none');
		$("#agree_more"+num+"_close").addClass('none');
	}
}

</script>

<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>