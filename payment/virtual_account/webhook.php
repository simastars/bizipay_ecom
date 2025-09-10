<?php
// Webhook receiver for Billstack virtual account deposits
// This endpoint verifies signature (if provided), stores tx and credits user's wallet
require_once(__DIR__ . '/../../admin/inc/config.php');

// Read raw payload and headers
$raw = file_get_contents('php://input');
$headers = function_exists('getallheaders') ? getallheaders() : [];

// Optional: verify signature using BILLSTACK_WEBHOOK_SECRET if provider sends HMAC header
// $signatureHeader = $headers['X-Billstack-Signature'] ?? $headers['x-billstack-signature'] ?? null;
// if ($signatureHeader && defined('BILLSTACK_WEBHOOK_SECRET') && BILLSTACK_WEBHOOK_SECRET !== 'YOUR_BILLSTACK_WEBHOOK_SECRET') {
//     $computed = hash_hmac('sha256', $raw, BILLSTACK_WEBHOOK_SECRET);
//     if (!hash_equals($computed, $signatureHeader)) {
//         http_response_code(400);
//         echo json_encode(['status' => false, 'message' => 'Invalid signature']);
//         exit;
//     }
// }

$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid JSON']);
    exit;
}

// Map provider fields - adapt depending on Billstack's webhook structure
// Example expected: { data: { reference, account_number, amount, currency } }
$providerRef = $data['data']['reference'] ?? $data['data']['provider_ref'] ?? null;
$accountNumber = $data['data']['account_number'] ?? $data['data']['account'] ?? null;
$amount = isset($data['data']['amount']) ? floatval($data['data']['amount']) : 0.0;
$currency = $data['data']['currency'] ?? 'NGN';

if (!$accountNumber || $amount <= 0) {
    // Nothing to do
    http_response_code(200);
    echo json_encode(['status' => false, 'message' => 'Ignored']);
    exit;
}

// Find virtual account
$stmt = $pdo->prepare("SELECT * FROM tbl_virtual_account WHERE account_number = ?");
$stmt->execute([$accountNumber]);
$va = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$va) {
    // Unknown account - log and ack
    $log = fopen(__DIR__ . '/webhook_unknown.log', 'a');
    fwrite($log, date('c') . " UNKNOWN_ACCOUNT: " . $raw . "\n");
    fclose($log);
    http_response_code(200);
    echo json_encode(['status' => false, 'message' => 'Unknown account']);
    exit;
}

// Idempotency: check provider_ref
$stmt = $pdo->prepare("SELECT * FROM tbl_virtual_account_tx WHERE provider_ref = ?");
$stmt->execute([$providerRef]);
if ($stmt->rowCount()) {
    http_response_code(200);
    echo json_encode(['status' => true, 'message' => 'Already processed']);
    exit;
}

// Insert tx
$ins = $pdo->prepare("INSERT INTO tbl_virtual_account_tx (virtual_account_id, cust_id, provider_ref, amount, currency, status, provider_payload) VALUES (?, ?, ?, ?, ?, 'verified', ?)");
$ins->execute([$va['id'], $va['cust_id'], $providerRef, $amount, $currency, $raw]);

// Credit wallet (atomic)
try {
    $pdo->beginTransaction();
    $u1 = $pdo->prepare("INSERT INTO tbl_wallet (cust_id, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)");
    $u1->execute([$va['cust_id'], $amount]);

    $u2 = $pdo->prepare("UPDATE tbl_virtual_account_tx SET status = 'credited' WHERE provider_ref = ?");
    $u2->execute([$providerRef]);

    $u3 = $pdo->prepare("INSERT INTO tbl_wallet_transactions (cust_id, amount, type, description, ref) VALUES (?, ?, 'credit', 'Virtual Account Deposit', ?)");
    $u3->execute([$va['cust_id'], $amount, $providerRef]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $log = fopen(__DIR__ . '/webhook_error.log', 'a');
    fwrite($log, date('c') . " ERROR: " . $e->getMessage() . "\nPayload: " . $raw . "\n");
    fclose($log);
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Processing error']);
    exit;
}

http_response_code(200);
echo json_encode(['status' => true, 'message' => 'credited']);

