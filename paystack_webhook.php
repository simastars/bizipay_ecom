<?php
require_once 'admin/inc/config.php';

// Debug mode: enabled if request originates from localhost or ?debug=1 is present
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
$debug = (isset($_GET['debug']) && $_GET['debug']) || in_array($remote, ['127.0.0.1', '::1']);
if ($debug) header('Content-Type: application/json');

$raw = @file_get_contents('php://input');
if (!$raw) {
    if ($debug) { echo json_encode(['status' => false, 'message' => 'Empty request body']); }
    exit;
}

$headerSig = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? ($_SERVER['HTTP_X-PAYSTACK-SIGNATURE'] ?? null);
if (empty($headerSig) || !defined('PAYSTACK_SECRET')) {
    error_log('Paystack webhook: missing signature or secret');
    if ($debug) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'Missing signature or PAYSTACK_SECRET not configured']);
    }
    exit;
}

$hash = hash_hmac('sha512', $raw, PAYSTACK_SECRET);
if (!hash_equals($hash, $headerSig)) {
    http_response_code(403);
    error_log('Paystack webhook: invalid signature');
    if ($debug) echo json_encode(['status' => false, 'message' => 'Invalid signature']);
    exit;
}

$payload = json_decode($raw, true);
if (!$payload) {
    if ($debug) { http_response_code(400); echo json_encode(['status' => false, 'message' => 'Invalid JSON payload']); }
    exit;
}

$data = $payload['data'] ?? [];
$account_number = $data['account_number'] ?? null;
if (empty($account_number)) {
    if (!empty($data['receiver']['account_number'])) $account_number = $data['receiver']['account_number'];
    if (!empty($data['destination']['account_number'])) $account_number = $data['destination']['account_number'];
    if (!empty($data['recipient']['account_number'])) $account_number = $data['recipient']['account_number'];
}

$amount_value = $data['amount'] ?? ($data['paid_amount'] ?? ($data['transfer_amount'] ?? null));
$status = strtolower($data['status'] ?? '');
$reference = $data['reference'] ?? $data['id'] ?? null;

if ($amount_value === null || !$account_number) {
    if ($debug) { http_response_code(400); echo json_encode(['status' => false, 'message' => 'Missing amount or account_number', 'data' => $data]); }
    exit;
}
if (!in_array($status, ['success','completed','paid'])) {
    if ($debug) { http_response_code(200); echo json_encode(['status' => true, 'message' => 'Ignored non-success status', 'status_value' => $status]); }
    exit;
}

$amount = $amount_value / 100.0;

try {
    $stmt = $pdo->prepare("SELECT cust_id FROM tbl_dedicated_account WHERE account_number = ? LIMIT 1");
    $stmt->execute([$account_number]);
    $map = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$map) {
        error_log("Paystack webhook: account_number {$account_number} not mapped");
        if ($debug) { http_response_code(404); echo json_encode(['status' => false, 'message' => 'Account not mapped']); }
        exit;
    }
    $cust_id = (int)$map['cust_id'];

    if ($reference) {
        $chk = $pdo->prepare("SELECT id FROM tbl_wallet_transactions WHERE cust_id = ? AND ref = ? LIMIT 1");
        $chk->execute([$cust_id, $reference]);
        if ($chk->fetch()) {
            if ($debug) { echo json_encode(['status' => true, 'message' => 'Duplicate transaction ignored']); }
            exit;
        }
    }

    $pdo->beginTransaction();

    $stmtW = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id = ? FOR UPDATE");
    $stmtW->execute([$cust_id]);
    $wallet = $stmtW->fetch(PDO::FETCH_ASSOC);

    if ($wallet) {
        $stmtUp = $pdo->prepare("UPDATE tbl_wallet SET balance = balance + ? WHERE cust_id = ?");
        $stmtUp->execute([$amount, $cust_id]);
        if ($stmtUp->rowCount() === 0) {
            $pdo->rollBack();
            error_log("Paystack webhook: failed to update wallet for cust {$cust_id}");
            if ($debug) { http_response_code(500); echo json_encode(['status' => false, 'message' => 'Failed to update wallet']); }
            exit;
        }
    } else {
        $stmtIns = $pdo->prepare("INSERT INTO tbl_wallet (cust_id, balance) VALUES (?, ?)");
        $stmtIns->execute([$cust_id, $amount]);
    }

    $desc = "Deposit via Paystack webhook (acct {$account_number})";
    $stmtT = $pdo->prepare("INSERT INTO tbl_wallet_transactions (cust_id, amount, type, description, ref) VALUES (?, ?, 'credit', ?, ?)");
    $stmtT->execute([$cust_id, $amount, $desc, $reference]);

    $pdo->commit();
    if ($debug) { http_response_code(200); echo json_encode(['status' => true, 'message' => 'Wallet credited', 'cust_id' => $cust_id, 'amount' => $amount]); }
    http_response_code(200);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Paystack webhook processing error: ".$e->getMessage());
    if ($debug) { http_response_code(500); echo json_encode(['status' => false, 'message' => 'Processing error', 'error' => $e->getMessage()]); }
    http_response_code(500);
    exit;
}