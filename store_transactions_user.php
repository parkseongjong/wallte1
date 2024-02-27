<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';


require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;



//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 //header('Location:index.php');
}

$db = getDbInstance();
//Get data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$del_id = filter_input(INPUT_GET, 'del_id');

$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');
if($pagelimit == "") {
	$pagelimit = 20;
}
if ($page == "") {
    $page = 1;
}
// If filter types are not selected we show latest added data first
if ($filter_col == "") {
    $filter_col = "id";
}
if ($order_by == "") {
    $order_by = "desc";
}
// select the columns
$select = array('id', 'user_id','user_wallet_address','store_id' ,'store_wallet_address','tx_id','points','amount','created_at');

// If user searches 
if ($search_string) {
    $db->where('email', '%' . $search_string . '%', 'like');
}

$db->where("user_id", $_SESSION['user_id']);
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}
function dd($var='') {
	ob_start();
	var_export($var);
	$result = ob_get_clean();
	die($result);
}


$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("store_transactions", $page, $select);
$total_pages = $db->totalPages;

// dd($resultData);


// get columns for order filter
foreach ($resultData as $value) {
    foreach ($value as $col_name => $col_value) {
        $filter_options[$col_name] = $col_name;
    }
    //execute only once
    break;
}


include_once 'includes/header.php';
?>

<style>
.well {
	background-color:inherit;
	text-align:left;
}

#page-wrapper {
	height:auto !important;
}
</style>

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header"><?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transactions"; ?></h1>
        </div>
        <div class="col-lg-6" style="">
            <!--<div class="page-action-links text-right">
            <a href="add_admin.php"> <button class="btn btn-success">Add new</button></a>
            </div>-->
        </div>
</div>
 <?php include('./includes/flash_messages.php') ?>

    <?php
    if (isset($del_stat) && $del_stat == 1) {
        echo '<div class="alert alert-info">Successfully deleted</div>';
    }
    ?>
    
   
    <hr>
	<!--<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a>-->
	<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="header">#</th>
                <th><?php echo !empty($langArr['store']) ? $langArr['store'] : "Store"; ?></th>
				<th><?php echo !empty($langArr['store_wallet_address']) ? $langArr['store_wallet_address'] : "Store Wallet Address"; ?></th>
				<th><?php echo !empty($langArr['tx_id']) ? $langArr['tx_id'] : "Tx Id"; ?> </th>
				<th><?php echo !empty($langArr['points']) ? $langArr['points'] : "Points"; ?> </th>
				<th><?php echo !empty($langArr['ctc_amount']) ? $langArr['ctc_amount'] : "Ctc Amount"; ?></th>
				<th><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?></th>
                
            </tr>
        </thead>
        <tbody>

            <?php 
            
            $totalCtcAmt = 0; 
           
            foreach ($resultData as $row) : ?>
                
				<?php 
            
                    $db = getDbInstance();
					$db->where("id", $row['user_id']);
					$getUserDetails = $db->getOne('admin_accounts');
                    
					
					$db = getDbInstance();
					$db->where("id", $row['store_id']);
					$getStoreDetails = $db->getOne('stores');
				?>
					
			
            <tr>
                <td><?php echo $row['id'] ?></td>
                <td><?php echo htmlspecialchars($getStoreDetails['store_name']) ?></td>
				<td><?php  echo $row['store_wallet_address'];  ?> </td>
              
				<td><?php echo $row['tx_id']; ?></td>
				<td><?php echo $row['points']; ?></td>
				<td><?php echo $row['amount']; ?></td>
			
				<td><?php echo htmlspecialchars($row['created_at']) ?></td>
                

            </tr>
			

            <?php endforeach; ?>   
        </tbody>
    </table>
	
	</div>
	

    <!--    Pagination links-->
    <div class="text-center">

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
                echo '<li' . $li_class . '><a href="' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';
            }
            echo '</ul></div>';
        }
        ?>
    </div>
</div>


<?php include_once 'includes/footer.php'; ?>