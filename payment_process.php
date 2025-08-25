<?php
require_once('header.php');
// payment_process.php
// ...existing code...

// if(isset($_POST['form_wallet'])) {
//     $cust_id = $_SESSION['customer']['cust_id'];
//     $amount = round(floatval($_POST['amount']), 2);

//     try {
//         // Start transaction
//         $pdo->beginTransaction();

//         // Lock wallet row
//         $stmt = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id = ? FOR UPDATE");
//         $stmt->execute([$cust_id]);
//         $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

//         if (!$wallet) {
//             // No wallet row found
//             $pdo->rollBack();
//             echo '<div class="alert alert-danger">Wallet not found for this account.</div>';
//         } else {
//             $balance = (float)$wallet['balance'];

//             if ($balance < $amount) {
//                 $pdo->rollBack();
//                 echo '<div class="alert alert-danger">Insufficient wallet balance.</div>';
//             } else {
//                 // Deduct balance
//                 $stmt = $pdo->prepare("UPDATE tbl_wallet SET balance = balance - ? WHERE cust_id = ?");
//                 $stmt->execute([$amount, $cust_id]);

//                 if ($stmt->rowCount() == 0) {
//                     // Unexpected: no row updated
//                     $pdo->rollBack();
//                     echo '<div class="alert alert-danger">Failed to deduct wallet. Please try again.</div>';
//                 } else {
//                     // Log transaction
//                     $ref = uniqid('ORDER_');
//                     $stmt2 = $pdo->prepare("INSERT INTO tbl_wallet_transactions (cust_id, amount, type, description, ref) VALUES (?, ?, 'debit', 'Order Payment', ?)");
//                     $stmt2->execute([$cust_id, $amount, $ref]);

//                     // Commit all changes
//                     $pdo->commit();

//                     // Optionally fetch new balance to show user
//                     $stmt3 = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id = ?");
//                     $stmt3->execute([$cust_id]);
//                     $newWallet = $stmt3->fetch(PDO::FETCH_ASSOC);
//                     $newBalance = $newWallet ? number_format((float)$newWallet['balance'], 2) : '0.00';

//                     echo '<div class="alert alert-success">Payment successful! Your order has been placed using your wallet. New balance: '.$newBalance.'</div>';
//                     // proceed with order placement (insert order rows, clear cart, etc.)
//                 }
//             }
//         }
//     } catch (Exception $e) {
//         if ($pdo->inTransaction()) $pdo->rollBack();
//         error_log("Wallet payment error: ".$e->getMessage());
//         echo '<div class="alert alert-danger">An error occurred while processing payment. Please try again.</div>';
//     }
// }

if(isset($_POST['form_wallet'])) {
    $cust_id = $_SESSION['customer']['cust_id'];
    $amount = round(floatval($_POST['amount']), 2);

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Atomically deduct only if sufficient balance exists
        $stmt = $pdo->prepare("UPDATE tbl_wallet SET balance = balance - :amt WHERE cust_id = :cid AND balance >= :amt");
        $stmt->bindValue(':amt', $amount);
        $stmt->bindValue(':cid', $cust_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            // Either wallet missing or insufficient funds — rollback and inform user
            $pdo->rollBack();

            // Distinguish missing wallet vs insufficient funds (optional)
            $chk = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id = ?");
            $chk->execute([$cust_id]);
            $walletExists = (bool)$chk->fetch(PDO::FETCH_ASSOC);

            if (!$walletExists) {
                echo '<div class="alert alert-danger">Wallet not found for this account.</div>';
            } else {
                echo '<div class="alert alert-danger">Insufficient wallet balance.</div>';
            }
        } else {
            // Log transaction (debit)
            $ref = uniqid('ORDER_');
            $stmt2 = $pdo->prepare("INSERT INTO tbl_wallet_transactions (cust_id, amount, type, description, ref) VALUES (?, ?, 'debit', ?, ?)");
            $stmt2->execute([$cust_id, $amount, 'Order Payment', $ref]);

            // Commit all changes
            $pdo->commit();

            // Fetch new balance to show user
            $stmt3 = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id = ?");
            $stmt3->execute([$cust_id]);
            $newWallet = $stmt3->fetch(PDO::FETCH_ASSOC);
            $newBalance = $newWallet ? number_format((float)$newWallet['balance'], 2) : '0.00';

            echo '<div class="alert alert-success">Payment successful! Your order has been placed using your wallet. New balance: '.$newBalance.'</div>';
            // proceed with order placement (insert order rows, clear cart, etc.)
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Wallet payment error: ".$e->getMessage());
        echo '<div class="alert alert-danger">An error occurred while processing payment. Please try again.</div>';
    }
}
?>