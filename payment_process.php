<?php
require_once('header.php');
// payment_process.php
// ...existing code...

if(isset($_POST['form_wallet'])) {
    $cust_id = $_SESSION['customer']['cust_id'];
    $amount = floatval($_POST['amount']);

    // Get wallet balance
    $stmt = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id=?");
    $stmt->execute([$cust_id]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $wallet ? $wallet['balance'] : 0;

    if($balance >= $amount) {
        // Deduct wallet
        $stmt = $pdo->prepare("UPDATE tbl_wallet SET balance = balance - ? WHERE cust_id = ? AND balance >= ?");
        $stmt->execute([$amount, $cust_id, $amount]);
        if($stmt->rowCount()) {
            // Log transaction
            $stmt2 = $pdo->prepare("INSERT INTO tbl_wallet_transactions (cust_id, amount, type, description, ref) VALUES (?, ?, 'debit', 'Order Payment', ?)");
            $stmt2->execute([$cust_id, $amount, uniqid('ORDER_')]);
            // Proceed with order placement logic here (insert into order tables, clear cart, etc.)
            echo '<div class="alert alert-success">Payment successful! Your order has been placed using your wallet.</div>';
            // Optionally redirect or clear cart session variables
        } else {
            echo '<div class="alert alert-danger">Failed to deduct wallet. Please try again.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Insufficient wallet balance.</div>';
    }
}
?>