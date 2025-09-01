<?php require_once('header.php'); ?>
<?php require_once __DIR__ . '/inc/order-tracking.php'; ?>

<?php
// If user submitted a payment id to view
$payment_id = isset($_GET['payment_id']) ? $_GET['payment_id'] : (isset($_POST['payment_id']) ? $_POST['payment_id'] : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && !empty($_POST['payment_id'])) {
    // store a simple customer message referencing the payment
    try {
        $payment_id_post = trim($_POST['payment_id']);
        $message_post = trim($_POST['message']);

        // Lookup customer_id for the given payment_id
        $stmt = $pdo->prepare("SELECT customer_id FROM tbl_payment WHERE payment_id = ? LIMIT 1");
        $stmt->execute([$payment_id_post]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        $cust_id = $p ? (int)$p['customer_id'] : null;

        if (!$cust_id) {
            error_log("[track-order] No customer found for payment_id=" . $payment_id_post);
        } else {
            $subject = 'Order Query: ' . $payment_id_post;
            $order_detail = 'Customer query regarding payment id: ' . $payment_id_post;

            $stmtIns = $pdo->prepare("INSERT INTO tbl_customer_message (subject, message, order_detail, cust_id) VALUES (?, ?, ?, ?)");
            $ok = $stmtIns->execute([$subject, strip_tags($message_post), $order_detail, $cust_id]);

            if ($ok) {
                // try to get last insert id when available
                $lastId = null;
                try { $lastId = $pdo->lastInsertId(); } catch (Exception $e) { /* ignore */ }
                error_log("[track-order] Inserted customer message id=" . var_export($lastId, true) . " for cust_id=" . $cust_id . " payment_id=" . $payment_id_post);
                $success_message = 'Your message was submitted. Admin will contact you.';
            } else {
                $err = $stmtIns->errorInfo();
                error_log("[track-order] Failed to insert customer message for cust_id={$cust_id}, payment_id={$payment_id_post}. SQLSTATE: " . var_export($err, true));
            }
        }

    } catch (Exception $ex) {
        error_log('[track-order] Exception while saving customer message: ' . $ex->getMessage());
    }
}

?>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php require_once('customer-sidebar.php'); ?>
            </div>
            <div class="col-md-12">
                <div class="user-content">
                    <h3>Track Order</h3>

                    <form method="get" class="form-inline" style="margin-bottom:12px;">
                        <div class="form-group">
                            <label>Payment ID</label>
                            <input type="text" name="payment_id" class="form-control" value="<?php echo htmlentities($payment_id); ?>" required>
                        </div>
                        <button class="btn btn-primary" type="submit">View</button>
                    </form>

                    <?php if(!empty($payment_id)){
                        $history = ot_get_history($pdo, $payment_id);
                        if(empty($history)){
                            echo '<p>No tracking history found for this order.</p>';
                        } else {
                            echo '<ul class="list-group">';
                            foreach($history as $h){
                                echo '<li class="list-group-item">';
                                echo '<strong>'.htmlentities($h['status_type']).'</strong>: '.htmlentities($h['status_value']);
                                if(!empty($h['note'])) echo '<br><small>'.htmlentities($h['note']).'</small>';
                                echo '<br><small class="text-muted">By '.htmlentities($h['changed_by']).' @ '.htmlentities($h['created_at']).'</small>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                        ?>

                        <hr>
                        <h4>Send a message about this order</h4>
                        <?php if(!empty($success_message)) echo '<div class="alert alert-success">'.htmlentities($success_message).'</div>'; ?>
                        <form method="post">
                            <input type="hidden" name="payment_id" value="<?php echo htmlentities($payment_id); ?>">
                            <div class="form-group">
                                <textarea name="message" class="form-control" rows="4" required></textarea>
                            </div>
                            <button class="btn btn-primary" type="submit">Submit</button>
                        </form>

                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
