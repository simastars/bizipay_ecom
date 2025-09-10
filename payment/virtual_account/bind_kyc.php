<?php
// Bind KYC (BVN) to an existing virtual account (Billstack sample)
require_once(__DIR__ . '/../../admin/inc/config.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!$payload) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid JSON']);
    exit;
}

$required = ['virtual_account_id','bvn'];
foreach ($required as $r) {
    if (empty($payload[$r])) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => "Missing $r"]);
        exit;
    }
}

$virtual_account_id = (int)$payload['virtual_account_id'];
$bvn = trim($payload['bvn']);

// fetch VA
$stmt = $pdo->prepare("SELECT * FROM tbl_virtual_account WHERE id = ?");
$stmt->execute([$virtual_account_id]);
$va = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$va) {
    http_response_code(404);
    echo json_encode(['status' => false, 'message' => 'Virtual account not found']);
    exit;
}

// Build provider payload
$providerPayload = [
    'customer' => json_decode($va['meta'] ?? '{}', true)['email'] ?? '',
    'bvn' => $bvn
];

$ch = curl_init(BILLSTACK_BIND_KYC_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . BILLSTACK_API_KEY,
    'Connection: close'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($providerPayload));

// Force HTTP/1.1 to avoid potential HTTP/2 stream issues with some PHP/cURL builds
if (defined('CURL_HTTP_VERSION_1_1')) {
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
}

// Prevent hanging requests: set reasonable connect and total timeouts.
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // seconds to establish connection
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // total seconds for the request
// Toggle SSL verification via a config flag for local dev if necessary.
// Use constant() only after checking defined() to avoid lint warnings; wrap in local variable.
$billstack_disable_ssl = defined('BILLSTACK_DISABLE_SSL_VERIFY') ? constant('BILLSTACK_DISABLE_SSL_VERIFY') : false;
if ($billstack_disable_ssl === true) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
} else {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
}
// Prefer IPv4 to avoid IPv6-related hangs on some Windows setups
if (defined('CURL_IPRESOLVE_V4')) {
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
}
// Enable verbose stderr logging into a debug file (helpful under Apache)
$verboseLog = __DIR__ . '/bind_kyc_verbose.log';
$fp = fopen($verboseLog, 'a');
if ($fp) {
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $fp);
}

$response = curl_exec($ch);
$curlErrno = curl_errno($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlInfo = curl_getinfo($ch); // full info for debugging
curl_close($ch);
// close verbose log handle if opened
if (!empty($fp) && is_resource($fp)) {
    fclose($fp);
}

if ($curlErrno) {
    // log curl error with errno + call info
    file_put_contents(__DIR__ . '/bind_kyc.log', date('c') . " CURL_ERR ({$curlErrno}): " . $curlError . "\nPayload: " . json_encode($providerPayload) . "\nCURL_INFO: " . json_encode($curlInfo) . "\n\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'cURL error: ' . $curlError, 'errno' => $curlErrno]);
    exit;
}

if ($response === false || $response === '') {
    // log empty/failed responses for provider inspection (include curl info)
    file_put_contents(__DIR__ . '/bind_kyc.log', date('c') . " EMPTY_RESPONSE_HTTP_CODE={$httpCode} Payload: " . json_encode($providerPayload) . "\nCURL_INFO: " . json_encode($curlInfo) . "\n\n", FILE_APPEND);
    http_response_code(502);
    echo json_encode(['status' => false, 'message' => 'No response from provider', 'http_code' => $httpCode]);
    exit;
}

// Attempt to decode provider response
$data = json_decode($response, true);
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    // log raw response for debugging
    file_put_contents(__DIR__ . '/bind_kyc.log', date('c') . " INVALID_JSON_RESPONSE: " . $response . "\nPayload: " . json_encode($providerPayload) . "\n\n", FILE_APPEND);
}
// Store KYC record
$stmt = $pdo->prepare("INSERT INTO tbl_virtual_account_kyc (virtual_account_id, cust_id, bvn, provider_response, status) VALUES (?, ?, ?, ?, ?)");
$status = (isset($data['status']) && $data['status']) || (isset($data['responseCode']) && ($data['responseCode'] == '00' || $data['responseCode'] === 0)) ? 'validated' : 'failed';
$stmt->execute([$virtual_account_id, $va['cust_id'], $bvn, json_encode($data), $status]);

if ($status === 'validated') {
    $u = $pdo->prepare("UPDATE tbl_virtual_account SET status='kyc_validated' WHERE id = ?");
    $u->execute([$virtual_account_id]);
}

$ok = ($status === 'validated');
// log provider response summary
file_put_contents(__DIR__ . '/bind_kyc.log', date('c') . " BIND_ATTEMPT: virtual_account_id={$virtual_account_id} cust_id={$va['cust_id']} status={$status} response=" . substr($response,0,1000) . "\n\n", FILE_APPEND);

if ($ok) {
    echo json_encode(['status' => true, 'message' => 'KYC validated', 'provider' => $data]);
} else {
    http_response_code(200);
    echo json_encode(['status' => false, 'message' => 'KYC validation failed', 'provider' => $data]);
}

