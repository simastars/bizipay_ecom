<?php
require_once('header.php');

// Quick debug page: shows last 20 rows from tbl_customer_message or filter by cust_id
$custFilter = isset($_GET['cust_id']) ? (int)$_GET['cust_id'] : 0;
try {
    if ($custFilter > 0) {
        $stmt = $pdo->prepare("SELECT * FROM tbl_customer_message WHERE cust_id = ? ORDER BY customer_message_id DESC LIMIT 200");
        $stmt->execute([$custFilter]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbl_customer_message ORDER BY customer_message_id DESC LIMIT 20");
        $stmt->execute();
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo '<div class="content">Error: ' . htmlentities($e->getMessage()) . '</div>';
    require_once('footer.php');
    exit;
}
?>

<section class="content-header">
    <h1>Debug: Recent Customer Messages</h1>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>customer_message_id</th>
                        <th>subject</th>
                        <th>cust_id</th>
                        <th>message</th>
                        <th>order_detail</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $i => $r): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo htmlentities($r['customer_message_id']); ?></td>
                        <td><?php echo htmlentities($r['subject']); ?></td>
                        <td><?php echo htmlentities($r['cust_id']); ?></td>
                        <td><?php echo nl2br(htmlentities($r['message'])); ?></td>
                        <td><?php echo nl2br(htmlentities($r['order_detail'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once('footer.php');
