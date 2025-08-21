<?php

require_once('header.php');
if(!isset($_SESSION['customer'])) {
    header('location: '.BASE_URL.'logout.php');
    exit;
}
$cust_id = $_SESSION['customer']['cust_id'];
$reference = $_GET['reference'] ?? '';
$amount = $_GET['amount'] ?? 0;

if($reference && $amount){
    // Verify with Paystack
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        "Authorization: Bearer sk_test_315fc78a0ab1058182741381e779681773796ffb",
        "Cache-Control: no-cache",
      ],
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if($err){
        echo "<div class='alert alert-danger'>Curl Error: $err</div>";
    } else {
        $result = json_decode($response, true);
        // var_dump($result);
        if($result && $result['status'] && $result['data']['status'] == 'success'){
            // Credit wallet
            $amount = floatval($result['data']['amount'])/100;
            // Update wallet
            $stmt = $pdo->prepare("INSERT INTO tbl_wallet (cust_id, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)");
            $stmt->execute([$cust_id, $amount]);
            // Log transaction
            $stmt2 = $pdo->prepare("INSERT INTO tbl_wallet_transactions (cust_id, amount, type, description, ref) VALUES (?, ?, 'credit', 'Wallet Funding', ?)");
            $stmt2->execute([$cust_id, $amount, $reference]);
            echo "<div class='alert alert-success'>Wallet funded successfully!</div>";
            echo "<script>setTimeout(function(){ window.location.href = 'dashboard.php'; }, 2000);</script>";
        } else {
            echo "<div class='alert alert-danger'>Payment verification failed.</div>";
        }
    }
} else {
    echo "<div class='alert alert-danger'>Invalid request.</div>";
}
require_once('footer.php');