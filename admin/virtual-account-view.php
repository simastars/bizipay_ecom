<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Virtual Account Details</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-md-8">
		<div class="box box-primary">
			<div class="box-body">
				<?php
				if(!isset($_GET['id'])){ echo 'Missing id'; exit; }
				$id = (int)$_GET['id'];
				$st = $pdo->prepare("SELECT * FROM tbl_virtual_account WHERE id = ?");
				$st->execute([$id]);
				$va = $st->fetch(PDO::FETCH_ASSOC);
				if(!$va){ echo 'Not found'; exit; }
				$meta = json_decode($va['meta'] ?? '{}', true);
				?>
				<table class="table table-bordered">
					<tr><th>ID</th><td><?php echo $va['id']; ?></td></tr>
					<tr><th>Customer</th><td><?php
				$stc = $pdo->prepare("SELECT cust_name, cust_email FROM tbl_customer WHERE cust_id = ?");
				$stc->execute([$va['cust_id']]);
				$cc = $stc->fetch(PDO::FETCH_ASSOC);
				echo $cc ? htmlentities($cc['cust_name']).' &lt;'.htmlentities($cc['cust_email']).'&gt;' : 'N/A';
				?></td></tr>
					<tr><th>Account Number</th><td><?php echo htmlentities($va['account_number']); ?></td></tr>
					<tr><th>Account Name</th><td><?php echo htmlentities($va['account_name']); ?></td></tr>
					<tr><th>Bank</th><td><?php echo htmlentities($va['bank_name']); ?></td></tr>
					<tr><th>Reference</th><td><?php echo htmlentities($va['reference']); ?></td></tr>
					<tr><th>Status</th><td><?php echo htmlentities($va['status']); ?></td></tr>
					<tr><th>Meta</th><td><pre><?php echo json_encode($meta, JSON_PRETTY_PRINT); ?></pre></td></tr>
				</table>

				<h4>Transactions</h4>
				<?php
				$stt = $pdo->prepare("SELECT * FROM tbl_virtual_account_tx WHERE virtual_account_id = ? ORDER BY id DESC");
				$stt->execute([$va['id']]);
				$txs = $stt->fetchAll(PDO::FETCH_ASSOC);
				if($txs){
					echo '<table class="table table-striped"><tr><th>ID</th><th>Provider Ref</th><th>Amount</th><th>Status</th><th>Date</th></tr>';
					foreach($txs as $t){
						echo '<tr><td>'.$t['id'].'</td><td>'.htmlentities($t['provider_ref']).'</td><td>'.htmlentities($t['amount']).'</td><td>'.htmlentities($t['status']).'</td><td>'.htmlentities($t['created_at']).'</td></tr>';
					}
					echo '</table>';
				} else {
					echo 'No transactions yet';
				}
				?>

				<p><a href="virtual-accounts.php" class="btn btn-default">Back</a></p>
			</div>
		</div>
	</div>
</div>
</section>

<?php require_once('footer.php'); ?>
