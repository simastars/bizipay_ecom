<?php      
         // Wallet balance
$stmtWallet = $pdo->prepare("SELECT balance FROM tbl_wallet WHERE cust_id=?");
$stmtWallet->execute([$_SESSION['customer']['cust_id']]);
$wallet = $stmtWallet->fetch(PDO::FETCH_ASSOC);
$balance = $wallet ? $wallet['balance'] : 0;

// Recent orders (limit 5)
$stmtOrders = $pdo->prepare("SELECT * FROM tbl_payment WHERE customer_id = ? ORDER BY id DESC LIMIT 5");
$stmtOrders->execute([$_SESSION['customer']['cust_id']]);
$recentOrders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
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
           <div class="d-block d-md-none mb-2">
                    <button class="btn btn-outline-primary btn-block" type="button" data-toggle="collapse" data-target="#innovSidebar" aria-expanded="false">Menu</button>
                </div>
                <div id="innovSidebar" class="collapse d-md-block">
                    <div class="sidebar-innovative">
                        <div class="sidebar-profile">
                            <img src="assets/img/default-user.jpg" alt="Profile">
                            <div>
                                <div class="sidebar-name"><?php echo htmlentities($_SESSION['customer']['cust_name']); ?></div>
                                <div class="sidebar-email"><?php echo htmlentities($_SESSION['customer']['cust_email']); ?></div>
                            </div>
                        </div>

                        <div class="sidebar-stats">
                            <div class="stat-pill"><span class="fa fa-wallet"></span> <?php echo '₦' . number_format($balance,2); ?></div>
                            <div class="stat-pill"><span class="fa fa-box"></span> <?php echo count($recentOrders); ?> Orders</div>
                        </div>

                        <div class="nav-innovative">
                            <a href="dashboard.php"><span class="icon fa fa-tachometer"></span> Dashboard</a>
                            <a href="customer-profile-update.php"><span class="icon fa fa-user"></span> Profile</a>
                            <a href="customer-order.php"><span class="icon fa fa-shopping-cart"></span> Orders</a>
                            <a href="wallet-fund.php"><span class="icon fa fa-credit-card"></span> Fund Wallet</a>
                            <a href="customer-billing-shipping-update.php"><span class="icon fa fa-map-marker"></span> Shipping</a>
                            <a href="customer-password-update.php"><span class="icon fa fa-lock"></span> Change Password</a>
                            <a href="logout.php" class="text-danger"><span class="icon fa fa-sign-out"></span> Logout</a>
                        </div>
                    </div>
                </div>