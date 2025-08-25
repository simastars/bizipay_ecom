<?php
// test_paystack_webhook.php - simulates Paystack webhook (run in CLI)
// Usage:
//   php test_paystack_webhook.php [webhook_url] [account_number] [amount_kobo] [reference]
// Example:
//   php test_paystack_webhook.php http://localhost/eCommerceSite-PHP/paystack_webhook.php 9930000737 250000 TEST_REF_123

require 'admin/inc/config.php'; // loads PAYSTACK_SECRET

// Defaults for local testing
$defaultUrl = 'http://localhost/eCommerceSite-PHP/test_paystack_webhook.php';
$defaultAccount = '9930000737';
$defaultAmount = 250000; // in kobo (2500.00 NGN)

// Allow overriding via CLI args
$url = $argv[1] ?? $defaultUrl;
$account_number = $argv[2] ?? $defaultAccount;
$amount = isset($argv[3]) ? (int)$argv[3] : $defaultAmount;
$reference = $argv[4] ?? 'TEST_REF_'.uniqid();

// Quick runtime checks
if (!defined('PAYSTACK_SECRET') || !PAYSTACK_SECRET) {
    fwrite(STDERR, "PAYSTACK_SECRET is not configured. Set it in admin/inc/config.php\n");
    exit(2);
}

$payload = [
  'event' => 'charge.success',
  'data' => [
    'account_number' => $account_number,
    'amount' => $amount,
    'status' => 'success',
    'reference' => $reference
  ]
];

$raw = json_encode($payload);
$sig = hash_hmac('sha512', $raw, PAYSTACK_SECRET);

// Diagnostics
echo "Sending test webhook to: $url\n";
echo "Payload:\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
echo "X-Paystack-Signature: $sig\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $raw);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'X-Paystack-Signature: '.$sig
]);
$res = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
    echo 'ERR: ' . $err . "\n";
    exit(3);
}

echo "HTTP/Code: $httpCode\n";
echo "Response: $res\n";