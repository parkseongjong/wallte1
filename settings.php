<?php



















/*
2020-06-01, YMJ

페이지가 변경되었습니다.
The page has been changed.

아래 적힌 페이지에서 수정해주시기 바랍니다.
Please correct it in the page written below.

/var/www/html/wallet2/settings.php

*/
























error_reporting("E_ALL");
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
 //Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
   $_SESSION['failure'] = "You can't perform this action!";
        //Redirect to the listing page,
        header('location: index.php');
}

$db = getDbInstance();

$getExchangeRate = $db->where("module_name", 'exchange_rate')->getOne('settings');
$getExchangePrice = $getExchangeRate['value'];


$getPointsExchange = $db->where("module_name", 'points_exchange')->getOne('settings');
$getPointsRate = $getPointsExchange['value'];

$getCtcFee = $db->where("module_name", 'send_ctc_fee')->getOne('settings');
$getCtcFeeVal = $getCtcFee['value'];

$getTokenFee = $db->where("module_name", 'send_token_fee')->getOne('settings');
$getTokenFeeVal = $getTokenFee['value'];

$getSendFreeCtc = $db->where("module_name", 'send_free_ctc')->getOne('settings');
$getSendFreeCtcVal = $getSendFreeCtc['value'];

$getSendFreeEth = $db->where("module_name", 'send_free_eth')->getOne('settings');
$getSendFreeEthVal = $getSendFreeEth['value'];


$getMinTransferAmountTp3 = $db->where("module_name", 'min_transfer_amount_tp3')->getOne('settings');
$getMinTransferAmountTp3Val = $getMinTransferAmountTp3['value'];

$getMinTransferAmountMC = $db->where("module_name", 'min_transfer_amount_mc')->getOne('settings');
$getMinTransferAmountMCVal = $getMinTransferAmountMC['value'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    $data_to_store = filter_input_array(INPUT_POST);
	$data_to_store['created_at'] = date('Y-m-d H:i:s');
	
/*
	echo "<pre>";
	var_export($data_to_store);
	die();
*/
	
	
	getDbInstance()->where("id", $getExchangeRate['id'])->update('settings', ['value'=>$data_to_store['exchange_rate']]);
	getDbInstance()->where("id", $getPointsExchange['id'])->update('settings', ['value'=>$data_to_store['points_exchange']]);
	getDbInstance()->where("id", $getCtcFee['id'])->update('settings', ['value'=>$data_to_store['send_ctc_fee']]);
	getDbInstance()->where("id", $getTokenFee['id'])->update('settings', ['value'=>$data_to_store['send_token_fee']]);
	getDbInstance()->where("id", $getSendFreeCtc['id'])->update('settings', ['value'=>$data_to_store['send_free_ctc']]);
	getDbInstance()->where("id", $getSendFreeEth['id'])->update('settings', ['value'=>$data_to_store['send_free_eth']]);
	getDbInstance()->where("id", $getMinTransferAmountTp3['id'])->update('settings', ['value'=>$data_to_store['min_transfer_amount_tp3']]);
	getDbInstance()->where("id", $getMinTransferAmountMC['id'])->update('settings', ['value'=>$data_to_store['min_transfer_amount_mc']]);

	$_SESSION['success'] = "Settings updated successfully!";
	header('location: settings.php');
	exit();

}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-12">
				<h2 class="page-header"><?php echo $langArr['settings']; ?></h2>
			</div>			
	</div>

	<?php include('./includes/flash_messages.php') ?>
	
	<div class="row">
		 <div class="col-lg-4">
			<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
				<fieldset>
					<div class="form-group">
						<label for="exchange_rate">Exchange Rate (1 ETH = <?php echo $getExchangePrice; ?> CTC)</label>
						  <input type="text" name="exchange_rate" value="<?php echo $getExchangePrice; ?>"  class="form-control" required="required" id="exchange_rate">
					</div> 
					<div class="form-group">
						<label for="points_exchange">Points Exchange (1 Bee Points = ₩<?php echo $getPointsRate; ?>)</label>
						  <input type="text" name="points_exchange" value="<?php echo $getPointsRate; ?>"  class="form-control" required="required" id="points_exchange" >
					</div>
					<div class="form-group">
						<label for="points_exchange">Send CTC Fee (%) </label>
						  <input type="text" name="send_ctc_fee" value="<?php echo $getCtcFeeVal; ?>"  class="form-control" required="required" id="send_ctc_fee" >
					</div>
					<div class="form-group">
						<label for="points_exchange">Send Token Fee (In CTC) </label>
						  <input type="text" name="send_token_fee" value="<?php echo $getTokenFeeVal; ?>"  class="form-control" required="required" id="send_ctc_fee" >
					</div>	
					<div class="form-group">
						<label for="points_exchange">Send Free CTC </label>
						  <input type="text" name="send_free_ctc" value="<?php echo $getSendFreeCtcVal; ?>"  class="form-control" required="required" id="send_free_ctc" >
					</div>
					<div class="form-group">
						<label for="points_exchange">Send Free ETH (In ETH)</label>
						  <input type="text" name="send_free_eth" value="<?php echo $getSendFreeEthVal; ?>"  class="form-control" required="required" id="send_free_eth" >
					</div>
					
					<div class="form-group">
						<label for="points_exchange">Minimum Transfer Amount TP3 (In TP3) </label>
						  <input type="text" name="min_transfer_amount_tp3" value="<?php echo $getMinTransferAmountTp3Val; ?>"  class="form-control" required="required" id="min_transfer_amount_tp3" >
					</div>
					
					<div class="form-group">
						<label for="points_exchange">Minimum Transfer Amount MC (In MC)</label>
						  <input type="text" name="min_transfer_amount_mc" value="<?php echo $getMinTransferAmountMCVal; ?>"  class="form-control" required="required" id="min_transfer_amount_mc" >
					</div>


					<div class="form-group text-center">
						<label></label>
						<button type="submit" class="btn btn-warning" ><?php echo $langArr['submit']; ?> <span class="glyphicon glyphicon-send"></span></button>
					</div>            
				</fieldset>
			</form>
		 </div>
	</div>
</div>


<script type="text/javascript">
$(document).ready(function(){
   $("#customer_form").validate({
       rules: {
            exchange_rate: {
                required: true,
                minlength: 3
            },
            points_exchange: {
                required: true,
                minlength: 3
            },   
        }
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>
