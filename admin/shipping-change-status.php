<?php require_once('header.php'); ?>

<?php
if( !isset($_REQUEST['id']) || !isset($_REQUEST['task']) ) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php
	$statement = $pdo->prepare("SELECT payment_id, customer_id FROM tbl_payment WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$paymentRow = $statement->fetch(PDO::FETCH_ASSOC);

	$statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status=? WHERE id=?");
	$statement->execute(array($_REQUEST['task'],$_REQUEST['id']));

	// record history
	require_once __DIR__ . '/../inc/order-tracking.php';
	if(!empty($paymentRow)){
		ot_add_history($pdo, $paymentRow['payment_id'], 'shipping_status', $_REQUEST['task'], 'Changed via admin panel', 'admin:'.$_SESSION['user']['id']);
	}

	header('location: order.php');
?>