<?php 

require('helpers.php');


$wallertAddress = '';$db = getDbInstance();

// for language
/* if(empty($_SESSION['lang'])) {
	$_SESSION['lang'] = "ko";
}
$langFolderPath = file_get_contents("lang/".$_SESSION['lang']."/index.json");
$langArr = json_decode($langFolderPath,true); */
// for language

// for check wallertAddress is empty or not start 
if(isset($_SESSION['user_id'])) {
	
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');
 

	if ($db->count > 0) {
		$wallertAddress = $row[0]['wallet_address'];
		$Login_userName = $row[0]['name'];
	
	}
}



	$result = $db->get("messages");
	$last = end($result);
	$last1 = $last['message_text'];
	
	$last2 = end($result);
	//print_r($last2);die('hello');
	$abc = $last2['status'];
	//print_r($abc);die('hello');
	if($abc=='Y'){
	// echo '<marquee>'.$last1.'</marquee>' ;
	}
  
  

 ?>

<?php
//if(empty($_SESSION['user_id']) || $_SESSION['user_id']!=1393){ 
?>
<!--<!doctype html>
<title>Site Maintenance</title>
<style>
  body { text-align: center; padding: 150px; }
  h1 { font-size: 50px; }
  body { font: 20px Helvetica, sans-serif; color: #333; }
  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
  a { color: #dc8100; text-decoration: none; }
  a:hover { color: #333; text-decoration: none; }
</style>

<article>
    <h1>We&rsquo;ll be back soon!</h1>
    <div>
        <p>Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. We&rsquo;ll be back online shortly!</p>
        <p>&mdash; The Team</p>
    </div>
</article>-->
<?php
//die;
//}
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
		<style>
		.sidebar-nav select {
			background: #ffcd07;
			border: none;
			padding: 11px;
			border-radius: 3px;
			width: 100%;
			margin-top: 10px;
			margin-bottom: 10px;
		}
		</style>
    </head>
    <body>
		<div id="loading-o" class="none">
			<div class="loading active">
				<div><i class="fa fa-cog"></i>
					<span>Working...</span>
				</div>
			</div>
		</div>
        <div id="wrapper">
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true ) { ?>
                <nav class="navbar navbar-default navbar-static-top" role="navigation" >
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="sr-only"><?php echo !empty($langArr['navigation']) ? $langArr['navigation'] : "Toggle navigation"; ?></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>		
                    </div>
                    <div class="navbar-default sidebar" role="navigation" style="margin-top:0px;overflow:auto;">
						<div class="sidebar-nav navbar-collapse collapse" aria-expanded="false">
							<div class="logo-part">
								<a href="index.php"><img src="images/eth_logo.png" width="100px" height="100px"></a>
							</div>
							<center>
							<select name="getlang" onChange="changeLanguage(this);" style="width:200px;height:40px;">
								<option <?php echo ($_SESSION['lang']=='ko') ? 'selected' : ""; ?> value="ko">한국어</option>
								<option <?php echo ($_SESSION['lang']=='en') ? 'selected' : ""; ?> value="en">English</option>
							</select>
							</center>
                            <ul class="nav" id="side-menu">

								<li><a href="index.php"><i class="fa fa-refresh fa-fw"></i> <?php echo !empty($langArr['dashboard']) ? $langArr['dashboard'] : "Dashboard"; ?></a></li>

								<li><a href="exchange.php"><i class="fa fa-exchange fa-fw"></i> <?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?></a></li>

								<!--<li><a href="send_token.php"><i class="fa fa-download fa-fw"></i> <?php echo !empty($langArr['send_ctc']) ? $langArr['send_ctc'] : "Send"; ?></a></li>

								<li><a href="receive_token.php"><i class="fa fa-download fa-fw"></i> <?php echo !empty($langArr['receive']) ? $langArr['receive'] : "Receive"; ?></a></li>
								
								<li><a href="coin_bank.php"><i class="fa fa-bank fa-fw"></i> <?php echo !empty($langArr['coin_bank']) ? $langArr['coin_bank'] : "Coin bank"; ?></a></li>-->

								<li><a href="all_stores.php"><i class="fa fa-gear fa-fw"></i> <?php echo !empty($langArr['customer_stores']) ? $langArr['customer_stores'] : "All Stores"; ?></a></li> 

								<li><a href="store_transactions_user.php"><i class="fa fa-download fa-fw"></i> <?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transactions"; ?></a></li>

								<li><a href="https://etherscan.io/address/<?php echo $wallertAddress; ?>" target="_blank"><i class="fa fa-history fa-fw"></i> <?php echo !empty($langArr['view_on_etherscan']) ? $langArr['view_on_etherscan'] : "View On Etherscan"; ?></a></li>

								<li><a href="profile.php"><i class="fa fa-user fa-fw"></i> <?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></a></li>	

									
								
								<li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> <?php echo !empty($langArr['logout']) ? $langArr['logout'] : "Logout"; ?></a>						

								<!--<li><a href="send_token.php"><i class="fa fa-share fa-fw"></i> Send Token</a></li>-->
								
								<!--<li><a href="users-trans.php"><i class="fa fa-user fa-fw"></i> Transactions</a></li> -->
								
								<!--<li><a href="kyc.php"><i class="fa fa-download fa-fw"></i> KYC</a></li>-->
								
								<!--<li><a href="coin_change.php"><i class="fa fa-exchange fa-fw"></i> Coin Change</a></li>-->
								
							<?php if ($_SESSION['admin_type'] == 'admin') { ?>
							
								<li style="text-align:center;font-weight:bold;text-decoration:underline;"> <?php echo !empty($langArr['admin_tools']) ? $langArr['admin_tools'] : "Admin Tools"; ?> </li>
							
								<li><a href="admin_users.php"><i class="fa fa-user fa-fw"></i> <?php echo !empty($langArr['registered_user']) ? $langArr['registered_user'] : "Registered User"; ?></a></li>  
																
								<li><a href="store_transactions.php"><i class="fa fa-exchange fa-fw"></i> <?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transaction"; ?></a></li>   

								<li><a href="stores.php"><i class="fa fa-gear fa-fw"></i> <?php echo !empty($langArr['admin_stores']) ? $langArr['admin_stores'] : "Admin Stores"; ?></a></li> 

								<li><a href="blocked_ip.php"><i class="fa fa-gear fa-fw"></i> <?php echo !empty($langArr['blocked_ip']) ? $langArr['blocked_ip'] : "Blocked IPs"; ?></a></li>  								

								<li><a href="settings.php"><i class="fa fa-gear fa-fw"></i> <?php echo !empty($langArr['settings']) ? $langArr['settings'] : "Settings"; ?></a></li> 			
								
								
								<li><a href="sendlog_list.php"><i class="fa fa-gear fa-fw"></i> <?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?></a></li>

								<?php if ($_SESSION['user_id'] == '5137') { ?>
									<li><a href="change_transfer_pass.php"><i class="fa fa-user fa-fw"></i> <?php echo !empty($langArr['change_transfer_pass_set']) ? $langArr['change_transfer_pass_set'] : "Set transmission password"; ?></a></li>	
								<?php } ?>

								<li>&nbsp;</li>   															
								
								<li>&nbsp;</li>   															

								<li>&nbsp;</li>   															
								
								<li>&nbsp;</li>  
								 															
								<!--li><a href="admin_wallet_blank_users.php"><i class="fa fa-user fa-fw"></i> <?php echo !empty($langArr['registered_user']) ? $langArr['registered_user'] : "Blank Wallet Address Users"; ?></a></li-->

								<!--li><a href="https://gruhn.github.io/vue-qrcode-reader/demos/DecodeAll.html"><i class="fa fa-gear fa-fw"></i> t</a></li-->

								<!--<li><a href="show_message.php"><i class="fa fa-user fa-fw"></i><?php //echo !empty($langArr['messages']) ? $langArr['messages'] : "Messages"; ?></a></li> --->

							<?php }	?>	

                            </ul>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </nav>
            <?php } ?>
