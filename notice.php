<?php
session_start();
require_once './config/config.php';
//include_once 'includes/header.php'; 
?> 
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no" />

        <meta name="description" content="">
        <meta name="author" content="">
        <title><?php echo !empty($langArr['title']) ? $langArr['title'] : "CyberTron Coin | Wallet"; ?></title>
		<link rel="icon"  href="favicon.ico" />
        <link  rel="stylesheet" href="css/bootstrap.min.css"/>
        <link href="js/metisMenu/metisMenu.min.css" rel="stylesheet">
        <link href="css/sb-admin-2.css?v=<?php echo rand(1000,9999);?>" rel="stylesheet">
        <link href="fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<link  rel="stylesheet" href="css/common.css" type="text/css" />
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script src="js/jquery.min.js" type="text/javascript"></script> 
		<script async src="https://www.googletagmanager.com/gtag/js?id=G-3XFB95C8VL"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());
		
		  gtag('config', 'G-3XFB95C8VL');
		</script>

    </head>
    <body>

<?php
$text1 = !empty($langArr['notice_1']) ? $langArr['notice_1'] : 'CTC Wallet 2.0';
$text2 = !empty($langArr['notice_2']) ? $langArr['notice_2'] : ' version released.';
$text3 = !empty($langArr['notice_3']) ? $langArr['notice_3'] : 'Please press the button below to move<br />to install the new version and use it.';
$text4 = !empty($langArr['notice_4']) ? $langArr['notice_4'] : 'Existing CTC Wallet 1.0 will no longer be updated.';
$text5 = !empty($langArr['notice_5']) ? $langArr['notice_5'] : 'Thank you for using our CTC Wallet.';
	
$useragent=$_SERVER['HTTP_USER_AGENT'];
if (stristr($useragent, "android") == true ) {
	//$link = 'market://details?id=com.cybertronchain.wallet2';
	$img = 'images/notice_playstore.png';
    //$link = 'market://details?id=com.cybertronchain.wallet2';
	$link = 'http://market.android.com/details?id=com.cybertronchain.wallet2';
	// Intent://호스트#Intent;scheme=스키마;package=com.cybertronchain.wallet2;end
	$class = 'playstore_logo_img';
} else if(strpos($useragent,"iPhone") || strpos($useragent,"iPod") || strpos($useragent, "iPad")) {
	$link = 'https://cybertronchain.com/wallet2/login.php';
	$img = 'images/eth_logo.png';
	$class = 'iphone_eth_logo';
} else {
	$link = 'https://play.google.com/store/apps/details?id=com.cybertronchain.wallet2';
	$img = 'images/notice_playstore.png';
	$class = 'playstore_logo_img';
}
?>

<?php
    if (stristr($useragent, "android") == true ) {
?>
<script>
$(function(){
    //updateAndroidMarketLinks();

    function updateAndroidMarketLinks(){
        $("a[href^='http://market.android.com/']").each(function() {
            this.href = this.href.replace(/^http:\/\/market\.android\.com\//, "market://");
        });
    }
});
</script>
<?php
    }
?>

<div class="notice">
	<div class="notice1">
		<div class="logo"><img src="images/notice_logo.png" alt="logo" /></div>
		<div class="text_a1">
			<span class="text1"><?php echo $text1; ?></span><span class="text2"><?php echo $text2; ?></span><br />
			<span class="text2"><?php echo $text3; ?></span>
		</div>
		<a href="<?php echo $link; ?>" target="_blank"  title="CTC Wallet(TP3) 2.0" class="playstore_logo"><img src="<?php echo $img; ?>" alt="wallet 2.0" class="<?php echo $class; ?>"></a>
		<!--<a href="javascript:;" onclick="market_move();" title="CTC Wallet(TP3) 2.0" class="playstore_logo"><img src="images/notice_playstore.png" alt="playstore"></a>-->
		<div class="text_a2">
			<p class="text3"><?php echo $text4; ?></p>
			<p class="text3"><?php echo $text5; ?></p>
		</div>
		
		 <select name="getlang" onChange="changeLanguage(this);" class="lang1">
			<option <?php echo ($_SESSION['lang']=='ko') ? 'selected' : ""; ?> value="ko">KOR</option>
			<option <?php echo ($_SESSION['lang']=='en') ? 'selected' : ""; ?> value="en">ENG</option>
		</select>
	</div>
</div>
<style type="text/css">
html, body {
	background-color: #FFEB61;
	font-size: 0.2vmin;
}

.notice {
	text-align: center;
	width: 100%;
	height: 100%;
}
.notice1 {
	max-width: 900px;
	margin: 0 auto;
	padding: 0 10px;
}
.logo {
	margin-top: 17.01vh;
	margin-bottom: 29.52vh;
}
.logo img {
	width: 61.6%;
	height: auto;
}

.text_a1 {
	margin-bottom: 6.111vh;
}
.text1 {
	font-size: 15rem;
	color: #000000;
	font-weight: bold;
}
.text2 {
	font-size: 15rem;
	color: #000000;
}

.playstore_logo {
	width: 43.14%;
}
.playstore_logo_img {
	width: 43.14%;
	height: auto;
}
.iphone_eth_logo {
	width: auto;
	height: 80px;
}
.text_a2 {
	margin-top: 8.928vh;
	margin-bottom: 4.77vh;
}
.text3 {
	font-size: 12rem;
	color: #000000;
}



.lang1 {
	padding: 11px;
	background-color: #FFEB61;
	border-radius: 3px;
	border: 1px solid #C0C0C0;
	color: #000000;
}

.lang1 {
	font-size: 12rem;
}
.lang1 option {
	font-size: 12rem;
}
</style>

<script>
/*
function market_move() {
	var usagent = navigator.userAgent.toLocaleLowerCase();
	if (usagent == 'android-web-view')
	{
		location.href='market://details?id=com.cybertronchain.wallet2';
		location.href ="Intent://호스트#Intent;scheme=스키마;package=com.cybertronchain.wallet2;end";
	}
	else if (usagent.search('android') > -1)
	{
	}
}*/
</script>
<?php include "includes/footer.php"; ?>
