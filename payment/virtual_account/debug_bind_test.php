<?php
// One-off diagnostic script to test PHP cURL to Billstack KYC endpoint
require_once(__DIR__ . '/../../admin/inc/config.php');

$payload = [
    'customer' => 'muhdmuhd158@gmail.com',
    'bvn' => '22414490053'
];

$ch = curl_init(BILLSTACK_BIND_KYC_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . BILLSTACK_API_KEY
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$billstack_disable_ssl = defined('BILLSTACK_DISABLE_SSL_VERIFY') ? constant('BILLSTACK_DISABLE_SSL_VERIFY') : false;
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $billstack_disable_ssl ? false : true);

$resp = curl_exec($ch);
$errno = curl_errno($ch);
$err = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

$out = [
    'timestamp' => date('c'),
    'errno' => $errno,
    'error' => $err,
    'http_code' => $info['http_code'] ?? null,
    'curl_info' => $info,
    'response' => $resp
];

file_put_contents(__DIR__ . '/debug_bind_test.log', json_encode($out) . "\n", FILE_APPEND);

header('Content-Type: application/json');
echo json_encode($out, JSON_PRETTY_PRINT);

// exit
?>
