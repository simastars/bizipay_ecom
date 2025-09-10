<?php
// Quick diagnostic: list last 10 customers and CSRF token/session info
require_once(__DIR__ . '/../admin/inc/config.php');
require_once(__DIR__ . '/../header.php');

$out = [];
try {
    $stmt = $pdo->prepare("SELECT cust_id, cust_name, cust_email, cust_status, cust_datetime FROM tbl_customer ORDER BY cust_id DESC LIMIT 10");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out['recent_customers'] = $rows;
} catch (Exception $e) {
    $out['db_error'] = $e->getMessage();
}

// session and CSRF
session_start();
$out['session_id'] = session_id();
$out['session_keys'] = array_keys($_SESSION);
$out['csrf_token'] = isset($_SESSION['_csrf']) ? $_SESSION['_csrf'] : null;

header('Content-Type: application/json');
echo json_encode($out, JSON_PRETTY_PRINT);
?>
