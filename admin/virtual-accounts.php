<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Virtual Accounts</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-body">
				<?php
				// Pagination
				$limit = 25;
				$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
				$start = ($page-1)*$limit;
				
				// Search
				$where = '';
				$params = [];
				if(!empty($_GET['q'])){
					$where = "WHERE account_number LIKE ? OR account_name LIKE ? OR bank_name LIKE ?";
					$q = '%'.$_GET['q'].'%';
					$params = [$q,$q,$q];
				}
				
				$stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_virtual_account $where ORDER BY id DESC LIMIT $start, $limit");
				$stmt->execute($params);
				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$total = $pdo->query("SELECT FOUND_ROWS() AS total")->fetch(PDO::FETCH_ASSOC)['total'];
				?>
				<form method="get" class="form-inline" style="margin-bottom:12px;">
					<div class="form-group">
						<input type="text" name="q" class="form-control" placeholder="Search account, name or bank" value="<?php echo htmlentities($_GET['q'] ?? ''); ?>">
					</div>
					<button class="btn btn-default">Search</button>
				</form>

				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Customer</th>
							<th>Account Number</th>
							<th>Account Name</th>
							<th>Bank</th>
							<th>Status</th>
							<th>Reserved At</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if($rows): foreach($rows as $r): ?>
						<tr>
							<td><?php echo $r['id']; ?></td>
							<td><?php
							$st = $pdo->prepare("SELECT cust_name, cust_email FROM tbl_customer WHERE cust_id = ?");
							$st->execute([$r['cust_id']]);
							$c = $st->fetch(PDO::FETCH_ASSOC);
							echo $c ? htmlentities($c['cust_name']).'<br><small>'.htmlentities($c['cust_email']).'</small>' : 'N/A';
							?></td>
							<td><?php echo htmlentities($r['account_number']); ?></td>
							<td><?php echo htmlentities($r['account_name']); ?></td>
							<td><?php echo htmlentities($r['bank_name']); ?></td>
							<td><?php echo htmlentities($r['status']); ?></td>
							<td><?php echo htmlentities($r['reserved_at']); ?></td>
							<td>
								<a href="virtual-account-view.php?id=<?php echo $r['id']; ?>" class="btn btn-xs btn-info">View</a>
								<a href="virtual-account-close.php?id=<?php echo $r['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Close this VA?');">Close</a>
							</td>
						</tr>
						<?php endforeach; else: ?>
						<tr><td colspan="8">No virtual accounts found</td></tr>
						<?php endif; ?>
					</tbody>
				</table>

				<?php
				// simple pagination
				$pages = ceil($total / $limit);
				for($p=1;$p<=$pages;$p++){
					echo '<a href="?page='.$p.'" class="btn btn-sm btn-default" style="margin-right:4px;">'.$p.'</a>';
				}
				?>
			</div>
		</div>
	</div>
</div>
</section>

<?php require_once('footer.php'); ?>
