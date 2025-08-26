<?php
// payment/wallet/init.php
// Process payment using user's in-site wallet. This endpoint is called from checkout.php form.

session_start();
require_once('../../admin/inc/config.php');

if (!isset($_SESSION['customer'])) {
    // not logged in
    header('Location: ../../' . BASE_URL . 'login.php');
    exit;
}

// Accept posted amount when present; otherwise compute server-side later
$cust_id = (int)$_SESSION['customer']['cust_id'];
$posted_amount = isset($_POST['amount']) ? round(floatval($_POST['amount']), 2) : null;

// Recompute final total on server to avoid tampering
$table_total_price = 0.0;
if (!isset($_SESSION['cart_p_id']) || !is_array($_SESSION['cart_p_id']) || count($_SESSION['cart_p_id']) == 0) {
    $_SESSION['error_message'] = 'Your cart is empty.';
    header('Location: ' . BASE_URL . 'cart.php');
    exit;
}

// Build total by iterating session arrays using their actual keys (preserve indexing used elsewhere)
$qtys = $_SESSION['cart_p_qty'] ?? [];
$prices = $_SESSION['cart_p_current_price'] ?? [];
foreach ($qtys as $k => $q) {
    $q = (int)$q;
    $p = isset($prices[$k]) ? floatval($prices[$k]) : 0.0;
    $table_total_price += ($p * $q);
}

// Shipping cost lookup (same logic as checkout.php)
$shipping_cost = 0.0;
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_shipping_cost WHERE country_id=?");
    $stmt->execute(array($_SESSION['customer']['cust_country']));
    if ($stmt->rowCount()) {
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $row) {
            $shipping_cost = (float)$row['amount'];
        }
        // echo "Shipping cost for your location: " . number_format($shipping_cost, 2);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbl_shipping_cost_all WHERE sca_id=1");
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $row) {
            $shipping_cost = (float)$row['amount'];
        }
        // echo "Shipping cost for your location: " . number_format($shipping_cost, 2);
    }
} catch (Exception $e) {
    error_log('Shipping lookup error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Unable to determine shipping cost.';
    header('Location: ../../' . BASE_URL . 'checkout.php');
    exit;
}

$final_total = round($table_total_price + $shipping_cost, 2);

// If posted amount was not supplied (client omitted it), use server-computed final_total
if ($posted_amount === null) {
    $posted_amount = $final_total;
}

if ($final_total != $posted_amount) {
    // Provide diagnostic info to help debug mismatches (do not leak sensitive data)
    $_SESSION['error_message'] = 'Payment amount mismatch. Expected: ' . number_format($final_total, 2) . ' Posted: ' . number_format($posted_amount, 2);
    header('Location: ../../' . BASE_URL . 'checkout.php');
    exit;
}

if ($final_total <= 0) {
    $_SESSION['error_message'] = 'Invalid payment amount.';
    header('Location: ../../' . BASE_URL . 'checkout.php');
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Atomically deduct only if sufficient balance exists
    $stmt = $pdo->prepare("UPDATE tbl_wallet SET balance = balance - :amt WHERE cust_id = :cid AND balance >= :amt");
    $stmt->bindValue(':amt', $final_total);
    $stmt->bindValue(':cid', $cust_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        // Either wallet missing or insufficient funds — rollback and inform user
        $pdo->rollBack();

        $chk = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id = ?");
        $chk->execute([$cust_id]);
        $walletExists = (bool)$chk->fetch(PDO::FETCH_ASSOC);

        if (!$walletExists) {
            $_SESSION['error_message'] = 'Wallet not found for this account.';
        } else {
            $_SESSION['error_message'] = 'Insufficient wallet balance.';
        }
        // echo $_SESSION['error_message'];
        header('Location: ../../' . BASE_URL . 'checkout.php');
        exit;
    }

    // Log wallet debit transaction
    $ref = uniqid('ORDER_');
    $stmt2 = $pdo->prepare("INSERT INTO tbl_wallet_transactions (cust_id, amount, type, description, ref) VALUES (?, ?, 'debit', ?, ?)");
    $stmt2->execute([$cust_id, $final_total, 'Order Payment', $ref]);

    // Insert into tbl_payment
    $payment_date = date('Y-m-d H:i:s');
    $item_amount = $final_total;
    $item_number = time();

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
    foreach ($_SESSION['cart_size_name'] as $key => $value) {
        $i++;
        $arr_cart_size_name[$i] = $value;
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

    // After successful commit, attempt to credit referrer (if any)
    try {
        // get referrer id for this buyer
        $stmtRef = $pdo->prepare("SELECT cust_referred_by FROM tbl_customer WHERE cust_id = ? LIMIT 1");
        $stmtRef->execute([$_SESSION['customer']['cust_id']]);
        $refRow = $stmtRef->fetch(PDO::FETCH_ASSOC);
        $referrerId = $refRow['cust_referred_by'] ?? null;

        // load referral_amount from settings (default 0)
        $refAmount = 0.00;
        $st = $pdo->prepare("SELECT referral_amount FROM tbl_settings WHERE id = 1 LIMIT 1");
        if ($st->execute()) {
            $srow = $st->fetch(PDO::FETCH_ASSOC);
            if ($srow && isset($srow['referral_amount'])) $refAmount = (float)$srow['referral_amount'];
        }

        // debug log
        error_log("Referral debug: buyer_id={$_SESSION['customer']['cust_id']}, referrerId=" . var_export($referrerId, true) . ", refAmount=" . var_export($refAmount, true));

        if ($referrerId && $refAmount > 0) {
            // credit the referrer's wallet (safe upsert)
            $pdo->beginTransaction();
            $stmtW = $pdo->prepare("INSERT INTO tbl_wallet (cust_id, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)");
            $stmtW->execute([$referrerId, $refAmount]);
            // log wallet transaction for referrer
            $txRef = 'REFR_' . uniqid();
            $desc = 'Referral reward from user ' . $_SESSION['customer']['cust_id'];
            $stmtTx = $pdo->prepare("INSERT INTO tbl_wallet_transactions (cust_id, amount, type, description, ref) VALUES (?, ?, 'credit', ?, ?)");
            $okTx = $stmtTx->execute([$referrerId, $refAmount, $desc, $txRef]);
            if ($okTx) {
                error_log("Referral debug: credited referrer {$referrerId} amount={$refAmount} txref={$txRef}");
            } else {
                error_log("Referral debug: failed to insert wallet transaction for referrer {$referrerId}");
            }
            $pdo->commit();
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Referral credit failed: ' . $e->getMessage());
    }

    // Redirect to success page
    header('Location: ../../' . BASE_URL . 'payment_success.php');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Wallet payment error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred while processing the payment. Please try again.';
    // header('Location: ' . BASE_URL . 'checkout.php');
    exit;
}
