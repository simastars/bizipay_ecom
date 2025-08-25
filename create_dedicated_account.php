<?php
require_once 'admin/inc/config.php';

// Always return JSON for programmatic clients; the message explains how to call the script.
header('Content-Type: application/json');

// Accept cust_id via POST (preferred) or GET (convenience when visiting in a browser)
$inputCust = 0;
if (isset($_POST['cust_id'])) {
    $inputCust = (int)$_POST['cust_id'];
} elseif (isset($_GET['cust_id'])) {
    $inputCust = (int)$_GET['cust_id'];
}

if (!$inputCust) {
    echo json_encode([
        'status' => false,
        'message' => 'cust_id required. Provide cust_id via POST or GET, e.g. ?cust_id=123'
    ]);
    exit;
}

// Basic runtime checks to help diagnose the "visit the URL and got an error" case
if (!defined('PAYSTACK_SECRET') || !PAYSTACK_SECRET) {
    echo json_encode(['status' => false, 'message' => 'PAYSTACK_SECRET is not configured in admin/inc/config.php']);
    exit;
}
// define('PAYSTACK_SECRET', 'sk_test_315fc78a0ab1058182741381e779681773796ffb');

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo json_encode(['status' => false, 'message' => 'Database connection ($pdo) is not available. Check admin/inc/config.php']);
    exit;
}

// payload - include your customer identifier if available
$payload = ['customer' => "CUS_{$inputCust}", 'preferred_bank' => 'titan-paystack'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/dedicated_account");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET,
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['status' => false, 'message' => 'cURL error: ' . $curlErr]);
    exit;
}

if ($httpCode >= 400) {
    // Return the raw response for debugging (Paystack returns JSON)
    $body = $response ?: json_encode(['http_code' => $httpCode]);
    $decoded = json_decode($body, true);
    echo json_encode(['status' => false, 'message' => 'Paystack API error', 'http_code' => $httpCode, 'response' => $decoded ?: $body]);
    exit;
}

$resp = json_decode($response, true);
if (!is_array($resp) || !isset($resp['status']) || $resp['status'] !== true) {
    echo json_encode(['status' => false, 'message' => 'Unexpected Paystack response', 'raw' => $response]);
    exit;
}

// map returned account to customer
$data = $resp['data'] ?? [];
$account_number = $data['account_number'] ?? null;
$paystack_account_id = $data['id'] ?? null;
$bank_name = $data['bank']['name'] ?? null;
$currency = $data['currency'] ?? 'NGN';
$metadata = isset($data['metadata']) ? json_encode($data['metadata']) : null;

if (!$account_number) {
    echo json_encode(['status' => false, 'message' => 'No account number returned by Paystack', 'data' => $data]);
    exit;
}

try {
    $sql = "INSERT INTO tbl_dedicated_account (cust_id, account_number, paystack_account_id, bank_name, currency, metadata) VALUES (?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE paystack_account_id = VALUES(paystack_account_id), bank_name = VALUES(bank_name), currency = VALUES(currency), metadata = VALUES(metadata)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inputCust, $account_number, $paystack_account_id, $bank_name, $currency, $metadata]);
    echo json_encode(['status' => true, 'message' => 'Dedicated account created and mapped', 'account' => $data]);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}