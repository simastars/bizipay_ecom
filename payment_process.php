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

            // At this point, create the payment and order records (within same transaction)
            // Prepare payment header
            $payment_date = date('Y-m-d H:i:s');
            $item_name = 'Product Item(s)';
            $item_amount = $amount; // total paid from wallet
            $item_number = time();

            // Insert into tbl_payment
            $statement = $pdo->prepare("INSERT INTO tbl_payment (
                        customer_id,
                        customer_name,
                        customer_email,
                        payment_date,
                        txnid,
                        paid_amount,
                        card_number,
                        card_cvv,
                        card_month,
                        card_year,
                        bank_transaction_info,
                        payment_method,
                        payment_status,
                        shipping_status,
                        payment_id
                    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

            $statement->execute(array(
                $_SESSION['customer']['cust_id'],
                $_SESSION['customer']['cust_name'],
                $_SESSION['customer']['cust_email'],
                $payment_date,
                '',
                $item_amount,
                '',
                '',
                '',
                '',
                '',
                'Wallet',
                'Completed',
                'Pending',
                $item_number
            ));

            // Build cart arrays (same pattern used elsewhere)
            $i = 0;
            foreach ($_SESSION['cart_p_id'] as $key => $value) {
                $i++;
                $arr_cart_p_id[$i] = $value;
            }

            $i = 0;
            foreach ($_SESSION['cart_size_id'] as $key => $value) {
                $i++;
                $arr_cart_size_id[$i] = $value;
            }

            $i = 0;
            foreach ($_SESSION['cart_size_name'] as $key => $value) {
                $i++;
                $arr_cart_size_name[$i] = $value;
            }

            $i = 0;
            foreach ($_SESSION['cart_color_id'] as $key => $value) {
                $i++;
                $arr_cart_color_id[$i] = $value;
            }

            $i = 0;
            foreach ($_SESSION['cart_color_name'] as $key => $value) {
                $i++;
                $arr_cart_color_name[$i] = $value;
            }

            $i = 0;
            foreach ($_SESSION['cart_p_qty'] as $key => $value) {
                $i++;
                $arr_cart_p_qty[$i] = $value;
            }

            $i = 0;
            foreach ($_SESSION['cart_p_current_price'] as $key => $value) {
                $i++;
                $arr_cart_p_current_price[$i] = $value;
            }

            $i = 0;
            foreach ($_SESSION['cart_p_name'] as $key => $value) {
                $i++;
                $arr_cart_p_name[$i] = $value;
            }

            // Fetch product quantities once to update stock
            $i = 0;
            $statement = $pdo->prepare("SELECT * FROM tbl_product");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $i++;
                $arr_p_id[$i] = $row['p_id'];
                $arr_p_qty[$i] = $row['p_qty'];
            }

            // Insert order items and update stock
            for ($i = 1; $i <= count($arr_cart_p_name); $i++) {
                $statement = $pdo->prepare("INSERT INTO tbl_order (
                            product_id,
                            product_name,
                            size,
                            color,
                            quantity,
                            unit_price,
                            payment_id
                        ) VALUES (?,?,?,?,?,?,?)");

                $statement->execute(array(
                    $arr_cart_p_id[$i],
                    $arr_cart_p_name[$i],
                    $arr_cart_size_name[$i],
                    $arr_cart_color_name[$i],
                    $arr_cart_p_qty[$i],
                    $arr_cart_p_current_price[$i],
                    $item_number
                ));

                // Update the stock
                for ($j = 1; $j <= count($arr_p_id); $j++) {
                    if ($arr_p_id[$j] == $arr_cart_p_id[$i]) {
                        $current_qty = $arr_p_qty[$j];
                        break;
                    }
                }
                $final_quantity = $current_qty - $arr_cart_p_qty[$i];
                $statement = $pdo->prepare("UPDATE tbl_product SET p_qty=? WHERE p_id=?");
                $statement->execute(array($final_quantity, $arr_cart_p_id[$i]));
            }

            // Clear cart sessions
            unset($_SESSION['cart_p_id']);
            unset($_SESSION['cart_size_id']);
            unset($_SESSION['cart_size_name']);
            unset($_SESSION['cart_color_id']);
            unset($_SESSION['cart_color_name']);
            unset($_SESSION['cart_p_qty']);
            unset($_SESSION['cart_p_current_price']);
            unset($_SESSION['cart_p_name']);
            unset($_SESSION['cart_p_featured_photo']);

            // Commit all changes
            $pdo->commit();

            // Redirect to success page
            header('Location: ' . BASE_URL . 'payment_success.php');
            exit();
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Wallet payment error: ".$e->getMessage());
        echo '<div class="alert alert-danger">An error occurred while processing payment. Please try again.</div>';
    }
}
?>