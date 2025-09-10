<?php
// Create / Reserve a virtual account for a customer (Billstack implementation sample)
require_once(__DIR__ . '/../../admin/inc/config.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

// Expect JSON payload
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!$payload) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid JSON']);
    exit;
}

// Basic validation
$required = ['email','reference','firstName','lastName','phone','bank'];
foreach ($required as $r) {
    if (empty($payload[$r])) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => "Missing $r"]);
        exit;
    }
}

$cust_id = isset($payload['cust_id']) ? (int)$payload['cust_id'] : 0; // optional, associate with user

// Check if reference exists locally
$stmt = $pdo->prepare("SELECT * FROM tbl_virtual_account WHERE reference = ?");
$stmt->execute([$payload['reference']]);
if ($stmt->rowCount()) {
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['status' => true, 'message' => 'Already reserved locally', 'data' => $existing]);
    exit;
}

// Call Billstack reserve VA endpoint
$ch = curl_init(BILLSTACK_VA_RESERVE_URL);
$body = json_encode([
    'email' => $payload['email'],
    'reference' => $payload['reference'],
    'firstName' => $payload['firstName'],
    'lastName' => $payload['lastName'],
    'phone' => $payload['phone'],
    'bank' => $payload['bank']
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . BILLSTACK_API_KEY
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

$response = curl_exec($ch);
$err = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'cURL error: ' . $err]);
    exit;
}

$data = json_decode($response, true);
if (!$data || !isset($data['status']) || !$data['status']) {
    http_response_code(502);
    echo json_encode(['status' => false, 'message' => 'Provider error', 'provider' => $data]);
    exit;
}

$accountInfo = $data['data']['account'][0];
$reference = $data['data']['reference'] ?? $payload['reference'];
$meta = json_encode($data['data']['meta'] ?? []);

// Persist
$ins = $pdo->prepare("INSERT INTO tbl_virtual_account (cust_id, reference, account_number, account_name, bank_name, bank_id, meta, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$ins->execute([
    $cust_id,
    $reference,
    $accountInfo['account_number'],
    $accountInfo['account_name'] ?? null,
    $accountInfo['bank_name'] ?? null,
    $accountInfo['bank_id'] ?? null,
    $meta,
    'reserved'
]);

$va_id = $pdo->lastInsertId();

echo json_encode(['status' => true, 'message' => 'Account reserved', 'data' => [
    'id' => $va_id,
    'reference' => $reference,
    'account' => $accountInfo,
    'meta' => $data['data']['meta'] ?? []
]]);

