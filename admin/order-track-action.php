<?php
require_once('header.php');
require_once __DIR__ . '/../inc/order-tracking.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: order.php');
    exit;
}

$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : '';
$status_type = isset($_POST['status_type']) ? $_POST['status_type'] : '';
$status_value = isset($_POST['status_value']) ? $_POST['status_value'] : '';
$note = isset($_POST['note']) ? $_POST['note'] : '';

if(empty($payment_id) || empty($status_type) || empty($status_value)){
    header('Location: order.php');
    exit;
}

// Update main table if necessary
if(in_array($status_type, ['payment_status','shipping_status'])){
    try{
        $stmt = $pdo->prepare("UPDATE tbl_payment SET $status_type=? WHERE payment_id=?");
        $stmt->execute(array($status_value, $payment_id));
    }catch(Exception $e){
        // ignore
    }
}

// Add history
ot_add_history($pdo, $payment_id, $status_type, $status_value, $note, 'admin:'.$_SESSION['user']['id']);

header('Location: order.php');
exit;

?>
