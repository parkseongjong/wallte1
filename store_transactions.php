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
	 header('Location:index.php');
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
$select = array('id', 'user_id','user_wallet_address','store_id' ,'store_wallet_address','tx_id','points','krw','amount','created_at');

// If user searches 
if ($search_string) {
    $db->where('email', '%' . $search_string . '%', 'like');
}


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
            <h1 class="page-header">Store Transactions</h1>
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
                <th>User</th>
				<th>Info</th>
 				<th>Points</th>
 				<th>KRW</th>
				<th>CTC Amount</th>
				<th>Dated</th>
                
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
                <td><?php echo htmlspecialchars($getUserDetails['name'])." ".htmlspecialchars($getUserDetails['lname']) ?></td>
				<td>
					<strong>Store Name:</strong> <?php echo htmlspecialchars($getStoreDetails['store_name']) ?><br>
					<strong>Store Wallet Address</strong> <?php  echo $row['store_wallet_address'];  ?><br>
					<strong>User Wallet Address:</strong> <?php echo $row['user_wallet_address']; ?><br>
					<strong>Purchase Tax ID:</strong> <?php echo $row['tx_id']; ?><br>					
				</td>
				<td><?php echo number_format($row['points'],2); ?></td>
				<td>₩<?php echo number_format($row['krw'],2); ?></td>
				<td><?php echo number_format($row['amount'],2); ?></td>
			
				<td><?php echo htmlspecialchars($row['created_at']) ?></td>
                

            </tr>
			
                <!-- Delete Confirmation Modal-->
                     <div class="modal fade" id="confirm-delete-<?php echo $row['id'] ?>" role="dialog">
                        <div class="modal-dialog">
                          <form action="delete_user.php" method="POST">
                          <!-- Modal content-->
                              <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                  <h4 class="modal-title">Confirm</h4>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="del_id" id = "del_id" value="<?php echo $row['id'] ?>">
                                    <p>Are you sure you want to delete this user?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-default pull-left">Yes</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                                </div>
                              </div>
                          </form>
                          
                        </div>
                    </div>
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