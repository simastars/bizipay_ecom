<?php
include("admin/inc/config.php");

$type = isset($_GET['type']) ? $_GET['type'] : '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;

if ($type == 'featured') {
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_featured=? AND p_is_active=? ORDER BY p_id DESC LIMIT $offset, $limit");
    $statement->execute(array(1,1));
} elseif ($type == 'latest') {
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_active=? ORDER BY p_id DESC LIMIT $offset, $limit");
    $statement->execute(array(1));
} elseif ($type == 'popular') {
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_active=? ORDER BY p_total_view DESC LIMIT $offset, $limit");
    $statement->execute(array(1));
} else {
    echo '';
    exit;
}

$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$html = '';
foreach ($result as $row) {
    ob_start();
    ?>
    <div class="item" data-pid="<?php echo htmlspecialchars($row['p_id'], ENT_QUOTES); ?>">
        <div class="thumb">
            <div class="photo" style="background-image:url(assets/uploads/<?php echo htmlspecialchars($row['p_featured_photo'], ENT_QUOTES); ?>);"></div>
            <div class="overlay"></div>
        </div>
        <div class="text">
            <h3><a href="product.php?id=<?php echo $row['p_id']; ?>"><?php echo htmlspecialchars($row['p_name'], ENT_QUOTES); ?></a></h3>
            <h4>
                $<?php echo htmlspecialchars($row['p_current_price'], ENT_QUOTES); ?>
                <?php if($row['p_old_price'] != ''): ?>
                <del>
                    $<?php echo htmlspecialchars($row['p_old_price'], ENT_QUOTES); ?>
                </del>
                <?php endif; ?>
            </h4>
            <?php if($row['p_qty'] == 0): ?>
                <div class="out-of-stock">
                    <div class="inner">
                        Out Of Stock
                    </div>
                </div>
            <?php else: ?>
                <p><a href="product.php?id=<?php echo $row['p_id']; ?>"><i class="fa fa-shopping-cart"></i> Add to Cart</a></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    $html .= ob_get_clean();
}

echo $html;
