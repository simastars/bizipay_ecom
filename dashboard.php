<?php require_once('header.php'); ?>

<?php
// Check if the customer is logged in or not
if(!isset($_SESSION['customer'])) {
    header('location: '.BASE_URL.'logout.php');
    exit;
} else {
    // If customer is logged in, but admin make him inactive, then force logout this user.
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute(array($_SESSION['customer']['cust_id'],0));
    $total = $statement->rowCount();
    if($total) {
        header('location: '.BASE_URL.'logout.php');
        exit;
    }
}
?>

<style>
/* dashboard styles */
.dashboard-card { border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06); padding:18px; background:#fff; }
.dashboard-hero { background: linear-gradient(90deg, #4e73df, #224abe); color:#fff; padding:18px; border-radius:8px; }
.metric { font-size:15px; color:#6c757d; }
.metric-value { font-size:22px; font-weight:700; }
.quick-action .btn { margin-bottom:8px; }
.recent-orders th, .recent-orders td { vertical-align:middle; }

/* New sidebar styles */
.sidebar-innovative { background: linear-gradient(180deg, #ffffff, #fbfbfb); border-radius:8px; padding:18px; box-shadow:0 1px 4px rgba(0,0,0,0.04); }
.sidebar-profile { display:flex; align-items:center; gap:12px; }
.sidebar-profile img { width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid #eee; }
.sidebar-name { font-weight:700; font-size:16px; }
.sidebar-email { font-size:13px; color:#777; }
.sidebar-stats { display:flex; gap:8px; margin-top:12px; }
.stat-pill { background:#f5f7ff; padding:8px 10px; border-radius:20px; font-size:13px; color:#333; display:flex; align-items:center; gap:8px; }
.nav-innovative { margin-top:14px; }
.nav-innovative a { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:6px; color:#333; text-decoration:none; }
.nav-innovative a:hover { background:#f2f6ff; text-decoration:none; }
.nav-innovative .icon { width:28px; text-align:center; color:#4e73df; }

@media (max-width:767px) {
    .sidebar-innovative { margin-bottom:12px; }
}
</style>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <?php include('customer-sidebar.php'); ?>
            </div>

            <div class="col-md-9">
                <div class="dashboard-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="dashboard-hero">
                                <h3 style="margin:0;">Welcome back, <?php echo htmlentities($_SESSION['customer']['cust_name']); ?>!</h3>
                                <p style="margin:6px 0 0;opacity:0.9;">Here is a quick summary of your account.</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div style="font-size:12px;color:#888;">Customer</div>
                            <div style="font-weight:700"><?php echo date('M d, Y'); ?></div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:18px;">
                        <div class="col-md-4">
                            <div class="dashboard-card text-center">
                                <div class="metric">Wallet Balance</div>
                                <div class="metric-value"><?php echo '₦' . number_format($balance,2); ?></div>
                                <div style="margin-top:10px;"><a href="wallet-fund.php" class="btn btn-sm btn-success">Fund Wallet</a></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="dashboard-card text-center">
                                <div class="metric">Recent Orders</div>
                                <div class="metric-value"><?php echo count($recentOrders); ?></div>
                                <div style="margin-top:10px;"><a href="customer-order.php" class="btn btn-sm btn-primary">View Orders</a></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="dashboard-card text-center">
                                <div class="metric">Profile</div>
                                <div class="metric-value">Details</div>
                                <div style="margin-top:10px;"><a href="customer-profile-update.php" class="btn btn-sm btn-outline-secondary">Edit Profile</a></div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:20px;">
                        <div class="col-md-8">
                            <div class="dashboard-card">
                                <h4 style="margin-top:0;">Recent Orders</h4>
                                <div class="table-responsive">
                                <table class="table table-striped recent-orders">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($recentOrders)): ?>
                                            <tr><td colspan="4">No recent orders</td></tr>
                                        <?php else: ?>
                                            <?php foreach($recentOrders as $o): ?>
                                                <tr>
                                                    <td><?php echo htmlentities($o['payment_id']); ?></td>
                                                    <td><?php echo htmlentities($o['payment_date']); ?></td>
                                                    <td><?php echo '₦' . number_format($o['paid_amount'],2); ?></td>
                                                    <td><?php echo htmlentities($o['payment_status']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="dashboard-card">
                                <h4 style="margin-top:0;">Quick Actions</h4>
                                <div class="quick-action">
                                    <a href="customer-profile-update.php" class="btn btn-block btn-outline-secondary">Edit Profile</a>
                                    <a href="customer-order.php" class="btn btn-block btn-primary">My Orders</a>
                                    <a href="wallet-fund.php" class="btn btn-block btn-success">Fund Wallet</a>
                                    <a href="customer-password-update.php" class="btn btn-block btn-warning">Change Password</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>