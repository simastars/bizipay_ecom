<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Dashboard</h1>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$total_top_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_mid_category");
$statement->execute();
$total_mid_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_end_category");
$statement->execute();
$total_end_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_product");
$statement->execute();
$total_product = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_status='1'");
$statement->execute();
$total_customers = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_subscriber WHERE subs_active='1'");
$statement->execute();
$total_subscriber = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_shipping_cost");
$statement->execute();
$available_shipping = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
$statement->execute(array('Completed'));
$total_order_completed = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE shipping_status=?");
$statement->execute(array('Completed'));
$total_shipping_completed = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
$statement->execute(array('Pending'));
$total_order_pending = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=? AND shipping_status=?");
$statement->execute(array('Completed','Pending'));
$total_order_complete_shipping_pending = $statement->rowCount();
?>

<section class="content">
	<style>
		.dashboard-card { background:#fff;border-radius:8px;padding:18px;margin-bottom:20px;box-shadow:0 6px 18px rgba(0,0,0,0.06); }
		.stat-title { color:#666;font-size:13px }
		.stat-value { font-size:28px;font-weight:700;color:#222 }
		.quick-action .btn { margin:6px 6px 6px 0 }
		.small-muted { color:#8a8a8a;font-size:13px }
	</style>

	<div class="row">
		<div class="col-md-8">
			<div class="dashboard-card">
				<div class="row">
					<div class="col-sm-3 text-center">
						<div class="stat-title">Products</div>
						<div class="stat-value"><?php echo $total_product; ?></div>
						<div class="small-muted"><i class="ionicons ion-android-cart"></i></div>
					</div>
					<div class="col-sm-3 text-center">
						<div class="stat-title">Pending Orders</div>
						<div class="stat-value"><?php echo $total_order_pending; ?></div>
						<div class="small-muted"><i class="ionicons ion-clipboard"></i></div>
					</div>
					<div class="col-sm-3 text-center">
						<div class="stat-title">Completed Orders</div>
						<div class="stat-value"><?php echo $total_order_completed; ?></div>
						<div class="small-muted"><i class="ionicons ion-android-checkbox-outline"></i></div>
					</div>
					<div class="col-sm-3 text-center">
						<div class="stat-title">Pending Shipments</div>
						<div class="stat-value"><?php echo $total_order_complete_shipping_pending; ?></div>
						<div class="small-muted"><i class="ionicons ion-load-a"></i></div>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-sm-6">
						<h4 style="margin-top:0">Recent Orders</h4>
						<div class="list-group">
						<?php
						$st = $pdo->prepare("SELECT payment_id, customer_name, paid_amount, payment_method, payment_status FROM tbl_payment ORDER BY id DESC LIMIT 6");
						$st->execute();
						$orders = $st->fetchAll(PDO::FETCH_ASSOC);
						if($orders){
							foreach($orders as $o){
								echo '<a class="list-group-item" href="order.php?payment_id='.htmlentities($o['payment_id']).'">';
								echo '<strong>'.htmlentities($o['customer_name']).'</strong> &nbsp; <span class="small-muted">('.htmlentities($o['payment_method']).')</span>';
								echo '<div class="pull-right">'.htmlentities($o['payment_status']).' - '.htmlentities($o['paid_amount']).'</div>';
								echo '<br><small class="small-muted">#'.htmlentities($o['payment_id']).'</small>';
								echo '</a>';
							}
						} else {
							echo '<div class="small-muted">No recent orders</div>';
						}
						?>
						</div>
					</div>
					<div class="col-sm-6">
						<h4 style="margin-top:0">Recent Customer Messages</h4>
						<div class="list-group">
						<?php
						$stm = $pdo->prepare("SELECT customer_message_id, subject, message, cust_id FROM tbl_customer_message ORDER BY customer_message_id DESC LIMIT 6");
						$stm->execute();
						$msgs = $stm->fetchAll(PDO::FETCH_ASSOC);
						if($msgs){
							foreach($msgs as $m){
								echo '<a class="list-group-item" href="customer-message.php?cust_id='.urlencode($m['cust_id']).'">';
								echo '<strong>'.htmlentities($m['subject']).'</strong>';
								echo '<div class="pull-right">#'.htmlentities($m['customer_message_id']).'</div>';
								echo '<br><small class="small-muted">'.htmlentities(mb_strimwidth(strip_tags($m['message']),0,80,'...')).'</small>';
								echo '</a>';
							}
						} else {
							echo '<div class="small-muted">No recent messages</div>';
						}
						?>
						</div>
					</div>
				</div>
			</div>

			<div class="dashboard-card">
				<h4 style="margin-top:0">Quick Actions</h4>
				<p class="small-muted">Common admin tasks</p>
				<div class="quick-action">
					<a href="product.php" class="btn btn-default"><i class="fa fa-shopping-bag"></i> Products</a>
					<a href="order.php" class="btn btn-default"><i class="fa fa-sticky-note"></i> Orders</a>
					<a href="customer.php" class="btn btn-default"><i class="fa fa-user"></i> Customers</a>
					<a href="subscriber.php" class="btn btn-default"><i class="fa fa-envelope"></i> Subscribers</a>
					<a href="settings.php" class="btn btn-primary"><i class="fa fa-sliders"></i> Settings</a>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="dashboard-card">
				<h4 style="margin-top:0">Site Snapshot</h4>
				<table class="table table-borderless">
					<tr><td class="small-muted">Active Customers</td><td class="text-right"><strong><?php echo $total_customers; ?></strong></td></tr>
					<tr><td class="small-muted">Subscribers</td><td class="text-right"><strong><?php echo $total_subscriber; ?></strong></td></tr>
					<tr><td class="small-muted">Available Shipping</td><td class="text-right"><strong><?php echo $available_shipping; ?></strong></td></tr>
					<tr><td class="small-muted">Top Categories</td><td class="text-right"><strong><?php echo $total_top_category; ?></strong></td></tr>
					<tr><td class="small-muted">Mid Categories</td><td class="text-right"><strong><?php echo $total_mid_category; ?></strong></td></tr>
					<tr><td class="small-muted">End Categories</td><td class="text-right"><strong><?php echo $total_end_category; ?></strong></td></tr>
				</table>
				<hr>
				<h5>Alerts</h5>
				<?php
				// Simple alerts: pending orders count
				if($total_order_pending>0){
					echo '<div class="alert alert-warning">You have <strong>'.intval($total_order_pending).'</strong> pending orders.</div>';
				} else {
					echo '<div class="alert alert-success">No pending orders.</div>';
				}
				?>
			</div>
		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>