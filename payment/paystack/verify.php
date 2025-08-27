<?php
// payment/paystack/verify.php
// Verifies Paystack transaction by reference and creates order (similar to wallet flow)

session_start();
require_once('../../admin/inc/config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['customer'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$reference = isset($_POST['reference']) ? trim($_POST['reference']) : '';
if (empty($reference)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing reference']);
    exit;
}

// Obtain Paystack secret from settings or config
$paystack_secret = null;
try {
    $st = $pdo->prepare("SELECT paystack_secret_key FROM tbl_settings WHERE id = 1 LIMIT 1");
    $st->execute();
    $r = $st->fetch(PDO::FETCH_ASSOC);
    if ($r && !empty($r['paystack_secret_key'])) {
        $paystack_secret = $r['paystack_secret_key'];
    }
} catch (Exception $e) {
    // ignore
}

if (empty($paystack_secret) && defined('PAYSTACK_SECRET')) {
    $paystack_secret = PAYSTACK_SECRET;
}

if (empty($paystack_secret)) {
    echo json_encode(['status' => 'error', 'message' => 'Paystack secret not configured']);
    exit;
}

// Prevent double-processing: check if txn already exists in tbl_payment
$chk = $pdo->prepare("SELECT id FROM tbl_payment WHERE txnid = ? LIMIT 1");
$chk->execute([$reference]);
if ($chk->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(['status' => 'success', 'message' => 'Already processed']);
    exit;
}

// Verify with Paystack
$verify_url = 'https://api.paystack.co/transaction/verify/' . urlencode($reference);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verify_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $paystack_secret,
    'Accept: application/json',
]);
$resp = curl_exec($ch);
if ($resp === false) {
    echo json_encode(['status' => 'error', 'message' => 'Network error verifying payment']);
    exit;
}
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200) {
    echo json_encode(['status' => 'error', 'message' => 'Paystack verification failed', 'http_code' => $http_code, 'body' => $resp]);
    exit;
}

$json = json_decode($resp, true);
if (!$json || !isset($json['status']) || $json['status'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid response from Paystack', 'body' => $resp]);
    exit;
}

$data = $json['data'];
if (!isset($data['status']) || $data['status'] !== 'success') {
    echo json_encode(['status' => 'error', 'message' => 'Payment not successful', 'data' => $data]);
    exit;
}

// amount returned is in kobo/cents (smallest currency unit)
$paid_amount = isset($data['amount']) ? (float)$data['amount'] / 100.0 : 0.0;

// Recompute server-side total for cart (same logic as wallet init)
$table_total_price = 0.0;
$qtys = $_SESSION['cart_p_qty'] ?? [];
$prices = $_SESSION['cart_p_current_price'] ?? [];
foreach ($qtys as $k => $q) {
    $q = (int)$q;
    $p = isset($prices[$k]) ? floatval($prices[$k]) : 0.0;
    $table_total_price += ($p * $q);
}

$shipping_cost = 0.0;
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_shipping_cost WHERE country_id=?");
    $stmt->execute(array($_SESSION['customer']['cust_country']));
    if ($stmt->rowCount()) {
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $row) {
            $shipping_cost = (float)$row['amount'];
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbl_shipping_cost_all WHERE sca_id=1");
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $row) {
            $shipping_cost = (float)$row['amount'];
        }
    }
} catch (Exception $e) {
    // ignore, leave shipping as 0
}

$final_total = round($table_total_price + $shipping_cost, 2);

// Allow small rounding differences (0.5) but require amounts to match
if (abs($paid_amount - $final_total) > 0.5) {
    echo json_encode(['status' => 'error', 'message' => 'Paid amount does not match order total', 'paid' => $paid_amount, 'expected' => $final_total]);
    exit;
}

// At this point payment verified and amount matches; create order atomically
try {
    $pdo->beginTransaction();

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
        $reference,
        $item_amount,
        '',
        '',
        '',
        '',
        '',
        'Paystack',
        'Completed',
        'Pending',
        $item_number
    ));

    // Build cart arrays
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

    $pdo->commit();

    echo json_encode(['status' => 'success']);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Paystack verify error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error while creating order']);
    exit;
}

?>
