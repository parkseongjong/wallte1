<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:./index.php');
	 exit;
}
?>
<style>
.sendlog_link {
	list-style-type: none;
}
.sendlog_link li {
	display: inline-block;
	margin: 5px 20px 5px 0;
}
</style>
<?
$db = getDbInstance();

$page = filter_input(INPUT_GET, 'page');
$type = filter_input(INPUT_GET, 'type');

if ($page == "") {
    $page = 1;
}

$pagelimit = 20;
$filter_col = "id";
$order_by = "desc";

if ($type == 'eth') {
	$title = $langArr['sendlog_subject2'];
	$tbl_name = 'ethsend';
	$select = array('id', 'user_id', 'tx_id', 'ethmethod', 'amount', 'coin_type', 'to_address', 'from_address', 'created');
} else if ($type == 'us') { // user_transactions
	$title = $langArr['sendlog_subject3'];
	$select = array('id', 'coin_type', 'sender_id', 'reciver_address', 'amount', 'fee_in_eth', 'fee_in_gcg', 'status', 'created_at', 'transactionId');
	$tbl_name = 'user_transactions';
} else { // user_transactions_all
	$title = $langArr['sendlog_subject1'];
	$select = array('id', 'send_type', 'coin_type', 'from_id', 'to_id', 'from_address', 'to_address', 'amount', 'fee', 'transactionId', 'status', 'created_at');
	$tbl_name = 'user_transactions_all';
}

$db->orderBy($filter_col, $order_by);

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate($tbl_name, $page, $select);
$total_pages = $db->totalPages;

include_once './includes/header.php';
?>

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
		<h1 class="page-header"><?php echo $title; ?></h1>
	</div>
</div>
 <?php include('./includes/flash_messages.php');
 if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') {
	 if ($type == '') {
		?>
		<a href="sendlog_list.pro.php?type=" title="Status value change">Status value change</a>
	 <?php
	}
 }
?>
<ul class="sendlog_link">
	<li><a href="sendlog_list.php" title="<?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?>">
		<?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?> (<?php echo !empty($langArr['sendlog_subject_text1']) ? $langArr['sendlog_subject_text1'] : "After 2020-05-18"; ?>)
	</a>
	<li><a href="sendlog_list.php?type=us" title="<?php echo !empty($langArr['sendlog_subject3']) ? $langArr['sendlog_subject3'] : "User transaction Logs"; ?>"><?php echo !empty($langArr['sendlog_subject3']) ? $langArr['sendlog_subject3'] : "User transaction Logs"; ?></a>
	<li><a href="sendlog_list.php?type=eth" title="<?php echo !empty($langArr['sendlog_subject2']) ? $langArr['sendlog_subject2'] : "Eth Logs"; ?>"><?php echo !empty($langArr['sendlog_subject2']) ? $langArr['sendlog_subject2'] : "Eth Logs"; ?></a>
	<li>
	<li>
</ul>
 <div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<?php
						if ($type == 'eth') {
							?>
							<tr>
								<?php if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?><th>ID</th><?php } ?>
								<th>Use ID</th>
								<th>TransactionId</th>
								<?php if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?><th>ethmethod</th><?php } ?>
								<th>Amount</th>
								<th>Coin Type</th>
								<th>To Wallet Address</th>
								<th>From Wallet Address</th>
								<th>date</th>
							</tr>
							<?php
							} else if ($type == 'us') {
							?>
							<tr>
								<?php if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?><th>ID</th><?php } ?>
								<th>Coin Type</th>
								<th>From ID</th>
								<th>To Wallet Address</th>
								<th>TransactionId</th>
								<th>Amount</th>
								<th>Fee in eth</th>
								<th>Fee in gcg</th>
								<th>Status</th>
								<th>date</th>
							</tr>
							<?php
						} else {
						?>
							<tr>
								<?php if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?><th>ID</th><?php } ?>
								<th>Send Type</th>
								<th>Coin Type</th>
								<th>From ID</th>
								<th>To ID</th>
								<th>From Wallet Address</th>
								<th>To Wallet Address</th>
								<th>Amount</th>
								<th>Fee</th>
								<th>TransactionId</th>
								<th>Status</th>
								<th>date</th>
							</tr>
						<?php } ?>
					</thead>
					<tbody>

					<?php 

						foreach ($resultData as $row) {
							if ( isset($row) ) {
								if ($type == 'eth') {
									$txid = !empty($row['tx_id']) ? $row['tx_id'] : '';
									$url =  !empty($row['tx_id']) ? 'https://etherscan.io/tx/'.$row['tx_id'] : '';
									?>
								
									<tr>
										<?php if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?><td><?php echo $row['id']; ?></td><?php } ?>
										<td><?php echo $row['user_id']; ?></td>
										<td><?php
											if ( !empty($url) ) {
												echo '<a href="'.$url.'" title="'.$txid.'" target="_blank">'.$txid.'</a>';
											}
										?></td>
										<?php if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?><td><?php echo $row['ethmethod']; ?></td><?php } ?>
										<td><?php echo $row['amount']; ?></td>
										<td><?php echo $row['coin_type']; ?></td>
										<td><?php echo $row['to_address']; ?></td>
										<td><?php echo $row['from_address']; ?></td>
										<td><?php echo $row['created']; ?></td>
									</tr>
								<?php
								} else if ($type == 'us') {
									$txid = !empty($row['transactionId']) ? $row['transactionId'] : '';
									$url =  !empty($row['transactionId']) ? 'https://etherscan.io/tx/'.$row['transactionId'] : '';
									?>
								
									<tr>
										<?php if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?><td><?php echo $row['id']; ?></td><?php } ?>
										<td><?php echo $row['coin_type']; ?></td>
										<td><?php echo $row['sender_id']; ?></td>
										<td><?php echo $row['reciver_address']; ?></td>
										<td><?php
											if ( !empty($url) ) {
												echo '<a href="'.$url.'" title="'.$txid.'" target="_blank">'.$txid.'</a>';
											}
										?></td>
										<td><?php echo $row['amount']; ?></td>
										<td><?php echo $row['fee_in_eth']; ?></td>
										<td><?php echo $row['fee_in_gcg']; ?></td>
										<td><?php echo $row['status']; ?></td>
										<td><?php echo $row['created_at']; ?></td>
									</tr>
								<?php
								} else {
									$txid = !empty($row['transactionId']) ? $row['transactionId'] : '';
									$url =  !empty($row['transactionId']) ? 'https://etherscan.io/tx/'.$row['transactionId'] : '';
									?>
								
									<tr>
										<?php if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?><td><?php echo $row['id']; ?></td><?php } ?>
										<td><?php echo $row['send_type']; ?></td>
										<td><?php echo $row['coin_type']; ?></td>
										<td><?php echo $row['from_id']; ?></td>
										<td><?php echo !empty($row['to_id']) ? $row['to_id'] : ''; ?></td>
										<td><?php echo $row['from_address']; ?></td>
										<td><?php echo $row['to_address']; ?></td>
										<td><?php echo $row['amount']; ?></td>
										<td><?php echo $row['fee']; ?></td>
										<td><?php
											if ( !empty($url) ) {
												echo '<a href="'.$url.'" title="'.$txid.'" target="_blank">'.$txid.'</a>';
											}
										?></td>
										<td><?php echo $row['status']; ?></td>
										<td><?php echo $row['created_at']; ?></td>
									</tr>
							<?php
								} // if ($type)
							}
						} ?>   
					</tbody>
				</table>
			
			</div>

		    <!--    Pagination links-->
		    <div class="text-center">
				<?php
				$showRecordPerPage = 10;
				if(isset($_GET['page']) && !empty($_GET['page'])){
					$currentPage = $_GET['page'];
				}else{
					$currentPage = 1;
				}
				$startFrom = ($currentPage * $showRecordPerPage) - $showRecordPerPage;
				$lastPage = $total_pages;
				$firstPage = 1;
				$nextPage = $currentPage + 1;
				$previousPage = $currentPage - 1;

				$a_link = '';
				if ( !empty($type) ) {
					$a_link = 'type='.$type.'&';
				}
				?>
					
				<ul class="pagination">
					<?php if($currentPage != $firstPage) { ?>
						<li class="page-item">
							<a class="page-link" href="?<?php echo $a_link; ?>page=<?php echo $firstPage ?>" tabindex="-1" aria-label="Previous">
								<span aria-hidden="true">First</span>
							</a>
						</li>
					<?php } ?>
					<?php if($currentPage >= 2) { ?>
						<li class="page-item"><a class="page-link" href="?<?php echo $a_link; ?>page=<?php echo $previousPage ?>"><?php echo $previousPage ?></a></li>
					<?php } ?>
					<li class="page-item active"><a class="page-link" href="?<?php echo $a_link; ?>page=<?php echo $currentPage ?>"><?php echo $currentPage ?></a></li>
					<?php if($currentPage != $lastPage) { ?>
						<li class="page-item"><a class="page-link" href="?<?php echo $a_link; ?>page=<?php echo $nextPage ?>"><?php echo $nextPage ?></a></li>
						<li class="page-item">
							<a class="page-link" href="?<?php echo $a_link; ?>page=<?php echo $lastPage ?>" aria-label="Next">
								<span aria-hidden="true">Last</span>
							</a>
						</li>
					<?php } ?>
				</ul>			
		        <?php
		        if (!empty($_GET)) {
		            //we must unset $_GET[page] if built by http_build_query function
		            unset($_GET['page']);
		            $http_query = "?" . http_build_query($_GET);
		        } else {
		            $http_query = "?";
		        }
		        if ($total_pages > 1) {
		            echo '<ul class="pagination text-center">';
		            for ($i = 1; $i <= $total_pages; $i++) {
		                ($page == $i) ? $li_class = ' class="active"' : $li_class = "";
		            }
		            echo '</ul></div>';
		        }
		        ?>
		    </div>
	    </div>
	</div>

</div>

<?php include_once './includes/footer.php'; ?>