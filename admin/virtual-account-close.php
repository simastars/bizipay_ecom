<?php require_once('header.php'); ?>
<?php
if(!isset($_GET['id'])) { header('Location: virtual-accounts.php'); exit; }
$id = (int)$_GET['id'];
$st = $pdo->prepare("UPDATE tbl_virtual_account SET status = 'closed' WHERE id = ?");
$st->execute([$id]);
header('Location: virtual-accounts.php');
exit;
?>
