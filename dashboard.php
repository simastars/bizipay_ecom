<?php require_once('header.php'); ?>

<?php
// Check if the customer is logged in or not
if(!isset($_SESSION['customer'])) {
    header('location: '.BASE_URL.'logout.php');
    exit;
} else {
    // If customer is logged in, but admin make him inactive, then force logout this user.
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute(array($_SESSION['customer']['cust_id'],0));
    $total = $statement->rowCount();
    if($total) {
        header('location: '.BASE_URL.'logout.php');
        exit;
    }
}
?>

<div class="page">
    <div class="container">
        <div class="row">            
            <div class="col-md-12"> 
                <?php require_once('customer-sidebar.php'); ?>
            </div>
            <div class="col-md-12">
                <div class="user-content">
                    <h3 class="text-center">
                        <?php echo LANG_VALUE_90; ?>
                    </h3>
                </div>                
            </div>
        </div>
    </div>
</div>
<div class="user-content">
   
    <div class="wallet-balance text-center" style="margin:20px 0;">
        <?php
        $statement = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id=?");
        $statement->execute([$_SESSION['customer']['cust_id']]);
        $wallet = $statement->fetch(PDO::FETCH_ASSOC);
        $balance = $wallet ? $wallet['balance'] : 0;
        ?>
        <h4>Wallet Balance: ₦<?php echo number_format($balance,2); ?></h4>
        <a href="wallet-fund.php" class="btn btn-success">Fund Wallet</a>
    </div>
</div>
<?php require_once('footer.php'); ?>